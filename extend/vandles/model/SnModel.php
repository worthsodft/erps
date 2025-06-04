<?php

namespace vandles\model;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

class SnModel extends BaseSoftDeleteModel {

    protected $name = 'ASn';

    // 设置字段信息
    protected $schema = [
        'id'      => 'int',
        'prefix'  => 'string',
        'date'    => 'date',
        'current' => 'int',

        'status'    => 'int',
        'sort'      => 'int',
        'create_at' => 'datetime',
        'update_at' => 'datetime',
        'deleted'   => 'int',
    ];

    /**
     * 创建数据库表
     * @param bool $isDelete
     * @throws \Exception
     */
    public function schema($isDeleteTable = false) {
        $table   = "a_sn";
        $comment = "全局编号流水表";

        if ($isDeleteTable) Db::connect($this->connection)->query("drop table if exists `a_sn`");
        $isExists = count(Db::connect($this->connection)->query("show tables like 'a_sn'")) > 0;
        if ($isExists) VException::runtime("表：a_sn 已存在，不需要创建。");

        $sql = "CREATE TABLE `a_sn`
        (
            `id`      bigint unsigned not null auto_increment comment 'id',
            `prefix`  varchar(50) not null comment '前缀',
            `date`    varchar(20) not null comment '流水日期',
            `current` int unsigned default 0 comment '当前流水号',
            
            `status`    tinyint(1) unsigned not null default 1 comment '状态(0.无效,1.有效)',
            `sort`      bigint(20) unsigned null default 0 comment '排序权重',
            `create_at` timestamp           not null default current_timestamp comment '创建时间',
            `update_at` datetime  default null on update current_timestamp comment '更新时间',
            `deleted`   tinyint(1) unsigned not null default 0 comment '状态(0.未删,1.已删)',
            primary key (`id`) using btree,
            unique `idx_a_sn4` (`prefix`) using btree,
        
            index `idx_a_sn1` (`sort`) using btree,
            index `idx_a_sn2` (`status`) using btree,
            index `idx_a_sn3` (`deleted`) using btree
        ) engine = InnoDB
          character set = utf8mb4
          collate = utf8mb4_unicode_ci comment = '全局编号流水表'";

        Db::query($sql);
    }


}