<?php

namespace app\master\controller\finance;

use Exception;
use think\admin\helper\QueryHelper;
use think\facade\Db;
use vandles\controller\MasterBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\InvoiceApplyService;
use vandles\service\OrderService;
use vandles\service\UserInfoService;

/**
 * 订单开票管理
 */
class Invoiceapply extends MasterBaseController {

    public function getModel(): BaseSoftDeleteModel {
        return InvoiceApplyService::instance()->getModel();
    }

    /**
     * 列表
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index() {
        $this->type = input('get.type', 't0');

        $this->total = ["t0"=>0, "t1"=>0];
        foreach ($this->getModel()->field('is_email,count(1) total')->group('is_email')->cursor() as $vo) {
            $this->total["t{$vo['is_email']}"] = $vo['total'];
        }

        $this->getModel()::mQuery()->layTable(function () {
            $this->title = '列表';
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'is_email' => intval($this->type !== 't0')]);
            $query->equal("buyer_type,invoice_type")->like('title,order_no,email,invoice_no')->dateBetween('create_at');

            $db = UserInfoService::instance()->mQuery()->field("openid")->like("phone");
            if($db->getOptions("where")){
                $query->whereRaw("openid in {$db->buildSql()}");
            }
        });
    }

    protected function _index_page_filter(&$data) {
        InvoiceApplyService::instance()->bind($data);

    }

    /**
     * 邮件状态
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function email() {
        if ($this->isGet()) {
            $this->getModel()::mForm();
        } elseif ($this->isPost()) {
            $post = $this->post();
            if (empty($post['id'])) $this->error("ID不能为空");
            if (empty($post['invoice_no'])) $this->error("发票号码不能为空");
            $apply = InvoiceApplyService::instance()->getOneById($post['id']);
            if (empty($apply)) $this->error("申请不存在");
            if ($apply->is_email == 1) $this->error("邮件已经发送过");
            $orders = OrderService::instance()->getListBySns($apply['order_sns']);
            if(count($orders) == 0) $this->error("订单不存在");
            foreach($orders as $order){
                if (empty($order)) $this->error("订单不存在: {$order['sn']}");
                if ($order->invoice_no) $this->error("订单已开过发票: {$order['sn']}");
            }

            DB::startTrans();
            try{

                $apply->is_email   = 1;
                $apply->invoice_no = $post['invoice_no'];
                $apply->email_at   = $post['email_at'];
                $apply->email_by   = session("user.id");
                $apply->save();

                foreach($orders as $order) {
                    $order->invoice_no       = $apply->invoice_no;
                    $order->invoice_email_at = $apply->email_at;
                    $order->invoice_email_by = $apply->email_by;
                    $order->save();
                }
                DB::commit();
            }catch(Exception $e){
                DB::rollback();
            }
            $this->success("设置成功");
        }
    }

    protected function _email_form_filter(&$data) {
        $sns = explode(",", $data['order_sns']);
        $orders =  OrderService::instance()->getModel()->whereIn('sn', $sns)->with(['subs'])->select();
        $this->orders = $orders;
    }

    /**
     * 查看详情
     * @auth true
     * @return void
     */
    public function show() {
        if ($this->isGet()) {
            $this->mode = "show";
            $this->getModel()::mForm();
        } elseif ($this->isPost()) {
            $this->error("查看详情不支持修改");
        }
    }

    protected function _show_form_filter(&$data) {
        $sns = explode(",", $data['order_sns']);
        $orders =  OrderService::instance()->getModel()->whereIn('sn', $sns)->with(['subs'])->select();
        $this->orders = $orders;
    }


    /**
     * 撤销申请
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove(InvoiceApplyService $invoiceApplyService){
        $id = input("id");
        $apply = $invoiceApplyService->getOneById($id, "id,is_email,order_sns");
        if(!$apply) $this->error("申请记录不存在");
        if($apply->is_email) $this->error("发票已发邮箱");

        DB::startTrans();
        try{
            OrderService::instance()->updateBySns($apply->order_sns, [
                'invoice_apply_at' => null
            ]);
            $invoiceApplyService->deleteById($id);
            DB::commit();
        }catch(Exception $e){
            DB::rollback();
            $this->error($e->getMessage());
        }
        $this->success("撤销成功");
    }



}