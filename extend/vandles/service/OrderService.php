<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\helper\Str;
use think\Model;
use think\Paginator;
use vandles\lib\FileLock;
use vandles\lib\pay\WxPay;
use vandles\lib\pay\WxShipping;
use vandles\lib\QrCode;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\GoodsModel;
use vandles\model\OrderDeliverModel;
use vandles\model\OrderModel;
use vandles\model\OrderSubModel;
use vandles\model\UserInfoModel;
use WeChat\Pay;

/**
 * @package vandles\service
 *
 */
class OrderService extends BaseService {
    protected static $instance;

    const TAKE_TYPE_SELF    = 0;
    const TAKE_TYPE_DELIVER = 1;

    const ORDER_STATUS_UNPAY      = 0;
    const ORDER_STATUS_DELIVERING = 1;
    const ORDER_STATUS_FINISHED   = 2;
    const ORDER_STATUS_REFUND     = 9;

    const ORDER_DELIVER_STATUS_NOT = 0; // 配送状态：待配货
    const ORDER_DELIVER_STATUS_ING = 1; // 配送状态：配送中
    const ORDER_DELIVER_STATUS_OK  = 2; // 配送状态：已配送

    const REFUND_APPLY_STATUS_UNREFUND       = 0;
    const REFUND_APPLY_STATUS_REFUNDING      = 1;
    const REFUND_APPLY_STATUS_REFUNDFINISHED = 2;
    const REFUND_APPLY_STATUS_REFUNDFAIL     = 3;

    // 微信发货订单状态, (1) 待发货；(2) 已发货；(3) 确认收货；(4) 交易完成；(5) 已退款。
    const WX_ORDER_STATE_NOT_SHIPPED = WxShipping::ORDER_STATE_NOT_SHIPPED;
    const WX_ORDER_STATE_SHIPPED     = WxShipping::ORDER_STATE_SHIPPED;
    const WX_ORDER_STATE_RECEIVED    = WxShipping::ORDER_STATE_RECEIVED;
    const WX_ORDER_STATE_FINISHED    = WxShipping::ORDER_STATE_FINISHED;
    const WX_ORDER_STATE_REFUNDED    = WxShipping::ORDER_STATE_REFUNDED;

    const PAY_TYPE_YUE        = "yue"; // 余额支付
    const PAY_TYPE_WEIXIN     = "weixin"; // 微信支付
    const PAY_TYPE_GIFTCARD   = "giftcard"; // 实物卡支付
    const PAY_TYPE_COM_CASH   = "com_cash"; // 现金支付(企业)
    const PAY_TYPE_COM_WEIXIN = "com_weixin"; // 微信支付(企业)
    const PAY_TYPE_COM_PRE    = "com_pre"; // 预付款支付(企业)

    public static function buildTableData(&$data) {
        $payTypes      = array_merge(config("a.order_pay_types"),config("a.com_order_pay_types"));
        $refundStatus  = config("a.order_refund_status");
        $deliverStatus = config("a.order_deliver_status");
        $status        = config("a.order_status_show");
        $takeTypes     = config("a.order_take_types");
        $stations      = OrderService::instance()->getStationOptsByOrder();

        foreach ($data as $k => &$item) {
            $item['pay_type_txt']       = $payTypes[$item['pay_type']]??'';
            $item['refund_status_txt']  = $refundStatus[$item['refund_status']]??'';
            $item['deliver_status_txt'] = $deliverStatus[$item['deliver_status']]??'';
            $item['status_txt']         = $status[$item['status']]??'';
            $item['take_type_txt']      = $takeTypes[$item['take_type']]['text']??'';
            $item['station_gid_txt']    = $stations[$item['station_gid']]['title'] ?? '';

            if (isset($data[$k - 1]) && $item['sn'] == $data[$k - 1]['sn']) {
//                $item['sn']                 = "";
                $item['create_at']          = "";
                $item['take_type_txt']      = "";
                $item['station_gid_txt']    = "";
                $item['take_district']      = "";
                $item['pay_at']             = "";
                $item['pay_type_txt']       = "";
                $item['goods_amount']       = "";
                $item['discount_amount']    = "";
                $item['real_amount']        = "";
                $item['deliver_amount']     = "";
                $item['pay_amount']         = "";
                $item['real_deduct']        = "";
                $item['give_deduct']        = "";
                $item['invoice_amount']     = "";
                $item['invoice_email_at']   = "";
                $item['refund_status_txt']  = "";
                $item['deliver_status_txt'] = "";
                $item['status_txt']         = "";
            }
        }
    }


