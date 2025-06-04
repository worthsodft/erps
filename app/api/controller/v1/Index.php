<?php
/*
 * 
 * Author: vandles
 * Date: 2022/4/11 15:36
 * Email: <vandles@qq.com>
 */


namespace app\api\controller\v1;

use app\addongiftcard\Giftcard;
use Exception;
use think\admin\extend\CodeExtend;
use think\admin\service\CaptchaService;
use think\admin\Storage;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Db;
use think\helper\Str;
use vandles\controller\ApiBaseController;
use vandles\lib\Enum;
use vandles\lib\FileUpload;
use vandles\lib\Request;
use vandles\lib\Validator;
use vandles\lib\VException;
use vandles\service\MyAddonService;
use vandles\service\ArticleService;
use vandles\service\CartService;
use vandles\service\ConfigService;
use vandles\service\CouponPublishService;
use vandles\service\DepotService;
use vandles\service\GiftCardLogService;
use vandles\service\GiftCardService;
use vandles\service\GoodsService;
use vandles\service\GoodsStockService;
use vandles\service\InvoiceApplyService;
use vandles\service\MoneyCardService;
use vandles\service\OrderPickService;
use vandles\service\OrderService;
use vandles\service\OrderSubService;
use vandles\service\RechargeWayService;
use vandles\service\ReportDayService;
use vandles\service\ServiceMenuService;
use vandles\service\UserAddressService;
use vandles\service\UserCouponService;
use vandles\service\UserInfoService;
use vandles\service\UserMoneyLogService;
use vandles\service\UserTempService;
use vandles\service\WaterStationService;
use WeChat\Pay;
use WeMini\Crypt;

class Index extends ApiBaseController {

    // 0. hello
    public function hello(Request $request, OrderService $orderService) {
//        $pageData = $orderService->getOrderPageDataByStatus($request->openid());
//        $orderService->bindSubs($pageData, "order_sn, goods_cover");
//        $this->success("订单列表数据", compact("pageData"));
    }

    // 66. 得到我的可用实物卡
    public function getMyUsableGiftCardList() {
        $openid       = request()->openid();
        try{
            $giftCardList = GiftCardService::instance()->getUsableListByOpenid($openid, "sn,has,init,use_type,useful_expire_at,last_use_at");
        }catch(\Error $e){
            $giftCardList = [];
        }
        $this->success('可用实物卡', compact('giftCardList'));
    }

    // 65. 验证码图片
    public function captcha() {
        $post = $this->_vali([
            'type.require' => '类型不能为空!',
            // 'token.require' => '标识不能为空!',
        ]);
        // $k = "captcha_".$post['type'];
        // $count = Cache::get($k, 0);
        // if ($count >= 10) $this->error("验证码获取次数过多，请明天再试");
        // cache($k, ++$count, 7200);
        $image   = CaptchaService::instance()->initialize();
        $captcha = ['image' => $image->getData(), 'uniqid' => $image->getUniqid()];
        $this->success('生成验证码成功', $captcha);
    }

    // 64. 我的实物卡使用记录
    public function getMyGiftCardLogPageData(Request $request, GiftCardLogService $giftCardLogService) {
        $pageData = $giftCardLogService->search(['use_openid' => $request->openid()])->order("id desc")->paginate();
        $giftCardLogService->bindPageData($pageData);
        $data = compact('pageData');
        $this->success("得到列表", $data);
    }

    // 63. 我的实物卡列表
    public function getMyGiftCardPageData(Request $request, GiftcardService $giftCardService) {
        $pageData = $giftCardService->search(['bound_openid' => $request->openid()])->order("useful_expire_at asc, bound_at asc, id asc")->paginate();
        $giftCardService->bindPageData($pageData);
        $data = compact('pageData');
        $this->success("得到列表", $data);
    }

