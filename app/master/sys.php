<?php

//use app\data\command\OrderClean;
use app\data\command\UserAgent;
use app\data\command\UserAmount;
use app\data\command\UserTransfer;
use app\data\command\UserUpgrade;
use app\data\service\OrderService;
use app\data\service\RebateService;
use app\data\service\UserBalanceService;
use app\data\service\UserRebateService;
use think\admin\Library;
use think\Console;
use vandles\command\Module;
use vandles\command\OrderClean;

if (Library::$sapp->request->isCli()) {
    // 动态注册操作指令
    Console::starting(function (Console $console) {
        $console->addCommand(OrderClean::class);
        $console->addCommand(Module::class);
//        $console->addCommand(UserAgent::class);
//        $console->addCommand(UserAmount::class);
//        $console->addCommand(UserUpgrade::class);
//        $console->addCommand(UserTransfer::class);
    });
} else {
    // 订单支付成功
    Library::$sapp->event->listen('paySuccess', function ($data) {
        alert("paySuccess事件已执行");
        alert($data);
    });
}