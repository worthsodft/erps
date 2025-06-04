<?php

namespace vandles\model;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

class MoneyCardLogModel extends BaseSoftDeleteModel {

    protected $name = 'AMoneyCardLog';


    /**
     * 创建数据库表
     * @param bool $isDelete
     * @throws \Exception
     */
    public function schema($isDeleteTable = false) {
        $table   = "a_money_card_log";
        $comment = "金额水卡日志表";

        if ($isDeleteTable) Db::connect($this->connection)->query("drop table if exists `a_money_card_log`");
        $isExists = count(Db::connect($this->connection)->query("show tables like 'a_money_card_log'")) > 0;
        if ($isExists) VException::runtime("表：a_money_card_log 已存在，不需要创建。");

        $sql = "CREATE TABLE `a_money_card_log`
        (
            `id`        bigint unsigned NOT NULL AUTO_INCREMENT,
            `gid`       varchar(36)  NOT NULL comment '编号',
            `openid`    varchar(50)  NOT NULL comment 'openid',
            `real_before` decimal(10, 2) default 0 comment '变动前充值金额',
            `give_before` decimal(10, 2) default 0 comment '变动前赠送金额',
            `total_before` decimal(10, 2) default 0 comment '变动前总金额',
            `real_delta` decimal(10, 2) default 0 comment '变动充值金额',
            `give_delta` decimal(10, 2) default 0 comment '变动赠送金额',
            `total_delta` decimal(10, 2) default 0 comment '变动总金额',
            `order_sn`    varchar(50) comment '订单编号',
            `money_card_gid`    varchar(50) comment '金额水卡gid',
            `log_type`    varchar(20) default 'order' comment '变动类型',
            
            `real_rate` decimal(10, 7) unsigned default 0 comment '充值扣减比例',
            `give_rate` decimal(10, 7) unsigned default 0 comment '赠送扣减比例',
        
            `status`    tinyint(1) unsigned not null default 1 comment '状态(0.无效,1.有效)',
            `sort`      bigint(20) unsigned null default 0 comment '排序权重',
            `create_at` timestamp           not null default current_timestamp comment '创建时间',
            `update_at` datetime  default null on update current_timestamp comment '更新时间',
            `deleted`   tinyint(1) unsigned not null default 0 comment '状态(0.未删,1.已删)',
            primary key (`id`) using btree,
            unique `idx_a_money_card_log_4` (`gid`) using btree,
            index `idx_a_money_card_log_5` (`openid`) using btree,
            index `idx_a_money_card_log_6` (`money_card_gid`) using btree,
            index `idx_a_money_card_log_7` (`log_type`) using btree,
        
            index `idx_a_money_card_log_1` (`sort`) using btree,
            index `idx_a_money_card_log_2` (`status`) using btree,
            index `idx_a_money_card_log_3` (`deleted`) using btree
        ) engine = InnoDB
          character set = utf8mb4
          collate = utf8mb4_unicode_ci comment = '金额水卡日志表'";

        Db::query($sql);
    }


}