<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/7/12
 * Time: 11:17
 */

namespace vandles\command;

use think\admin\Command;
use think\admin\service\ProcessService;
use think\console\Input;
use think\console\Output;
use vandles\service\OrderService;

/**
 * 未支付订单清量
 */
class OrderClean extends Command {


    protected function configure(){
        $this->setName('vandles:OrderClean');
        $this->setDescription('未支付订单清理');
    }

    protected function execute(Input $input, Output $output){
        $count = OrderService::instance()->clean();
        $this->setQueueSuccess("清理订单成功,共清理 {$count} 条数据");
    }
}