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
 * 核销(核销人员)
 */
class VerifyStrategy implements OrderActionStrategy {

    public function execute($data) {
        Log::info("核销(核销人员)");
    }
}