<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/12/31
 * Time: 15:15
 */

namespace vandles\lib\order\strategy;

use think\facade\Log;

/**
 * 驳回退款(后台)
 */
class RefundRejectStrategy implements OrderActionStrategy {

    public function execute($data) {
        Log::info("驳回退款(后台)");
    }
}