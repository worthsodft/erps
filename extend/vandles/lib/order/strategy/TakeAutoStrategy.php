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
 * 自动收货
 */
class TakeAutoStrategy implements OrderActionStrategy {

    public function execute($data) {
        Log::info("自动收货");
    }
}