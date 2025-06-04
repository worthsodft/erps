<?php

namespace vandles\model;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

class ServiceMenuModel extends BaseSoftDeleteModel {

    protected $name = 'AServiceMenu';

// 设置字段信息
    protected $schema = [
        'id'      => 'int',
        'gid'     => 'string',
        'icon'    => 'string',
        'title'   => 'string',
        'url'     => 'string',
        'type'    => 'string',
        'auth'    => 'int',
        'desc'    => 'string',

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
        $table   = "a_service_menu";
        $comment = "功能菜单表";

        if ($isDeleteTable) Db::connect($this->connection)->query("drop table if exists {$table}");
        $isExists = count(Db::connect($this->connection)->query("show tables like '$table'")) > 0;
        if ($isExists) VException::runtime("表：{$table} 已存在，不需要创建。");

        $sql = "CREATE TABLE `{$table}`
        (
            `id`       bigint unsigned not null auto_increment comment 'id',
            `gid`      varchar(32)   comment 'gid',
            `icon`     varchar(32)   comment '图片',
            `title`    varchar(32)   comment '标题',
            `url`      varchar(100)  comment '跳转路径',
            `type`     varchar(20)  comment '类型',
            `auth`     int unsigned not null default 0 comment '权限，二进制',
            `desc`     varchar(100)  comment '说明',
            
            `status`    tinyint(1) unsigned not null default 1 comment '状态(0.无效,1.有效)',
            `sort`      bigint(20) unsigned null default 0 comment '排序权重',
            `create_at` timestamp           not null default current_timestamp comment '创建时间',
            `update_at` datetime  default null on update current_timestamp comment '更新时间',
            `deleted`   tinyint(1) unsigned not null default 0 comment '状态(0.未删,1.已删)',
            primary key (`id`) using btree,
            unique `idx_{$table}4` (`gid`) using btree,
            index `idx_{$table}5` (`auth`) using btree,
        
            index `idx_{$table}1` (`sort`) using btree,
            index `idx_{$table}2` (`status`) using btree,
            index `idx_{$table}3` (`deleted`) using btree
        ) engine = InnoDB
          character set = utf8mb4
          collate = utf8mb4_unicode_ci comment = '$comment'";

        Db::query($sql);
    }


}