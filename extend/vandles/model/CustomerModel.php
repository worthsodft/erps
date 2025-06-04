<?php

namespace vandles\model;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

class CustomerModel extends BaseSoftDeleteModel {

    protected $name = 'ACustomer';


    /**
     * 创建数据库表
     * @param bool $isDelete
     * @throws \Exception
     */
    public function schema($isDeleteTable = false) {
        $table   = "a_customer";

        if ($isDeleteTable) Db::connect($this->connection)->query("drop table if exists `a_customer`");
        $isExists = count(Db::connect($this->connection)->query("show tables like 'a_customer'")) > 0;
        if ($isExists) VException::runtime("表：a_customer 已存在，不需要创建。");

        $sql = "CREATE TABLE `a_customer`
        (
            `id`         bigint unsigned NOT NULL AUTO_INCREMENT,
            `gid`        varchar(36)  NOT NULL comment '编号',
            `title`      varchar(100)  NOT NULL comment '标题',
            `taxno`      varchar(100) comment '税号',
            `address`     varchar(200) comment '详情地址',
            `link_name`  varchar(50) comment '联系人',
            `link_phone` varchar(50) comment '联系电话',
            `lng`    decimal(10, 6) unsigned default 116.397428 comment '经度',
            `lat`    decimal(10, 6) unsigned default 39.908427 comment '纬度',
            
            `remark`    varchar(200) comment '备注',
        
            `status`    tinyint(1) unsigned not null default 1 comment '状态(0.无效,1.有效)',
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
          collate = utf8mb4_unicode_ci comment = '大客户表'";

        Db::query($sql);
    }


}