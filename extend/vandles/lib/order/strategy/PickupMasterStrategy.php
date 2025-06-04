<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/12/31
 * Time: 15:10
 */

namespace vandles\lib\order\strategy;

use think\facade\Log;
use vandles\lib\order\OrderStatusMachine;
use vandles\lib\VException;
use vandles\service\OrderService;

/**
 * 后台配货(后台)策略
 */
class PickupMasterStrategy implements OrderActionStrategy {

    public function execute($data) {
        $order = $data['order'];

        if ($order['status'] != 1) VException::throw('订单不是待配送状态');
        if ($order['deliver_status'] != 0) VException::throw('订单不是待配送状态');

        // 变更状态
        $sname = session("user.nickname");
        $data = [
            'deliver_status' => 1,
            'pick_at'        => now(),
            'pick_by'        => "master|$sname",
        ];
        $order->save($data);

        // 微信发货，此接口已经官方小程序后台关闭，注释掉下面的代码
        // if ($order['sn']) {
        //     OrderService::instance()->wxShippingByOutTradeNo($order['sn']);
        // }
    }
}