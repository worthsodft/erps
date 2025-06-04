<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 16:02
 * Email: <vandles@qq.com>
 **/

namespace vandles\model;


use think\facade\Db;
use vandles\lib\VException;

class ConfigModel extends BaseSoftDeleteModel {
    protected $name = 'AConfig';

    // 设置字段信息
    protected $schema = [
        'id'         => 'int',
        'title'      => 'string',
        'name'       => 'string',
        'input_type' => 'string',
        'xtype'      => 'string',
        'value'      => 'string',
        'default'    => 'string',
        'desc'       => 'string',

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
    public static function schema($isDeleteTable = false) {
        $table   = "a_config";
        $comment = "配置选项表";

        if ($isDeleteTable) Db::query("drop table if exists {$table}");
        $isExists = count(Db::query("show tables like '$table'")) > 0;
        if ($isExists) VException::runtime("表：{$table} 已存在，不需要创建。");

        $sql = "CREATE TABLE `{$table}`
        (
            `id`         bigint(20)   unsigned not null auto_increment,
            `title`      varchar(100) not null  comment '标题',
            `name`       varchar(100) not null  comment '配置名',
            `input_type` varchar(50)  null default 'input'    comment '类型',
            `xtype`      varchar(50)  null default 'app'    comment '配置类型，app,sys',
            `value`      varchar(500) null      comment '配置值',
            `default`    varchar(500) null      comment '默认值',
            `desc`       varchar(100) null      comment '描述',

            `status`    tinyint(1) unsigned not null default 1 comment '状态(0.无效,1.有效)',
            `sort`      bigint(20) unsigned null default 0 comment '排序权重',
            `create_at` timestamp           not null default current_timestamp comment '创建时间',
            `update_at` datetime  default null comment '更新时间',
            `deleted`   tinyint(1) unsigned not null default 0 comment '状态(0.未删,1.已删)',
            primary key (`id`) using btree,
            unique `idx_{$table}4` (`name`) using btree,
        
            index `idx_{$table}1` (`sort`) using btree,
            index `idx_{$table}2` (`status`) using btree,
            index `idx_{$table}3` (`deleted`) using btree
        ) engine = InnoDB
          character set = utf8mb4
          collate = utf8mb4_unicode_ci comment = '$comment'";

        Db::query($sql);
    }


}