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

class OrderModel extends BaseSoftDeleteModel {
    protected $name = 'AOrder';


    /**
     * 创建数据库表
     * @param bool $isDelete
     * @throws \Exception
     */
    public function schema($isDeleteTable = false) {
        $table   = "a_order";
        $comment = "订单表";

        if ($isDeleteTable) Db::connect($this->connection)->query("drop table if exists {$table}");
        $isExists = count(Db::connect($this->connection)->query("show tables like '$table'")) > 0;
        if ($isExists) VException::runtime("表：{$table} 已存在，不需要创建。");

        $sql = "CREATE TABLE `{$table}`
        (
            `id`             bigint(20)  unsigned not null auto_increment,
            `sn`             varchar(50) not null comment '商品编码',
            `goods_total`    int unsigned not null default 0 comment '商品数量',
            `goods_amount`   decimal(10,2) unsigned not null default 0 comment '商品总金额(数量 * 单价)',
            `discount_amount`decimal(10,2) unsigned not null default 0 comment '优惠金额（用券）',
            `real_amount`    decimal(10,2) unsigned not null default 0 comment '商品实付金额 = goods_amount - discount_amount',
            `deliver_amount` decimal(10,2) unsigned not null default 0 comment '配送金额（服务费）',
            `pay_amount`     decimal(10,2) unsigned not null default 0 comment '支付金额 = real_amount + deliver_amount',
            `real_deduct`    decimal(10,2) unsigned not null default 0 comment '商品实际消费金额（yue）, real_amount * 实充占比',
            `give_deduct`    decimal(10,2) unsigned not null default 0 comment '赠送金额, real_amount - real_deduct',
            `invoice_amount` decimal(10,2) unsigned not null default 0 comment '开票金额，real_amount - give_deduct + deliver_amount',
            `money_card_snap`text comment '金额水卡消费快照，用于退款时，原路返还',
            `openid`         varchar(50) not null comment '用户openid',
            `take_type`      tinyint(1) unsigned not null default 0 comment '0自提，1配送',
            `address_gid`    varchar(50) null comment '收货地址gid',
            `take_name`      varchar(50) null comment '收货人姓名',
            `take_phone`     varchar(50) null comment '收货人电话',
            `take_privince`  varchar(50) null comment '收货省',
            `take_city`      varchar(50) null comment '收货市',
            `take_district`  varchar(50) null comment '收货区',
            `take_street`    varchar(50) null comment '收货街道',
            `station_gid`    varchar(50) null comment '水站gid',
            `station_title`  varchar(50) null comment '水站标题',
            `station_address`    varchar(200) null comment '水站地址',
            `station_link_name`    varchar(50) null comment '水站联系人姓名',
            `station_link_phone`   varchar(50) null comment '水站联系人电话',
            `coupon_gid`    varchar(50) null comment '优惠券gid',
            `pay_type`      varchar(20) null comment '支付方式，weixin,yue',
            `pay_at`         datetime null comment '支付时间',
            `pick_gid`       varchar(50) null comment '配货单gid，配送订单需要的字段',
            `pick_at`        datetime null comment '配货时间',
            `deliver_images` varchar(1000) null comment '配送图片',
            `deliver_remark` varchar(255) null comment '配送说明',
            `take_at`        datetime null comment '自提时间，收货时间',
            `take_by`        varchar(50) null comment '核销人，完成人openid',
            `transaction_id` varchar(50) null comment '微信侧支付编号',
            `refund_before_status`  tinyint(1) unsigned not null default 0 comment '退款前状态(0.待支付,1.配送中,2.已完成,9.退款)',
            `refund_status`  tinyint(1) unsigned not null default 0 comment '退款状态(0.未退款,1.退款中,2.退款完成,3.退款失败)',
            `refund_reason`  varchar(200) null comment '退款原因',
            `refund_apply_at`      datetime null comment '退款申请时间',
            `refund_feedback_at`      datetime null comment '退款反馈时间',
            `refund_feedback_msg`  varchar(200) null comment '退款反馈备注',
            
            `invoice_apply_at` datetime null comment '发票申请时间',
            `invoice_no`       varchar(50) null comment '发票号码',
            `invoice_email_at` datetime null comment '发邮件时间',
            `invoice_email_by` bigint unsigned not null default 0 comment '发邮件suid',
            
            
            `remark`         varchar(200) null comment '备注',

            `status`    tinyint(1) unsigned not null default 0 comment '状态(0.待支付,1.配送中,2.已完成,9.退款)',
            `deliver_status`    tinyint(1) unsigned not null default 0 comment '配送状态(0.待配货,1.配送中,2.已配送)',
            `sort`      bigint(20) unsigned null default 0 comment '排序权重',
            `create_at` timestamp           not null default current_timestamp comment '创建时间',
            `update_at` datetime  default null on update current_timestamp comment '更新时间',
            `deleted`   tinyint(1) unsigned not null default 0 comment '状态(0.未删,1.已删)',
            primary key (`id`) using btree,
            index `idx_{$table}5` (`sn`) using btree,
            index `idx_{$table}6` (`openid`) using btree,
            index `idx_{$table}7` (`take_type`) using btree,
            index `idx_{$table}8` (`pay_type`) using btree,
            index `idx_{$table}9` (`transaction_id`) using btree,
            index `idx_{$table}10` (`take_district`) using btree,
            index `idx_{$table}11` (`station_gid`) using btree,
            index `idx_{$table}12` (`deliver_status`) using btree,
        
            index `idx_{$table}1` (`sort`) using btree,
            index `idx_{$table}2` (`status`) using btree,
            index `idx_{$table}3` (`deleted`) using btree
        ) engine = InnoDB
          character set = utf8mb4
          collate = utf8mb4_unicode_ci comment = '$comment'";

        Db::query($sql);
    }


    public function subs(){
        return $this->hasMany(OrderSubModel::class, 'order_sn', 'sn');
    }


}