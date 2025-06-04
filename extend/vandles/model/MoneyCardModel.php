<?php

namespace vandles\model;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

class MoneyCardModel extends BaseSoftDeleteModel {

    protected $name = 'AMoneyCard';

    // 设置字段信息
    protected $schema = [
        'id'     => 'int',
        'gid'    => 'string',
        'openid' => 'string',

        'real_init'  => 'float',
        'give_init'  => 'float',
        'total_init' => 'float',

        'real_rate' => 'float',
        'give_rate' => 'float',

        'real_has'  => 'float',
        'give_has'  => 'float',
        'total_has' => 'float',

        'expire_at' => 'datetime',

        'trans_id' => 'string',
        'way_gid'  => 'string',

        'refund_remark' => 'string',
        'refund_at'     => 'datetime',
        'refund_sn'     => 'string',
        'refund_id'     => 'string',

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
        $table   = "a_money_card";
        $comment = "金额水卡表";

        if ($isDeleteTable) Db::connect($this->connection)->query("drop table if exists {$table}");
        $isExists = count(Db::connect($this->connection)->query("show tables like '$table'")) > 0;
        if ($isExists) VException::runtime("表：{$table} 已存在，不需要创建。");

        $sql = "CREATE TABLE `{$table}`
        (
            `id`        bigint unsigned NOT NULL AUTO_INCREMENT,
            `gid`       varchar(36)  NOT NULL comment '编号',
            `openid`    varchar(50)  NOT NULL comment 'openid',
            `real_init` decimal(10, 2) unsigned default 0 comment '初始充值金额',
            `give_init` decimal(10, 2) unsigned default 0 comment '初始赠送金额',
            `total_init` decimal(10, 2) unsigned default 0 comment '初始总金额',
            `expire_at` datetime       null comment '余额过期时间',
            
            `real_rate` decimal(10, 7) unsigned default 0 comment '充值扣减比例',
            `give_rate` decimal(10, 7) unsigned default 0 comment '赠送扣减比例',
            
            `real_has` decimal(10, 2) unsigned default 0 comment '充值余额',
            `give_has` decimal(10, 2) unsigned default 0 comment '赠送余额',
            `total_has` decimal(10, 2) unsigned default 0 comment '总余额',
            
            `trans_id`    varchar(50) comment '微信侧支付单号',
            `way_gid`    varchar(50) comment '充值优惠方式gid',
            
            `refund_remark`   varchar(255) comment '退款描述',
            `refund_at`       datetime comment '退款时间',
            `refund_sn`       varchar(50) comment '退款单号',
            `refund_trans_id` varchar(50) comment '微信侧退款单号',
        
            `status`    tinyint(1) unsigned not null default 1 comment '状态(0.已退款,1.有效)',
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