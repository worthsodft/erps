<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/12/31
 * Time: 15:10
 */

namespace vandles\lib\order\strategy;

use think\facade\Log;

/**
 * 申请退款(用户)
 */
class RefundRequestStrategy implements OrderActionStrategy {

    public function execute($data) {
        Log::info("申请退款(用户)");
    }
}