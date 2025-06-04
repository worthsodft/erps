<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\helper\Str;
use think\Model;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CouponTplModel;
use vandles\model\InvoiceApplyModel;
use vandles\model\UserInfoModel;

class InvoiceApplyService extends BaseService {
    protected static $instance;

    const BUYER_TYPE_P = 0; // 个人
    const BUYER_TYPE_E = 1; // 公司

    const INVOICE_TYPE_N = 0; // 普通
    const INVOICE_TYPE_S = 1; // 专用


    public static function instance(): InvoiceApplyService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);

        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|InvoiceApplyModel
     */
    public function getModel() {
        return InvoiceApplyModel::mk();
    }

    public function check($order) {
        if(is_string($order)) $order = OrderService::instance()->getOrderBySn($order, "id,sn,status,openid,invoice_apply_at,invoice_email_at");
        if($order['status'] != OrderService::ORDER_STATUS_FINISHED) VException::throw("订单不是已完成状态：{$order['sn']}");
        if(!empty($order['invoice_apply_at'])) VException::throw("发票已申请：{$order['sn']}");
        if(!empty($order['invoice_email_at'])) VException::throw("发票已发邮件：{$order['sn']}");
        $apply = $this->getOneByOrderSnInSet($order['sn']);
        if(!$apply) return true;
        if($apply['is_email'] == 1) VException::throw("发票已发邮箱：{$order['sn']}");
        else VException::throw("申请已存在：{$order['sn']}");
    }

    public function getOneByOrderSnInSet($sn, $field="*") {
        return $this->search()->whereRaw("find_in_set('{$sn}', order_sns)")->field($field)->order("id desc")->find();
    }
    public function getOneByOrderSn($sn, $field="*") {
        return $this->search(["order_sn" => $sn])->field($field)->order("id desc")->find();
    }

    public function bind(&$data) {
        $buyerTypes = config("a.buyer_types");
        $invoiceTypes = config("a.invoice_types");
        $openids = array_column($data, 'openid');
        $userInfos = UserInfoService::instance()->getUserInfoByOpenids($openids, 'openid,phone,nickname,realname', 'openid');
        foreach ($data as &$vo){
            $vo['buyer_type_txt']   = $buyerTypes[$vo['buyer_type']];
            $vo['invoice_type_txt'] = $invoiceTypes[$vo['invoice_type']];
            if(isset($userInfos[$vo['openid']])){
                $vo['user'] = $userInfos[$vo['openid']];
//                $vo['phone'] = $userInfos[$vo['openid']]['phone'];
//                $vo['nickname'] = $userInfos[$vo['openid']]['nickname'];
            }else{
                $vo['user'] = [];
            }

        }
    }

    /**
     * 撤销发送邮件为申请状态
     * @return boolean
     */
    public function unInvoiceEmailByOrderSn($orderSn) {
        VException::throw("多张订单开一张发票的撤销暂不支持");
        $applies = $this->getModel()->where("order_sn", $orderSn)->select();
        $count = count($applies);
        if($count > 1) VException::throw("开票申请不只一条，请手动处理");
        if(empty($applies[0])) VException::throw("开票申请不存在");
        $apply = $applies[0];

        $apply->is_email   = 0;
        $apply->invoice_no = null;
        $apply->email_at   = null;
        $apply->email_by   = 0;
        $apply->save();

        $order = OrderService::instance()->getOrderBySn($orderSn);
        if(!$order) VException::throw("订单不存在");
        $order->invoice_no       = null;
        $order->invoice_email_at = null;
        $order->invoice_email_by = null;
        $order->save();

        return true;
    }




}