<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\helper\Str;
use think\Model;
use vandles\lib\JwtUtil;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\UserInfoModel;

/**
 * Class UserInfoService
 * @package vandles\service
 *
 */
class UserInfoService extends BaseService {
    protected static $instance;
    public static $AUTH_FINISH_ORDER = 0b1000000000; // 订单核销权限
    public static $AUTH_DELIVER_ORDER = 0b0100000000; // 订单配送权限


    public static function instance(): UserInfoService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $data = [
            [
                "openid" => Str::random(32),
            ], [
                "openid" => Str::random(32),
            ]
        ];

        $this->getModel()->saveAll($data);
        $list = $this->getModel()->select();
        dd($list->toArray());
    }

    /**
     * @param $openid
     * @param null $field
     * @return array|mixed|Model|BaseSoftDeleteModel|UserInfoModel|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getUserInfoByOpenid($openid, $field = null) {
        $query = $this->getModel()->where('openid', $openid);
        if ($field) $query->field($field);
        return $query->find();
    }

    public function getOneByOpenid($openid, $field = null) {
        $query = $this->getModel()->where("status", 1)->where('openid', $openid);
        if ($field) $query->field($field);
        return $query->find();
    }


    public function setToken($userInfo) {
        $expire = 7200;

        $openid = md5($userInfo['openid']);
        $time = md5(time());
        $token  = Str::random(32) . "{$time}{$openid}" . Str::random(32);
        cache($token, $userInfo, $expire);
        $expire_at     = time() + ($expire - 600); // 前台提前10分钟到期
        $expire_at_txt = date("Y-m-d H:i:s", $expire_at);
        $is_phone      = !empty($userInfo['phone']) ? true : false;
        return compact('token', 'userInfo', 'expire_at', 'expire_at_txt', 'is_phone');
    }

    public function genJWTToken($userInfo, $expire = 7200) {
        if(empty($userInfo)) return null;
        if(!is_array($userInfo)) $userInfo = $userInfo->toArray();
        $payload = [
            // 'id'     => $userInfo['id'],
            // 'status' => $userInfo['status']
            'openid' => $userInfo['openid'],
        ];
        $token = JwtUtil::instance()->encode($payload, $expire);

        $expire_at     = time() + ($expire - 600); // 前台提前10分钟到期
        $expire_at_txt = date("Y-m-d H:i:s", $expire_at);
        $is_phone      = !empty($userInfo['phone']) ? true : false;
        return compact('token', 'userInfo', 'expire_at', 'expire_at_txt', 'is_phone');
    }

    public function parseJWTToken($token) {
        try{
            $payload = JwtUtil::instance()->decode($token);
        }catch (\Exception $e){
            return null;
        }
        // $userInfo = json_encode($userInfo);
        // $userInfo = json_decode($userInfo, true);

        $userInfo = $this->getUserInfoByOpenid($payload->openid, "id,openid");
        if(!$userInfo) return null;
        $userInfo = $userInfo->toArray();

        $userInfo['iat_txt'] = date("Y-m-d H:i:s", $payload->iat);
        $userInfo['exp_txt'] = date("Y-m-d H:i:s", $payload->exp);
        $userInfo['nbf_txt'] = date("Y-m-d H:i:s", $payload->nbf);

        // $arr = explode('.', $token);
        // dd(base64_decode($arr[1]));

        return $userInfo;
    }

    /**
     * @return BaseSoftDeleteModel|UserInfoModel
     */
    public function getModel() {
        return UserInfoModel::mk();
    }

    public function getUserInfoByUsername($username, $field = null) {
        $query = $this->search(['username' => $username]);
        if ($field) $query->field($field);
        return $query->find();
    }

    /**
     * 是否是新用户
     * @param $openid
     * @return bool
     */
    public function isNew($openid) {
        $paidOrderCount = OrderService::instance()->getPaidOrderCount($openid);
        return $paidOrderCount == 0;
    }

    public function updateByOpenid($openid, array $userData) {
        return $this->getModel()->where("openid", $openid)->update($userData);
    }

    /**
     * 充值成功后的操作
     * @param string $wayGid 充值方式gid
     * @param string $targetGid 记录目标gid($out_trade_no)
     * @param string $openid 用户openid
     * @param string $transactionId 支付(微信)侧交易号
     */
    public function rechargeSuccess($wayGid, $targetGid, $openid, $transactionId) {
        $way = RechargeWayService::instance()->getByGid($wayGid);
        if (empty($way)) VException::runtime("充值方式不存在");
        if ($way->status != 1) VException::runtime("充值方式已关闭");

        Db::startTrans();
        try {
            // 增加余额
            if (!empty($way->money)) {
                $this->incMoney($openid, $way->money, UserMoneyLogService::MONEY_LOG_TYPE_RECHARGE, $targetGid, $transactionId, "充值成功", $way->gid);
            }
            // 增加赠送金额
            if (!empty($way->give_money)) {
                $this->incMoney($openid, $way->give_money, UserMoneyLogService::MONEY_LOG_TYPE_GIVE, $targetGid, $transactionId, "赠送余额", $way->gid);
            }
            // 更新余额有效期：当前时间 + 365天
            // $expire_at = date("Y-m-d H:i:s", time() + config("a.money_expire_add_sec"));
            // $this->getModel()->where("openid", $openid)->update([
            //     'money_expire_at' => $expire_at
            // ]);

            // 增加金额水卡记录
            $realMoney  = $way->money;
            $giveMoney  = $way->give_money ?: 0;
            $totalMoney = bcadd($realMoney, $giveMoney, 2);
            $realRate   = bcdiv($realMoney, $totalMoney, 7);
            $giveRate   = bcsub(1, $realRate, 7);
            MoneyCardService::instance()->create([
                "gid"        => guid(),
                "openid"     => $openid,
                "real_init"  => $realMoney,
                "give_init"  => $giveMoney,
                "total_init" => $totalMoney,
                "expire_at"  => null,
                "real_rate"  => $realRate,
                "give_rate"  => $giveRate,
                "real_has"   => $realMoney,
                "give_has"   => $giveMoney,
                "total_has"  => $totalMoney,
                "trans_id"   => $transactionId,
                "way_gid"    => $way->gid,
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            VException::runtime("充值成功后增加余额失败: ".$e->getMessage());
        }
        return true;
    }

    /**
     * 减少余额
     * @param string $openid 用户openid
     * @param float $delta 减少金额
     * @param string $logType 记录类型
     * @param string $targetGid 记录目标gid
     * @param string $transactionId 支付(微信)侧交易号
     * @param string $remark 备注
     * @return true
     */
    public function reduceMoney($openid, $delta, $logType, $targetGid = "", $transactionId = "", $remark = "") {
        Db::transaction(function () use ($openid, $delta, $logType, $targetGid, $transactionId, $remark) {
            $userInfo = $this->getModel()->where("openid", $openid)->lock(true)->find();
            if ($userInfo['money'] < $delta) VException::runtime("用户余额不足");

            // 1. 减少余额
            $before          = $userInfo->money;
            $userInfo->money = bcsub($userInfo->money, $delta, 2);
            $userInfo->save();

            // 2. 增加余额记录
            $data = UserMoneyLogService::instance()->create([
                'gid'            => uuid(),
                'openid'         => $openid,
                'before'         => $before,
                'delta'          => -$delta,
                'log_type'       => $logType,
                'target_gid'     => $targetGid,
                'transaction_id' => $transactionId,
                'remark'         => $remark,
            ]);
            $suid = session("user.id");
            sysoplog("用户余额变动", "用户:{$openid},余额减少:-{$delta},操作人:{$suid},余额记录gid:{$data->gid}");
        });
        return true;
    }

    /**
     * 增加余额
     * @param string $openid 用户openid
     * @param float $delta 增加金额
     * @param string $logType 记录类型
     * @param string $targetGid 记录目标gid
     * @param string $transactionId 支付(微信)侧交易号
     * @param string $remark 备注
     * @return true
     */
    public function incMoney($openid, $delta, $logType, $targetGid = "", $transactionId = "", $remark = "", $wayGid = "") {
        Db::startTrans();
        try{
            // 1. 增加余额
            $userInfo        = $this->getModel()->where("openid", $openid)->lock(true)->find();
            $before          = $userInfo->money;
            $userInfo->money = bcadd($userInfo->money, $delta, 2);
            $userInfo->save();

            // 2. 增加余额记录
            $data = UserMoneyLogService::instance()->create([
                'gid'              => uuid(),
                'openid'           => $openid,
                'before'           => $before,
                'delta'            => $delta,
                'log_type'         => $logType,
                'recharge_way_gid' => $wayGid ?: null,
                'target_gid'       => $targetGid,
                'transaction_id'   => $transactionId,
                'remark'           => $remark,
            ]);
            $suid = session("user.id");
            sysoplog("用户余额变动", "用户:{$openid},余额增加:{$delta},操作人:{$suid},余额记录gid:{$data->gid}");
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }

    public function mQuery() {
        return $this->getModel()::mQuery();
    }

    public function getListByOpenids(array $openids, string $field = '*') {
        return $this->getModel()->whereIn('openid', $openids)
            ->field($field)
            ->select();
    }

    /**
     * 是否有核销订单权限
     * @param $openid
     * @return boolean
     */
    public function isAuthFinishOrder($openid) {
        $userAuth = $this->getModel()->where("openid", $openid)->value("auth");
        if (empty($userAuth)) return false;
        return (self::$AUTH_FINISH_ORDER & $userAuth) == self::$AUTH_FINISH_ORDER;
    }

    /**
     * 是否有配送订单权限
     * @param $openid
     * @return boolean
     */
    public function isAuthDeliverOrder($openid) {
        $userAuth = $this->getModel()->where("openid", $openid)->value("auth");
        if (empty($userAuth)) return false;
        return (self::$AUTH_DELIVER_ORDER & $userAuth) == self::$AUTH_DELIVER_ORDER;
    }

    /**
     * 返回权限字符串
     * @param $openid
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getAuthStrByOpenid($openid) {
        $userInfo = UserInfoService::instance()->getModel()->where("openid", $openid)->find();
        if (empty($userInfo)) VException::runtime("用户不存在");
        $userAuth = decbin($userInfo->auth);
        $auths    = str_pad($userAuth, 10, "0", STR_PAD_LEFT);
        return $auths;
    }

    public function getUserInfoListByPhones(array $phones, $fields = "*") {
        $query = $this->getModel()->field($fields)->whereIn("phone", $phones);
        return $query->select();
    }

    public function updateByPhone($phone, $data) {
        return $this->getModel()->where("phone", $phone)->update($data);
    }

    public function getUserInfoByPhone($phone) {
        return $this->getModel()->where("phone", $phone)->find();
    }

    public function checkMoney($openid, $pay_money) {
        $userInfo = UserInfoService::instance()->getOneByOpenid($openid, "openid, money, money_expire_at");
        if ($pay_money > $userInfo->money) VException::runtime("余额不足，请充值");
        // if (time() > strtotime($userInfo->money_expire_at)) VException::runtime("余额已过期，请充值");
    }

    public function getUserInfoByOpenids(array $openids, $field = "*", $asKey = "") {
        $query = $this->getModel()->whereIn("openid", $openids)->order("sort desc, id desc");

        if (!$asKey) return $query->field($field)->select();
        else return $query->column($field, $asKey);
    }

    public function getListByDistrict(string $district, string $field = "*") {
        $query = $this->getModel()->whereFindInSet("districts", $district);
        $query->field($field);
        return $query->select();
    }

    public function getDistrictsByOpenid($openid) {
        $res = $this->getModel()->where("openid", $openid)->value("districts");
        if (empty($res)) return [];
        $list = [];
        foreach (explode(",", $res) as $v) {
            $list[] = [
                'value' => $v,
                'text'  => $v,
            ];
        }
        return $list;
    }

//    /**
//     * 用户余额拆分为：实际充值金额 和 充值赠送金额
//     * @param string $openid
//     * @return array
//     */
//    public function moneySplitByOpenid(string $openid) {
//        $real_money = $give_money = 0;
//
//        return compact("real_money", "give_money");
//    }

    public function getUserInfoByUid($uid, string $field="*") {
        $query = $this->getModel()->where('id', $uid);
        if ($field) $query->field($field);
        return $query->find();
    }

    public function spreadCountInc($p_openid) {
        $this->getModel()->where('openid', $p_openid)->inc('spread_count')->update();
    }

    public function getListOptsByOpenids(array $openids) {
        $query = $this->getModel()->whereIn('openid', $openids);
        return $query->column('nickname', 'openid');
    }
}