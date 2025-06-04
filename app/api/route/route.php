<?php
/*
 * 
 * Author: vandles
 * Date: 2022/6/27 16:02
 * Email: <vandles@qq.com>
 */

use think\facade\Route;
use vandles\middleware\UserInfoTokenMiddleware;
// 自动任务
Route::group('v1/schedule', function(){

    // 充值自动微信发货
    Route::any('rechargeAutoWxShipping', 'rechargeAutoWxShipping');


})->prefix("v1.schedule/");





// 通用
Route::group('v1', function(){

    // 不需要登录
    Route::group(function(){
        Route::any('hello', 'hello');

        // 1-1. 微信小程序登录
        Route::any('login', 'login');
        // 1-2. H5注册
        Route::any('register', 'register');
        // 1-3. H5登录
        Route::any('loginH5', 'loginH5');
        // 1-4. H5用phone登录
        // Route::any('loginH5Phone', 'loginH5Phone');

        // 2-1. 订单退款成功通知
        Route::any('refundSuccessNotify', 'refundSuccessNotify');
        // 2-2. 订单（余额）支付成功通知
        Route::any('paySuccessNotify', 'paySuccessNotify');

        // 3. 首页页面数据
        Route::any('getInitDataIndex', 'getInitDataIndex')
            ->middleware(UserInfoTokenMiddleware::class)->option(["isForce"=>false]);

        // 5-1. 得到商品分页列表
        Route::any('getGoodsPageData', 'getGoodsPageData')
            ->middleware(UserInfoTokenMiddleware::class)->option(["isForce"=>false]);
        // 5-2. 得到实物卡商品分页列表
        Route::any('getGiftCardGoodsPageData', 'getGiftCardGoodsPageData')
            ->middleware(UserInfoTokenMiddleware::class)->option(["isForce"=>false]);

        // 7. 得到购物车总数量
        Route::any('getCartTotal', 'getCartTotal')
            ->middleware(UserInfoTokenMiddleware::class)->option(["isForce"=>false]);

        // 9. 得到商品详情
        Route::any('goodsDetail/:sn', 'goodsDetail');

        // 34. 得到应用配置
        Route::any('getAppConfig', 'getAppConfig');

        // 41. 自动清理订单
        Route::any('autoCleanOrder', 'autoCleanOrder');

        // 42. 自动收货订单
        Route::any('autoTakeOrder', 'autoTakeOrder');

        // 43. 自动任务
        Route::any('autoTask', 'autoTask');

        // 46. 自动重置优惠券剩余数量（任意金额减20元）
        Route::any('autoResetCouponHasCount', 'autoResetCouponHasCount');

        // 59. 自动任务生成昨日统计数据
        Route::any('autoTaskGenReportDayYesterday', 'autoTaskGenReportDayYesterday');

        // 65. 验证码图片
        Route::any('captcha', 'captcha');




    });
//    Route::any('saveUserProfile', 'v1.index/saveUserProfile');
//    Route::any('getUserInfo$', 'v1.index/getUserInfo');
//
//    Route::any('pay$', 'v1.index/pay');
//    Route::any('refund$', 'v1.index/refund');
//    Route::any('refundNotify$', 'v1.index/refundNotify');
//
//    Route::any('getPhoneDetail/:id$', 'v1.index/getPhoneDetail');
//    Route::any('userLikePhoto/:id$', 'v1.index/userLikePhoto');
//    Route::any('getInitDataCity$', 'v1.index/getInitDataCity');
//    Route::any('sendCode$', 'v1.index/sendCode');
//    Route::any('savePhoneByCode$', 'v1.index/savePhoneByCode');

    // 需要登录
    Route::group(function(){

        // 4. 得到实时用户信息
        Route::any('getUserInfo', 'getUserInfo');

        // 6-1. 添加到购物车
        Route::any('add2cart', 'add2cart');
        // 6-2. 更新到购物车
        Route::any('update2cart', 'update2cart');
        // 6-3. 删除到购物车
        Route::any('del2cart', 'del2cart');
        // 6-4. 选择到购物车
        Route::any('check2cart', 'check2cart');

        // 8. 得到购物车商品列表
        Route::any('getInitDataCart', 'getInitDataCart');

        // 10. 订单确认数据
        Route::any('getOrderConfirmInfo', 'getOrderConfirmInfo');
        Route::any('getOrderConfirmInfoV2', 'getOrderConfirmInfoV2');

        // 11. 创建订单
        Route::any('createOrderFromConfirm', 'createOrderFromConfirm');
        Route::any('createOrderFromConfirmV2', 'createOrderFromConfirmV2');
        Route::any('createOrderFromConfirm_dev', 'createOrderFromConfirm_dev');

        // 12. 支付订单
        Route::any('payOrder/:sn', 'payOrder');

        // 13. 支付成功
        Route::any('getInitDataPaySuccess/:type/:sn', 'getInitDataPaySuccess');

        // 14. 我的收货地址列表
        Route::any('getAddressList', 'getAddressList');

        // 15. 新增（或修改）我的收货地址
        Route::any('saveAddress', 'saveAddress');

        // 16. 得到我的收货地址详情
        Route::any('getAddressDetail/:gid', 'getAddressDetail');

        // 17. 删除我的收货地址
        Route::any('delAddress/:gid', 'delAddress');

        // 18. 得到水站列表(含距离)
        Route::any('getStationListWithDistance', 'getStationListWithDistance');

        // 19. 我的页，订单数量
        Route::any('getUserOrderTabsData', 'getUserOrderTabsData');

        // 20. 订单列表页，表头
        Route::any('getOrderTabsData', 'getOrderTabsData');

        // 21. 订单列表数据
        Route::any('getOrderPageDataByStatus/:status', 'getOrderPageDataByStatus');
        Route::any('getOrderPageDataByStatus_dev/:status', 'getOrderPageDataByStatus_dev');

        // 22. 订单详情
        Route::any('getOrderDetail/:sn', 'getOrderDetail');

        // 23. 得到订单确认页面数据
        Route::any('getInitDataConfirm', 'getInitDataConfirm');

        // 24. 用户收货操作
        Route::any('takeOrder/:sn', 'takeOrder');

        // 25. 得到余额记录分页列表
        Route::any('getUserMoneyLogPageData', 'getUserMoneyLogPageData');

        // 26. 领取优惠券
        Route::any('fetchCoupon/:gid', 'fetchCoupon');

        // 27. 我的优惠券列表
        Route::any('getMyCouponPageData', 'getMyCouponPageData');

        // 28. 我的优惠券数量和余额
        Route::any('getUserBarData', 'getUserBarData');

        // 29. 上传图片
        Route::any('upload', 'upload');

        // 30. 更新用户信息
        Route::any('updateUserInfo', 'updateUserInfo');

        // 31. 充值方式列表
        Route::any('rechargeWayList', 'rechargeWayList');

        // 32. 支付预下单
        Route::any('getPayInfo', 'getPayInfo');

        // 33. 申请退款
        Route::any('refundApply/:sn', 'refundApply');

        // 35. 得到我的页服务菜单
        Route::any('getServiceMenu', 'getServiceMenu');

        // 36. 自提订单核销（设为已完成）
        Route::any('finishOrder/:sn', 'finishOrder');

        // 37. 获取订单核销二维码
        Route::any('getFinishOrderQrcode/:sn', 'getFinishOrderQrcode');

        // 38. 用户是否有核销权限
        Route::any('isAuthFinishOrder', 'isAuthFinishOrder');

        // 39. 根据微信code获取手机号
        Route::any('getPhoneNumberByCode', 'getPhoneNumberByCode');

        // 40. 用户取消订单操作
        Route::any('cancelOrder/:sn', 'cancelOrder');

        // 44. 订单详情用于核销订单
        Route::any('getOrderDetailForFinish/:sn', 'getOrderDetailForFinish');

        // 45. 供用户选择的配送区域
        Route::any('getCities', 'getCities');

        // 47. 用户开票申请
        Route::any('invoiceApply', 'invoiceApply');

        // 48. 我的核销列表
        Route::any('getMyFinishOrderPageData', 'getMyFinishOrderPageData');

        // 49. 用户是否有配送权限
        Route::any('isAuthDeliverOrder', 'isAuthDeliverOrder');

        // 50. 我的配送列表
        Route::any('getMyDeliverOrderPageData', 'getMyDeliverOrderPageData');

        // 51. 我负责配送的区域
        Route::any('getUserInfoDistricts', 'getUserInfoDistricts');

        // 52. 订单配送
        Route::any('deliverOrder', 'deliverOrder');

        // 53. 生成订单配货单
        Route::any('orderPick', 'orderPick');

        // 54. 得到我的金额充值卡列表
        Route::any('getMyMoneyCardList', 'getMyMoneyCardList');

        // 55. 金额充值卡退款
        Route::any('moneyCardRefund/:gid', 'moneyCardRefund');

        // 56. 得到营业中的水站列表
        Route::any('getStationOpeningList', 'getStationOpeningList');

        // 57. 得到我水站的配货单
        Route::any('getMyStationOrderPickPageData', 'getMyStationOrderPickPageData');

        // 58. 库房/水站配货确认
        Route::any('orderPickConfirm/:gid', 'orderPickConfirm');

        // 60. 实物卡绑定页面初始化数据
        Route::any('getInitDataGiftCardBind', 'getInitDataGiftCardBind');

        // 61. 实物卡绑定时查询卡信息
        Route::any('searchGiftCardForMiniBind', 'searchGiftCardForMiniBind');

        // 62. 实物卡绑定
        Route::any('bindGiftCardForMiniBind', 'bindGiftCardForMiniBind');

        // 63. 我的实物卡列表
        Route::any('getMyGiftCardPageData', 'getMyGiftCardPageData');

        // 64. 我的实物卡使用记录
        Route::any('getMyGiftCardLogPageData', 'getMyGiftCardLogPageData');

        // 66. 我的可用实物卡使用记录
        Route::any('getMyUsableGiftCardList', 'getMyUsableGiftCardList');



    })->middleware(UserInfoTokenMiddleware::class);

})->prefix("v1.index/");