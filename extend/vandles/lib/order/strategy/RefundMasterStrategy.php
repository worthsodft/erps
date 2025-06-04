<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/12/31
 * Time: 15:10
 */

namespace vandles\lib\order\strategy;

use think\facade\Db;
use think\facade\Log;
use vandles\lib\order\OrderStatusMachine;
use vandles\lib\VException;
use vandles\service\OrderService;

/**
 * 后台退款(后台)策略
 */
class RefundMasterStrategy implements OrderActionStrategy {

    public function execute($data) {
        $order        = $data['order'];
        $refundReason = $data['refundReason'];
        if ($order['status'] != 1) VException::throw('订单不是待配送状态');
        if ($order['deliver_status'] != 0) VException::throw('订单不是待配送状态');

        $orderService = OrderService::instance();
        Db::startTrans();
        try{
            $order = $orderService->refundApply($order, $refundReason);
            $orderService->refund($order, $order->pay_amount, $refundReason);

            Db::commit();
        }catch(\Exception $e){
            Db::rollback();
            VException::throw($e->getMessage());
        }


    }
}