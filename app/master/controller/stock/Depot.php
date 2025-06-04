<?php

namespace app\master\controller\stock;

use Exception;
use think\admin\helper\QueryHelper;
use vandles\controller\MasterCrudBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\DepotService;
use vandles\service\WaterStationService;

/**
 * 仓库信息管理
 */
class Depot extends MasterCrudBaseController {

    public function getModel(): BaseSoftDeleteModel {
        return DepotService::instance()->getModel();
    }

    /**
     * 仓库信息管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
        $this->type = input('get.type', 'index');
        $this->getModel()::mQuery()->layTable(function () {
            $this->title = '仓库信息管理';
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
            $query->like('title')->dateBetween('create_at');
        });
    }

    protected function _add_form_filter(&$data){
        $data['gid'] = "DE_".guid();
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
     * 得到仓库和水站，用于库存各种操作
     * @return void
     */
    public function getDepotList() {
        $depotList = DepotService::instance()->getDepotListAndWaterStationList();
        $this->success("仓库列表", compact("depotList"));
    }



}