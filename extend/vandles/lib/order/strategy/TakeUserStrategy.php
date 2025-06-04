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
 * 用户收货
 */
class TakeUserStrategy implements OrderActionStrategy {

    public function execute($data) {
        Log::info("用户收货");
    }
}