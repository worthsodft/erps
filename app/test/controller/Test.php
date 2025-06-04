<?php
/*
 * 
 * Author: vandles
 * Date: 2022/6/27 15:50
 * Email: <vandles@qq.com>
 */


namespace app\test\controller;


use app\api\controller\v1\Index;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use think\admin\extend\JwtExtend;
use think\admin\support\middleware\JwtSession;
use think\admin\tests\JwtTest;
use think\facade\Db;
use think\helper\Str;
use vandles\lib\code\CodeBuilder;
use vandles\lib\DateUtil;
use vandles\lib\JwtUtil;
use vandles\lib\Request;
use vandles\lib\Tool;
use vandles\lib\VException;
use vandles\service\ArticleService;
use vandles\service\CartService;
use vandles\service\ConfigService;
use vandles\service\CouponPublishService;
use vandles\service\CouponTplService;
use vandles\service\CustomerService;
use vandles\service\DepotService;
use vandles\service\GiftCardLogService;
use vandles\service\GiftCardService;
use vandles\service\GoodsService;
use vandles\service\GoodsStockService;
use vandles\service\InStockService;
use vandles\service\InStockSubService;
use vandles\service\InvoiceApplyService;
use vandles\service\MoneyCardService;
use vandles\service\OrderService;
use vandles\service\OrderSubService;
use vandles\service\PayInService;
use vandles\service\RechargeWayService;
use vandles\service\ReportDayService;
use vandles\service\ServiceMenuService;
use vandles\service\UserAddressService;
use vandles\service\UserCouponService;
use vandles\service\UserInfoService;
use vandles\service\UserMoneyLogService;
use vandles\service\UserTempService;
use vandles\service\WaterStationService;

class Test {

    public function debug() {
        $res                 = "res";
        $openid              = "obzAZ7c85NvxGhEXyOEXnP6uY0JM";
        $order_sn            = "ORDERWE9IQKUNGBF2DMS";
        $goodsService        = GoodsService::instance();
        $orderService        = OrderService::instance();
        $orderSubService     = OrderSubService::instance();
        $userInfoService     = UserInfoService::instance();
        $publishService      = CouponPublishService::instance();
        $userMoneyLogService = UserMoneyLogService::instance();
        $userCouponService   = UserCouponService::instance();
        $rechargeWayService  = RechargeWayService::instance();
        $invoiceApplyService = InvoiceApplyService::instance();
        $moneyCardService    = MoneyCardService::instance();


        // $ids = "1351,1350,1335,1183";
        // $ids = "1351,1350,1342";
        // $res = GiftCardService::instance()->softDeleteByIds($ids);


        dd($res);

        dd('bedug');
    }

    public function test($count) {
        d('aa');
        return ['errcode' => $count];
    }

    public function temp() {

        $this->debug();

        // 10. 去除测试数据中的敏感信息
        // $this->clearSensitiveInfo();

        // 9. 测试实物卡
        // $this->testGiftCard();
        // 8. 代码生成器
        // $this->testCodeBuilder();
        // 7. jwt测试
        // $this->testJwt();
        // 6. 模拟用户余额充值
        // $this->testRechargeSuccess();
        // 5. 用户余额拆分
       // $this->splitUserMoney();
        // 4. 微信小程序发货
       // $this->testOrderWxShipping();
        // 3. 充值成功回调
       // $this->testPaySuccess();
        // 2. 队列与自动任务
       // $this->testQueue();
       //  1. 系统模型初始货
       // $this->testSystemInit();
        dd('temp');
    }




