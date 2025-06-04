<?php

namespace vandles\model;

use think\facade\Db;
use vandles\lib\VException;

class CouponPublishModel extends BaseSoftDeleteModel {

    protected $name = 'ACouponPublish';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'gid'           => 'string',
        'title'         => 'string',
        'money'         => 'float',
        'discount'      => 'int',
        'min_use_money' => 'float',

        'remark' => 'string',

        'count'     => 'int',
        'has_count' => 'int',
        'per_count' => 'int',

        'fetch_openids' => 'string',
        'use_scope'  => 'string',
        'is_new'     => 'int',

        'expire_at'   => 'datetime',
        'expire_days' => 'int',

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
        $table   = "a_coupon_publish";
        $comment = "优惠券发布表";

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
            `remark`         text comment '备注说明',

            `count`       int unsigned default 0 comment '发布数量',
            `has_count`   int unsigned default 0 comment '剩余数量',
            `per_count`   int unsigned default 0 comment '每人限领数量',
            
            `fetch_openids`    text comment '指定领取人openid，逗号分隔',
            `use_scope`     varchar(50) default 'order' comment '使用范围，order（目前只一种，即下单时可使用）',
            `is_new`        tinyint(1) default 0 comment '仅新用户可领可用, 0否，1是',
            
            `expire_at`     datetime NULL comment '有效期至, 时间绝对值, 优先级高于expire_days',
            `expire_days`   smallint default 0 comment '有效期天数, 如果值为7, 则: 领取后, 7天内有效',
            
            `status`    tinyint(1) unsigned not null default 0 comment '状态(0.无效,1.有效)',
            `sort`      bigint(20) unsigned null default 0 comment '排序权重',
            `create_at` timestamp           not null default current_timestamp comment '创建时间',
            `update_at` datetime  default null on update current_timestamp comment '更新时间',
            `deleted`   tinyint(1) unsigned not null default 0 comment '状态(0.未删,1.已删)',
            primary key (`id`) using btree,
            unique `idx_{$table}4` (`gid`) using btree,
            index `idx_{$table}5` (`is_new`) using btree,
        
            index `idx_{$table}1` (`sort`) using btree,
            index `idx_{$table}2` (`status`) using btree,
            index `idx_{$table}3` (`deleted`) using btree
        ) engine = InnoDB
          character set = utf8mb4
          collate = utf8mb4_unicode_ci comment = '$comment'";

        Db::query($sql);
    }

}