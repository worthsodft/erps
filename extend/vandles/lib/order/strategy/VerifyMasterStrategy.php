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
 * 后台核销(后台)
 */
class VerifyMasterStrategy implements OrderActionStrategy {

    public function execute($data) {
        $order = $data['order'];

        if ($order['status'] != 1) VException::throw('订单不是待配送状态');
        if ($order['deliver_status'] == 2) VException::throw('订单已配送，不能重复配送');

        // 变更状态
        $sname = session("user.nickname");
        $data = [
            'status'         => 2,
            'deliver_status' => 2,
            'take_at'        => now(),
            'take_by'        => "master|$sname",
        ];
        $order->save($data);
    }
}