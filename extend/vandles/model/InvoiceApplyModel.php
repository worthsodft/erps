<?php

namespace vandles\model;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

class InvoiceApplyModel extends BaseSoftDeleteModel {

    protected $name = 'AInvoiceApply';

    // 设置字段信息
    protected $schema = [
        'id'           => 'int',
        'gid'          => 'string',
        'order_sns'    => 'string',
        'buyer_type'   => 'int',
        'invoice_type' => 'int',
        'title'        => 'string',
        'taxno'        => 'string',
        'address'      => 'string',
        'phone'        => 'string',
        'bank_name'    => 'string',
        'bank_account' => 'string',
        'email'        => 'string',
        'openid'       => 'string',
        'is_email'     => 'int',
        'email_at'     => 'datetime',
        'email_by'     => 'int',

        'invoice_no'   => 'string',


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
        $table   = "a_invoice_apply";
        $comment = "用户开票申请";

        if ($isDeleteTable) Db::connect($this->connection)->query("drop table if exists {$table}");
        $isExists = count(Db::connect($this->connection)->query("show tables like '$table'")) > 0;
        if ($isExists) VException::runtime("表：{$table} 已存在，不需要创建。");

        $sql = "CREATE TABLE `{$table}`
        (
            `id`           bigint unsigned not null auto_increment comment 'id',
            `gid`          varchar(32) not null comment 'gid',
            `order_sns`    varchar(1000) not null comment '订单编号集合',
            `buyer_type`   tinyint(1) unsigned not null default 0 comment '购买方类型(0.个人,1.公司)',
            `invoice_type` tinyint(1) unsigned not null default 0 comment '发票类型(0.普通,1.专用)',
            `title`        varchar(50) not null comment '开票名称',
            `taxno`        varchar(50) null comment '公司税号',
            `address`      varchar(200) null comment '公司地址',
            `phone`        varchar(50) null comment '公司电话',
            `bank_name`    varchar(50) null comment '开户银行',
            `bank_account` varchar(50) null comment '银行账号',
            `email`        varchar(50) not null comment '收票邮箱',
            `openid`       varchar(50) not null comment 'openid',
            
            `is_email`     tinyint(1) unsigned not null default 0 comment '是否已发邮箱(0.否,1.是)',
            `email_at`     datetime  default null comment '发邮箱时间',
            `email_by`     bigint unsigned  default 0 comment '操作人',
            `invoice_no`   varchar(50) not null comment '发票号码',
            
            `status`    tinyint(1) unsigned not null default 1 comment '状态(0.无效,1.有效)',
            `sort`      bigint(20) unsigned null default 0 comment '排序权重',
            `create_at` timestamp           not null default current_timestamp comment '创建时间',
            `update_at` datetime  default null on update current_timestamp comment '更新时间',
            `deleted`   tinyint(1) unsigned not null default 0 comment '状态(0.未删,1.已删)',
            primary key (`id`) using btree,
            unique `idx_{$table}4` (`gid`) using btree,
            index `idx_{$table}5` (`order_sn`) using btree,
            index  `idx_{$table}6` (`openid`) using btree,
        
            index `idx_{$table}1` (`sort`) using btree,
            index `idx_{$table}2` (`status`) using btree,
            index `idx_{$table}3` (`deleted`) using btree
        ) engine = InnoDB
          character set = utf8mb4
          collate = utf8mb4_unicode_ci comment = '$comment'";

        Db::query($sql);
    }


}