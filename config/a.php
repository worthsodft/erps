<?php
return [
    'order_auto_clean_sec' => 1800, // 订单自动清理时间
    'order_auto_take_sec'  => 86400 * 5, // 订单自动收货时间
    'money_expire_add_sec' => 86400 * 365, // 余额有效期的增加秒数
//    'free_deliver_money' => 50, // 运费满50元，免服务费
    'is_show_demo'         => 0, // 是否显示我的页的demo菜单
    'finish_qrcode_tip'    => "请使用本人账号订单进行核销，非本人下单的订单截图不予核销，谢谢配合",
    "cities"               => [
        ["code" => "120101", "name" => "和平区"],
        ["code" => "120102", "name" => "河东区"],
        ["code" => "120103", "name" => "河西区"],
        ["code" => "120104", "name" => "南开区"],
        ["code" => "120105", "name" => "河北区"],
        ["code" => "120106", "name" => "红桥区"],
        ["code" => "120112", "name" => "津南区"],
        ["code" => "120113", "name" => "北辰区"],
    ],
    'goods_types'          => ['销售商品', '原材料', '实物卡'],
    "coupon_use_scopes"    => ['order' => '订单'],
    "user_info_tabs"       => [
        "优惠券列表", "余额记录", "收货地址"
    ],
    "customer_info_tabs"       => [
        "预付款记录"
    ],
    "article_types"        => [
        "notice" => "首页通知", "index_swiper" => "首页轮播", "about" => "关于我们"
    ],
    "order_take_types"     => [
        ["text" => "自提", "value" => 0],
        ["text" => "配送", "value" => 1],
    ],

    "order_status"         => [
        ["status" => 0, "title" => "待支付", "icon" => "wallet"],
        ["status" => 1, "title" => "配送中", "icon" => "paperplane"],
        ["status" => 2, "title" => "已完成", "icon" => "checkbox"],
        ["status" => 9, "title" => "退　款", "icon" => "refresh"],
    ],
    "order_status_show"    => [
        0 => "待支付",
        1 => "配送中",
        2 => "已完成",
        9 => "退　款",
    ],

    // 订单配送状态
    "order_deliver_status" => [
        "待配货", "配送中", "已配送"
    ],

    "order_refund_status"   => [
        "未退款", "退款中", "已退款", "已驳回"
    ],
    // 微信发货状态
    "shipping_order_states" => [
        1 => ["order_state" => 1, "order_state_txt" => '待发货'],
        2 => ["order_state" => 2, "order_state_txt" => '已发货'],
        3 => ["order_state" => 3, "order_state_txt" => '确认收货'],
        4 => ["order_state" => 4, "order_state_txt" => '交易完成'],
        5 => ["order_state" => 5, "order_state_txt" => '已退款'],
    ],

    // 用户余额记录类型
    "money_log_types"       => [
        "order"            => "订单支付",
        "recharge"         => "余额充值",
        "refund"           => "订单退款",
        "give"             => "余额赠送",
//        "import"   => "excel导入",
        "card_refund_real" => "余额退款(实充)",
        "card_refund_give" => "余额退款(赠送)",
    ],
//    "recharge_way_remarks" => [
//        "余额有效期自末次充值日期起一年内有效",
//        "有任何疑问，可在“我的”页功能区联系客服咨询。",
//    ],

    // 企业预付款记录类型
    "pre_money_log_types"   => [
        "order"    => "订单支付", // target_gid为：订单编号
        "refund"   => "订单退款", // target_gid为：订单编号
        "recharge" => "预付款充值", // target_gid为：预付款单号
        // "freeze"   => "出库冻结", // target_gid为：出库单号
        // "transfer" => "冻结转支付", // target_gid为：出库单号
    ],
    // 微信支付类型
    "wx_pay_types"    => [
        "recharge" => "余额充值",
        "order"    => "订单支付",
        "other"    => "其他支付"
    ],
    // 小程序订单支付类型
    "order_pay_types" => [
        "weixin"   => "微信",
        "yue"      => "余额",
        "giftcard" => "实物卡",
    ],
    // 公司订单支付类型
    "com_order_pay_types" => [
        "com_cash"   => "现金(公司)",
        "com_weixin" => "微信(公司)",
        "com_elec"   => "电汇(公司)",
        "com_cheque" => "支票(公司)",
        "com_pre"    => "预付款(公司)",
        "com_credit" => "挂账(公司)",
    ],
    // 开票买家类型
    "buyer_types"     => [
        '自然人', '公司'
    ],
    "invoice_types"   => [
        '普通发票', '专用发票'
    ],

    "money_card_loy_types"         => [
        "order"  => "订单支付",
        "refund" => "订单退款",
    ],
    // 生产工序
    "produce_process"              => [
        ["id" => 1, "title" => "上瓶(桶)"],
        ["id" => 2, "title" => "灌装"],
        ["id" => 3, "title" => "包装"],
        ["id" => 4, "title" => "码垛"],
    ],
    // 生产计划时间段
    "plan_time_slots"              => [
        "1" => "上午",
        "2" => "下午"
    ],
    "weeks"                        => [
        "0" => "星期日",
        "1" => "星期一",
        "2" => "星期二",
        "3" => "星期三",
        "4" => "星期四",
        "5" => "星期五",
        "6" => "星期六"
    ],
    "plan_status"                  => [
        0  => "未审核",
        1  => "已审核",
        2  => "生产中",
        3  => "已完成",
        4  => "已入库",
        -1 => "已驳回"
    ],
    "plan_sub_status"              => [
        0 => "未开始",
        1 => "生产中",
        2 => "已完成",
        3 => "已入库"
    ],
    "in_stock_status"              => [
        0  => "未入库",
        1  => "已入库",
        -1 => "已驳回"
    ],
    // 入库类型
    "in_stock_types"               => [
        "purchase"    => '采购入库',
        "produce"     => '生产入库',
        "refund"      => '退货入库',
        "produceback" => '原料退还',
    ],
    // 采购单状态
    "buy_order_status"             => [
        0  => "未审核",
        1  => "已审核",
        -1 => "已驳回"
    ],
    // 出库单状态
    "out_stock_status"             => [
        0  => "未出库",
        1  => "已出库",
        -1 => "已驳回"
    ],
    // 出库类型
    "out_stock_types"              => [
        "sale"       => '销售出库',
        "produceget" => '原料申领',
    ],
    // 销售单状态
    "sale_order_status"            => [
        0  => "未审核",
        1  => "已审核",
        -1 => "已驳回"
    ],
    // 库存调拨单状态
    "transfer_stock_status"        => [
        0  => "未审核",
        1  => "已审核",
        -1 => "已驳回"
    ],
    // 库存损溢单状态
    "change_stock_status"          => [
        0  => "未审核",
        1  => "已审核",
        -1 => "已驳回"
    ],
    // 库存变更记录
    "stock_log_op_types"           => [
        "change"   => "库存损溢",
        "instock"  => "商品入库",
        "outstock" => "商品出库",
        "transfer" => "库存调拨",
        "taking"   => "库存盘点",
    ],
    "open_show_urls"               => [
        "change"   => url("/master/stock.changestock/show")->build(),
        "instock"  => url("/master/stock.instock/show")->build(),
        "outstock" => url("/master/stock.outstock/show")->build(),
        "transfer" => url("/master/stock.transferstock/show")->build(),
        "taking"   => url("/master/stock.takingstock/show")->build()
    ],
    // 库存盘点单状态
    "taking_stock_status"          => [
        0  => "未审核",
        1  => "已审核",
        2  => "已完成",
        -1 => "已驳回"
    ],
    // 企业客户预付款状态
    "buyer_pre_money_status"    => [
        0  => "未审核",
        1  => "已审核",
        -1 => "已驳回"
    ],
    // 企业客户预付款支付方式
    "buyer_pre_money_pay_types" => [
        0 => "现金",
        1 => "电汇",
        2 => "支票"
    ],

    // 收款单状态
    "pay_in_status"                => [
        0  => "未审核",
        1  => "已审核",
        -1 => "已驳回"
    ],

    // 收款单支付方式与 com_order_pay_types 同步（除了com_credit挂账）
    "pay_in_types"                 => [
        "com_cash"   => "现金(公司)",
        "com_weixin" => "微信(公司)",
        "com_elec"   => "电汇(公司)",
        "com_cheque" => "支票(公司)",
        "com_pre"    => "预付款(公司)",
    ],

    // 付款单状态
    "pay_out_status"               => [
        0  => "未审核",
        1  => "已审核",
        -1 => "已驳回"
    ],
    // 付款单支付方式
    "pay_out_types"                => [
        "com_cash"   => "现金(公司)",
        "com_weixin" => "微信(公司)",
        "com_elec"   => "电汇(公司)",
        "com_cheque" => "支票(公司)",
        "com_pre"    => "预付款(公司)",
    ],

    "giftcard_use_types" => [
        0 => "金额",
        // 1 => "计次",
    ],

    // 实物卡订单是否可以余额支付
    "is_gift_card_order_use_yue" => true,

    // 实物卡订单是否可以使用优惠券
    "is_gift_card_order_use_coupon" => false,

    // 实物卡使用说明
    "gift_card_remarks" => [
        "用于在小程序平台购水使用",
        "请于绑定有效期到期之前绑定",
        "请于使用有效期到期之前使用",
        "不可与优惠券等其他活动同时使用",
        "输入密码时，不含“-”",
    ],
    // 实物卡绑定页图片（730 × 460px）
    "gift_card_bind_banner" => domain() . "/static/images/gift_card_0.jpg",
    "gift_card_bind_banners" => [
        domain() . "/static/images/gift_card_1000.jpg",
        domain() . "/static/images/gift_card_500.jpg",
        domain() . "/static/images/gift_card_200.jpg",
        domain() . "/static/images/gift_card_100.jpg",
        domain() . "/static/images/gift_card_50.jpg",
        domain() . "/static/images/gift_card_b.jpg",
    ],

    // 客户类型
    "froms" => [
        'mini'    => '小程序',
        'company' => '企业',
        'partner' => '经销商',
    ],

    // 插件中心网址
    'addoncenter_url' => 'https://kdk.yaodianma.com',
    // 'addoncenter_url' => 'https://direct-pup-light.ngrok-free.app',

];