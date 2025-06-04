<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/12/31
 * Time: 13:40
 */

namespace vandles\lib\order;

use Exception;
use vandles\lib\order\strategy\CancelAutoStrategy;
use vandles\lib\order\strategy\CancelUserStrategy;
use vandles\lib\order\strategy\ConfirmStrategy;
use vandles\lib\order\strategy\OrderActionStrategy;
use vandles\lib\order\strategy\PaySuccessStrategy;
use vandles\lib\order\strategy\PickupMasterStrategy;
use vandles\lib\order\strategy\PickupShipperStrategy;
use vandles\lib\order\strategy\RechargeSuccessStrategy;
use vandles\lib\order\strategy\RefundApproveStrategy;
use vandles\lib\order\strategy\RefundRejectStrategy;
use vandles\lib\order\strategy\RefundRequestStrategy;
use vandles\lib\order\strategy\TakeAutoStrategy;
use vandles\lib\order\strategy\TakeUserStrategy;
use vandles\lib\order\strategy\VerifyMasterStrategy;
use vandles\lib\order\strategy\VerifyStrategy;
use vandles\lib\VException;

/**
 * 订单状态机
 */
class OrderStatusMachine {
    const STATUS_PENDING_PAY        = 0; // 待支付（status）
    const STATUS_SHIPPING           = 1; // 配送中（status）
    const STATUS_SHIPPING_PENDING   = 0; // 待配货（deliver_status）
    const STATUS_SHIPPING_DOING     = 1; // 配送中（deliver_status）
    const STATUS_SHIPPING_COMPLETED = 2; // 已配送（deliver_status）
    const STATUS_COMPLETED          = 2; // 已完成（status）
    const STATUS_REFUND             = 9; // 退款（status）
    const STATUS_REFUND_NO          = 0; // 未退款（refund_status）
    const STATUS_REFUND_DOING       = 1; // 退款中（refund_status）
    const STATUS_REFUND_COMPLETED   = 2; // 已退款（refund_status）
    const STATUS_REFUND_REJECT      = 3; // 退款驳回（refund_status）
    const STATUS_CANCEL             = -1; // 取消（删除订单）（status）

    const ACTION_PAY_SUCCESS      = 'paySuccess'; // 订单支付成功(用户)
    const ACTION_RECHARGE_SUCCESS = 'rechargeSuccess'; // 充值支付成功(用户)
    const ACTION_PICKUP_SHIPPER   = 'pickupShipper'; // 配货(配送人员)
    const ACTION_PICKUP_MASTER    = 'pickupMaster'; // 后台配货(后台)
    const ACTION_CONFIRM          = 'confirm'; // 确认送达(配送人员)
    const ACTION_VERIFY           = 'verify'; // 核销(核销人员)
    const ACTION_VERIFY_MASTER    = 'verifyMaster'; // 后台核销(后台)
    const ACTION_REFUND_REQUEST   = 'refundRequest'; // 申请退款(用户)
    const ACTION_REFUND_APPROVE   = 'refundApprove'; // 同意退款(后台)
    const ACTION_REFUND_REJECT    = 'refundReject'; // 驳回退款(后台)
    const ACTION_CANCEL_USER      = 'cancelUser'; // 取消订单(用户)
    const ACTION_CANCEL_AUTO      = 'cancelAuto'; // 取消订单(自动)
    const ACTION_TAKE_USER        = 'takeUser'; // 用户收货
    const ACTION_TAKE_AUTO        = 'takeAuto'; // 自动收货
    const ACTION_REFUND_MASTER    = 'refundMaster'; // 后台退款

    // private $strategies = []; // 策略
    private $statusTransitions = []; // 操作与状态
    private static $obj = null;