    // 10. 去除测试数据中的敏感信息
    private function clearSensitiveInfo() {
        // 敏感信息: userInfo: nickname, realname, phone
        $userInfoList = UserInfoService::instance()->getList([], "id,nickname,realname,phone,avatar");
        $userInfoList->each(function ($item) {
            $item->nickname = "昵称_".strtoupper(Str::random(4));
            $item->realname = "姓名_".strtoupper(Str::random(4));
            $item->phone   = 1 . random_int(3,9) . Str::random(9, 1);
            $item->avatar = "https://dev.yaodianma.com/upload/68/d9aa96c78d3ca13f28bbe53ca9898e.png";
            $item->save();
        });

        // 收货地址
        $userAddressList = UserAddressService::instance()->getList([], "id,detail,link_name,link_phone");
        $userAddressList->each(function ($item) {
            $item->detail = "XXX街道解放南路475号长达公寓1115号楼A单元10{$item->id}号";
            $item->link_name = "收货人_".strtoupper(Str::random(4));
            $item->link_phone   = 1 . random_int(3,9) . Str::random(9, 1);
            $item->save();
        });
        // 水站信息
        $stationList = WaterStationService::instance()->getList([], "id,title,detail,link_name,link_phone");
        $stationList->each(function ($item) {
            $item->title = "水站_".strtoupper(Str::random(4));
            $item->detail = "解放南路号长虹大厦底商{$item->id}号";
            $item->link_name = "张小明_".strtoupper(Str::random(2));
            $item->link_phone   = 1 . random_int(3,9) . Str::random(9, 1);
            $item->save();
        });

        dd($stationList->toArray());

    }


    // 9. 测试实物卡
    public function testGiftCard() {
        $res      = "";
        $uid      = 886;
        $openid   = "obzAZ7c85NvxGhEXyOEXnP6uY0JM";
        $goodsId1 = 56; // 56,57,58
        $goodsId2 = 57; // 56,57,58

        // 1. 根据实物卡商品批量生成实物卡
        // $res = GiftCardService::instance()->batchGiftCardByGoodsId($goodsId, 5);

        // 2. 为用户生成实物卡，通常在支付成功时
        // $res = GiftCardService::instance()->genGiftCardForOpenidByGoodsId($openid, $goodsId1, 1);
        // $res = GiftCardService::instance()->genGiftCardForUidByGoodsId($uid, $goodsId2, 1);

        // 3. 为用户绑定一张实物卡
        // $cardSn = "537914022545068032"; // 金额
        $cardSn = "537914760373473280"; // 计次
        // $res = GiftCardService::instance()->bindGiftCardForOpenid($cardSn, $openid);
        // $res = GiftCardService::instance()->bindGiftCardForUid($cardSn, $uid);

        // 用户使用实物卡
        // $res = GiftCardService::instance()->expend($cardSn, 1.5, null, $openid);


        $vo = GiftCardLogService::instance()->getById(7);
        GiftCardLogService::instance()->bindOne($vo);
        dd($vo->toArray());

        dd($res);

        dd("testGiftCard");
    }

    // 8. 测试代码生成器
    public function testCodeBuilder() {

        // 创建插件模块
        // CodeBuilder::instance()->setIsAddon(true)->setIsForce(false)->setControlPrefix("spread")->buildAll("user_poster_tpl", "海报模板");
        dd("创建插件模块");
        // 实物卡
        // CodeBuilder::instance()->setIsForce(true)->setControlPrefix("sale")->buildAll("gift_card", "实物卡管理");
        // 实物卡变动记录
        // CodeBuilder::instance()->setIsForce(true)->setControlPrefix("report")->buildAll("gift_card_log", "实物卡变动记录");

        dd("testCodeBuilder");
    }

    // 7. 测试jwt
    public function testJwt() {
        $data = [
            "username" => "abcdef"
        ];
        // $token = JwtExtend::token($data);
        // dd($token);
        $token_ = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MzYxMzM3ODAsImVuYyI6ImV5SnBkaUk2SW1oek5Xa3paMjh4YVdFM01HVnRkMkVpTENKMllXeDFaU0k2SWsxRlpVUnFVSHBwVURkVFdVNUlZa2RsWm5Sek1YRnhRbTlpVTJGMloySXJURmhKT1cxQ01VVk5SWEZ4VWx3dlNXNDNZa0ozVlRoM1JqRnZNbVpwWkhJd2QzSnRLMDVCVTBaNVYxTnRWVEo0SzJsVFJHVXhVRmxDUmpSVFJHRm5OM2hTV2xsMmIxcDZTM1l5U1QwaWZRIn0.3e1J6pFrlPmS_iP4ESLLKm2Csap0p0dsYMPyFnTozNM";

        $res = JwtExtend::verify($token_);

        d($res);
        $iat = date("Y-m-d h:i:s", $res['iat']);

        dd($iat);
        dd("testJwt");
    }

