<?php

namespace vandles\model;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

class CouponTplModel extends BaseSoftDeleteModel {

    protected $name = 'ACouponTpl';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'gid'           => 'string',
        'title'         => 'string',
        'money'         => 'float',
        'discount'      => 'int',
        'min_use_money' => 'float',
        'remark' => 'string',

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
        $table   = "a_coupon_tpl";
        $comment = "优惠券模板表";

        if ($isDeleteTable) Db::connect($this->connection)->query("drop table if exists {$table}");
        $isExists = count(Db::connect($this->connection)->query("show tables like '$table'")) > 0;
        if ($isExists) VException::runtime("表：{$table} 已存在，不需要创建。");

        $sql = "CREATE TABLE `{$table}`
        (
            `id`             bigint unsigned not null auto_increment comment 'id',
            `gid`            varchar(32) not null comment 'gid',
            `title`          varchar(50) not null comment '标题',
            `money`          decimal(10,2) unsigned default 0 comment '面值金额, 值为5, 则优惠5元，如果与discount同时设置，优先以money为准',
            `discount`       int unsigned default 0 comment '折扣%, 值为 5, 则: 优惠5%，如果与money同时设置，优先以money为准',
            `min_use_money`  decimal(10,2) unsigned default 0 comment '最小使用金额, 值为5, 则支付满5元可用',
            `remark`         varchar(500) default '' comment '备注',
            
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
          collate = utf8mb4_unicode_ci comment = '$comment'";

        Db::query($sql);
    }



}