    public function __construct() {
        // 策略映射
        // $this->strategies        = [
        //     self::ACTION_PAY_SUCCESS      => new PaySuccessStrategy(),
        //     self::ACTION_RECHARGE_SUCCESS => new RechargeSuccessStrategy(), // 充值策略，特殊处理
        //     self::ACTION_PICKUP_SHIPPER   => new PickupShipperStrategy(),
        //     self::ACTION_PICKUP_MASTER    => new PickupMasterStrategy(),
        //     self::ACTION_CONFIRM          => new ConfirmStrategy(),
        //     self::ACTION_VERIFY           => new VerifyStrategy(),
        //     self::ACTION_VERIFY_MASTER    => new VerifyMasterStrategy(),
        //     self::ACTION_REFUND_REQUEST   => new RefundRequestStrategy(),
        //     self::ACTION_REFUND_APPROVE   => new RefundApproveStrategy(),
        //     self::ACTION_REFUND_REJECT    => new RefundRejectStrategy(),
        //     self::ACTION_CANCEL_USER      => new CancelUserStrategy(),
        //     self::ACTION_CANCEL_AUTO      => new CancelAutoStrategy(),
        //     self::ACTION_TAKE_USER        => new TakeUserStrategy(),
        //     self::ACTION_TAKE_AUTO        => new TakeAutoStrategy(),
        // ];
        // 状态转换
        $this->statusTransitions = [
            // 订单支付成功(用户)
            self::ACTION_PAY_SUCCESS => [
                "status"         => [self::STATUS_PENDING_PAY, self::STATUS_SHIPPING],
                "deliver_status" => [self::STATUS_SHIPPING_PENDING, self::STATUS_SHIPPING_PENDING],
            ],
            // 配货(配送人员)
            self::ACTION_PICKUP_SHIPPER => [
                "status"         => [self::STATUS_SHIPPING, self::STATUS_SHIPPING],
                "deliver_status" => [self::STATUS_SHIPPING_PENDING, self::STATUS_SHIPPING_DOING],
            ],
            // 后台配货(后台)
            self::ACTION_PICKUP_MASTER => [
                "status"         => [self::STATUS_SHIPPING, self::STATUS_SHIPPING],
                "deliver_status" => [self::STATUS_SHIPPING_PENDING, self::STATUS_SHIPPING_DOING],
            ],
            // 确认送达(配送人员)
            self::ACTION_CONFIRM => [
                "status"         => [self::STATUS_SHIPPING, self::STATUS_COMPLETED],
                "deliver_status" => [self::STATUS_SHIPPING_DOING, self::STATUS_SHIPPING_COMPLETED],
            ],
            // 核销(核销人员)
            self::ACTION_VERIFY => [
                "status"         => [self::STATUS_SHIPPING, self::STATUS_COMPLETED],
                "deliver_status" => [self::STATUS_SHIPPING_DOING, self::STATUS_SHIPPING_COMPLETED],
            ],
            // 后台核销(后台)
            self::ACTION_VERIFY_MASTER => [
                "status"         => [self::STATUS_SHIPPING, self::STATUS_COMPLETED],
                "deliver_status" => [self::STATUS_SHIPPING_DOING, self::STATUS_SHIPPING_COMPLETED],
            ],
            // 申请退款(用户)
            self::ACTION_REFUND_REQUEST => [
                "status"         => [self::STATUS_SHIPPING, self::STATUS_REFUND],
                "refund_status"  => [self::STATUS_REFUND_NO, self::STATUS_REFUND_DOING],
            ],
            // 同意退款(后台)
            self::ACTION_REFUND_APPROVE => [
                "status"         => [self::STATUS_REFUND, self::STATUS_REFUND],
                "refund_status"  => [self::STATUS_REFUND_DOING, self::STATUS_REFUND_COMPLETED],
            ],
            // 驳回退款(后台)
            self::ACTION_REFUND_REJECT => [
                "status"         => [self::STATUS_REFUND, "before|STATUS_SHIPPING"],// 驳回时，退回到申请退款前的状态 | STATUS_SHIPPING
                "refund_status"  => [self::STATUS_REFUND_DOING, self::STATUS_REFUND_REJECT],
            ],
            // 取消订单(用户)
            self::ACTION_CANCEL_USER => [
                "status"         => [self::STATUS_PENDING_PAY, "delete"],
            ],
            // 取消订单(自动)
            self::ACTION_CANCEL_AUTO => [
                "status"         => [self::STATUS_PENDING_PAY, "delete"],
            ],
            // 用户收货
            self::ACTION_TAKE_USER => [
                "status"         => [self::STATUS_SHIPPING, self::STATUS_COMPLETED],
                "deliver_status" => [self::STATUS_SHIPPING_DOING, self::STATUS_SHIPPING_COMPLETED],
            ],
            // 自动收货
            self::ACTION_TAKE_AUTO => [
                "status"         => [self::STATUS_SHIPPING, self::STATUS_COMPLETED],
                "deliver_status" => [self::STATUS_SHIPPING_DOING, self::STATUS_SHIPPING_COMPLETED],
            ],
            // 后台退款(后台)
            self::ACTION_REFUND_MASTER => [
            "status"         => [self::STATUS_SHIPPING, self::STATUS_SHIPPING],
            "deliver_status" => [self::STATUS_SHIPPING_PENDING, self::STATUS_SHIPPING_DOING],
        ],
        ];
    }

    public static function instance(): OrderStatusMachine {
        if (!self::$obj) self::$obj = new self();
        return self::$obj;
    }

    public function applyAction(OrderActionStrategy $strategy, array $data) {
        return $strategy->execute($data);
    }


}