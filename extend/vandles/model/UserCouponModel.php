<?php

namespace vandles\model;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

class UserCouponModel extends BaseSoftDeleteModel {

    protected $name = 'AUserCoupon';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'gid'           => 'string',
        'coupon_publish_gid' => 'string',
        'fetch_openid'    => 'string',
        'openid'          => 'string',
        'title'         => 'string',

        'money'         => 'float',
        'discount'      => 'int',
        'min_use_money' => 'float',

        'remark'     => 'string',
        'use_scope'  => 'string',
        'is_new'     => 'int',

        'expire_at'   => 'datetime',
        'fetch_at'    => 'datetime',
        'use_at'      => 'datetime',
        'target_gid'  => 'string',

        'is_shared' => 'int',

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
        $table   = "a_user_coupon";
        $comment = "用户优惠券领取表";

        if ($isDeleteTable) Db::connect($this->connection)->query("drop table if exists {$table}");
        $isExists = count(Db::connect($this->connection)->query("show tables like '$table'")) > 0;
        if ($isExists) VException::runtime("表：{$table} 已存在，不需要创建。");

        $sql = "CREATE TABLE `{$table}`
        (
            `id`                 bigint unsigned NOT NULL AUTO_INCREMENT,
            `gid`                varchar(36)  NOT NULL comment '券编号',
            `title`              varchar(100) NOT NULL comment '标题',
            `coupon_publish_gid` varchar(36)  NOT NULL comment '发布gid',
            `fetch_openid`       varchar(36)  NOT NULL comment '首次领取人openid',
            `openid`             varchar(50)  NOT NULL comment '拥有人openid',
        
            `use_scope`         varchar(100) NULL DEFAULT 'order' comment '可使用的范围 order（目前只一种，即下单时可使用）',
            `is_new`            tinyint(1) DEFAULT 0 comment '仅新用户可领可用, 0否，1是',
        
            `money`             decimal(10, 2)        default 0 comment '面值金额, 值为5, 则优惠5元，如果与discount同时设置，优先以money为准',
            `discount`          smallint              default 0 comment '折扣%, 值为 5, 则: 优惠5%，如果与money同时设置，优先以money为准',
            `min_use_money`     decimal(10, 2)        default 0 comment '最小使用金额, 值为5, 则支付满5元可用',
        
            `remark`            text comment '使用说明',
        
            `expire_at`         datetime              DEFAULT NULL comment '有效期至, 时间绝对值',
        
            `fetch_at`          datetime              DEFAULT NULL comment '领取时间，可能是领取别人分享的',
            `use_at`            datetime              DEFAULT NULL comment '使用时间',
            `target_gid`         varchar(50) comment '核销主体gid',
            
        
            `is_shared`         tinyint(1) DEFAULT 0 comment '分享状态，0未被分享过，1已被分享过',
            
            `status`    tinyint(1) unsigned not null default 1 comment '状态(0.已使用,1.可使用)',
            `sort`      bigint(20) unsigned null default 0 comment '排序权重',
            `create_at` timestamp           not null default current_timestamp comment '创建时间',
            `update_at` datetime  default null on update current_timestamp comment '更新时间',
            `deleted`   tinyint(1) unsigned not null default 0 comment '状态(0.未删,1.已删)',
            primary key (`id`) using btree,
            unique `idx_{$table}4` (`gid`) using btree,
            index `idx_{$table}5` (`openid`) using btree,
        
            index `idx_{$table}1` (`sort`) using btree,
            index `idx_{$table}2` (`status`) using btree,
            index `idx_{$table}3` (`deleted`) using btree
        ) engine = InnoDB
          character set = utf8mb4
          collate = utf8mb4_unicode_ci comment = '$comment'";

        Db::query($sql);
    }



}