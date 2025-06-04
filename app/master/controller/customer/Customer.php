<?php

namespace app\master\controller\customer;

use think\admin\helper\QueryHelper;
use vandles\controller\MasterCrudBaseController;
use vandles\lib\Validator;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\BuyerMoneyLogService;
use vandles\service\CustomerService;

/**
 * 企业客户管理
 */
class Customer extends MasterCrudBaseController {

    public function getModel(): BaseSoftDeleteModel {
        return CustomerService::instance()->getModel();
    }

    /**
     * 企业客户管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
        $this->type = input('get.type', 'index');
        $this->getModel()::mQuery()->layTable(function () {
            $this->title = '企业客户管理';
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
            $query->like('title,taxno')->dateBetween('create_at');
        });
    }

    protected function _form_filter(&$data){
        if($this->isPost()) {
            if (CustomerService::instance()->isExistTaxno($data['taxno'], $data['id'] ?? '')) {
                $this->error("纳税人识别号已存在，请更换后重试");
            }
            if(!Validator::taxno($data['taxno'])) $this->error("纳税人识别号格式不正确");
        }
    }

    protected function _add_form_filter(&$data){
        $data['gid'] = guid();
    }

    /**
     * 用户详情
     * @auth true
     */
    public function detail() {
        $this->type     = $this->get('type', 0);
        $this->buyer_gid   = $this->get('buyer_gid', 0);
        $this->customer = CustomerService::instance()->getByGid($this->buyer_gid);
        if (empty($this->customer)) $this->error('用户不存在');
        $this->customer->status_txt = $this->customer->status == 1 ? "显示" : "隐藏";
        $this->total = $this->getSubListTotal();
        $this->title = config("a.customer_info_tabs." . $this->type);
        switch ($this->type) {
            case "0": // 预付款记录
                BuyerMoneyLogService::instance()->mQuery()->where("buyer_gid", $this->buyer_gid)
                    ->where("status", 1)
                    ->order("id desc")
                    ->page(true, true, false, 10);
                break;
            default:
                $this->error("未知的列表类型");
        }
    }

    protected function _detail_page_filter(&$data) {
        switch ($this->type) {
            case "0": // 预付款记录
                BuyerMoneyLogService::instance()->bind($data);
                break;
            default:
                $this->error("未知的列表类型");
        }
    }
    private function getSubListTotal() {
        return [
            BuyerMoneyLogService::instance()->getTotalByBuyerGid($this->buyer_gid),
        ];
    }


    /**
     * 选择客户
     * @auth true
     * @return void
     */
    public function select() {
        $this->title = '选择客户';
        $this->type = 'index';
        $this->getModel()::mQuery()->layTable(function () {
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => 1])->order("sort desc");
            $query->like('title')->dateBetween('create_at');
        });
    }

    /**
     * 添加明细时，查询客户
     * @return void
     */
    public function getCustomerByGid() {
        $gid    = $this->get("gid");
        $one = CustomerService::instance()->getCustomerByGid($gid, "id,gid,title");
        if (empty($one)) $this->error("客户不存在");
        $this->success("获取成功", compact('one'));
    }
}