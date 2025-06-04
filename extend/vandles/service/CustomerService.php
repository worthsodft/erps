<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\Model;
use vandles\lib\FileLock;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CartModel;
use vandles\model\CouponTplModel;
use vandles\model\CustomerModel;
use vandles\model\SupplierModel;
use vandles\model\UserAddressModel;
use vandles\model\WaterStationModel;

class CustomerService extends BaseService {
    protected static $instance;


    public static function instance(): CustomerService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|CustomerModel
     */
    public function getModel() {
        return CustomerModel::mk();
    }

    public function mQuery(): QueryHelper {
        return CustomerModel::mQuery();
    }

    public function getCustomerByGid($gid, string $field = "*") {
        return $this->getModel()->where(['status' => 1])->where(['gid' => $gid])->field($field)->find();
    }

    public function getListOptsByGids(array $gids = null) {
        $query = $this->getModel()->order("sort desc, id desc");
        if (!empty($gids)) $query->whereIn('gid', $gids);
        return $query->column('title', 'gid');
    }


    /**
     * 减少余额
     * @param $customer_gid
     * @param $delta
     * @param $log_type string 记录类型 ,order:订单消费
     * @param $target_gid string 变动对象gid，log_type为order时，取订单编号，recharge时，取预付款单号
     * @return true
     * @throws \Exception
     */
    public function reduceMoney($customer_gid, $delta, string $log_type, string $target_gid) {
        if ($delta <= 0) VException::runtime("金额必须大于0");

        $lock = FileLock::instance("company_money_" . $customer_gid);
        if (!$lock->lock(true)) VException::runtime("系统繁忙，请稍后再试");

        Db::startTrans();
        try {
            $buyer = $this->getModel()->where("gid", $customer_gid)->lock(true)->find();
            if (empty($buyer)) VException::runtime("企业客户不存在");
            if ($buyer['money'] < $delta) VException::runtime("企业客户余额不足");

            // 1. 减少余额
            $before       = $buyer->money;
            $buyer->money = round($buyer->money - $delta, 2);
            $buyer->save();

            // 2. 增加余额记录
            $data = BuyerMoneyLogService::instance()->create([
                'gid'          => uuid(),
                'buyer_gid'    => $customer_gid,
                'before'       => $before,
                'delta'        => -$delta,
                'log_type'     => $log_type,
                'target_gid'   => $target_gid,
                'buyer_type'   => 'company',
            ]);

            // 3. 增加系统操作日志
            $suid = session("user.id");
            sysoplog("企业客户余额变动", "企业客户:{$customer_gid},原金额：{$before},余额减少:-{$delta},操作人:{$suid},余额记录gid:{$data->gid}");
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }

    /**
     * 增加余额
     * @param $customer_gid
     * @param $delta
     * @param $log_type string 记录类型,recharge:预付款增加
     * @param $target_gid string 变动对象gid，log_type为order时，取订单编号，recharge时，取预付款单号
     * @return true
     * @throws \Exception
     */
    public function incMoney($customer_gid, $delta, string $log_type, string $target_gid) {
        if ($delta <= 0) VException::runtime("金额必须大于0");

        $lock = FileLock::instance("money_" . $customer_gid);
        if (!$lock->lock(true)) VException::runtime("系统繁忙，请稍后再试");

        Db::startTrans();
        try {
            // 1. 增加余额
            $customer = $this->getModel()->where("gid", $customer_gid)->lock(true)->find();
            if (empty($customer)) VException::runtime("企业客户不存在");

            $before          = $customer->money;
            $customer->money = round($customer->money + $delta, 2);
            $customer->save();

            // 2. 增加余额记录
            $data = BuyerMoneyLogService::instance()->create([
                'gid'          => uuid(),
                'buyer_gid'    => $customer_gid,
                'before'       => $before,
                'delta'        => $delta,
                'log_type'     => $log_type,
                'target_gid'   => $target_gid,
                'buyer_type'   => 'company',
            ]);

            // 3. 增加系统操作日志
            $suid = session("user.id");
            sysoplog("企业客户余额变动", "企业客户:{$customer_gid},原金额：{$before},余额增加:{$delta},操作人:{$suid},余额记录gid:{$data->gid}");
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }


    /**
     * 冻结
     *
     * ① 减余额
     * ② 增加冻结记录
     * ③ 增加系统操作日志
     *
     * $freezeData = [
     *     'money'        => $main['amount'],
     *     'customer_gid' => $main['customer_gid'],
     *     'out_stock_sn' => $sn,
     * ];
     *
     * @param array $freezeData
     * @return boolean
     * @throws \Exception
     */
    public function freezeMoney(array $freezeData) {
        $log_type     = "freeze";
        $customer_gid = $freezeData['buyer_gid'];
        $target_gid   = $freezeData['out_stock_sn'];
        $delta        = $freezeData['money'];
        if ($delta <= 0) VException::runtime("冻结金额必须大于0");

        $lock = FileLock::instance("money_" . $freezeData['customer_gid']);
        if (!$lock->lock(true)) VException::runtime("系统繁忙，请稍后再试");

        Db::startTrans();
        try {
            $customer = $this->getModel()->where("gid", $customer_gid)->lock(true)->find();
            if ($customer['money'] < $delta) VException::runtime("企业客户余额不足");

            // 1. 减少余额
            $before          = $customer->money;
            $customer->money = round($customer->money - $delta, 2);
            $customer->save();

            // 2. 增加余额记录
            $data = BuyerMoneyLogService::instance()->create([
                'gid'          => uuid(),
                'buyer_gid'    => $customer_gid,
                'before'       => $before,
                'delta'        => -$delta,
                'log_type'     => $log_type,
                'target_gid'   => $target_gid,
                'buyer_type'   => 'company',
            ]);

            // 3. 增加系统操作日志
            $suid = session("user.id");
            sysoplog("企业客户余额变动", "企业客户:{$customer_gid},原金额：{$before},余额冻结:{$delta},操作人:{$suid},余额记录gid:{$data->gid}");
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }

    /**
     * 解冻
     * @param $customer_gid
     * @param $target_gid
     * @return boolean
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function unfreezeMoney($customer_gid, $target_gid) {
        $logService = BuyerMoneyLogService::instance();
        $log        = $logService->getModel()->where([
            "buyer_gid"    => $customer_gid,
            "target_gid"   => $target_gid,
            "log_type"     => "freeze",
            "buyer_type"   => "company",
        ])->find();
        if (!$log) return false;

        $lock = FileLock::instance("money_" . $customer_gid);
        if (!$lock->lock(true)) VException::runtime("系统繁忙，请稍后再试");
        Db::startTrans();
        try {
            // 1. 增加余额
            $customer        = $this->getModel()->where("gid", $customer_gid)->lock(true)->find();
            $before          = $customer->money;
            $customer->money = round($customer->money + abs($log->delta), 2);
            $customer->save();

            // 2. 删除冻结记录
            $logService->softDelByBuyerGidAndTargetGid($customer_gid, $target_gid, "freeze", "company");

            // 3. 增加系统操作日志
            $suid = session("user.id");
            sysoplog("企业客户余额变动", "企业客户:{$customer_gid},原金额：{$before},余额解冻:{$log->delta},操作人:{$suid},余额记录gid:{$log->gid}");
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }

    /**
     * 冻结转支付
     * @param $log_gid
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function freezeToPay($log_gid) {
        $logService = BuyerMoneyLogService::instance();
        $log        = $logService->getModel()->where("gid", $log_gid)->find();
        if (!$log) VException::runtime("冻结记录不存在");
        $customer_gid = $log['buyer_gid'];
        $target_gid   = $log['target_gid'];
        $delta        = abs($log['delta']);
        $buyer_type   = $log['buyer_type'];

        $lock = FileLock::instance("money_" . $customer_gid);
        if (!$lock->lock(true)) VException::runtime("系统繁忙，请稍后再试");
        Db::startTrans();
        try {

            // 1. 删除冻结记录
            $logService->softDelByBuyerGidAndTargetGid($customer_gid, $target_gid, "freeze", $buyer_type);

            // 2. 增加余额记录
            $customer        = $this->getModel()->where("gid", $customer_gid)->lock(true)->find();
            $before          = round($customer->money + abs($log->delta), 2);
            $data = BuyerMoneyLogService::instance()->create([
                'gid'          => uuid(),
                'buyer_gid'    => $customer_gid,
                'before'       => $before,
                'delta'        => -$delta,
                'log_type'     => "transfer",
                'target_gid'   => $target_gid,
            ]);

            // 3. 增加系统操作日志
            $suid = session("user.id");
            sysoplog("企业客户余额冻结转支付", "企业客户:{$customer_gid},原金额：{$before},冻结转支付:{$delta},操作人:{$suid},余额记录gid:{$data->gid}");
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }


    /**
     * 获取我的冻结金额
     * @param $customer_gid
     * @return float
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getMyFreezeMoney($customer_gid) {
        $money = BuyerMoneyLogService::instance()->getModel()->where("buyer_gid", $customer_gid)
            ->where("log_type", "freeze")
            ->where("buyer_type", "company")
            ->where("status", 1)
            ->sum("delta");
        return abs($money);
    }

    public function isExistTaxno($sn, $id) {
        $query = $this->getModel()->where("taxno", $sn);
        if ($id) $query->whereRaw("id <> $id");
        $count = $query->count();
        return $count > 0;
    }

    public function getListByGids(array $gids=[], string $field="*", string $asKey="") {
        $query = $this->getModel()->order("sort desc, id desc");
        if($gids) $query->whereIn("gid", $gids);
        if($field) $query->field($field);
        $list = $query->select();

        if(empty($asKey) || empty($list)) return $list;
        else return array_column($list->toArray(), null, $asKey);
    }

    public function getCompanyOrPartnerByGidAndFrom($gid, $from, $field="*") {
        if(empty($gid) || empty($from)) return null;
        if($from == 'company') $query = CustomerService::instance()->getModel()->where('gid', $gid);
        elseif($from == 'partner') $query = WaterStationService::instance()->getModel()->where('gid', $gid);
        else return null;
        return $query->field($field)->find();
    }

    public function getCompanyOrPartnerOptsByList($list=[]) {
        $companyGids = $partnerGids = [];
        if(!empty($list)) {
            foreach ($list as $v) {
                if (empty($v['buyer_gid'])) continue;
                if ($v['from'] == 'company') $companyGids[] = $v['buyer_gid'];
                elseif ($v['from'] == 'partner') $partnerGids[] = $v['buyer_gid'];
            }
            $companyGids = array_unique($companyGids);
            $partnerGids = array_unique($partnerGids);
        }
        $companyList = CustomerService::instance()->getListOptsByGids($companyGids);
        $partnerList = WaterStationService::instance()->getListOptsByGids($partnerGids);
        return [$companyList, $partnerList];
    }

}