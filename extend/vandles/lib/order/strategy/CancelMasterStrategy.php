<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/12/31
 * Time: 15:10
 */

namespace vandles\lib\order\strategy;

use think\facade\Log;
use vandles\lib\VException;
use vandles\service\OrderService;

/**
 * 后台取消订单(后台)
 */
class CancelMasterStrategy implements OrderActionStrategy {

    public function execute($data) {
        $order = $data['order'];

        if ($order['status'] != 0) VException::throw('订单不是未支付状态');

        // 变更状态
        $sname = session("user.nickname");
        $remark = "后台取消订单：" . "master|$sname|".now();
        OrderService::instance()->cancelOrder($order, $remark);
    }
}