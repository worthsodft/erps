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

class UserInfoModel extends BaseSoftDeleteModel {
    protected $name = 'AUserInfo';

    /**
     * 创建数据库表
     * @param bool $isDelete
     * @throws \Exception
     */
    public function schema($isDeleteTable = false) {
        $table   = "a_user_info";
        $comment = "用户信息表";

        if ($isDeleteTable) Db::connect($this->connection)->query("drop table if exists {$table}");
        $isExists = count(Db::connect($this->connection)->query("show tables like '$table'")) > 0;
        if ($isExists) VException::runtime("表：{$table} 已存在，不需要创建。");

        $sql = "CREATE TABLE `{$table}`
        (
            `id`        bigint(20) unsigned not null auto_increment,
            `openid`    varchar(50)         not null comment 'openid',
            `nickname`  varchar(50)         null comment '用户昵称',
            `avatar`    varchar(255)        null comment '头像',
            `realname`  varchar(50)         null comment '真实姓名',
            `phone`     varchar(20)         null comment '用户电话',
            `username`  varchar(50)         null comment '用户登录名',
            `password`  varchar(50)         null comment '登录密码',
            `gender`    tinyint(1) unsigned not null default 0 comment '性别，0未知，1男，2女',
            `email`     varchar(100)        null comment '邮箱',

            `money`      decimal(10, 2) unsigned not null default 0.00 comment '余额',
            `real_money` decimal(10, 2) unsigned not null default 0.00 comment '实际充值余额',
            `give_money` decimal(10, 2) unsigned not null default 0.00 comment '充值赠送余额',
            `money_expire_at` datetime       null comment '余额过期时间',
            `score`     decimal(10, 2) unsigned not null default 0.00 comment '积分',
            `auth`      int        null comment '操作权限',
            `districts` varchar(500) null comment '配送区域',
            `station_gid` varchar(50)         null comment '所属水站',
            `card_no`   varchar(100) null comment '水卡号',
            

            `status`    tinyint(1) unsigned not null default 1 comment '状态(0.无效,1.有效)',
            `sort`      bigint(20) unsigned null default 0 comment '排序权重',
            `create_at` timestamp           not null default current_timestamp comment '创建时间',
            `update_at` datetime  default null on update current_timestamp comment '更新时间',
            `deleted`   tinyint(1) unsigned not null default 0 comment '状态(0.未删,1.已删)',
            primary key (`id`) using btree,
            unique `idx_{$table}5` (`openid`) using btree,
            unique `idx_{$table}6` (`phone`) using btree,
            unique `idx_{$table}7` (`username`) using btree,
            unique `idx_{$table}8` (`station_gid`) using btree,
        
            index `idx_{$table}1` (`sort`) using btree,
            index `idx_{$table}2` (`status`) using btree,
            index `idx_{$table}3` (`deleted`) using btree
        ) engine = InnoDB
          character set = utf8mb4
          collate = utf8mb4_unicode_ci comment = '$comment'";

        Db::query($sql);
    }


}