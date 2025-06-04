<?php

namespace app\master\controller;

use think\admin\helper\QueryHelper;
use vandles\controller\MasterCrudBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\WaterStationService;

/**
 * 模块模板
 */
class Tpl extends MasterCrudBaseController {

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
            $query->like('title')->dateBetween('create_at');
        });
    }

    protected function _add_form_filter(&$data){
        $data['gid'] = guid();
    }

}