    public static function instance(): OrderService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $datas = [];

//        $this->getModel()->saveAll($datas);
//        $list = OrderService::instance()->getList();
//        dd($list->toArray());
    }

    /**
     * 生成订单编码
     * @return string
     */
    public function genSn($refund = "") {
        $prefix = "ORDER";
        if ($refund) $prefix = "REFUND";
        return $prefix . strtoupper(Str::random(15));
    }

    /**
     * @return BaseSoftDeleteModel|OrderModel
     */
    public function getModel() {
        return OrderModel::mk();
    }

    public function bindList(&$data) {
        foreach ($data as &$item) {
            $this->bindOne($item);
        }
    }

    public function bindOne(&$data, $buyers = null) {
        if (isset($data['take_type'])) isset($data['take_type']) && $data['take_type_txt'] = config("a.order_take_types." . $data['take_type'] . ".text");
        isset($data['from']) && $data['from_txt'] = config("a.froms." . $data['from']);
        $types = array_merge(config("a.order_pay_types"), config("a.com_order_pay_types"));
        isset($data['pay_type']) && $data['pay_type_txt'] = $types[$data['pay_type']]??'未知';
        isset($data['status']) && $data['status_txt'] = config("a.order_status_show." . $data['status']);

        if(empty($buyers)){
            if($data['from'] == "company"){
                $buyers = CustomerService::instance()->getModel()->where("gid", $data['openid'])->column("id,gid,title", "gid");
            }elseif ($data['from'] == "partner"){
                $buyers = WaterStationService::instance()->getListByGids([$data['openid']], 'id,gid,title', 'gid');
            }elseif ($data['from'] == "mini"){
                $buyers = UserInfoService::instance()->getUserInfoByOpenids([$data['openid']], 'id,openid gid, nickname title', 'openid');
            }
        }
        if(isset($data['openid'])) $data['openid_txt'] = $buyers[$data['openid']]['title'] ?? "";
    }

    public function getOneBySn($sn, $field = "*") {
        $data = $this->getModel()->where("sn", $sn)
            ->field($field)
            ->find();
        $this->bindOne($data);
        return $data;
    }

    /**
     * 解析购物车列表
     * 商品实付总金额(real_amount) = 商品总金额(goods_amount) - 优惠金额(discount_amount)
     * 支付总金额(pay_amount) = 商品实付总金额(real_amount) + 配送总金额(deliver_amount)
     * @param $cartList
     */
    public function parseCartList($cartList, $take_type = OrderService::TAKE_TYPE_SELF, $address = null, $coupon_gid = null) {
        $goods_total        = 0; // 商品总数量
        $goods_amount       = 0; // 商品总金额
        $discount_amount    = 0; // 优惠总金额
        $real_amount        = 0; // 商品实付总金额
        $deliver_amount     = 0; // 运费总金额
        $deliver_amount_old = 0; // 运费总金额(免之前的)
        $free_deliver_txt   = ""; // 免运费文字
        $pay_amount         = 0; // 支付总金额

        $subs          = [];
        $errDeliver    = []; // 是否达到配送条件
        $isDeliver     = false;
        $hasGoodsType0 = false; // 有普通商品
        $hasGoodsType2 = false; // 有实物卡商品
        foreach ($cartList as $key => $item) {
            if (empty($item['is_checked'])) continue;

            if ($item['goods_type'] == 0) $hasGoodsType0 = true;
            elseif ($item['goods_type'] == 2) $hasGoodsType2 = true;
            // 如果同时为true，说明普通商品 与 实物卡商品同时存在，这是不可以的
            if ($hasGoodsType0 && $hasGoodsType2) VException::runtime("普通商品 与 实物卡商品不能一起下单，请调整下单商品");


            if ($item['number'] < 0) VException::runtime("商品：{$item['name']} 购买数量不能为负数");

            // 最早的简单库存检查
            // if ($item['number'] > $item['stock']) VException::runtime("商品：{$item['name']} 购买数量超出库存");
            // 最新的库存松本，库存严格模式，检查商品库存（但是在这里，因为用户还没有选择是自动还是配送，所以无法获取仓库信息（即$depot_gid），故任何时候都不检查，注释如下代码）
            // if(ConfigService::instance()->isStockStrict()){
            //     GoodsStockService::instance()->checkStock($subs, $depot_gid);
            // }

            if ($take_type == $this::TAKE_TYPE_DELIVER && $item['number'] < $item['min_buy_number']) {
                $errDeliver[] = "商品：{$item['name']} 购买数量小于起送量：{$item['min_buy_number']}";
            } elseif ($take_type == $this::TAKE_TYPE_DELIVER) {
                if (!$isDeliver) $isDeliver = true;
            }

            $subs[] = [
                "goods_sn"             => $item['sn'],
                "goods_name"           => $item['name'],
                "goods_cover"          => $item['cover'],
                "goods_unit"           => $item['unit'],
                "goods_min_buy_number" => $item['min_buy_number'],
                "goods_self_price"     => $item['self_price'],
                "goods_deliver_fee"    => $item['deliver_fee'],
                "goods_market_price"   => $item['market_price'],
                "goods_stock"          => $item['stock'],
                "goods_type"           => $item['goods_type'], // 0: 普通商品，1：原材料，2：实物卡商品

                "goods_amount" => $item['amount'],
                "goods_number" => $item['number'],
            ];

            $goods_amount = bcadd($goods_amount, $item['amount'], 2);
            $goods_total  += $item['number'];
        }

        if (count($subs) == 0) VException::throw("商品列表不能为空");

        // 如果自提，配送货为0元
        if ($take_type == $this::TAKE_TYPE_SELF) { // 自提
            $deliver_amount     = 0;
            $deliver_amount_old = 0;
            $free_deliver_txt   = "";
        } elseif ($take_type == $this::TAKE_TYPE_DELIVER) { // 配送
            // 根据商品计算配送费，后期会改进算法，比如：按配送地址计算
            [$deliver_amount, $deliver_amount_old] = $this->calculateDeliverAmount($subs, $address); // 计算配送费

            $money            = ConfigService::instance()->getAppConfig("free_deliver_money");
            $free_deliver_txt = "(运费满{$money}元";
            if ($deliver_amount_old > 0) {
                $free_deliver_txt .= "，已减免" . floatval($deliver_amount_old) . "元)";
            } else {
                if (+$deliver_amount == 0) {
                    $free_deliver_txt = "(免服务费)";
                } elseif (+$deliver_amount < $money) {
                    $delta            = floatval(bcsub($money, $deliver_amount, 2));
                    $free_deliver_txt .= "免服务费，还差{$delta}元)";
                } else {
                    $free_deliver_txt .= "，免服务费)";
                }
            }

            // 检查是否配送
            if (!$isDeliver) {
                VException::throw($errDeliver[0] ?? "商品不满足起送条件");
            }
        }

        if ($hasGoodsType2 && !empty($coupon_gid) && !config("a.is_gift_card_order_use_coupon")) VException::runtime("实物卡商品不能使用优惠券");

        // 使用优惠券优惠金额
        $userCouponService = UserCouponService::instance();
        if (!empty($coupon_gid)) {
            $openid = request()->openid();
            $coupon = $userCouponService->isUsable($coupon_gid, $openid, $goods_amount);
            // 金额优先，折扣次之
            if ($coupon->money > 0) $discount_amount = $coupon->money;
            elseif ($coupon->discount > 0) $discount_amount = bcmul($goods_amount, $coupon->discount / 100, 2);
            $coupon = $coupon->toArray();
        } else {
            $coupon = null;
        }

        // 商品实付总金额 = 商品总金额 - 优惠金额
        if ($goods_amount < $discount_amount) {
            $real_amount     = 0;
            $discount_amount = $goods_amount;
        } else {
            $real_amount = bcsub($goods_amount, $discount_amount, 2);
        }
        // 支付总金额 = 商品实付总金额 + 配送总金额
        $pay_amount = bcadd($real_amount, $deliver_amount, 2);

        if ($real_amount < 0) VException::runtime("商品实付总金额不能为负数");
        if ($pay_amount < 0) VException::runtime("支付总金额不能为负数");

        $order         = [
            "goods_total"        => $goods_total,
            "goods_amount"       => $goods_amount,
            "discount_amount"    => $discount_amount,
            "real_amount"        => $real_amount,
            "deliver_amount"     => $deliver_amount,
            "deliver_amount_old" => $deliver_amount_old,
            "free_deliver_txt"   => $free_deliver_txt,
            "pay_amount"         => $pay_amount,
            "take_type"          => $take_type,
            "coupon"             => $coupon,
            "goods_type"         => $subs[0]['goods_type'], // 0: 普通商品，1：原材料，2：实物卡商品
        ];
        $order['subs'] = $subs;
        return $order;
    }

    /**
     * 根据客户的购买信息，得到订单信息
     * $buyInfo = [
     *  from,
     *  take_type,
     *  sn,
     *  number,
     * ]
     *
     * @param array $buyInfo
     * @param $openid
     * @return array
     */
    public function getOrderInfoFromCart(array $buyInfo, $openid, $address = null, $coupon_gid = null) {
        $cartService  = CartService::instance();
        $goodsService = GoodsService::instance();
        $from         = $buyInfo['from'];
        if ($from == "cart") {
            $cart = $cartService->getOneByOpenid($openid, "gid,goods_snap");
            $cartService->bind($cart);
            $cartList = $cart['list'];
        } elseif ($from == "goods") {
            if ((empty($buyInfo['goods_sn']) || empty($buyInfo['goods_number']))) VException::runtime("商品购买数量不存在");
            $goods = $goodsService->getShowOneBySn($buyInfo['goods_sn'], "id,sn,name,cover,unit,min_buy_number,self_price,deliver_fee,market_price,stock,goods_type");
            if (empty($goods)) VException::throw("商品不存在或已下架：" . $goods['name']);
            $goodsService->bindCart($goods, $buyInfo['goods_number']);
            $cartList = [$goods->toArray()];
        } else VException::runtime("参数超出取值范围");
        $orderInfo           = $this->parseCartList($cartList, $buyInfo['take_type'], $address, $coupon_gid);
        $orderInfo['openid'] = $openid;
        $orderInfo['status'] = $this::ORDER_STATUS_UNPAY; // 待支付
        return $orderInfo;
    }

    /**
     * 创建订单
     * 1. 创建主单
     * 2. 创建明细单
     * 3. 核销优惠券（如果有）
     * 4. 减商品库存
     */
    public function createFromConfirm(array $orderInfo): Model {
        $subs = $orderInfo['subs'];
        unset($orderInfo['subs']);
        $sn              = $this->genSn();
        $orderInfo['sn'] = $sn;
        if (!empty($orderInfo['coupon'])) {
            $orderInfo['coupon_gid'] = $orderInfo['coupon']['gid'];
            unset($orderInfo['coupon']);
        }

        Db::startTrans();
        try{
            // 库存严格模式，检查商品库存
            if(ConfigService::instance()->isStockStrict()) {
                GoodsStockService::instance()->checkStock($subs, $orderInfo['depot_gid']);
            }

            $order        = $this->create($orderInfo);
            $goodsService = GoodsService::instance();
            foreach ($subs as &$sub) {
                $sub['order_sn'] = $orderInfo['sn'];
                // todo vandles 增加销售数量
                /*
                $goodsService->updateBySn($sub['goods_sn'], [
                    'sale_number' => Db::raw("sale_number + {$sub['goods_number']}")
                ]);*/
            }
            $subs = $this->getSubModel()->saveAll($subs);

            $order['subs'] = $subs;
            Db::commit();
        }catch(Exception $e){
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return $order;
    }

    private function getSubModel(): OrderSubModel {
        return OrderSubModel::mk();
    }

    public function payOrderWithYue($sn, $openid) {
        $userInfoService = UserInfoService::instance();
        $order           = $this->getOneBySn($sn);

        // $userInfo = $userInfoService->getOneByOpenid($openid, "openid, money_expire_at");
        // if (time() > strtotime($userInfo->money_expire_at)) VException::runtime("余额已过期，请充值");

        if (!$this->checkBeforePay($order, $openid)) return false;


        Db::transaction(function () use ($order, $openid, $userInfoService) {
            // 1. 修改用户余额
            $res1 = $userInfoService->reduceMoney($openid, $order['pay_amount'], UserMoneyLogService::MONEY_LOG_TYPE_ORDER, $order['sn'], "", "订单支付");

            // 2. 支付成功操作
            $res2 = $this->payOrderSuccess($order, "yue", $openid);
//            d($res1);
//            dd($res2);
        });


        return true;
    }

    /**
     * 实物卡支付
     * @param $orderSn
     * @param $giftCardSns
     * @param $openid
     * @return bool
     */
    public function payOrderWithGiftCard($orderSn, $giftCardSns, $openid) {
        $giftCardService = GiftcardService::instance();
        $order = $this->getOrderBySn($orderSn, "id,sn,openid,status,pay_amount,goods_type,pay_type,from,depot_gid");
        if(empty($order)) VException::throw("订单不存在");
        if($order['pay_amount'] <= 0) VException::throw("订单支付金额必须大于0");

        if (!$this->checkBeforePay($order, $openid)) return false;
        if(!$giftCardService->checkGiftCard($openid, $giftCardSns, $order->toArray())) return false;

        /**
         * 1. 实物卡扣减余额
         * 2. 订单支付成功操作
         */
        $cards = $giftCardService->getListBySns($giftCardSns, "sn,bound_openid,has,init,use_type,status,useful_expire_at,last_use_at");
        $payAmount = $order['pay_amount'];
        $lock = FileLock::instance('expend_' . $orderSn);
        if (!$lock->lock()) {
            VException::throw("系统繁忙，请稍后访问！");
        }
        // 开发测试用
        // $cards = [
        //     ['sn'=>"549120920250224640", 'has'=>50],
        //     ['sn'=>"549120920313139200", 'has'=>25],
        //     ['sn'=>"549120920187310080", 'has'=>50],
        // ];
        // $payAmount = 56;
        Db::startTrans();
        try{
            // 1. 实物卡扣减余额
            foreach ($cards as $card){
                if($card['has'] >= $payAmount){
                    $delta = $payAmount;
                }else{
                    $delta = $card['has'];
                }
                $giftCardService->expend($card['sn'], $delta, $orderSn);
                $payAmount = $payAmount - $delta;
                if($payAmount <= 0 ) break;
            }
            if($payAmount > 0) VException::throw("实物卡合计余额不足");

            // 2. 订单支付成功操作
            $res2 = $this->payOrderSuccess($order, "giftcard", $openid);
            Db::commit();
        }catch(\Exception $e){
            Db::rollback();
            VException::throw($e->getMessage());
        }

        return true;
    }



    /**
     * 支付时，检查订单状态及合法性
     * @param $order
     * @param $openid
     * @return true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function checkBeforePay($order, $openid = null) {
        if (is_string($order)) $order = $this->getOneBySn($order);

        if (empty($order)) VException::runtime("订单不存在");
        if (!empty($openid) && $order['openid'] != $openid) VException::runtime("订单不属于当前用户");
        if ($order['status'] != self::ORDER_STATUS_UNPAY) VException::runtime("订单不是未支付状态");
        if ($order['pay_amount'] < $order['real_amount']) VException::runtime("支付金额不能小于商品实付总金额");
        if ($order['pay_amount'] < 0) VException::runtime("支付金额不能小于0");
        if ($order['goods_total'] < 0) VException::runtime("商品总数量不能小于0");
        if ($order['goods_type'] == 2 && !config("a.is_gift_card_order_use_yue")) VException::runtime("实物卡商品不允许使用余额支付");
        if ($order['goods_type'] == 2 && !empty($order['coupon_gid']) && !config("a.is_gift_card_order_use_coupon")) VException::runtime("实物卡商品不能使用优惠券");

        $subs = $this->getSubModel()->where('order_sn', $order['sn'])->select();

        $sns       = array_column($subs->toArray(), "goods_sn");
        $goodsList = GoodsService::instance()->getListBySns($sns, "id,sn,name,cover,unit,min_buy_number,self_price,deliver_fee,market_price,stock", "sn");

        // 库存严格模式，检查商品库存
        if(ConfigService::instance()->isStockStrict()){
            GoodsStockService::instance()->checkStock($subs, $order->depot_gid);
        }

        $errDeliver = []; // 是否达到配送条件
        $isDeliver  = false;
        foreach ($subs as $sub) {
            if (empty($goodsList[$sub['goods_sn']])) VException::runtime("商品：{$sub['goods_sn']} 不存在，或已下架");
            $goods = $goodsList[$sub['goods_sn']];
            if ($order['take_type'] == self::TAKE_TYPE_DELIVER && $sub['goods_number'] < $goods['min_buy_number']) {
                $errDeliver[] = "商品：{$sub['goods_name']} 购买数量小于起订量：{$goods['min_buy_number']}";
            } elseif ($order['take_type'] == self::TAKE_TYPE_DELIVER) {
                if (!$isDeliver) $isDeliver = true;
            }
        }

        if ($order['take_type'] == self::TAKE_TYPE_DELIVER && !$isDeliver) {
            VException::throw($errDeliver[0] ?? "商品不满足起送条件");
        }

        unset($subs);
        unset($order);
        return true;
    }

    /**
     * 全局唯一订单支付成功处理
     * @param $order
     * @param $pay_type
     * @param $openid
     * @param $pay_total
     * @param $transaction_id
     * @return true
     */
    public function payOrderSuccess($order, $pay_type, $openid = null, $pay_total = null, $transaction_id = null) {

        if (is_string($order)) $order = $this->getOneBySn($order);
        if (empty($order)) VException::runtime("订单不存在");
        if ($order['status'] != self::ORDER_STATUS_UNPAY) VException::runtime("订单状态错误");
        if (!empty($openid) && $order['openid'] != $openid) VException::runtime("订单不属于当前用户");
        if ($pay_total !== null && $order['pay_amount'] != $pay_total) VException::runtime("支付金额与订单金额不一致");

        $order->status   = self::ORDER_STATUS_DELIVERING;
        $order->pay_type = $pay_type;
        // 公司挂账不更新支付时间
        if($order->pay_type != 'com_credit') {
            $order->pay_at = now();
        }
        if ($transaction_id) $order->transaction_id = $transaction_id;

        $result = false;
        Db::startTrans();
        try {
            // 1. 保存订单支付信息
            $order->save();

            // 2. 如果是余额支付，拆分余额（处理1000+300这种）
            if($order->pay_type == OrderService::PAY_TYPE_YUE){
                MoneyCardService::instance()->expend($order);
            }

            // 3-1. 出库 & 审核
            $stock = OutStockService::instance()->createSaleByOrderSn($order['sn']);
            $stock = OutStockService::instance()->passOnlyByStockSn($stock['sn']);

            // 3-2. 如果不是挂账（目前没有涉及挂账订单，后期可能会有），自动创建收款单并审核
            if($order->pay_type != 'com_credit'){
                PayInService::instance()->createAndPassByStockSn($stock->sn);
            }

            // 3.3 库存严格模式，减库存
            if(ConfigService::instance()->isStockStrict()){
                GoodsStockService::instance()->updateByOutStockSn($stock['sn']);
            }
            Db::commit();
            $result = true;
        } catch (\Exception $e) {
            Db::rollback();
            error("支付成功后的处理出现异常({$order->sn})：" . $e->getMessage());
            VException::throw($e->getMessage());
        }
        return $result;
    }

    /**
     * 计算配送费
     * @param array $subs
     * @param $address
     * @return array
     */
    private function calculateDeliverAmount(array $subs, $address) {
        $deliver_amount = $deliver_amount_old = 0;
        foreach ($subs as $sub) {
            $amount         = bcmul($sub['goods_deliver_fee'], $sub['goods_number'], 2);
            $deliver_amount = bcadd($deliver_amount, $amount, 2);
        }
//        $free_deliver_money = config("a.free_deliver_money");
        $free_deliver_money = ConfigService::instance()->getAppConfig("free_deliver_money");
        if ($deliver_amount >= $free_deliver_money) {
            $deliver_amount_old = $deliver_amount;
            $deliver_amount     = 0;
        }
        return [$deliver_amount, $deliver_amount_old];
    }

    public function getTabsData() {
        $result = config("a.order_status");
        return array_column($result, "title");
    }

    public function getMyUserOrderTabsDataWithCount($openid) {
        $result             = config("a.order_status");
        $result[0]['count'] = $this->getMyOrderCountByStatus($openid, 0);
        $result[1]['count'] = $this->getMyOrderCountByStatus($openid, 1);
        $result[2]['count'] = $this->getMyOrderCountByStatus($openid, 2);
        $result[3]['count'] = $this->getMyOrderCountByStatus($openid, 9);

        return $result;
    }

    private function getMyOrderCountByStatus(string $openid, int $status) {
        return $this->search(['status' => $status, 'openid' => $openid])->count();
    }

    public function getOrderPageDataByStatus($openid, $status) {
        return $this->search(['status' => $status, 'openid' => $openid])->order("id desc")->paginate();
    }

    public function bindSubs(\think\Paginator &$pageData, $field = "*", $cb = null) {
        $sns = array_column($pageData->items(), "sn");
        if ($field != "*" && !Str::contains($field, "order_sn")) {
            $field .= ",order_sn";
        }
        $subLists = OrderSubService::instance()->getSubsBySns($sns, $field);

        $subs = [];
        foreach ($subLists as $sub) {
            $subs[$sub['order_sn']][] = $sub;
        }
        $openids = array_column($pageData->items(), "pick_by");
        $userInfos = UserInfoService::instance()->getUserInfoByOpenids($openids, "id,openid, IFNULL(NULLIF(realname, ''), nickname) AS username,phone", 'openid');
        foreach ($pageData->items() as &$order) {
            $order['subs']              = $subs[$order['sn']];
            $order['refund_status_txt'] = config("a.order_refund_status." . $order['refund_status']);
            // 字符串，从第 第1位开始，截取到第10位 用3个星号代替
            if ($order['pick_gid']) $order['pick_gid_'] = cstar($order['pick_gid'], 5, 22);
            if ($order['pick_by']){
                $order['pick_by_txt']   = $userInfos[$order['pick_by']]['username'] ?? "";
                $order['pick_by_phone'] = $userInfos[$order['pick_by']]['phone'] ?? "";
            }

            if ($cb) call_user_func($cb, $order);
        }
    }

    public function bindSub(&$order, $field = "*") {
        $order['subs'] = OrderSubService::instance()->getSubsBySn($order['sn'], $field);
    }

    /**
     * 核销（完成）订单
     * $finish_openid == null && $deliverInfo == null: 用户收货
     * $finish_openid != null && $deliverInfo == null: 核销
     * $finish_openid != null && $deliverInfo != null: 配送
     * @param $order
     * @param $finish_openid
     * @return array|mixed|Model|BaseSoftDeleteModel|OrderModel|null
     */
    public function finishOrder($order, $finish_openid = null, $deliverInfo = null) {
        if (is_string($order)) {
            $order = $this->getOneBySn($order, "sn,openid,status,station_gid");
        }
        if (empty($order)) VException::runtime("订单完成失败，订单不存在");
        if ($order->take_type != OrderService::TAKE_TYPE_SELF) VException::runtime("不是自提订单，不允许核销");
        if ($order->status == OrderService::ORDER_STATUS_FINISHED) VException::runtime("订单已完成，不需要重复操作");
        if ($order->status != OrderService::ORDER_STATUS_DELIVERING) VException::runtime("订单完成失败，订单不是配送中状态");

        /*
         * 三种情况
         * $finish_openid == null && $deliverInfo == null: 用户收货
         * $finish_openid != null && $deliverInfo == null: 核销
         * $finish_openid != null && $deliverInfo != null: 配送
         */

        // 核销
        if($finish_openid != null && $deliverInfo == null){
            $userInfo = UserInfoService::instance()->getOneByOpenid($finish_openid, "id,station_gid");
            if($order->station_gid != $userInfo->station_gid) VException::runtime("订单完成失败，自提水站不匹配");
        }


        if (!empty($finish_openid)) {
            if (empty($deliverInfo) && !UserInfoService::instance()->isAuthFinishOrder($finish_openid)) { // 核销
                VException::runtime("订单核销失败，您没有权限核销订单");
            } elseif (!empty($deliverInfo) && !UserInfoService::instance()->isAuthDeliverOrder($finish_openid)) { // 配送
                VException::runtime("订单核销失败，您没有权限配送订单");
            }
            $openid = $finish_openid;
        } else {
            $openid = $order->openid;
        }

        $order->status         = OrderService::ORDER_STATUS_FINISHED;
        $order->deliver_status = OrderService::ORDER_DELIVER_STATUS_OK;
        $order->take_at        = now();
        $order->take_by        = $openid;

        // 保存配送信息
        if (!empty($deliverInfo['urls'])) $order->deliver_images = implode("|", $deliverInfo['urls']);
        if (!empty($deliverInfo['remark'])) $order->deliver_remark = $deliverInfo['remark'];

        $key  = "finish_order_{$order->sn}";
        $lock = FileLock::instance($key);
        if (!$lock->lock()) {
            VException::throw("系统繁忙，请稍后重试！");
        }
        $order->save();
        return $order;
    }

    public function finishOrder_dev($order, $finish_openid = null, $deliverInfo = null) {
        if (is_string($order)) {
            $order = $this->getOneBySn($order, "sn,openid,status");
        }
        if (empty($order)) VException::runtime("订单完成失败，订单不存在");
//        if ($order->take_type != OrderService::TAKE_TYPE_SELF) VException::runtime("不是自提订单，不能核销");
        if ($order->status == OrderService::ORDER_STATUS_FINISHED) VException::runtime("订单已完成，不需要重复操作");
        if ($order->status != OrderService::ORDER_STATUS_DELIVERING) VException::runtime("订单完成失败，订单不是配送中状态");

        /*
         * 三种情况
         * $finish_openid == null && $deliverInfo == null: 用户收货
         * $finish_openid != null && $deliverInfo == null: 核销
         * $finish_openid != null && $deliverInfo != null: 配送
         */

        if (!empty($finish_openid)) {
            if (empty($deliverInfo) && !UserInfoService::instance()->isAuthFinishOrder($finish_openid)) {
                VException::runtime("订单核销失败，您没有权限核销订单");
            } elseif (!empty($deliverInfo) && !UserInfoService::instance()->isAuthDeliverOrder($finish_openid)) {
                VException::runtime("订单核销失败，您没有权限配送订单");
            }
            $openid = $finish_openid;
        } else {
            $openid = $order->openid;
        }

        $order->status         = OrderService::ORDER_STATUS_FINISHED;
        $order->deliver_status = OrderService::ORDER_DELIVER_STATUS_OK;
        $order->take_at        = now();
        $order->take_by        = $openid;

        // 保存配送信息
        if (!empty($deliverInfo['urls'])) $order->deliver_images = implode("|", $deliverInfo['urls']);
        if (!empty($deliverInfo['remark'])) $order->deliver_remark = $deliverInfo['remark'];

        $key  = "finish_order_{$order->sn}";
        $lock = FileLock::instance($key);
        if (!$lock->lock()) {
            VException::throw("系统繁忙，请稍后重试！");
        }
        $order->save();
        $lock->unlock();
        return $order;
    }

    public function finishOrder_old1($order, $finish_openid = null, $isDeliver = false) {
        if (is_string($order)) {
            $order = $this->getOneBySn($order, "sn,openid,status");
        }
        if (empty($order)) VException::runtime("订单完成失败，订单不存在");
//        if ($order->take_type != OrderService::TAKE_TYPE_SELF) VException::runtime("不是自提订单，不能核销");
        if ($order->status == OrderService::ORDER_STATUS_FINISHED) VException::runtime("订单已完成，不需要重复操作");
        if ($order->status != OrderService::ORDER_STATUS_DELIVERING) VException::runtime("订单完成失败，订单不是配送中状态");

        if (!empty($finish_openid)) {
            if (!$isDeliver && !UserInfoService::instance()->isAuthFinishOrder($finish_openid)) {
                VException::runtime("订单核销失败，您没有权限核销订单");
            } elseif ($isDeliver && !UserInfoService::instance()->isAuthDeliverOrder($finish_openid)) {
                VException::runtime("订单核销失败，您没有权限配送订单");
            }
            $openid = $finish_openid;
        } else {
            $openid = $order->openid;
        }

        $order->status  = OrderService::ORDER_STATUS_FINISHED;
        $order->take_at = now();
        $order->take_by = $openid;
        $order->save();
        return $order;
    }

    /**
     * 已支付订单的数量
     * @param $openid
     * @return int
     */
    public function getPaidOrderCount($openid) {
        return $this->getModel()->where('openid', $openid)->whereIn('status', [OrderService::ORDER_STATUS_DELIVERING, OrderService::ORDER_STATUS_FINISHED])->count();
    }

    public function mQuery() {
        return $this->getModel()::mQuery();
    }

    public function refundApply($order, $reason) {
        if (is_string($order)) {
            $order = $this->getOneBySn($order, "sn,openid,status,pay_amount,pay_at");
        }
        if (empty($order)) VException::runtime("退款申请失败，订单不存在");
        if ($order->status == OrderService::ORDER_STATUS_UNPAY) VException::runtime("退款申请失败，订单未支付");
        if(empty($order['pay_at'])) VException::throw("退款申请失败，订单未支付");

        if ($order->status == OrderService::ORDER_STATUS_REFUND) VException::runtime("退款申请失败，订单已申请退款");

        $order->refund_before_status = $order->status;
        $order->status               = OrderService::ORDER_STATUS_REFUND;
        $order->refund_status        = OrderService::REFUND_APPLY_STATUS_REFUNDING;
        $order->refund_reason        = $reason;
        $order->refund_apply_at      = now();
        $order->save();
        return $order;
    }
    public function refundApply_dev($order, $reason) {
        if (is_string($order)) {
            $order = $this->getOneBySn($order, "sn,openid,status,pay_amount,pay_at");
        }
        if (empty($order)) VException::runtime("退款申请失败，订单不存在");
        if ($order->status == OrderService::ORDER_STATUS_UNPAY) VException::runtime("退款申请失败，订单未支付");
        if(empty($order['pay_at'])) VException::throw("退款申请失败，订单未支付");

        if ($order->status == OrderService::ORDER_STATUS_REFUND) VException::runtime("退款申请失败，订单已申请退款");

        dd($order);
        $order->refund_before_status = $order->status;
        $order->status               = OrderService::ORDER_STATUS_REFUND;
        $order->refund_status        = OrderService::REFUND_APPLY_STATUS_REFUNDING;
        $order->refund_reason        = $reason;
        $order->refund_apply_at      = now();
        $order->save();
        return $order;
    }

    /**
     * 订单退款
     * 1. 退钱给用户，原路返回
     * 2. 退款成功操作，包括：修改订单状态、回退优惠券
     * @param $order
     * @param $refund_money
     * @param $refund_reason
     * @param $refund_feedback_msg
     * @throws \Exception
     */
    public function refund($order, $refund_money, $refund_reason, $refund_feedback_msg=null) {
        if (is_string($order)) $order = $this->getOneBySn($order);
        if (empty($order)) VException::throw("订单不存在");
        if ($order['status'] == OrderService::ORDER_STATUS_UNPAY) VException::runtime("订单未支付");
        if ($order['status'] != OrderService::ORDER_STATUS_REFUND) VException::runtime("订单未申请退款");
        if(empty($order['pay_at'])) VException::throw("订单未支付");

        if ($order['refund_status'] == OrderService::REFUND_APPLY_STATUS_UNREFUND) VException::runtime("订单未申请退款");
        if ($order['refund_status'] == OrderService::REFUND_APPLY_STATUS_REFUNDFINISHED) VException::runtime("订单已退款");
        if ($order['refund_status'] == OrderService::REFUND_APPLY_STATUS_REFUNDFAIL) VException::runtime("订单退款已驳回");
        Db::startTrans();
        try{
            // 1. 支付款原路退回
            if ($refund_money > $order->pay_amount) VException::runtime("退款金额不能大于支付金额");
            if ($order->pay_type == "yue") {
                // 小程序余额支付，原路退回
                $this->refundYue($order, $refund_money, $refund_reason);
            } elseif ($order->pay_type == "weixin") {
                // 小程序微信支付，原路退回
                $this->refundWeixin($order, $refund_money, $refund_reason);
            } elseif ($order->pay_type == "giftcard") {
                // 小程序实付卡支付，原路退回
                $this->refundGiftcard($order, $refund_money, $refund_reason);
            } elseif ($order->pay_type == "com_pre") {
                // 企业预付款支付，退回到企业账户
                $this->refundComPre($order, $refund_money);
            } elseif (in_array($order->pay_type, ['com_cash','com_weixin','com_elec','com_cheque'])){
                // 线下支付，线下退款，此处不做操作
                // dd($order->pay_type);
            } else {
                VException::runtime("退款失败，不支持的支付方式");
            }

            // 2. 订单退款成功
            $this->refundSuccess($order, $refund_feedback_msg);

            Db::commit();
        }catch(\Exception $e){
            Db::rollback();
            VException::throw($e->getMessage());
        }
    }

    /**
     * 退款成功操作 包括：修改订单状态、回退优惠券
     * @param $order
     */
    public function refundSuccess(OrderModel $order, $refund_feedback_msg = null) {
        Db::startTrans();
        try{
            $order->refund_status       = OrderService::REFUND_APPLY_STATUS_REFUNDFINISHED;
            $order->refund_feedback_msg = $refund_feedback_msg;
            $order->refund_feedback_at  = now();
            $order->save();
            // 退回优惠券
            if ($order->coupon_gid) {
                UserCouponService::instance()->unWriteOff($order->coupon_gid);
            }
            // todo vandles 销售数量减回
//            $subs = OrderSubService::instance()->getSubsBySn($order->sn, "id,goods_sn, goods_number");
//            $goodsService        = GoodsService::instance();
//            foreach ($subs as $sub) {
//                $goodsService->updateBySn($sub['goods_sn'], [
//                    'sale_number' => Db::raw("sale_number - {$sub['goods_number']}")
//                ]);
//            }

            // 生成退货入库单 && 审核
            $stock = InStockService::instance()->createRefundByOrderSn($order['sn']);
            $stock = InStockService::instance()->passOnlyByStockSn($stock['sn']);
            // 生成付款单 && 审核
            PayOutService::instance()->createAndPassByStockSn($stock->sn);

            //  库存严格模式，退款时，商品库存原路返回
            if(ConfigService::instance()->isStockStrict()){
                GoodsStockService::instance()->updateByInStockSn($stock['sn']);

            }
            Db::commit();
        }catch(\Exception $e){
            Db::rollback();
            VException::throw($e->getMessage());
        }
    }

    /**
     * 微信退款
     * @param $order
     * @param $refund_money
     * @param $refund_reason
     * @return void
     * @throws \think\admin\Exception
     */
    private function refundWeixin($order, $refund_money, $refund_reason) {
        $wxPay = WxPay::instance(ConfigService::instance()->getConfigMini());
        $opts  = [
            'out_trade_no'  => $order->sn,
            'out_refund_no' => $this->genSn("refund"),
            'total_fee'     => $order->pay_amount,
            'refund_fee'    => $refund_money,
            'refund_desc'   => $refund_reason
        ];
        $res   = $wxPay->refund($opts);
        $res   = $wxPay->refundQueryByRefundId($res['refund_id']);
        $count = 0;
        sleep(1);
        while ($count++ < 15 && $res['refund_status_0'] == "PROCESSING") {
            $res = $wxPay->refundQueryByRefundId($res['refund_id_0']);
            sleep(1);
        }
        $res = $wxPay->refundQueryByRefundId($res['refund_id_0']);

        if ($res['refund_status_0'] != "SUCCESS") {
            VException::runtime("退款超时，请稍后再试");
        }
    }

    /**
     * 订单退款（余额支付的）
     * @param $order
     * @param $refund_money
     * @param $refund_reason
     * @return void
     */
    private function refundYue($order, $refund_money, $refund_reason) {
        Db::startTrans();
        try {
            // 用户余额退回
            UserInfoService::instance()->incMoney($order->openid, $refund_money, UserMoneyLogService::MONEY_LOG_TYPE_REFUND, $order->sn, "", $refund_reason);
            // 退回拆分金额
            MoneyCardService::instance()->refund($order);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 实物卡支付订单退款
     * @param $order
     * @param $refund_money
     * @param $refund_reason
     * @return void
     * @throws Exception
     */
    private function refundGiftcard($order, $refund_money, $refund_reason=null) {
        VException::throw("实物卡支付暂不支持退款");
    }

    /**
     * 企业预付订单退款
     * @param $order
     * @param $refund_money
     * @return true
     * @throws Exception
     */
    private function refundComPre($order, $refund_money) {
        if($order->from == 'company'){
            CustomerService::instance()->incMoney($order->openid, $refund_money, 'refund', $order->sn);
        }elseif($order->from == 'partner'){
            WaterStationService::instance()->incMoney($order->openid, $refund_money, 'refund', $order->sn);
        }
        return true;
    }

    /**
     * 得到超时未支付订单
     * @return array|\think\Collection|BaseSoftDeleteModel[]|OrderModel[]
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getOrderListUnpayTimeout() {
        $n    = config("a.order_auto_clean_sec");
        $time = time() - $n;

        $list = $this->getModel()->where("status", OrderService::ORDER_STATUS_UNPAY)
            ->where("create_at", "<", date("Y-m-d H:i:s", $time))
            ->select();
        return $list;
    }

    /**
     * 清理n时间(秒)之内未支付的订单(仅清理小程序订单)
     * @return int
     */
    public function autoClean() {
        return $this->clean();
    }

    /**
     * 清理n时间(秒)之内未支付的订单(仅清理小程序订单)
     * @return int
     */
    public function clean() {
        $n     = config("a.order_auto_clean_sec");
        $time  = time() - $n;
        $list  = $this->getModel()->where("status", OrderService::ORDER_STATUS_UNPAY)
            ->where("from", 'mini')
            ->where("create_at", "<", date("Y-m-d H:i:s", $time))
            ->select();
        $count = count($list);
        if ($count == 0) {
            return $count;
        }

        $list = $list->toArray();
        $this->doCancel($list);

        alert("清理未支付订单: {$count} 个(b)");
        return $count;
    }

    public function cancelOrder($order, $remark="") {
        if (is_string($order)) $order = $this->getOneBySn($order);
        if (!$order) VException::runtime("订单不存在");
        if ($order->status != $this::ORDER_STATUS_UNPAY) VException::runtime("订单状态不允许取消");
        $list = [$order];
        $this->doCancel($list, $remark);
        return 1;
    }

    private function doCancel($list, $remark="") {
        Db::startTrans();
        try {
            // 1. 恢复库存(下单时不减库存，所以，取消时也不恢复库存)
            // $sns          = array_column($list, 'sn');
            // $subs         = OrderSubService::instance()->getSubsBySns($sns);
            // $goodsService = GoodsService::instance();
            // foreach ($subs as $sub) {
            //     $goodsService->getModel()->where("sn", $sub->goods_sn)->update([
            //         "stock" => DB::raw("stock + {$sub->goods_number}")
            //     ]);
            // }
            // 2. 恢复优惠券
            $couponService = UserCouponService::instance();
            foreach ($list as $order) {

                if ($order['coupon_gid']) {
                    $couponService->unWriteOff($order['coupon_gid']);
                }
                // 3. 删除订单
                $data = ["deleted" => 1];
                // 追加备注
                if($remark){
                    $data['remark'] = $order['remark']  . ";" . $remark;
                }
                $this->getModel()->where('sn', $order['sn'])->update($data);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            error("清理未支付订单: 失败: " . $e->getMessage());
//            VException::runtime("清理未支付订单: 失败: " . $e->getMessage());
        }
        return count($list);
    }

    public function getFinishOrderQrcode($sn) {
        $res = QrCode::instance()->createQrCode(encode($sn), true);
        return $res;
    }

    /**
     * 清理n时间(秒)之内支付、未收货的、配送订单
     * @return int
     */
    public function autoTake() {
        $n     = config("a.order_auto_take_sec");
        $time  = time() - $n;
        $list  = $this->getModel()->where("status", OrderService::ORDER_STATUS_DELIVERING)
            ->where("pay_at", "<", date("Y-m-d H:i:s", $time))
            ->where('take_type', $this::TAKE_TYPE_DELIVER)
            ->select();
        $count = count($list);
        if ($count == 0) {
            return $count;
        }

        $list = $list->toArray();
        $this->doTake($list);

        alert("自动收货配送订单: {$count} 个(b)");
        return $count;
    }

    public function doTake(array $list) {
//        Db::startTrans();
        try {
            foreach ($list as $order) {
                $this->getModel()->where('sn', $order['sn'])->update([
                    "status"         => $this::ORDER_STATUS_FINISHED,
                    "deliver_status" => $this::ORDER_DELIVER_STATUS_OK,
                    "take_at"        => now(),
                    "take_by"        => "auto"
                ]);
            }
//            Db::commit();
        } catch (Exception $e) {
//            Db::rollback();
            error("未收货订单收货操作: 失败: " . $e->getMessage());
            VException::runtime("未收货订单收货操作: 失败: " . $e->getMessage());
        }
        return count($list);
    }

    /**
     * @param $sn
     * @param $openid
     * @param $isAuth boolean 是否有查看权限
     * @return array|mixed|Model|BaseSoftDeleteModel|OrderModel|null
     * @throws Exception
     */
    public function getDetailBySn($sn, $openid, $isAuth = false) {
        $order = $this->getOneBySn($sn);
        if (empty($order)) VException::throw("订单不存在");

        if (!$isAuth && $order->openid != $openid) VException::throw("订单不属于当前用户");
//        if ($isFinish && !UserInfoService::instance()->isAuthFinishOrder($openid)) VException::throw("没有核销权限");

        $this->bindSub($order);

        $station = $address = $coupon = null;
        if ($order->take_type == OrderService::TAKE_TYPE_SELF) {
            $station = WaterStationService::instance()->getOneByGid($order->station_gid, "gid,title,province,city,district,detail,link_name,link_phone,open_time,remark,lng,lat");
        } else if ($order->take_type == OrderService::TAKE_TYPE_DELIVER) {
            $address = UserAddressService::instance()->getOneByGid($order->address_gid, "gid,openid,province,city,district,detail,link_name,link_phone");
        }
        if ($order->coupon_gid) {
            $coupon = UserCouponService::instance()->getByGid($order->coupon_gid, "gid,title");
        }
        $order->station = $station;
        $order->address = $address;
        $order->coupon  = $coupon;

        if ($order->deliver_images) $order->deliver_images = explode("|", $order->deliver_images);

        return $order;
    }

    public function getOrderBySn(string $sn, string $field = "*") {
        return $this->getModel()->where("sn", $sn)->field($field)->find();
    }

    /**
     * 调用微信发货接口
     * @param string $outTradeNo ORDER开头的为订单，否则为充值订单
     * @throws Exception
     */
    public function wxShippingByOutTradeNo(string $outTradeNo) {
        $wxShipping = WxShipping::instance(ConfigService::instance()->getConfigMini());

        if (Str::startsWith($outTradeNo, "ORDER")) {
            $order = $this->getOrderBySn($outTradeNo, "sn,openid,take_type");
            if (!$order) VException::throw("订单不存在");
            $item_desc = "饮用水";
            if ($order->take_type == OrderService::TAKE_TYPE_SELF) {
                $logistics_type = $wxShipping::LOGISTICS_TYPE_SELF;
            } else {
                $logistics_type = $wxShipping::LOGISTICS_TYPE_DELIVER;
            }
        } else {
            $order = UserMoneyLogService::instance()->getLogByTargetGid($outTradeNo, "target_gid,openid");
            if (!$order) VException::throw("充值不存在");

            $item_desc      = "余额充值";
            $logistics_type = $wxShipping::LOGISTICS_TYPE_VIRTUAL;
        }
        $opts = [
            "order_key"      => [
                "order_number_type" => $wxShipping::ORDER_NUMBER_TYPE_MER,
                "out_trade_no"      => $outTradeNo
            ],
            "logistics_type" => $logistics_type,
            "delivery_mode"  => $wxShipping::LOGISTICS_MODE_UNIFIED,
            "shipping_list"  => [
                ["item_desc" => $item_desc]
            ],
            "upload_time"    => date("Y-m-d\TH:i:s+08:00"),
            "payer"          => [
                "openid" => $order->openid
            ]
        ];
        $res  = $wxShipping->uploadShippingInfo($opts);
        // $count = 0;
        // while($res['errcode'] != 0 && $count < 10){
        //     $count++;
        //     sleep(1);
        //     $res  = $wxShipping->uploadShippingInfo($opts);
        // }
        if ($res['errcode'] != 0) VException::throw($res['errmsg'] . "({$res['errcode']})");
        return $res;
    }

    public function wxShippingByOutTradeNo_dev(string $outTradeNo) {
        $wxShipping = WxShipping::instance(ConfigService::instance()->getConfigMini());

        if (Str::startsWith($outTradeNo, "ORDER")) {
            $order = $this->getOrderBySn($outTradeNo, "sn,openid,take_type");
            if (!$order) VException::throw("订单不存在");
            $item_desc = "饮用水";
            if ($order->take_type == OrderService::TAKE_TYPE_SELF) {
                $logistics_type = $wxShipping::LOGISTICS_TYPE_SELF;
            } else {
                $logistics_type = $wxShipping::LOGISTICS_TYPE_DELIVER;
            }
        } else {
            $order = UserMoneyLogService::instance()->getLogByTargetGid($outTradeNo, "target_gid,openid");
            if (!$order) VException::throw("充值不存在");

            $item_desc      = "余额充值";
            $logistics_type = $wxShipping::LOGISTICS_TYPE_VIRTUAL;
        }
        $opts = [
            "order_key"      => [
                "order_number_type" => $wxShipping::ORDER_NUMBER_TYPE_MER,
                "out_trade_no"      => $outTradeNo
            ],
            "logistics_type" => $logistics_type,
            "delivery_mode"  => $wxShipping::LOGISTICS_MODE_UNIFIED,
            "shipping_list"  => [
                ["item_desc" => $item_desc]
            ],
            "upload_time"    => date("Y-m-d\TH:i:s+08:00"),
            "payer"          => [
                "openid" => $order->openid
            ]
        ];
        $res  = $wxShipping->uploadShippingInfo($opts);
        if ($res['errcode'] != 0) VException::throw($res['errmsg'] . "({$res['errcode']})");
        return $res;
    }

    /**
     * 查询微信发货状态
     * order_state: 1待发货，2已发货，3确认收货，4交易完成，5已退款
     * in_complaint：是否处在交易纠纷中
     * @param string $outTradeNo
     */
    public function getWxShippingByOutTradeNo(string $outTradeNo) {
        $wxShipping = WxShipping::instance(ConfigService::instance()->getConfigMini());
        $res        = $wxShipping->getOrder($outTradeNo);
        if (isset($res['order']['order_state'])) {
            $data                 = config("a.shipping_order_states." . $res['order']['order_state']);
            $data['in_complaint'] = $res['order']['in_complaint'];
            $data['out_trade_no'] = $res['order']['merchant_trade_no'];
            return $data;
        } else VException::throw("查询微信发货状态失败: " . $res['errmsg']);
    }

    public function getWxShippingByOutTradeNo_dev(string $outTradeNo) {
        $wxShipping = WxShipping::instance(ConfigService::instance()->getConfigMini());
        $res        = $wxShipping->getOrder_dev($outTradeNo);
        if (isset($res['order']['order_state'])) {
            $data                 = config("a.shipping_order_states." . $res['order']['order_state']);
            $data['in_complaint'] = $res['order']['in_complaint'];
            $data['out_trade_no'] = $res['order']['merchant_trade_no'];
            return $data;
        } else VException::throw("查询微信发货状态失败: " . $res['errmsg']);
    }

    /**
     * 查询微信发货列表
     * @param string $openid
     */
    public function getWxOrderList(string $openid = null, int $order_state = null) {
        $wxShipping = WxShipping::instance(ConfigService::instance()->getConfigMini());
        $res        = $wxShipping->getOrderList($openid, $order_state);
        if ($res['errcode'] != 0) VException::throw("查询微信发货列表失败: " . $res['errmsg']);
        $list = [];
        foreach ($res['order_list'] as $vo) {
            $list[] = [
                "transaction_id"    => $vo['transaction_id'],
                "out_trade_no"      => $vo['merchant_trade_no'],
                "description"       => $vo['description'],
                "paid_amount"       => bcdiv($vo['paid_amount'], 100, 2),
                "trade_create_time" => date("Y-m-d H:i:s", $vo['trade_create_time']),
                "pay_time"          => date("Y-m-d H:i:s", $vo['pay_time']),
                "order_state"       => $vo['order_state'],
                "order_state_txt"   => config("a.shipping_order_states.{$vo['order_state']}.order_state_txt"),
                "in_complaint"      => $vo['in_complaint'],
            ];
        }
        return $list;
    }

    /**
     * 从订单得到水站选择项
     * @return array
     */
    public function getStationOptsByOrder() {
        $station_gids = $this->getModel()->where("take_type", OrderService::TAKE_TYPE_SELF)->whereRaw("station_gid is not null && station_gid != ''")->column("distinct station_gid");
        $stations     = WaterStationService::instance()->getListByGids($station_gids, "id, gid,title", "gid");
        return $stations;
    }

    /**
     * 从订单得到收货区域选择项
     * @return array
     */
    public function getTakeDistrictOptsByOrder() {
        $districts = OrderService::instance()->getModel()->where("take_type", OrderService::TAKE_TYPE_DELIVER)->whereRaw("take_district is not null && take_district != ''")->column("distinct take_district");
        if (empty($districts)) return [];
        $list = [];
        foreach ($districts as $v) {
            $list[] = ['name' => $v];
        }
        return $list;
    }

    public function updateBySn($orderSn, array $data) {
        return $this->getModel()->where("sn", $orderSn)->update($data);
    }

    /**
     * @param $openid
     * @param array|null $takeRange
     * @param string $field
     * @return Paginator
     * @throws DbException
     */
    public function getFinishOrderPageDataByTakeBy($openid, array $takeRange = null, string $field = "*") {
        $query = $this->search(['take_by' => $openid])->field($field)->order("take_at desc");
        if ($takeRange) $query->whereBetweenTime("take_at", $takeRange[0], $takeRange[1]);

        return $query->paginate();
    }

    /**
     * @param $openid
     * @param array|null $takeRange
     * @param string $field
     * @return Paginator
     * @throws DbException
     */
    public function getOrderPageDataByTakeBy($openid, array $takeRange = null, array $districts = [], string $field = "*") {
        $query = $this->search([
            'take_by'   => $openid,
            'status'    => $this::ORDER_STATUS_FINISHED,
            'take_type' => $this::TAKE_TYPE_DELIVER
        ])->field($field)->order("take_at desc");
        if ($takeRange) $query->whereBetweenTime("take_at", $takeRange[0], $takeRange[1]);
        if ($districts) $query->whereIn("take_district", $districts);

        return $query->paginate();
    }

    /**
     * 分派配送员
     * @param string $take_district
     * @return \think\Collection
     */
    public function assignDeliver(string $order_sn, string $take_district) {
        $userInfoList = UserInfoService::instance()->getListByDistrict($take_district, "id,openid,nickname");
        if ($userInfoList->isEmpty()) VException::throw("该区域没有配送员");
        $data = [];
        foreach ($userInfoList as $user) {
            $data[] = [
                "openid"   => $user->openid,
                "order_sn" => $order_sn,
                "district" => $take_district
            ];
        }

        $res = OrderDeliverModel::mk()->saveAll($data);
        return $res;
    }

    /**
     * 我负责的待配货订单列表
     * @param $openid
     * @param array|null $takeRange 支付时间
     * @param string $field
     * @return Paginator
     * @throws DbException
     */
    public function getUnPickOrderPageDataByDeliverDistrict($openid, array $takeRange = null, array $districts = [], string $field = "*") {
        $query = $this->search([
            'status'         => $this::ORDER_STATUS_DELIVERING,
            "deliver_status" => $this::ORDER_DELIVER_STATUS_NOT,
            'take_type'      => $this::TAKE_TYPE_DELIVER
        ])->field($field)->order("take_district, create_at asc");
        if ($takeRange) $query->whereBetweenTime("create_at", $takeRange[0], $takeRange[1]);
//        if ($district) $query->where("take_district", $district);
        if ($districts) $query->whereIn("take_district", $districts);

        // 用关联表的方式查询区域订单
//        $db = OrderDeliverModel::mk()->where("openid", $openid)->field("order_sn");
//        $query->whereRaw("sn in {$db->buildSql()}");

        // 直接用用户区域查询区域订单
        $db = UserInfoService::instance()->getModel()->field("districts")->where("openid", $openid);
        $query->whereRaw("find_in_set(take_district, {$db->buildSql()})");

        return $query->paginate();
    }

    /**
     * 已配货，配送中的订单列表
     * @param $openid
     * @param array|null $takeRange
     * @param string $district
     * @param string $field
     * @return Paginator
     * @throws DbException
     */
    public function getDeliveringOrderPageDataByDeliverDistrict($openid, array $takeRange = null, array $districts = [], string $field = "*") {
        $query = $this->search([
            'status'         => $this::ORDER_STATUS_DELIVERING,
            "deliver_status" => $this::ORDER_DELIVER_STATUS_ING,
            'take_type'      => $this::TAKE_TYPE_DELIVER
        ])->whereRaw("(pick_by = '{$openid}' or take_by = '{$openid}')")->field($field)->order("take_district, create_at asc");
        if ($takeRange) $query->whereBetweenTime("create_at", $takeRange[0], $takeRange[1]);
        if ($districts) $query->whereIn("take_district", $districts);


        // 用关联表的方式查询区域订单
//        $db = OrderDeliverModel::mk()->where("openid", $openid)->field("order_sn");
//        $query->whereRaw("sn in {$db->buildSql()}");

        // 直接用用户区域查询区域订单
//        $db = UserInfoService::instance()->getModel()->field("districts")->where("openid", $openid);
//        $query->whereRaw("find_in_set(take_district, {$db->buildSql()})");

        return $query->paginate();
    }

    /**
     * @param $sns
     * @param string $field
     * @return array|\think\Collection|BaseSoftDeleteModel[]|OrderModel[]
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getListBySns($sns, string $field = "*") {
        return $this->getModel()->field($field)->whereIn('sn', $sns)->select();
    }

    public function updateBySns($sns, array $data) {
        return $this->getModel()->whereIn('sn', $sns)->update($data);
    }

    /**
     * 批量微信发货
     * @param $sns
     * @return int
     */
    public function wxShippingByOutTradeNos($sns) {
        $count_shipping = 0; // 微信发货数;
        foreach ($sns as $sn) {
            try {
                $res = $this->getWxShippingByOutTradeNo($sn);
            } catch (Exception $e) {
                $res['order_state'] = 0;
                $error              = $e->getMessage();
            }
            if (!isset($res['order_state']) || $res['order_state'] == 0) {
                error("订单配送微信发货失败(sn:{$sn}): " . $error ?? "");
            } elseif ($res['order_state'] == 1) {
                $this->wxShippingByOutTradeNo($sn);
                $count_shipping++;
            }
        }
        return $count_shipping;
    }

    /**
     * 创建企业或经销商订单
     * @param $vo
     * @param $subs
     * @return OrderModel
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function createFromComOrPartner($vo, $subs) {
        if (empty($vo)) VException::throw("订单信息不能为空");
        if (empty($subs)) VException::throw("请选择商品");

        // 设置自提或配送信息
        if ($vo['take_type'] == 0) { // 自提
            if (empty($vo['station_gid'])) VException::throw("请选择自提水站");

            // 0自提：客户选择的水站
            $vo['depot_gid'] = $vo['station_gid'];

            $station = WaterStationService::instance()->getOpeningOneByGid($vo['station_gid'], "id,gid,title,district,detail,link_name,link_phone");
            if (!$station) VException::throw("自提水站不存在或已关闭");
            $vo['station_title']      = $station['title'];
            $vo['station_address']    = $station['district'] . $station['detail'];
            $vo['station_link_name']  = $station['link_name'];
            $vo['station_link_phone'] = $station['link_phone'];

            $vo['address_gid']   = null;
            $vo['take_name']     = null;
            $vo['take_phone']    = null;
            $vo['take_province'] = null;
            $vo['take_city']     = null;
            $vo['take_district'] = null;
            $vo['take_street']   = null;
            $vo['take_address']  = null;

        } elseif ($vo['take_type'] == 1) { // 配送
            if (empty($vo['address_gid'])) VException::throw("请选择收货地址");
            $address = UserAddressService::instance()->getOneByGid($vo['address_gid']);
            if (empty($address)) VException::throw("收货地址不存在或已删除");

            // 1配送：系统配置的默认的库房或水站（或二期自动匹配）
            $vo['depot_gid'] = DepotService::instance()->getDefaultDepotOrStationGid();

            $address_ = $address->detail;
            if ($address->street) $address_ = $address->street . "," . $address_;
            if ($address->district) $address_ = $address->district . "," . $address_;
            if ($address->city) $address_ = $address->city . "," . $address_;
            if ($address->province) $address_ = $address->province . "," . $address_;
            $vo['take_address'] = $address_;
            $vo['take_name']     = $address->link_name;
            $vo['take_phone']    = $address->link_phone;
            $vo['take_province'] = $address->province;
            $vo['take_city']     = $address->city;
            $vo['take_district'] = $address->district;
            $vo['take_street']   = $address->street;


            $vo['station_gid']        = null;
            $vo['station_title']      = null;
            $vo['station_address']    = null;
            $vo['station_link_name']  = null;
            $vo['station_link_phone'] = null;
        }


        $sns       = array_column($subs, 'goods_sn');
        $goodsList = GoodsService::instance()->getListForCreateComOrderBySns($sns);
        foreach ($subs as &$sub) {
            if (!isset($goodsList[$sub['goods_sn']])) VException::throw("商品不存在或已下架");
            $number            = $sub['goods_number'];
            $sub               = $goodsList[$sub['goods_sn']];
            $sub['is_checked'] = true;
            $sub['number']     = $number;
            $sub['amount']     = round($sub['self_price'] * $number, 2);
        }

        // 组织订单及商品明细
        $orderInfo = $this->parseCartList($subs, $vo['take_type']);
        $orderInfo = array_merge($vo, $orderInfo);
        if(empty($orderInfo['pay_at']))  $orderInfo['pay_at'] = null;
        if(empty($orderInfo['take_at'])) $orderInfo['take_at'] = null;

        $order = $this->createFromConfirm($orderInfo);
        return $order;
    }

    /**
     * 企业或经销商支付时，扣预付款（如果是预付款支付）
     * @param $order
     * @param $pay_type
     * @param $pay_remark
     * @return true
     * @throws Exception
     */
    public function payOrderComSimple($order, $pay_type, $pay_remark = null) {
        if (is_string($order)) $order = $this->getOneBySn($order);
        if (empty($order)) VException::throw("订单不存在");
        if(!$this->isComOrder($order->from)) VException::throw("仅支持企业（或经销商）订单支付");

        Db::startTrans();
        try {
            // 1. 预付款方式，用企业预付款进行支付
            if($pay_type == 'com_pre'){
                if($order->from == 'company'){
                    CustomerService::instance()->reduceMoney($order->openid, $order->pay_amount, 'order', $order->sn);
                }elseif($order->from == 'partner'){
                    WaterStationService::instance()->reduceMoney($order->openid, $order->pay_amount, 'order', $order->sn);
                }
            }

            // 2. 更新订单数据
            $data = [];
            if($pay_remark) $data['pay_remark'] = $pay_remark;
            if($data){
                $this->updateBySn($order->sn, $data);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            VException::throw($e->getMessage());
        }

        return true;
    }

    /**
     * 判断是否企业或经销商订单
     * @param $from
     * @return bool
     */
    private function isComOrder($from) {
        $arr = ['company'=>1, 'partner'=>1];
        return !empty($arr[$from]);
    }


    /**
     * 是否微信侧全部退款
     * @param $sn
     * @return bool
     */
    public function isWxRefundAll($sn) {
        $wxPay = WxPay::instance(ConfigService::instance()->getConfigMini());
        try{
            $res = $wxPay->refundQueryByOutTradeNo($sn);
        }catch(\Exception $e){
            if(Str::contains($e->getMessage(), 'REFUNDNOTEXIST')){
                return false;
            }else VException::throw($e->getMessage());
        }
        if(!isset($res['total_fee']) || !isset($res['refund_fee'])) VException::throw("微信退款查询失败");
        return $res['total_fee'] == $res['refund_fee'];

    }


}