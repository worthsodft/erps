<?php

namespace app\master\controller\sale;

use Exception;
use think\admin\helper\QueryHelper;
use vandles\controller\MasterCrudBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\CustomerService;
use vandles\service\DepotService;
use vandles\service\WaterStationService;

/**
 * 水站信息管理
 */
class Waterstation extends MasterCrudBaseController {

    public function getModel(): BaseSoftDeleteModel {
        return WaterStationService::instance()->getModel();
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
            $query->equal("district")->like('title')->dateBetween('create_at');
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
            }
        }
    }

    public function _state_save_filter($query, &$data) {
        if($data['status'] == 0) $data['is_open'] = 0;
    }

    /**
     * 修改营业状态
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function is_open(){
        $this->getModel()::mSave($this->_vali([
            'is_open.in:0,1'  => '状态值范围异常！',
            'is_open.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 修改是否默认
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function is_default(){
        $this->getModel()::mSave($this->_vali([
            'is_default.in:0,1'  => '状态值范围异常！',
            'is_default.require' => '状态值不能为空！',
        ]));
    }

    public function _is_default_save_filter($query, $data) {
        try{
            // 设置默认水站（仓库）
            DepotService::instance()->setDefaultDepotOrStation($query->value("gid"), $data['is_default']);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
    }

    /**
     * 选择水站
     * @auth true
     * @return void
     */
    public function select() {
        $this->title = '选择水站';
        $this->type = 'index';
        $this->getModel()::mQuery()->layTable(function () {
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => 1])->order("sort desc");
            $query->where("is_open", 1);
            $query->like('title')->dateBetween('create_at');
        });
    }

    /**
     * 选择经销商
     * @auth true
     * @return void
     */
    public function selectPartner() {
        $this->title = '选择经销商';
        $this->type = 'index';
        $this->isPartner = true;
        $this->getModel()::mQuery()->layTable(function () {
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => 1])->order("sort desc");
            // $query->where("is_open", 1);
            $query->like('title')->dateBetween('create_at');
        }, 'select');
    }

    /**
     * 添加明细时，查询客户
     * @return void
     */
    public function getStationByGid() {
        $gid    = $this->get("gid");
        $one = WaterStationService::instance()->getOneByGid($gid, "id,gid,title");
        if (empty($one)) $this->error("水站不存在");
        $this->success("获取成功", compact('one'));
    }

}