    // 6. 模拟用户余额充值
    private function testRechargeSuccess() {
        $wayGid1 = "89cb1a0679c94ea48c16aedd7fe3f5ec"; // 200 + 20
        $wayGid2 = "7b6f2bf62d98e42cb826c31434b1a4af"; // 500 + 100
        $wayGid3 = "57742aee24f7737cdee644c77dc8bb70"; // 1000 + 300
        $openid  = "17247368523017776587";

        // $res = UserInfoService::instance()->rechargeSuccess($wayGid1, "out_trade_no_".uuid(), $openid, "10000".Str::random(18, 1));
        // $res = UserInfoService::instance()->rechargeSuccess($wayGid1, "out_trade_no_".uuid(), $openid, "10000".Str::random(18, 1));
        // $res = UserInfoService::instance()->rechargeSuccess($wayGid1, "out_trade_no_".uuid(), $openid, "10000".Str::random(18, 1));
        $res = UserInfoService::instance()->rechargeSuccess($wayGid3, "out_trade_no_" . uuid(), $openid, "10000" . Str::random(18, 1));
        // $res = UserInfoService::instance()->rechargeSuccess($wayGid2, "out_trade_no_".uuid(), $openid, "10000".Str::random(18, 1));
        // $res = UserInfoService::instance()->rechargeSuccess($wayGid1, "out_trade_no_".uuid(), $openid, "10000".Str::random(18, 1));
        // $res = UserInfoService::instance()->rechargeSuccess($wayGid1, "out_trade_no_".uuid(), $openid, "10000".Str::random(18, 1));

        dd("6. 用户余额充值");
    }

