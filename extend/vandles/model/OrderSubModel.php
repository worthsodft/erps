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

class OrderSubModel extends BaseSoftDeleteModel {
    protected $name = 'AOrderSub';

    /**
     * 创建数据库表
     * @param bool $isDelete
     * @throws \Exception
     */
    public function schema($isDeleteTable = false) {
        $table   = "a_order_sub";
        $comment = "订单明细表";

        if ($isDeleteTable) Db::connect($this->connection)->query("drop table if exists {$table}");
        $isExists = count(Db::connect($this->connection)->query("show tables like '$table'")) > 0;
        if ($isExists) VException::runtime("表：{$table} 已存在，不需要创建。");

        $sql = "CREATE TABLE `{$table}`
        (
            `id`             bigint(20)  unsigned not null auto_increment,
            `order_sn`             varchar(50) not null comment '订单编码',
            `goods_sn`             varchar(50) not null comment '商品编码',
            `goods_cover`          varchar(200) not null comment '商品封面',
            `goods_name`           varchar(50) not null comment '商品名称',
            `goods_deliver_fee`    decimal(10,2) not null default 0 comment '配送费',
            `goods_unit`           varchar(50) not null comment '单位',
            `goods_min_buy_number` int unsigned not null default 0 comment '最小购买数量',
            `goods_self_price`     decimal(10,2) unsigned not null default 0 comment '商品单价',
            `goods_price`     decimal(10,2) unsigned not null default 0 comment '商品价格',
            `goods_market_price`     decimal(10,2) unsigned not null default 0 comment '商品市场价',
            `goods_stock`    int unsigned not null default 0 comment '商品库存',
            
            `goods_amount`   decimal(10,2) unsigned not null default 0 comment '商品金额',
            `goods_number`    int unsigned not null default 0 comment '商品数量',

            `status`    tinyint(1) unsigned not null default 1 comment '状态(0.无效,1.有效)',
            `sort`      bigint(20) unsigned null default 0 comment '排序权重',
            `create_at` timestamp           not null default current_timestamp comment '创建时间',
            `update_at` datetime  default null on update current_timestamp comment '更新时间',
            `deleted`   tinyint(1) unsigned not null default 0 comment '状态(0.未删,1.已删)',
            primary key (`id`) using btree,
            index `idx_{$table}5` (`order_sn`) using btree,
        
            index `idx_{$table}1` (`sort`) using btree,
            index `idx_{$table}2` (`status`) using btree,
            index `idx_{$table}3` (`deleted`) using btree
        ) engine = InnoDB
          character set = utf8mb4
          collate = utf8mb4_unicode_ci comment = '$comment'";

        Db::query($sql);
    }


}