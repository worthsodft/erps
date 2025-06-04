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
 * 确认送达(配送人员)
 */
class ConfirmStrategy implements OrderActionStrategy {

    public function execute($data) {
        Log::info("确认送达(配送人员)");
    }
}