    // 5. 用户余额拆分
    private function splitUserMoney() {
        $moneyCardService = MoneyCardService::instance();

        // 根据充值记录批量生成金额水卡
        Db::query("truncate table a_money_card");
        Db::query("truncate table a_money_card_log");
        $count = $moneyCardService->batchCreateFromRechargeLog();
//        dd("生成金额水卡: " . $count);

        $cardService = MoneyCardService::instance();
        $orderList   = OrderService::instance()->search()->whereRaw("status = 1 or status = 2 or status = 9")->order("id asc")
            ->where("real_amount > 0")
//            ->where("openid", "obzAZ7bteeiH09OyO4MVA04n-6Vo") // 张总
//            ->where("sn", "ORDERQZKQPUPPJFXQCGD")
//            ->whereBetweenTime("create_at", "2024-08-01 00:00:00", "2024-08-31 23:59:59")
//            ->whereBetweenTime("create_at", "2024-09-01 00:00:00", "2024-09-30 23:59:59")
//            ->whereBetweenTime("create_at", "2024-10-01 00:00:00", "2024-10-31 23:59:59")
            ->select();

//        dd("订单数：" . count($orderList));
        $count = ['order' => 0, 'refund' => 0];
        DB::startTrans();
        try {
            foreach ($orderList as $k => $order) {
                try {
                    if ($moneyCardService->expend($order)) $count['order']++;
                } catch (\Exception $e) {
                    $error = "订单余额拆分出现异常({$order->sn})：" . $e->getMessage();
                    error($error);
                    VException::throw($error);
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            VException::throw("余额拆分时发生异常({$order->openid})：" . $e->getMessage());
        }
        dd("已处理 {$count['order']} 个订单");
        dd("splitUserMoney");
    }

    // 4. 微信小程序发货
    private function testOrderWxShipping() {
        $orderService = OrderService::instance();


        dd("testOrderWxShipping");
    }

    // 3. 充值成功回调
    private function testPaySuccess() {
        $userInfoService = UserInfoService::instance();

        $post              = [
            'appid'          => 'wx4f8a0a36cd1e624a',
            'attach'         => '{"type":"recharge","gid":"7c1598d895e79fbed05bcc9d9bbba89e"}',
            'bank_type'      => 'OTHERS',
            'cash_fee'       => '1',
            'fee_type'       => 'CNY',
            'is_subscribe'   => 'N',
            'mch_id'         => '1227693802',
            'nonce_str'      => 'amxnqch29ig5joxgeduskbqutuuke72m',
            'openid'         => 'obzAZ7Xkkk5XGX0si13ngW4Q9ZEA',
            'out_trade_no'   => '183f74383742e33ec71bc8d0d84d5c86',
            'result_code'    => 'SUCCESS',
            'return_code'    => 'SUCCESS',
            'sign'           => '270C43B730769BB1DA3C529DD6571DAB',
            'time_end'       => '20240820100554',
            'total_fee'      => '1',
            'trade_type'     => 'JSAPI',
            'transaction_id' => '4200002316202408203492358459',
        ];
        $attach            = json_decode($post['attach'], true);
        $post['total_fee'] = bcdiv($post['total_fee'], 100, 2);
        $userInfoService->rechargeSuccess($attach['gid'], $post['out_trade_no'], $post['openid'], $post['transaction_id']);


    }


    // 1-15. 功能菜单
    private function testServiceMenu() {
        ServiceMenuService::instance()->init(1, 1);
        dd("testServiceMenu");
    }

    // 1-14. 订单明细
    private function testOrderSub() {
        OrderSubService::instance()->init(1, 1);
        dd("testOrderSub");
    }

    // 1-13. 订单
    private function testOrder() {
        OrderService::instance()->init(1, 1);
        dd("testOrder");
    }

    // 1-12. 水站
    private function testWaterStation() {
        WaterStationService::instance()->init(1, 1);
        dd("testWaterStation");
    }

    // 1-11. 充值方式
    private function testRechargeWay() {
        RechargeWayService::instance()->init(1, 1);
        dd("testRechargeWay");
    }

    // 1-10. 用户余额记录
    private function testUserMoneyLog() {
        UserMoneyLogService::instance()->init(1, 1);
        dd("testUserMoneyLog");
    }

    // 1-9. 用户优惠券
    private function testUserCoupon() {
        UserCouponService::instance()->init(1, 1);
        dd("testUserCoupon");
    }

    // 1-8. 用户地址
    private function testUserAddress() {
        UserAddressService::instance()->init(1, 1);
        dd("testUserAddress");
    }

    // 1-7. 购物车
    private function testCart() {
        CartService::instance()->init(1, 1);
        dd("testCart");
    }

    // 1-6. 文章
    private function testArticle() {
        ArticleService::instance()->init(1, 1);
        dd("testArticle");
    }

    // 1-5. 优惠券发布
    private function testCouponPublish() {
        CouponPublishService::instance()->init(1, 1);
        dd("testCouponPublish");
    }

    // 1-4. 优惠券模板
    private function testCouponTpl() {
        CouponTplService::instance()->init(1, 1);
        dd("testCouponTpl");
    }

    // 1-3. 用户业务
    private function testUserInfoService() {
        UserInfoService::instance()->init(1, 1);
        dd("testGoodsService");
    }

    // 1-2. 商品业务
    private function testGoodsService() {
        GoodsService::instance()->init(1, 1);
        dd("testGoodsService");
    }

    // 1-1. 系统配置业务
    private function testConfigService() {
//        ConfigService::instance()->init(1,1);
        $list   = ConfigService::instance()->getList();
        $config = ConfigService::instance()->getConfigMini();
        dd("testConfigService");
    }


    // 2. 队列与自动任务
    private function testQueue() {

//        event("paySuccess", ["no"=>"2022062715500001"]);


//        $id = sysqueue("测试订单清理任务", "vandles:OrderClean", 0, ["name"=>"tom"], 1, 600);
//        dd($id);

        dd("testQueue");
    }

    // 1. 系统模型初始货
    private function testSystemInit() {
        // 1-16. excel导入的临时用户
        $this->testUserTemp();
        // 1-15. 功能菜单
//        $this->testServiceMenu();
        // 1-14. 订单明细
//        $this->testOrderSub();
        // 1-13. 订单
//        $this->testOrder();
        // 1-12. 水站
//        $this->testWaterStation();
        // 1-11. 充值方式
//        $this->testRechargeWay();
        // 1-10. 用户余额记录
//        $this->testUserMoneyLog();
        // 1-9. 用户优惠券
//        $this->testUserCoupon();
        // 1-8. 用户地址
//        $this->testUserAddress();
        // 1-7. 购物车
//        $this->testCart();
        // 1-6. 首页最新消息
//        $this->testArticle();
        // 1-5. 优惠券发布业务
//        $this->testCouponPublish();
        // 1-4. 优惠券模板业务
//        $this->testCouponTpl();
        // 1-3. 用户业务
//        $this->testUserInfoService();
        // 1-2. 商品业务
//        $this->testGoodsService();
        dd("testSystemInit");
    }

    private function testUserTemp() {
        UserTempService::instance()->init(1, 1);
        dd("testUserTemp");
    }

}