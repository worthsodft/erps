<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2025/1/2
 * Time: 17:47
 */

namespace app\api\controller\v1;

use Exception;
use vandles\controller\ApiBaseController;
use vandles\service\OrderService;
use vandles\service\ScheduleService;
use vandles\service\UserMoneyLogService;

/**
 * 自动任务控制器
 */
class Schedule extends ApiBaseController{

    /**
     * 1. 余额充值后自动微信发货
     * @return void
     */
    public function rechargeAutoWxShipping() {
        $res = ScheduleService::instance()->rechargeAutoWxShipping();
        $this->success("余额充值后自动微信发货", $res);
    }
}