    // 62. 实物卡绑定
    public function bindGiftCardForMiniBind(GiftCardService $cardService) {
        $post = $this->_vali([
            "sn.require"      => "请输入卡号",
            "code.require"    => "请输入密码",
            "vcode.require"   => "请输入验证码",
            "vuniqid.require" => "验证码标识不能为空",
            "type.require"    => "请输入验证码类型",
        ]);

        $openid = $this->request->openid();
        if (!CaptchaService::instance()->check($post['vcode'], $post['vuniqid'])) {
            $k     = "captcha_error_{$openid}_" . $post['type'];
            $count = Cache::get($k, 0);
            if ($count >= 5) $this->error("验证码错误次数过多，请稍后再试");
            cache($k, ++$count, 7200);
            $this->error('图形验证码验证失败，请重新输入!');
        }


        try {
            $card = $cardService->verify($post['sn'], $post['code']);
            $cardService->bindGiftCardForOpenid($post['sn'], $openid);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success("绑定成功");
    }

    // 61. 实物卡绑定时查询卡信息
    public function searchGiftCardForMiniBind(GiftCardService $cardService) {
        $post = $this->_vali([
            "sn.require"      => "请输入卡号",
            "code.require"    => "请输入密码",
            "vcode.require"   => "请输入验证码",
            "vuniqid.require" => "验证码标识不能为空",
            "type.require"    => "请输入验证码类型",
        ]);

        $openid = $this->request->openid();
        if (!CaptchaService::instance()->check($post['vcode'], $post['vuniqid'])) {
            $k     = "captcha_error_{$openid}_" . $post['type'];
            $count = Cache::get($k, 0);
            if ($count >= 5) $this->error("验证码错误次数过多，请稍后再试");
            cache($k, ++$count, 7200);
            $this->error('图形验证码验证失败，请重新输入!');
        }

        try {
            $card = $cardService->verify($post['sn'], $post['code']);
            $cardService->bindOne($card);
            if ($card->bound_openid) VException::throw("实物卡已被绑定");
            if ($card->bind_expire_at < now()) VException::throw("实物卡已过绑定有效期");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success("查询成功", compact("card"));
    }

    // 60. 实物卡绑定页面初始化数据
    public function getInitDataGiftCardBind() {
        $banner  = ConfigService::instance()->getGiftCardBindBanner();
        $banners = Giftcard::instance()->getConfigBindSwiperImages();
        $remarks = ConfigService::instance()->getGiftCardRemarks();
        $this->success("实物卡绑定页面初始化数据", compact("banner", "banners", "remarks"));
    }

    // 59. 自动任务生成昨日统计数据(一般凌晨生成前一日报表)
    public function autoTaskGenReportDayYesterday() {
        $data = ReportDayService::instance()->getReportDayYesterday();
        ReportDayService::instance()->createOrUpdate($data);
        $this->success("昨日统计数据生成成功");
    }

    // 58. 库房/水站配货确认
    public function orderPickConfirm($gid, Request $request, OrderPickService $orderPickService) {

        $openid = $request->openid();
        $isAuth = UserInfoService::instance()->isAuthFinishOrder($openid);
        if (!$isAuth) $this->error("当前用户没有配货确认权限");

        $orderPick = OrderPickService::instance()->getByGid($gid);
        if (empty($orderPick)) $this->error("配货单不存在");
        if ($orderPick->status == OrderPickService::STATUS_PICK_YES) $this->error("配货单已确认");
        $res = $orderPickService->confirm($orderPick, $openid);
        $this->success("配货确认成功", compact("res"));
    }

    // 57. 得到我水站的配货单
    public function getMyStationOrderPickPageData(Request $request, OrderPickService $orderPickService) {
        $createAtRange = $this->post("createAtRange");
        if (empty($createAtRange)) [$today = today(), $createAtRange = [$today . " 00:00:00", $today . " 23:59:59"]];
        else {
            $createAtRange[0] = date("Y-m-d 00:00:00", strtotime($createAtRange[0]));
            $createAtRange[1] = date("Y-m-d 23:59:59", strtotime($createAtRange[1]));
        }
        $openid = $request->openid();
        $isAuth = UserInfoService::instance()->isAuthFinishOrder($openid);
        if (!$isAuth) $this->error("当前用户没有核销权限");
        try{
            $pageData = $orderPickService->getMyStationOrderPickPageDataByOpenid($openid, $createAtRange);
        }catch (Exception $e){
            $this->error($e->getMessage());
        }
        $orderPickService->bindSubs($pageData, "order_sn, goods_cover, goods_name, goods_number, goods_unit");
        $this->success("订单列表数据", compact("pageData"));
    }

    // 56. 得到营业中的水站列表
    public function getStationOpeningList() {
        $stationList = WaterStationService::instance()->getStationOpeningOpts("id,gid value,title text");
        $this->success("营业中的水站列表", compact("stationList"));
    }

    // 55. 金额充值卡退款
    public function moneyCardRefund(Request $request, MoneyCardService $moneyCardService, $gid) {
        try {
            $moneyCardService->refundByGid($gid);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("退款受理成功");
    }

    // 54. 得到我的金额充值卡列表
    public function getMyMoneyCardList(Request $request, MoneyCardService $moneyCardService) {

        $list = $moneyCardService->getModel()->where([
            'openid' => $request->openid()
        ])->select();

        $this->success("订单列表数据", compact("list"));
    }

    // 53. 生成订单配货单
    public function orderPick(Request $request, OrderService $orderService) {
        $sns         = $this->post("sns");
        $station_gid = $this->post("station_gid");
        if (empty($sns)) $this->error("请选择订单编号");
        if (empty($station_gid)) $this->error("请选择水站");
        $openid = $request->openid();
        $isAuth = UserInfoService::instance()->isAuthDeliverOrder($openid);
        if (!$isAuth) $this->error("当前用户没有配送权限");

        $orderList = $orderService->getListBySns($sns, "id,sn,status,deliver_status")->toArray();
        if (count($orderList) == 0) $this->error("订单不存在");

        $station = WaterStationService::instance()->getOneByGid($station_gid);
        if (empty($station)) $this->error("水站不存在");


        foreach ($orderList as $order) {
            if ($order['status'] != OrderService::ORDER_STATUS_DELIVERING)
                $this->error("订单（{$order['sn']}）不是待配货状态，不能配货");
            if ($order['deliver_status'] != OrderService::ORDER_DELIVER_STATUS_NOT)
                $this->error("订单（{$order['sn']}）不是待配货状态，不能配货");
        }

        // 1. 创建配货单商品列表
        $goodsList = OrderSubService::instance()->getSubsGroupByGoodsSnBySns($sns)->toArray();
        $total     = 0;
        foreach ($goodsList as $goods) {
            $total += $goods['goods_total'];
        }
        $pickData = [
            "gid"           => guid(),
            "openid"        => $openid,
            "station_gid"   => $station_gid,
            "station_title" => $station['title'],
            "goods_total"   => $total,
            "goods_snap"    => json_encode($goodsList, JSON_UNESCAPED_UNICODE),
            "order_sns"     => implode(",", $sns)
        ];

        // 2. 订单需要修改的数据
        $data = [
            'pick_gid'       => $pickData['gid'],
            'pick_at'        => now(),
            'pick_by'        => $openid, // 配货人
            'deliver_status' => OrderService::ORDER_DELIVER_STATUS_ING
        ];

        Db::startTrans();
        try {
            OrderPickService::instance()->create($pickData);
            $orderService->getModel()->whereIn("sn", $sns)->update($data);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            VException::throw($e->getMessage());
        }
        // 批量微信发货
        // $orderService->wxShippingByOutTradeNos($sns);
        $this->success("订单配货成功");
    }

    // 52. 订单配送
    public function deliverOrder(Request $request, OrderService $orderService) {
        $post = $this->_vali([
            "sn.require"     => "请输入订单编号",
            "urls.require"   => "请上传配送图片",
            "remark.default" => ""
        ]);

        $order = $orderService->getOneBySn($post['sn'], "sn,openid,status");
        if (empty($order)) $this->error("订单不存在");

        try {
            $order = $orderService->finishOrder($order, $request->openid(), $post);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        try {
            // 微信发货
            $res = $orderService->getWxShippingByOutTradeNo($post['sn']);
            if (isset($res['order_state']) && $res['order_state'] == 1) {
                $orderService->wxShippingByOutTradeNo($post['sn']);
            }
        } catch (Exception $e) {
            error($e->getMessage());
        }

        $this->success("订单配送成功", compact("order"));
    }

    // 51. 我负责配送的区域
    public function getUserInfoDistricts(Request $request, OrderService $orderService) {
        $districts = UserInfoService::instance()->getDistrictsByOpenid($request->openid());
        if (empty($districts)) $this->error("未设置负责区域");
        $this->success("配送区域", compact("districts"));
    }

    // 50. 我的配送列表
    public function getMyDeliverOrderPageData(Request $request, OrderService $orderService) {
        $takeRange = $this->post("takeAtRange");
        $current   = $this->post("current", 0);
        $districts = $this->post("district", []);
        if (empty($takeRange)) [$today = today(), $takeRange = [$today . " 00:00:00", $today . " 23:59:59"]];
        elseif(strlen($takeRange[0]) <= 13) {
            $takeRange[0] = date("Y-m-d 00:00:00", strtotime($takeRange[0]));
            $takeRange[1] = date("Y-m-d 23:59:59", strtotime($takeRange[1]));
        }
        $openid = $request->openid();
        $isAuth = UserInfoService::instance()->isAuthDeliverOrder($openid);
        if (!$isAuth) $this->error("当前用户没有配送权限");

        if ($current == 0) { // 我负责的待配货列表
            $pageData = $orderService->getUnPickOrderPageDataByDeliverDistrict($openid, $takeRange, $districts);
        } elseif ($current == 1) { // 已配货配送中列表
            $pageData = $orderService->getDeliveringOrderPageDataByDeliverDistrict($openid, $takeRange, $districts);
        } elseif ($current == 2) { // 我已配送的
            $pageData = $orderService->getOrderPageDataByTakeBy($openid, $takeRange, $districts);
        }else{
            $this->error("不支持的切换类型");
        }

        $districtTotal = [];

        $orderService->bindSubs($pageData, "order_sn, goods_sn, goods_cover, goods_name, goods_number, goods_unit", function ($order) use (&$districtTotal) {
            $districtTotal[$order['take_district']] = $districtTotal[$order['take_district']] ?? 0 + 1;
        });


        $this->success("订单列表数据", compact("pageData", "districtTotal"));
    }

    // 49. 用户是否有配送权限
    public function isAuthDeliverOrder(Request $request) {
        $isAuth = UserInfoService::instance()->isAuthDeliverOrder($request->openid());

        if (!$isAuth) $this->error("没有配送权限");
        $this->success("配送权限", compact('isAuth'));
    }

    // 48. 我的核销列表
    public function getMyFinishOrderPageData(Request $request, OrderService $orderService) {
        $takeRange = $this->post("takeAtRange");
        if (empty($takeRange)) [$today = today(), $takeRange = [$today . " 00:00:00", $today . " 23:59:59"]];
        else {
            $takeRange[0] = date("Y-m-d 00:00:00", strtotime($takeRange[0]));
            $takeRange[1] = date("Y-m-d 23:59:59", strtotime($takeRange[1]));
        }
        $openid = $request->openid();
//        if ($openid == 'obzAZ7c85NvxGhEXyOEXnP6uY0JM') {
//            $openid    = 'obzAZ7ZH0ddefjJ3n45RhCFfWSPc';
//            $takeRange = [
//                date("Y-m-d 00:00:00", strtotime($takeRange[0]) - 86400 * 3),
//                date("Y-m-d 23:59:59", strtotime($takeRange[1]) - 86400)
//            ];
//        }
        $isAuth = UserInfoService::instance()->isAuthFinishOrder($openid);
        if (!$isAuth) $this->error("当前用户没有核销权限");
        $pageData = $orderService->getFinishOrderPageDataByTakeBy($openid, $takeRange);
        $orderService->bindSubs($pageData, "order_sn, goods_cover, goods_name, goods_number, goods_unit");
        $this->success("订单列表数据", compact("pageData"));
    }

    // 47. 用户开票申请
    public function invoiceApply(Request $request, OrderService $orderService, InvoiceApplyService $invoiceApplyService) {
        $post = $this->_vali([
            "sns.require"          => "订单编号不能为空",
            "buyer_type.require"   => "购买方类型不能为空",
            "buyer_type.in:0,1"    => "购买方类型超出取值范围",
            "invoice_type.require" => "开票类型不能为空",
            "invoice_type.in:0,1"  => "开票类型超出取值范围",
            "title.require"        => "收票名称不能为空",
            "taxno.default"        => "",
            "address.default"      => "",
            "phone.default"        => "",
            "bank_name.default"    => "",
            "bank_account.default" => "",
            "email.require"        => "收票邮箱不能为空",
            "email.email"          => "邮箱格式错误",
        ]);

//        $order = OrderService::instance()->getOrderBySn($post['order_sn'], "id,sn,status,openid,invoice_apply_at,invoice_email_at");

        $orders = $orderService->getListBySns($post['sns']);
        if (count($orders) == 0) $this->error("订单不存在");
        $openid = $request->openid();
        try {
            foreach ($orders as $order) {
                if ($openid != $order['openid']) VException::throw("订单不属于当前用户，无法申请开票");
                $invoiceApplyService->check($order);
            }

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        if ($post['invoice_type'] == InvoiceApplyService::BUYER_TYPE_E && empty($post['taxno'])) {
            $this->error("税号不能为空");
        }
        if ($post['invoice_type'] == InvoiceApplyService::BUYER_TYPE_E && !Validator::taxno($post['taxno'])) {
            $this->error("税号格式不正确");
        }

        $post['gid']       = guid();
        $post['openid']    = $openid;
        $post['order_sns'] = implode(",", $post['sns']);
        DB::startTrans();
        try {
            $apply = $invoiceApplyService->create($post);
            $now   = now();
            foreach ($orders as $order) {
                $order->invoice_apply_at = $now;
                $order->save();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $this->error($e->getMessage());
        }
        $data = compact("apply");
        $this->success("用户开票申请成功", $data);
    }

    // 46. 自动重置优惠券剩余数量（任意金额减20元）
    public function autoResetCouponHasCount(Request $request) {
        $res = [];

        $couponGid = "f2ae81edc035470b9dd5a01bdd477d24"; // 任意金额减20优惠券gid
        CouponPublishService::instance()->updateByGid($couponGid, ['has_count' => 50]);

        $data = compact("res");
        $this->success("自动重置优惠券剩余数量（任意金额减20元）", $data);
    }


    // 45. 供用户选择的配送区域
    public function getCities() {
        $cities = config("a.cities");
        $data   = compact("cities");
        $this->success("success", $data);
    }

    // 44. 订单详情用于核销订单
    public function getOrderDetailForFinish(Request $request, OrderService $orderService, $sn) {
        try {
            if (!Str::startsWith($sn, "ORDER")) $sn = decode($sn);
        } catch (Exception $e) {
            $this->error("订单编号不存在");
        }
        $openid = $request->openid();
        $isAuth = UserInfoService::instance()->isAuthFinishOrder($openid);
        $isAuth2 = UserInfoService::instance()->isAuthDeliverOrder($openid);

        try {
            $order = $orderService->getDetailBySn($sn, $openid, $isAuth||$isAuth2);
            if ($order->take_type != OrderService::TAKE_TYPE_SELF) VException::throw("不是自提订单，不允许核销");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $station = $order->station;
        $address = $order->address;
        $coupon  = $order->coupon;
        $this->success("订单详情", compact("order", "station", "address", "coupon"));
    }

    // 43. 自动任务
    public function autoTask(OrderService $orderService) {
        $count = $orderService->autoClean();
        $msg   = "清理订单 {$count} 个";
        $count = $orderService->autoTake();
        $msg   .= ", 自动收货自提订单 {$count} 个";
        $this->success($msg);
    }

    // 42. 自动收货订单
    public function autoTakeOrder(OrderService $orderService) {
        $count = $orderService->autoTake();
        $this->success("自动收货订单 {$count} 个");
    }

    // 41. 自动清理订单
    public function autoCleanOrder(OrderService $orderService) {
        $count = $orderService->autoClean();
        $this->success("自动清理订单 {$count} 个");
    }

    // 40. 用户取消订单操作
    public function cancelOrder(Request $request, OrderService $orderService, $sn) {
        $order = $orderService->getOneBySn($sn);
        if (empty($order)) $this->error("订单不存在");
        if ($order->openid != $request->openid()) $this->error("订单不属于当前用户");

        try {
            $order = $orderService->cancelOrder($order);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("取消成功", compact("order"));
    }

    // 39. 根据微信code获取手机号
    public function getPhoneNumberByCode(Request $request, UserInfoService $userInfoService) {
        $code = $this->post('code');
        if (empty($code)) $this->error("code不能为空");
        $config = ConfigService::instance()->getConfigMini();
        $res    = Crypt::instance($config)->getPhoneNumber($code);
        if (isset($res['phone_info']['phoneNumber'])) {
            $phone = $res['phone_info']['phoneNumber'];
        } else $this->error("获取手机号码失败");
        $this->success("获取手机号码", compact("phone"));
    }

    // 38. 用户是否有核销权限
    public function isAuthFinishOrder(Request $request) {
        $isAuth = UserInfoService::instance()->isAuthFinishOrder($request->openid());

        if (!$isAuth) $this->error("没有核销权限");
        $this->success("核销权限", compact('isAuth'));
    }

    // 37. 获取订单核销二维码
    public function getFinishOrderQrcode(Request $request, OrderService $orderService, $sn) {
        $order = $orderService->getOneBySn($sn, "sn,openid,status");
        if (empty($order)) $this->error("订单不存在");
        if ($order->openid != $request->openid()) $this->error("订单不属于当前用户");
        if ($order->status != OrderService::ORDER_STATUS_DELIVERING) $this->error("订单状态不匹配，无法生成核销二维码");

        $info = $orderService->getFinishOrderQrcode($sn);
        $url  = $info['url'];
        $this->success("二维码", compact("url"));
    }

    // 36. 自提订单核销（设为已完成）
    public function finishOrder(Request $request, OrderService $orderService, UserInfoService $userInfoService, $sn) {
        if (!Str::startsWith($sn, "ORDER")) $sn = decode($sn);
        $order = $orderService->getOneBySn($sn, "sn,openid,take_type,status,station_gid");
        if (empty($order)) $this->error("订单不存在");
        // 检查核销权限
        $openid = $request->openid();
        if (!$userInfoService->isAuthFinishOrder($openid)) $this->error("当前用户没有核销订单权限");
        if ($order->take_type != OrderService::TAKE_TYPE_SELF) $this->error("不是自提订单，不允许核销");

        try {
            $order = $orderService->finishOrder($order, $openid);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $order = $orderService->getOneBySn($sn);
        $orderService->bindSub($order);
        if ($order->coupon_gid) {
            $coupon        = UserCouponService::instance()->getByGid($order->coupon_gid, "gid,title");
            $order->coupon = $coupon;
        } else $order->coupon = null;
        $this->success("订单核销成功", compact("order"));
    }

    // 35. 得到我的页服务菜单
    public function getServiceMenu(Request $request, ServiceMenuService $serviceMenuService) {
        $list = $serviceMenuService->getListWithOpenid($request->openid())->toArray();

        // 处理插件权限
        MyAddonService::instance()->menuAuthFilter($list);


        $data = compact("list");
        $this->success("服务菜单", $data);
    }

    // 34. 得到应用配置
    public function getAppConfig() {
        $config = ConfigService::instance()->getAppConfig();
        $data   = compact("config");
        $this->success("得到应用配置", $data);
    }

    // 33. 申请退款
    public function refundApply(Request $request, OrderService $orderService, $sn) {
        ['reason' => $reason] = $this->_vali(["reason.require" => "请输入退款原因"]);
        $order = $orderService->getOneBySn($sn, "sn,openid,status,pay_amount,pay_at");
        if (empty($order)) $this->error("订单不存在");
        if ($order->openid != $request->openid()) $this->error("申请失败，订单不属于当前用户");
        if ($order->status != 1) $this->error("申请失败，订单不是配送中状态");
        try {
            $order = $orderService->refundApply($order, $reason);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("申请退款成功", compact("order"));
    }
    public function refundApply_dev(Request $request, OrderService $orderService, $sn) {
        ['reason' => $reason] = $this->_vali(["reason.require" => "请输入退款原因"]);
        $order = $orderService->getOneBySn($sn, "sn,openid,status,pay_amount,pay_at");
        if (empty($order)) $this->error("订单不存在");
        if ($order->openid != $request->openid()) $this->error("申请失败，订单不属于当前用户");
        if ($order->status != 1) $this->error("申请失败，订单不是配送中状态");
        try {
            $order = $orderService->refundApply_dev($order, $reason);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("申请退款成功", compact("order"));
    }

    // 32. 支付预下单
    public function getPayInfo(Request $request, OrderService $orderService) {
        $post   = $this->_vali([
            "type.require" => "下单类型",
            "gid.require"  => "下单编号",
        ]);
        $type   = $post['type'];
        $config = ConfigService::instance()->getConfigMini();

        if ($type == 'recharge') {
            $rechargeWay = RechargeWayService::instance()->getOneByGid($post['gid'], "gid,money,give_money");
            $money       = bcmul($rechargeWay->money, 100);
            $desc        = "充值{$rechargeWay->money}";
            if (floatval($rechargeWay->give_money)) $desc .= "送" . $rechargeWay->give_money;
            $attach = ["type" => "recharge", "gid" => $rechargeWay->gid];
            $gid    = uuid();
        } elseif ($type == "order") {
            $order = $orderService->getOneBySn($post['gid']);
            if (empty($order)) $this->error("订单不存在");
            if ($order->status != 0) $this->error("订单已支付");
            if ($order->openid != $request->openid()) $this->error("订单不属于当前用户");

            try {
                $orderService->checkBeforePay($order, $request->openid());
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }

            $money  = bcmul($order->pay_amount, 100);
            $desc   = "订单支付";
            $attach = ["type" => "order", "sn" => $post['gid']];
            $gid    = $order->sn;
        }

        $wechat = Pay::instance($config);
        $opts   = [
            'body'             => $desc,
            'out_trade_no'     => $gid,
            'total_fee'        => $money,
            'openid'           => $request->openid(),
            'trade_type'       => 'JSAPI',
            'notify_url'       => $config['notify_url'],
            'spbill_create_ip' => '127.0.0.1',
        ];
        if ($attach) $opts['attach'] = json_encode($attach);
        $payInfo = $wechat->createOrder($opts);
        if (empty($payInfo['prepay_id'])) $this->error("预下单失败");
        $payInfo = $wechat->createParamsForJsApi($payInfo['prepay_id']);
        $this->success("预下单", compact("payInfo"));
    }

    // 31. 充值方式列表
    public function rechargeWayList() {
        $list       = RechargeWayService::instance()->search(['status' => 1])->order("sort desc,id desc")
            ->select();
        $remarkList = ConfigService::instance()->getRechargeWayRemark();


        $data = compact("list", "remarkList");
        $this->success('充值方式列表', $data);
    }

    // 30. 更新用户信息
    public function updateUserInfo(Request $request, UserInfoService $userInfoService, UserTempService $userTempService) {
        $post   = $this->_vali([
            "nickname.require" => "请输入昵称",
            "realname.default" => "",
            "avatar.require"   => "请选择图片",
            "phone.require"    => "请选择手机号",
            "phone.mobile"     => "手机号格式错误"
        ]);
        $openid = $request->openid();
        alert($openid . $post['phone']);
        $userInfo = $userInfoService->getModel()->where("openid", "<>", $openid)
            ->field("id,phone,openid")
            ->where("phone", $post['phone'])->find();

        if (!empty($userInfo)) $this->error("电话号码已存在，请更换");
        $userTemp = $userTempService->search(['phone' => $post['phone'], 'status' => 1])->find();
        Db::startTrans();
        try {
            if (!empty($userTemp) && $userTemp->money > 0) {
                $userInfo = $userInfoService->getModel()->field("phone,openid,money")->where("phone", $post['phone'])->lock(true)->find();

                $post['realname'] = $userTemp->realname;
                $post['money']    = bcadd($userInfo->money, $userTemp->money, 2);
                $userTemp->status = 0;
                $userTemp->save();

                // 增加余额初始化日志
                UserMoneyLogService::instance()->create([
                    'gid'            => uuid(),
                    'openid'         => $openid,
                    'before'         => $userInfo->money,
                    'delta'          => $userTemp->money,
                    'log_type'       => UserMoneyLogService::MONEY_LOG_TYPE_IMPORT,
                    'target_gid'     => "",
                    'transaction_id' => "",
                    'remark'         => "初始化时，从excel导入",
                ]);
            }
            // $post['money_expire_at'] = date("Y-m-d H:i:s", time() + config("a.money_expire_add_sec"));
            $post['money_expire_at'] = null;
            $userInfoService->updateByOpenid($openid, $post);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }


        $userInfo  = $userInfoService->getUserInfoByOpenid($openid);
        $tokenData = $userInfoService->genJWTToken($userInfo);
        $this->success('更新用户成功', $tokenData);
    }

    // 29. 上传图片
    public function upload() {
        $file = $this->request->file("file");
        try {
            $info = FileUpload::instance()->save($file);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $url = $info['url'];
        $this->success('上传成功', compact('url'));
    }

    // 28. 我的优惠券数量和余额
    public function getUserBarData(Request $request) {
        $couponCount = UserCouponService::instance()->getMyCouponCount($request->openid());
        $userInfo    = UserInfoService::instance()->getOneByOpenid($request->openid());
        if (!empty($userInfo)) {
            $balance   = $userInfo->money;
            $expire_at = $userInfo->money_expire_at;
        } else {
            $balance   = 0;
            $expire_at = "";
        }
        $data = compact("couponCount", "balance", "expire_at");
        $this->success("得到数量", $data);
    }

    // 27. 我的优惠券列表
    public function getMyCouponPageData(Request $request, UserCouponService $userCouponService) {
        $pageData = $userCouponService->getCouponPageDataUsableByOpenid($request->openid());
        $data     = compact('pageData');
        $this->success("商品分页列表获取成功", $data);
    }

    // 26. 领取优惠券
    public function fetchCoupon(Request $request, UserCouponService $userCouponService, $gid) {
        try {
            $userCouponService->fetchCoupon($request->openid(), $gid);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success("优惠券领取成功，请至[我的优惠券]中查看");
    }

    // 25. 得到余额记录分页列表
    public function getUserMoneyLogPageData(Request $request, UserMoneyLogService $moneyLogService) {
        $pageData = $moneyLogService->search(['openid' => $request->openid()])->order("id desc")->paginate();
        $moneyLogService->bindPageData($pageData);
        $data = compact('pageData');
        $this->success("得到余额记录分页列表", $data);
    }

    // 24. 用户收货操作
    public function takeOrder(Request $request, OrderService $orderService, $sn) {
        $order = $orderService->getOneBySn($sn, "sn,openid,status");
        if (empty($order)) $this->error("订单不存在");
        if ($order->openid != $request->openid()) $this->error("订单不属于当前用户");

        try {
            $order = $orderService->finishOrder($order);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("收货成功", compact("order"));
    }

    // 23. 得到订单确认页面数据
    public function getInitDataConfirm() {
        // 收货方式
        $takeTypes = config("a.order_take_types");
        $this->success("收货方式", compact("takeTypes"));
    }

    // 22. 订单详情
    public function getOrderDetail(Request $request, OrderService $orderService, $sn) {
        $openid = $request->openid();
        $isAuth = UserInfoService::instance()->isAuthFinishOrder($openid);
        $isAuth2 = UserInfoService::instance()->isAuthDeliverOrder($openid);

        try {
            $order = $orderService->getDetailBySn($sn, $openid, $isAuth||$isAuth2);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $station  = $order->station;
        $address  = $order->address;
        $coupon   = $order->coupon;

        if($order->status == 0){
            $payTypes = config("a.order_pay_types");
        }else{
            $payTypes = array_merge(config("a.order_pay_types"), config("a.com_order_pay_types"));
        }

        $this->success("订单详情", compact("order", "station", "address", "coupon", "payTypes"));
    }

    // 21. 订单列表数据
    public function getOrderPageDataByStatus(Request $request, OrderService $orderService, $status) {
        $pageData = $orderService->getOrderPageDataByStatus($request->openid(), $status);
        $orderService->bindSubs($pageData, "order_sn, goods_cover");
        $this->success("订单列表数据", compact("pageData"));
    }

    public function getOrderPageDataByStatus_dev(Request $request, OrderService $orderService, $status) {
        $openid = $request->openid();
        if (isDev($request->openid())) $openid = "obzAZ7bTnvqnJkAW49PgW0Ga4MNM";

        $pageData = $orderService->getOrderPageDataByStatus($openid, $status);
        $orderService->bindSubs($pageData, "order_sn, goods_cover");
        $this->success("订单列表数据", compact("pageData"));
    }

    // 20. 订单列表页，表头
    public function getOrderTabsData(OrderService $orderService) {
        $tabsData        = $orderService->getTabsData();
        $finishQrcodeTip = config("a.finish_qrcode_tip");
        $this->success("订单表头", compact("tabsData", "finishQrcodeTip"));
    }

    // 19. 我的页，订单数量
    public function getUserOrderTabsData(Request $request, OrderService $orderService) {
        $tabsData = $orderService->getMyUserOrderTabsDataWithCount($request->openid());
        $this->success("我的页订单数量", compact("tabsData"));
    }

    // 18. 得到水站列表(含距离)
    public function getStationListWithDistance(Request $request, WaterStationService $stationService) {
        $post = $this->_vali([
            "lat.require" => "请选择位置",
            "lng.require" => "请选择位置",
        ]);
        $list = $stationService->getStationListWithDistance($post, "gid,title,province,city,district,detail,link_name,link_phone,is_open,open_time,remark,lng,lat");

        $this->success("水站列表", compact("list"));
    }


    // 17. 删除我的收货地址
    public function delAddress(Request $request, $gid, UserAddressService $addressService) {
        $res = $addressService->mQuery()->where([
            "gid"    => $gid,
            "openid" => $request->openid(),
        ])->update(['deleted' => 1]);
        if ($res) $this->success("删除成功");
        else $this->error("删除失败");
    }

    // 16. 得到我的收货地址详情
    public function getAddressDetail(Request $request, $gid, UserAddressService $addressService) {
        $address = $addressService->getOneByGid($gid);
        if (empty($address)) $this->error("地址不存在");
        if ($address->openid != $request->openid()) $this->error("收货地址不属于当前用户");
        $this->success("收货地址详情", compact("address"));
    }

    // 15. 新增（或修改）我的收货地址
    public function saveAddress(Request $request, UserAddressService $addressService) {
        $post = $this->_vali([
            "gid.default"        => "",
            "district.require"   => "请选择区域",
            "detail.require"     => "请输入详细地址",
            "link_name.require"  => "请输入联系人",
            "link_phone.require" => "请输入联系电话",
            "is_default.in:0,1"  => "超出取值范围",
        ]);

        $post['province'] = "天津市";
        $post['city']     = "天津市";
        $post['street']   = "";

        // 验证电话号码格式是否正确
        $post['link_phone'] = trim($post['link_phone']);
        if (!isPhone($post['link_phone'])) $this->error("联系电话格式不正确");

        if (empty($post['gid'])) {
            if (!empty($post['is_default'])) $addressService->setIsDefaultBatchByOpenid($request->openid());
            $post['gid']    = uuid();
            $post['openid'] = $request->openid();
            $post           = $addressService->create($post);
        } else {
            $address = $addressService->getOneByGid($post['gid'], "gid,openid");
            if (empty($address)) $this->error("地址不存在");
            if (($address->openid != $request->openid())) $this->error("收货地址不属于当前用户");
            if (!empty($post['is_default'])) $addressService->setIsDefaultBatchByOpenid($request->openid());
            $addressService->updateByGid($post['gid'], $post);
        }
        $this->success("保存成功", ['item' => $post]);
    }

    // 14. 我的收货地址列表
    public function getAddressList(Request $request) {
        $list = UserAddressService::instance()->getListByOpenid($request->openid());
        $this->success("我的收货地址列表", compact("list"));
    }

    // 13. 支付成功页面数据
    public function getInitDataPaySuccess(Request $request, OrderService $orderService, $sn, $type) {
        if ($type == 'order') {
            $order = $orderService->getOneBySn($sn, "sn,openid,pay_amount,deliver_amount");
            if (!$order) $this->error("订单不存在");
            if ($order->openid != $request->openid()) $this->error("订单不属于当前用户");
        }
        $money = $order->pay_amount;
        $this->success("支付成功", compact('money'));
    }

    // 12. 支付订单
    public function payOrder(Request $request, OrderService $orderService, $sn) {
        $pay_type = $request->post("pay_type");
        if (empty($pay_type)) $this->error("请选择支付方式");
        if ($pay_type == 'yue') {
            try {
                $res = $orderService->payOrderWithYue($sn, $request->openid());
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success("余额支付成功");
        } elseif ($pay_type == 'giftcard') {
            $giftCardSns = $request->post("gift_card_sns");
            if (empty($giftCardSns)) $this->error("请选择实物卡");
            try {
                $res = $orderService->payOrderWithGiftCard($sn, $giftCardSns, $request->openid());
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success("余额支付成功");
        } elseif ($pay_type == 'weixin') {
            dd("微信支付");
        } else {
            $this->error("不支持的支付方式");
        }

    }

    // 11. 创建订单
    public function createOrderFromConfirmV2(Request $request, OrderService $orderService) {
        $post    = $this->_vali([
            "from.require"          => "参数错误",
            "goods_sn.default"      => "",
            "goods_number.default"  => "",
            "take_type.default"     => "",
            "address_gid.default"   => "",
            "station_gid.default"   => "",
            "coupon_gid.default"    => "",
            "remark.default"        => "",
            "pay_type.default"      => "",
            "gift_card_sns.default" => "",
        ]);
        $openid  = $request->openid();
        $address = null;
        if ($post['take_type'] == OrderService::TAKE_TYPE_SELF) { // 自提
            if (empty($post['station_gid'])) $this->error("请选择自提水站");
        } elseif ($post['take_type'] == OrderService::TAKE_TYPE_DELIVER) { // 配送
            if (empty($post['address_gid'])) $this->error("请选择收货地址");

            $address = UserAddressService::instance()->getOneByGid($post['address_gid']);
            if (!$address) VException::runtime("收货地址不存在");
            elseif ($address->openid != $openid) VException::runtime("收货地址不属于当前用户");
        } else $this->error("不支持的收货方式");

        try {
            $orderInfo           = $orderService->getOrderInfoFromCart($post, $openid, $address, $post['coupon_gid']);
            $orderInfo['remark'] = $post['remark'];

            if ($post['pay_type'] == 'yue') {
                if ($orderInfo['goods_type'] == 2 && !config("a.is_gift_card_order_use_yue")) VException::runtime("实物卡商品不允许使用余额支付");
                UserInfoService::instance()->checkMoney($openid, $orderInfo['pay_amount']);
            } elseif ($post['pay_type'] == 'giftcard') {
                GiftCardService::instance()->checkGiftCard($openid, $post['gift_card_sns'], $orderInfo);
                // $orderInfo['gift_card_sns'] = $post['gift_card_sns'];
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        if (empty($orderInfo['subs'])) $this->error("购买商品为空");

        if ($post['take_type'] == OrderService::TAKE_TYPE_SELF) { // 自提
            $station                    = WaterStationService::instance()->getOneByGid($post['station_gid'], "gid,title,province,city,district,street,detail,link_name,link_phone");
            $orderInfo['station_gid']   = $station->gid;
            $orderInfo['station_title'] = $station->title;

            $address_ = "$station->detail,$station->title";
            if ($station->street) $address_ = $station->street . "," . $address_;
            if ($station->district) $address_ = $station->district . "," . $address_;
            if ($station->city) $address_ = $station->city . "," . $address_;
            if ($station->province) $address_ = $station->province . "," . $address_;
            $orderInfo['station_address'] = $address_;

            $orderInfo['station_link_name']  = $station->link_name;
            $orderInfo['station_link_phone'] = $station->link_phone;

            // 0自提：客户选择的自提点为出库仓库
            $orderInfo['depot_gid'] = $station->gid; // 出库仓库
        } elseif ($post['take_type'] == OrderService::TAKE_TYPE_DELIVER) { // 配送
            $orderInfo['address_gid'] = $address->gid;

            $address_ = $address->detail;
            if ($address->street) $address_ = $address->street . "," . $address_;
            if ($address->district) $address_ = $address->district . "," . $address_;
            if ($address->city) $address_ = $address->city . "," . $address_;
            if ($address->province) $address_ = $address->province . "," . $address_;
            $orderInfo['take_address'] = $address_;

            $orderInfo['take_name']     = $address->link_name;
            $orderInfo['take_phone']    = $address->link_phone;
            $orderInfo['take_province'] = $address->province;
            $orderInfo['take_city']     = $address->city;
            $orderInfo['take_district'] = $address->district;
            $orderInfo['take_street']   = $address->street;

            // 1配送：系统配置的默认的库房或水站（或二期自动匹配）
            $orderInfo['depot_gid'] = DepotService::instance()->getDefaultDepotOrStationGid();
        }

        // 创建订单
        Db::startTrans();
        try {
            $order = $orderService->createFromConfirm($orderInfo);

            if ($order->coupon_gid) {
                UserCouponService::instance()->writeOff($order->coupon_gid, $order);
            }

            if ($post['from']) {
                // 清除已选中的购物车商品
                CartService::instance()->clearCheckedCart($openid);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        $this->success("创建订单成功", ['sn' => $order->sn]);
    }

    public function createOrderFromConfirm(Request $request, OrderService $orderService) {
        $post = $this->_vali([
            "from.require"         => "参数错误",
            "goods_sn.default"     => "",
            "goods_number.default" => "",
            "take_type.default"    => "",
            "address_gid.default"  => "",
            "station_gid.default"  => "",
            "coupon_gid.default"   => "",
            "remark.default"       => "",
            "pay_type.default"     => "",

        ]);

        $address = null;
        if ($post['take_type'] == OrderService::TAKE_TYPE_SELF) { // 自提
            if (empty($post['station_gid'])) $this->error("请选择自提水站");
        } elseif ($post['take_type'] == OrderService::TAKE_TYPE_DELIVER) { // 配送
            if (empty($post['address_gid'])) $this->error("请选择收货地址");

            $address = UserAddressService::instance()->getOneByGid($post['address_gid']);
            if (!$address) VException::runtime("收货地址不存在");
            elseif ($address->openid != $request->openid()) VException::runtime("收货地址不属于当前用户");
        } else $this->error("不支持的收货方式");

        try {
            $orderInfo           = $orderService->getOrderInfoFromCart($post, $request->openid(), $address, $post['coupon_gid']);
            $orderInfo['remark'] = $post['remark'];

            if ($post['pay_type'] == 'yue') {
                if ($orderInfo['goods_type'] == 2 && !config("a.is_gift_card_order_use_yue")) VException::runtime("实物卡商品不允许使用余额支付");
                UserInfoService::instance()->checkMoney($request->openid(), $orderInfo['pay_amount']);
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        if (empty($orderInfo['subs'])) $this->error("购买商品为空");

        if ($post['take_type'] == OrderService::TAKE_TYPE_SELF) { // 自提
            $station                    = WaterStationService::instance()->getOneByGid($post['station_gid'], "gid,title,province,city,district,street,detail,link_name,link_phone");
            $orderInfo['station_gid']   = $station->gid;
            $orderInfo['station_title'] = $station->title;

            $address_ = "$station->detail,$station->title";
            if ($station->street) $address_ = $station->street . "," . $address_;
            if ($station->district) $address_ = $station->district . "," . $address_;
            if ($station->city) $address_ = $station->city . "," . $address_;
            if ($station->province) $address_ = $station->province . "," . $address_;
            $orderInfo['station_address'] = $address_;

            $orderInfo['station_link_name']  = $station->link_name;
            $orderInfo['station_link_phone'] = $station->link_phone;
        } elseif ($post['take_type'] == OrderService::TAKE_TYPE_DELIVER) { // 配送
            $orderInfo['address_gid'] = $address->gid;

            $address_ = $address->detail;
            if ($address->street) $address_ = $address->street . "," . $address_;
            if ($address->district) $address_ = $address->district . "," . $address_;
            if ($address->city) $address_ = $address->city . "," . $address_;
            if ($address->province) $address_ = $address->province . "," . $address_;
            $orderInfo['take_address'] = $address_;

            $orderInfo['take_name']     = $address->link_name;
            $orderInfo['take_phone']    = $address->link_phone;
            $orderInfo['take_province'] = $address->province;
            $orderInfo['take_city']     = $address->city;
            $orderInfo['take_district'] = $address->district;
            $orderInfo['take_street']   = $address->street;

        }

        // 创建订单
        Db::startTrans();
        try {
            $order = $orderService->createFromConfirm($orderInfo);

            if ($order->coupon_gid) {
                UserCouponService::instance()->writeOff($order->coupon_gid, $order);
            }

            if ($post['from']) {
                // 清除已选中的购物车商品
                CartService::instance()->clearCheckedCart($request->openid());
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        $this->success("创建订单成功", ['sn' => $order->sn]);
    }

    public function createOrderFromConfirm_dev(Request $request, OrderService $orderService) {
        $post = $this->_vali([
            "from.require"         => "参数错误",
            "goods_sn.default"     => "",
            "goods_number.default" => "",
            "take_type.default"    => "",
            "address_gid.default"  => "",
            "station_gid.default"  => "",
            "coupon_gid.default"   => "",
            "remark.default"       => "",
            "pay_type.default"     => "",

        ]);

        $address = null;
        if ($post['take_type'] == OrderService::TAKE_TYPE_SELF) { // 自提
            if (empty($post['station_gid'])) $this->error("请选择自提水站");
        } elseif ($post['take_type'] == OrderService::TAKE_TYPE_DELIVER) { // 配送
            if (empty($post['address_gid'])) $this->error("请选择收货地址");

            $address = UserAddressService::instance()->getOneByGid($post['address_gid']);
            if (!$address) VException::runtime("收货地址不存在");
            elseif ($address->openid != $request->openid()) VException::runtime("收货地址不属于当前用户");
        } else $this->error("不支持的收货方式");

        try {
            $orderInfo           = $orderService->getOrderInfoFromCart($post, $request->openid(), $address, $post['coupon_gid']);
            $orderInfo['remark'] = $post['remark'];

            if ($post['pay_type'] == 'yue') {
                UserInfoService::instance()->checkMoney($request->openid(), $orderInfo['pay_amount']);
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        if (empty($orderInfo['subs'])) $this->error("购买商品为空");

        if ($post['take_type'] == OrderService::TAKE_TYPE_SELF) { // 自提
            $station                    = WaterStationService::instance()->getOneByGid($post['station_gid'], "gid,title,province,city,district,street,detail,link_name,link_phone");
            $orderInfo['station_gid']   = $station->gid;
            $orderInfo['station_title'] = $station->title;

            $address_ = "$station->detail,$station->title";
            if ($station->street) $address_ = $station->street . "," . $address_;
            if ($station->district) $address_ = $station->district . "," . $address_;
            if ($station->city) $address_ = $station->city . "," . $address_;
            if ($station->province) $address_ = $station->province . "," . $address_;

            $orderInfo['station_address']    = $address_;
            $orderInfo['station_link_name']  = $station->link_name;
            $orderInfo['station_link_phone'] = $station->link_phone;
        } elseif ($post['take_type'] == OrderService::TAKE_TYPE_DELIVER) { // 配送
            $orderInfo['address_gid'] = $address->gid;

            $address_ = $address->detail;
            if ($address->street) $address_ = $address->street . "," . $address_;
            if ($address->district) $address_ = $address->district . "," . $address_;
            if ($address->city) $address_ = $address->city . "," . $address_;
            if ($address->province) $address_ = $address->province . "," . $address_;
            $orderInfo['take_address'] = $address_;

            $orderInfo['take_name']     = $address->link_name;
            $orderInfo['take_phone']    = $address->link_phone;
            $orderInfo['take_province'] = $address->province;
            $orderInfo['take_city']     = $address->city;
            $orderInfo['take_district'] = $address->district;
            $orderInfo['take_street']   = $address->street;

        }

        // 创建订单
        Db::startTrans();
        try {
            $order = $orderService->createFromConfirm($orderInfo);

            // 分派配送员
            if ($post['take_type'] == OrderService::TAKE_TYPE_DELIVER) {
                $orderService->assignDeliver($order->sn, $order->take_district);
            }

            if ($order->coupon_gid) {
                UserCouponService::instance()->writeOff($order->coupon_gid, $order);
            }

            if ($post['from']) {
                $cartService = CartService::instance();
                $cartService->clearCart($request->openid());
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

        $this->success("创建订单成功", ['sn' => $order->sn]);
    }

    // 10. 订单确认数据
    public function getOrderConfirmInfoV2(Request            $request, OrderService $orderService,
                                          UserAddressService $addressService,
                                          UserCouponService  $userCouponService) {
        $post = $this->_vali([
            "from.require"         => "参数错误",
            "goods_sn.default"     => "",
            "goods_number.default" => "",
            "take_type.default"    => OrderService::TAKE_TYPE_SELF,
            "address_gid.default"  => "",
            "station_gid.default"  => "",
            "coupon_gid.default"   => "",
        ]);

        $openid = $request->openid();

        // 用户收货地址
        if (empty($post['address_gid'])) {
            $address = $addressService->getDefaultAddressByOpenid($openid);
        } else {
            $address = $addressService->getOneByGid($post['address_gid']);
            if (!empty($address) && $address->openid != $openid) $this->error("收货地址不属于当前用户");
        }

        try {
            $orderInfo = $orderService->getOrderInfoFromCart($post, $openid, $address, $post['coupon_gid']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        // 可用优惠券列表
        $couponList = $userCouponService->getUsableListByOpenid($openid, $orderInfo['goods_amount']);

        $coupon = $orderInfo['coupon'];
        unset($orderInfo['coupon']);

        // 自提水站列表
        $stationList = WaterStationService::instance()->getListForOrderConfirm();

        // 免运费文字
        $freeDeliverTxt = $orderInfo['free_deliver_txt'];

        // 支付方式
        $payTypes = config("a.order_pay_types");

        $data = compact("orderInfo", "couponList", "address", "stationList", "coupon", "freeDeliverTxt", "payTypes");
        $this->success("订单确认数据", $data);
    }

    public function getOrderConfirmInfo(Request $request, OrderService $orderService, UserAddressService $addressService, UserCouponService $userCouponService) {
        $post = $this->_vali([
            "from.require"         => "参数错误",
            "goods_sn.default"     => "",
            "goods_number.default" => "",
            "take_type.default"    => OrderService::TAKE_TYPE_SELF,
            "address_gid.default"  => "",
            "station_gid.default"  => "",
            "coupon_gid.default"   => "",
        ]);

        // 用户收货地址
        if (empty($post['address_gid'])) {
            $address = $addressService->getDefaultAddressByOpenid($request->openid());
        } else {
            $address = $addressService->getOneByGid($post['address_gid']);
            if (!empty($address) && $address->openid != $request->openid()) $this->error("收货地址不属于当前用户");
        }

        try {
            $orderInfo = $orderService->getOrderInfoFromCart($post, $request->openid(), $address, $post['coupon_gid']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        // 可用优惠券列表
        $couponList = $userCouponService->getUsableListByOpenid($request->openid(), $orderInfo['goods_amount']);

        $coupon = $orderInfo['coupon'];
        unset($orderInfo['coupon']);

        // 自提水站列表
        $stationList = WaterStationService::instance()->getListForOrderConfirm();

        // 免运费文字
        $freeDeliverTxt = $orderInfo['free_deliver_txt'];

        $data = compact("orderInfo", "couponList", "address", "stationList", "coupon", "freeDeliverTxt");
        $this->success("订单确认数据", $data);
    }

    // 9. 得到商品详情
    public function goodsDetail(Request $request, GoodsService $goodsService, $sn) {
        $goods = $goodsService->getShowOneBySn($sn);
        if (empty($goods)) $this->error("商品不存在");
        $data = compact("goods");
        $this->success("得到商品详情", $data);
    }

    // 8. 得到购买车商品列表
    public function getInitDataCart(Request $request, CartService $cartService) {
        $cartService = CartService::instance();
        $cart        = $cartService->getOneByOpenid($request->openid(), "gid,goods_snap");

        $cartService->bind($cart);
        $data = compact("cart");
        $this->success("购物车", $data);
    }

    // 7. 得到购物车总数量
    public function getCartTotal(Request $request, CartService $cartService) {
        $openid = $request->openid();
        if (empty($openid)) {
            $total = 0;
        } else {
            $total = $cartService->getCartTotalByOpenid($openid);
        }

        $data = compact("total");
        $this->success("添加到购物车", $data);
    }

    // 6-4. 选择到购物车
    public function check2cart(Request $request, CartService $cartService) {
        $post   = $this->_vali([
            "sn.require"         => "商品SN不能为空",
            "is_checked.require" => "商品是否选择",
        ]);
        $openid = $request->openid();
        $cart   = $cartService->check2cart($openid, $post['sn'], $post['is_checked']);
        $cartService->bind($cart);
        $total  = $cart['total'];
        $amount = $cart['amount'];

        $data = compact("total", "amount", "cart");
        $this->success("更新到购物车", $data);
    }

    // 6-3. 删除到购物车
    public function del2cart(Request $request, CartService $cartService) {
        $post   = $this->_vali([
            "sn.require" => "商品SN不能为空",
        ]);
        $openid = $request->openid();
        $cart   = $cartService->del2cart($openid, $post['sn']);
        $cartService->bind($cart);
        $total  = $cart['total'];
        $amount = $cart['amount'];

        $data = compact("total", "amount", "cart");
        $this->success("更新到购物车", $data);
    }

    // 6-2. 更新到购物车
    public function update2cart(Request $request, CartService $cartService) {
        $post   = $this->_vali([
            "sn.require"     => "商品SN不能为空",
            "number.require" => "商品数量不能为空",
        ]);
        $openid = $request->openid();
        $cart   = $cartService->operate2cart($openid, $post['sn'], $post['number'], "update");
        $cartService->bind($cart);
        $total  = $cart['total'];
        $amount = $cart['amount'];

        $data = compact("total", "amount", "cart");
        $this->success("更新到购物车", $data);
    }

    // 6-1. 添加到购物车
    public function add2cart(Request $request, CartService $cartService) {
        $post   = $this->_vali([
            "sn.require"     => "商品SN不能为空",
            "number.require" => "商品数量不能为空",
        ]);
        $openid = $request->openid();
        $cart   = $cartService->operate2cart($openid, $post['sn'], $post['number'], "add");
        $cartService->bind($cart);
        $total  = $cart['total'];
        $amount = $cart['amount'];

        $data = compact("total", "amount");
        $this->success("添加到购物车", $data);

    }

    // 5-2. 得到实物卡商品分页列表
    public function getGiftCardGoodsPageData(Request $request, GoodsService $goodsService) {
        $pageData = $goodsService->search(['goods_type' => 2, 'status' => 1, "is_show" => 1])->order("sort desc, id desc")->paginate();
        $list     = $pageData->items();
        $openid   = $request->openid();
        if ($openid && count($list) > 0) {
            $goodsService->bindList($list, $openid);
            $pageData->data = $list;
        }
        $data = compact('pageData');
        $this->success("商品分页列表获取成功", $data);
    }

    // 5-1. 得到商品分页列表
    public function getGoodsPageData(Request $request, GoodsService $goodsService) {
        $pageData = $goodsService->search(['goods_type' => 0, 'status' => 1, "is_show" => 1])->order("sort desc, id desc")->paginate();
        $list     = $pageData->items();
        $openid   = $request->openid();
        if ($openid && count($list) > 0) {
            $goodsService->bindList($list, $openid);
            $pageData->data = $list;
        }
        $data = compact('pageData');
        $this->success("商品分页列表获取成功", $data);
    }

    // 4. 得到实时用户信息
    public function getUserInfo(Request $request, UserInfoService $userInfoService) {
        $openid = $request->openid();
//        dd($openid);
        $userInfo = UserInfoService::instance()->getOneByOpenid($openid);
        if (empty($userInfo)) $this->error("用户不存在");
        elseif (empty($userInfo['status'])) $this->error("用户已被禁用");
        $this->success("用户信息获取", compact('userInfo'));
    }

    // 3. 首页页面数据
    public function getInitDataIndex(Request $request, ArticleService $articleService) {
        $openid     = $request->openid();
        $swiperList = $articleService->getSwiperList();
        $noticeList = $articleService->getNoticeList();
        if (empty($openid)) {
            $couponList = CouponPublishService::instance()->getListForFetchWithHasCount0();
        } else {
            $couponList = CouponPublishService::instance()->getListForOpenidWithHasCount0($openid);
        }
        $hotGoodsList    = GoodsService::instance()->getHotGoodsList($openid);
        $hotGiftCardList = GoodsService::instance()->getHotGiftCardList($openid);
        $aboutContent    = $articleService->getAboutContent();;

        $data = compact("swiperList", "noticeList", "couponList", "hotGoodsList", "hotGiftCardList", "aboutContent");
        $this->success("初始化数据获取成功", $data);
    }

    // 2-2. 订单支付成功通知
    public function paySuccessNotify() {
        $orderService = OrderService::instance();
        $config       = ConfigService::instance()->getConfigMini();
        $wechat       = Pay::instance($config);
        $post         = $wechat->getNotify();
        $attach       = json_decode($post['attach'] ?? "", true);
        if (empty($attach)) VException::runtime("缺少必要的参数：attach");
        alert(config("a.wx_pay_types." . ($attach['type'] ?? "other")) . "成功回调");
        alert($post);
        if ($post['return_code'] === 'SUCCESS' && $post['result_code'] === 'SUCCESS') {
            $post['total_fee'] = bcdiv($post['total_fee'], 100, 2);
            $post['cash_fee']  = bcdiv($post['cash_fee'], 100, 2);
            if ($attach['type'] == 'recharge') {
                $moneyLog = UserMoneyLogService::instance()->getOneByTargetGid($post['out_trade_no']);
                if (!empty($moneyLog)) return "success";
                UserInfoService::instance()->rechargeSuccess($attach['gid'], $post['out_trade_no'], $post['openid'], $post['transaction_id']);
                // 微信发货（小程序强制要求）
                /*                try {
                                    // 会增加显式的发货按钮，及用户收货按钮
                                    sleep(2); // 演示2秒，避免微信发货失败（及个别情况，延时也可能会失败）
                                    $res = $orderService->wxShippingByOutTradeNo($post['out_trade_no']);
                                } catch (Exception $e) {
                                    error("充值时，微信发货失败(target_gid: {$post['out_trade_no']}): " . $e->getMessage());
                                }*/
            } elseif ($attach['type'] == 'order') {
                $order = OrderService::instance()->getOneBySn($attach['sn']);
                if ($order->status != 0) return "success";
                OrderService::instance()->payOrderSuccess($order, "weixin", $post['openid'], $post['total_fee'], $post['transaction_id']);
                // 微信发货（小程序强制要求）
                // try {
                // 订单列表增加显式的后台配货（对应微信的发货），及完成订单（对应微信的收货）
                // sleep(2); // 演示2秒，避免微信发货失败（及个别情况，延时也可能会失败）
                // $res = $orderService->wxShippingByOutTradeNo($post['out_trade_no']);
                // } catch (Exception $e) {
                //     error("下单时，微信发货失败(sn: {$post['out_trade_no']}): " . $e->getMessage());
                // }
            }
            return "success";
        }
        return "";
    }

    // 2-1. 订单退款成功通知
    public function refundSuccessNotify() {
        dd("refundSuccessNotify");
    }

    // 1-4. H5用phone登录
    // public function loginH5Phone(UserInfoService $userInfoService) {
    //     $post     = $this->_vali([
    //         "phone.require" => "phone不能为空",
    //         "nickname.require" => "nickname不能为空",
    //     ]);
    //     if($post['nickname'] != 'vandles') $this->error("nickname不正确");
    //     $userInfo = $userInfoService->getUserInfoByPhone($post['phone']);
    //     if (!$userInfo) {
    //         $userInfo = $userInfoService->create($post);
    //     }
    //     $tokenData = $userInfoService->genJWTToken($userInfo);
    //     $this->success("登录成功", $tokenData);
    // }
    // 1-3. H5登录
    public function loginH5(UserInfoService $userInfoService) {
        $post     = $this->_vali([
            "openid.require" => "OPENID不能为空",
        ]);
        $userInfo = $userInfoService->getUserInfoByOpenid($post['openid']);
        if (!$userInfo) {
            $userInfo = $userInfoService->create($post);
        }
        $tokenData = $userInfoService->genJWTToken($userInfo);
        $this->success("登录成功", $tokenData);
    }

    // 1-2. H5注册
    public function register(UserInfoService $userInfoService) {
        $post = $this->_vali([
            "username.require"           => "用户名不能为空",
            "password.require"           => "密码不能为空",
            "password2.require"          => "密码不能为空",
            "password2.confirm:password" => "两次密码不一致",
            "code.require"               => "验证码不能为空",
        ]);
        if ($post['code'] !== '8888') $this->error("验证码错误");
        $userInfo = $userInfoService->getUserInfoByUsername($post['username']);
        if ($userInfo) $this->error('用户名已存在');
        $post['openid']   = 'JM' . Str::random(32);
        $post['password'] = md5($post['password'] . 'JM');
        $userInfo         = $userInfoService->create($post);
        $tokenData        = $userInfoService->genJWTToken($userInfo);
        $this->success("注册成功", $tokenData);
    }

    // 1-1. 微信小程序登录
    public function login() {
        $params = $this->param();
        if (empty($params['code'])) $this->error("参数错误");
        $userInfoService = UserInfoService::instance();
        $p_openid        = $params['pid'] ?? "";
        $code            = $params['code'];
        $config          = ConfigService::instance()->getConfigMini();
        try {
            $mini = new Crypt($config);
            $res  = $mini->session($code);
        } catch (Exception $e) {
            $this->error("系统错误，请稍后再试");
        }

        if (empty($res['openid'])) $this->error('登录失败');
        $openid   = $res['openid'];
        $userInfo = $userInfoService->getUserInfoByOpenid($openid);
        if (empty($userInfo)) {
            Db::startTrans();
            try {
                $userInfo = $userInfoService->create(compact('openid', 'p_openid'));
                $userInfoService->spreadCountInc($p_openid);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error('登录失败: ' . $e->getMessage());
            }
        }
        $tokenData = $userInfoService->genJWTToken($userInfo);
        $this->success('登录成功', $tokenData);
    }


}