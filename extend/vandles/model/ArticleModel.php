<?php

namespace vandles\model;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

class ArticleModel extends BaseSoftDeleteModel {

    protected $name = 'AArticle';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'gid'           => 'string',
        'title'         => 'string',
        'intro'         => 'string',
        'url'           => 'string',
        'cover'         => 'string',
        'icon'          => 'string',
        'desc'          => 'string',
        'article_type'  => 'string',

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
        $table   = "a_article";
        $comment = "文章表";

        if ($isDeleteTable) Db::connect($this->connection)->query("drop table if exists {$table}");
        $isExists = count(Db::connect($this->connection)->query("show tables like '$table'")) > 0;
        if ($isExists) VException::runtime("表：{$table} 已存在，不需要创建。");

        $sql = "CREATE TABLE `{$table}`
        (
            `id`           bigint unsigned not null auto_increment comment 'id',
            `gid`          varchar(32) not null comment 'gid',
            `title`        varchar(100) not null comment '标题',
            `intro`        varchar(200) comment '简介',
            `url`          varchar(200) comment '跳转路径',
            `cover`        varchar(200) comment '封面图片',
            `icon`         varchar(50) comment '图标',
            `desc`         text comment '富文本内容',
            `article_type` varchar(50) not null default 'notice' comment '类型：notice, index_swiper, about',
            
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