<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\helper\QueryHelper;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CartModel;
use vandles\model\CouponTplModel;
use vandles\model\UserAddressModel;
use vandles\model\UserMoneyLogModel;

class UserMoneyLogService extends BaseService {
    protected static $instance;

    const MONEY_LOG_TYPE_ORDER       = "order"; // 订单支付
    const MONEY_LOG_TYPE_RECHARGE    = "recharge"; // 余额充值
    const MONEY_LOG_TYPE_REFUND      = "refund"; // 订单退款
    const MONEY_LOG_TYPE_GIVE        = "give"; // 余额赠送
    const MONEY_LOG_TYPE_IMPORT      = "import"; // excel初始化导入
    const MONEY_LOG_TYPE_CARD_REFUND_REAL = "card_refund_real"; // 余额退款(实充)
    const MONEY_LOG_TYPE_CARD_REFUND_GIVE = "card_refund_give"; // 余额退款(赠送)

    public static function instance(): UserMoneyLogService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $data = [
            [
                "gid"    => uuid(),
                "openid" => uuid(),
            ], [
                "gid"    => uuid(),
                "openid" => uuid(),
            ]
        ];

        $res  = $this->getModel()->saveAll($data);
        $list = $this->getModel()->select();
        dd($list->toArray());
    }

    /**
     * @return BaseSoftDeleteModel|UserMoneyLogModel
     */
    public function getModel() {
        return UserMoneyLogModel::mk();
    }

    public function mQuery(): QueryHelper {
        return UserMoneyLogModel::mQuery();
    }

    public function getTotalByOpenid($openid) {
        return $this->getModel()->where("status", 1)->where("openid", $openid)->count();
    }

    public function bindPageData(\think\Paginator $pageData) {
        $moneyLogTypes = config("a.money_log_types");
        $pageData->each(function ($item) use ($moneyLogTypes) {
            $item->log_type_txt = $moneyLogTypes[$item->log_type] ?? '未知';
        });
    }

    public function getOneByTargetGid($target_gid) {
        return $this->getModel()->where("target_gid", $target_gid)->find();
    }

    public function bind(array &$data, $userInfos = null, $rechargeWays = null) {
        foreach ($data as &$item) {
            $this->bindOne($item, $userInfos, $rechargeWays);
        }
    }

    public function bindOne(array &$vo, $userInfos = null, $rechargeWays = null) {
        $vo['log_type_txt'] = config("a.money_log_types." . $vo['log_type']);
        if ($userInfos) $vo['user'] = $userInfos[$vo['openid']] ?? [];
        if ($rechargeWays) {
            $vo['way_title'] = $rechargeWays[$vo['recharge_way_gid']]['title'] ?? "-";
        }
    }

    public function getLogByTargetGid(string $targetGid, string $field = "*") {
        return $this->getModel()->where("target_gid", $targetGid)->field($field)->find();
    }


    public function totalCount() {
        $list = $this->getModel()->alias("a")->where("log_type", "recharge")->where("transaction_id", ">", 0)
            ->field("a.delta,b.money,b.give_money, count(*) count")
            ->leftJoin("a_recharge_way b", "b.gid=a.recharge_way_gid")
            ->group("a.delta, b.money, b.give_money")
            ->select();
        $data = [];
        foreach ($list as $vo) {
            $data['xLabels'][] = "充" . floatval($vo->money) . "送" . floatval($vo->give_money);
            $data['data'][]    = $vo->count;
        }
        return $data;
    }

    public function totalCount_dev() {
        $list = $this->getModel()->alias("a")->where("log_type", "recharge")->where("transaction_id", ">", 0)
            ->field("a.delta,b.money,b.give_money, count(*) count")
            ->leftJoin("a_recharge_way b", "b.gid=a.recharge_way_gid")
            ->group("a.delta, b.money, b.give_money")
            ->select();
        $data = [];
        foreach ($list as $vo) {
            $data['xLabels'][] = "充" . floatval($vo->money) . "送" . floatval($vo->give_money);
            $data['data'][]    = $vo->count;
        }
        return $data;
    }

    public function getLogRechargeByTransId($trans_id, $field="*") {
        return $this->getModel()->field($field)->where("log_type", "recharge")->where("transaction_id", $trans_id)->find();
    }


}