<?php

namespace vandles\model;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

class RechargeWayModel extends BaseSoftDeleteModel {

    protected $name = 'ARechargeWay';

    // 设置字段信息
    protected $schema = [
        'id'         => 'int',
        'gid'        => 'string',
        'money'      => 'float',
        'give_money' => 'float',

        'status'    => 'int',
        'sort'      => 'int',
        'create_at' => 'datetime',
        'update_at' => 'datetime',
        'deleted' => 'int',
    ];

    /**
     * 创建数据库表
     * @param bool $isDelete
     * @throws \Exception
     */
    public function schema($isDeleteTable = false) {
        $table   = "a_recharge_way";
        $comment = "充值方式表";

        if ($isDeleteTable) Db::connect($this->connection)->query("drop table if exists {$table}");
        $isExists = count(Db::connect($this->connection)->query("show tables like '$table'")) > 0;
        if ($isExists) VException::runtime("表：{$table} 已存在，不需要创建。");

        $sql = "CREATE TABLE `{$table}`
        (
            `id`         bigint unsigned NOT NULL AUTO_INCREMENT,
            `gid`        varchar(36)  NOT NULL comment '券编号',
            `money`      decimal(10, 2) unsigned default 0 comment '充值金额',
            `give_money` decimal(10, 2) unsigned default 0 comment '赠送金额',
        
            `status`    tinyint(1) unsigned not null default 0 comment '状态(0.无效,1.有效)',
            `sort`      bigint(20) unsigned null default 0 comment '排序权重',
            `create_at` timestamp           not null default current_timestamp comment '创建时间',
            `update_at` datetime  default null on update current_timestamp comment '更新时间',
            `deleted`   tinyint(1) unsigned not null default 0 comment '状态(0.未删,1.已删)',
            primary key (`id`) using btree,
            unique `idx_{$table}4` (`gid`) using btree,
        
            index `idx_{$table}1` (`sort`) using btree,
            index `idx_{$table}2` (`status`) using btree,
            index `idx_{$table}3` (`deleted`) using btree
        ) engine = InnoDB
          character set = utf8mb4
          collate = utf8mb4_unicode_ci comment = '$comment'";

        Db::query($sql);
    }



}