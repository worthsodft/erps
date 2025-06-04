<?php

namespace app\master\controller\finance;

use think\admin\helper\QueryHelper;
use think\facade\Db;
use vandles\controller\MasterCrudBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\CustomerService;
use vandles\service\OutStockService;
use vandles\service\PayInService;

/**
 * 收款单管理
 */
class Payin extends MasterCrudBaseController {
    public $mode;
    public function getModel(): BaseSoftDeleteModel {
        return PayInService::instance()->getModel();
    }

    /**
     * 收款单管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
        $this->type = input('get.type', '9');

        $this->total = [
            9  => $this->getModel()->count(),
            0  => $this->getModel()->where('status', 0)->count(),
            1  => $this->getModel()->where('status', 1)->count(),
            -1 => $this->getModel()->where('status', -1)->count()
        ];

        $this->getModel()::mQuery()->layTable(function () {
            $this->title = '收款单管理';
            [$companyList, $partnerList] = CustomerService::instance()->getCompanyOrPartnerOptsByList();
            // $this->buyers = CustomerService::instance()->getListOptsByGids();
            $this->buyers = array_merge($companyList, $partnerList);

        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0]);

            if($this->type != '9') $query->where('status', $this->type);

            $query->like("sn")->equal('buyer_gid,pay_type')->dateBetween('create_at');
        });
    }

    protected function _index_page_filter(&$data) {
        PayInService::instance()->bind($data);
    }


    protected function _form_filter(&$data){
        if($this->isGet()){
            // $this->buyers = CustomerService::instance()->getListOptsByGids();
            [$companyList, $partnerList] = CustomerService::instance()->getCompanyOrPartnerOptsByList();
            $this->buyers = array_merge($companyList, $partnerList);
        }else{
            if(empty($data['buyer_gid'])) $this->error('请选择客户');
            if($data['pay_type'] == '') $this->error('请选择支付方式');
            if(empty($data['out_stock_sns'])) $this->error('请选择出库单');
            if(empty($data['money'])) $this->error('请输入收款金额');

            $unpaid_amount = OutStockService::instance()->getUnPaidAmountBySns($data['out_stock_sns']);
            if($data['money'] > $unpaid_amount) $this->error("收款金额不能大于出库单合计应收款金额");

            $out_stock_sns = explode(",", $data['out_stock_sns']);
            $items = OutStockService::instance()->getDeltaItems($data['money'], $out_stock_sns);
            if (count($out_stock_sns) != count($items)) $this->error("收款金额不能覆盖所有出库单，请重新选择出库单");

            // 设置未支付金额字段的值，安全性考虑，此字段实时计算得出，不使用表单值
            $data['un_paid_amount'] = $unpaid_amount;

            // 查询出库单所关联的、未审核的收款单
            $payInSns = OutStockService::instance()->getUnPassedPayInSnsByOutStockSns($data['out_stock_sns']);
            // 所有收款单 - 当前收款单（收款单中，不应包含当前收款单）
            $payInSns = array_diff($payInSns, [$data['sn']]);
            if($payInSns){
                $payInSns = implode(",", $payInSns);
                $this->error("当前选择的出库单存在未审核的收款单：{$payInSns}，请先审核收款单");
            }

            // 事务开始
            Db::startTrans();
        }
    }
    protected function _form_result($result, $data){
        if($result){
            // 更新出库单的付款单编号
            $list = OutStockService::instance()->getMainListBySns(explode(',',$data['out_stock_sns']), "id,sn,amount,paid_amount,pay_in_sns");
            try{
                foreach ($list as $item) {
                    $pay_in_sns = $item['pay_in_sns'] ? explode(",", $item['pay_in_sns']) : [];
                    if(!in_array($data['sn'], $pay_in_sns)) $pay_in_sns[] = $data['sn'];
                    $item->pay_in_sns = implode(",", $pay_in_sns);

                    $item->save();
                }
                // 事务结束
                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
            }
            $this->success("操作成功", "javascript:$.form.reload();");
        }else{
            Db::rollback();
        }
    }
    protected function _add_form_filter(&$data){
        if($this->isPost()){
            $data['sn'] = PayInService::instance()->genSn();
            $data['create_by'] = session("user.id");
        }
    }

    /**
     * 审核
     * @auth true
     * @return void
     */
    public function pass(){
        $this->mode = 'pass';
        if($this->isGet()){
            $id = $this->get("id");
            $this->vo = $this->getModel()::findOrEmpty($id);
            PayInService::instance()->bindOne($this->vo);
            $this->fetch("show");
        }else{
            $post = $this->post();
            $is_pass = !empty($post['is_pass']); // 是否是审核通过
            // 审核不需要提交图片
            unset($post['images']);

            try{
                PayInService::instance()->pass($post);
            }catch(\Exception $e){
                $this->error($e->getMessage());
            }

            $this->success(($is_pass?"审核通过":"审核驳回") . "成功", "javascript:$.form.reload();");
        }
    }

    /**
     * 查看
     * @auth true
     * @return void
     */
    public function show(){
        $this->mode = 'show';
        if($this->isGet()){
            $id = $this->get("id");
            $this->vo = $this->getModel()::findOrEmpty($id);
            PayInService::instance()->bindOne($this->vo);
            $this->fetch();
        }
    }
}