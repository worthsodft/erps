<?php

namespace app\master\controller\finance;

use think\admin\helper\QueryHelper;
use think\facade\Db;
use vandles\controller\MasterCrudBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\PayInService;
use vandles\service\SupplierService;
use vandles\service\InStockService;
use vandles\service\PayOutService;

/**
 * 付款单管理
 */
class Payout extends MasterCrudBaseController {
    public $mode;
    public function getModel(): BaseSoftDeleteModel {
        return PayOutService::instance()->getModel();
    }

    /**
     * 付款单管理
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
            $this->title = '付款单管理';
            $this->suppliers = SupplierService::instance()->getListOptsByGids();

        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0]);

            if($this->type != '9') $query->where('status', $this->type);

            $query->like("sn")->equal('supplier_gid,pay_type')->dateBetween('create_at');
        });
    }

    protected function _index_page_filter(&$data) {
        PayOutService::instance()->bind($data);
    }


    protected function _form_filter(&$data){
        if($this->isGet()){
            $this->suppliers = SupplierService::instance()->getListOptsByGids();

        }else{
            if(empty($data['supplier_gid'])) $this->error('请选择企业客户');
            if($data['pay_type'] == '') $this->error('请选择支付方式');
            if(empty($data['in_stock_sns'])) $this->error('请选择入库单');
            if(empty($data['money'])) $this->error('请输入付款金额');

            $unpaid_amount = InStockService::instance()->getUnPaidAmountBySns($data['in_stock_sns']);
            if($data['money'] > $unpaid_amount) $this->error("付款金额不能大于入库单合计应付款金额");

            $in_stock_sns = explode(",", $data['in_stock_sns']);
            $items = InStockService::instance()->getDeltaItems($data['money'], $in_stock_sns);
            if (count($in_stock_sns) != count($items)) $this->error("付款金额不能覆盖所有入库单，请重新选择入库单");

            // 设置未支付金额字段的值，安全性考虑，此字段实时计算得出，不使用表单值
            $data['un_paid_amount'] = $unpaid_amount;

            // 查询入库单所关联的、未审核的付款单
            $payOutSns = InStockService::instance()->getUnPassedPayOutSnsByInStockSns($data['in_stock_sns']);
            // 所有付款单 - 当前付款单（付款单中，不应包含当前付款单）
            $payOutSns = array_diff($payOutSns, [$data['sn']]);
            if($payOutSns){
                $payOutSns = implode(",", $payOutSns);
                $this->error("当前选择的入库单存在未审核的付款单：{$payOutSns}，请先审核付款单");
            }
            // 事务开始
            Db::startTrans();
        }
    }
    protected function _form_result($result, $data){
        if($result){
            // 更新入库单的付款单编号
            $list = InStockService::instance()->getMainListBySns(explode(',',$data['in_stock_sns']), "id,sn,pay_out_sns");
            foreach ($list as $item) {
                $pay_out_sns = $item['pay_out_sns'] ? explode(",", $item['pay_out_sns']) : [];
                if(!in_array($data['sn'], $pay_out_sns)) $pay_out_sns[] = $data['sn'];
                $item->pay_out_sns = implode(",", $pay_out_sns);
                $item->save();
            }

            // 事务结束
            Db::commit();
            $this->success("操作成功", "javascript:$.form.reload();");
        }
    }
    protected function _add_form_filter(&$data){
        if($this->isPost()){
            $data['sn'] = PayOutService::instance()->genSn();
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
            PayOutService::instance()->bindOne($this->vo);
            $this->fetch("show");
        }else{
            $post = $this->post();
            $is_pass = !empty($post['is_pass']); // 是否是审核通过
            // 审核不需要提交图片
            unset($post['images']);

            try{
                PayOutService::instance()->pass($post);
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
            PayOutService::instance()->bindOne($this->vo);
            $this->fetch();
        }
    }
}