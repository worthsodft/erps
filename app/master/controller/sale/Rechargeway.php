<?php

namespace app\master\controller\sale;

use think\admin\helper\QueryHelper;
use vandles\controller\MasterCrudBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\RechargeWayService;

/**
 * 充值优惠管理
 */
class Rechargeway extends MasterCrudBaseController {

    public function getModel(): BaseSoftDeleteModel {
        return RechargeWayService::instance()->getModel();
    }

    /**
     * 列表
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
        $this->type = input('get.type', 'index');
        $this->getModel()::mQuery()->layTable(function () {
            $this->title = '列表';
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
            $query->like('title')->dateBetween('create_at');
        });
    }
    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_page_filter(&$data){
    }

    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function _form_filter(&$data){
        if($this->isPost()){
            if(empty($data['id'])){
                $data['gid'] = uuid();
            }else{
                $data['status'] = 0; // 每次修改后，状态变为隐藏，需要审核才能显示
            }
        }
    }

    /**
     * 充值说明
     * @auth true
     */
    public function remark(){
        if($this->isGet()){
            $remarks = sysdata("config.recharge_way_remarks");
            $this->remarks = $remarks;
            $this->fetch();
        }elseif($this->isPost()){
            $remarks = $this->post("remarks");
            sysdata("config.recharge_way_remarks", $remarks);
            $this->success("保存成功");
        }
    }

}