<?php

namespace {{Namespace}};

use think\admin\helper\QueryHelper;
use vandles\controller\MasterBaseController;
use vandles\model\BaseSoftDeletedModel;
use {{UseService}}\{{TableName}}Service;


/**
 * {{Title}}
 */
class {{ClassName}} extends MasterBaseController {

    public function getModel(): BaseSoftDeletedModel {
        return {{TableName}}Service::instance()->getModel();
    }

    /**
     * {{Title}}
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
       $this->type = $this->param('type', 'index');
        {{TableName}}Service::instance()->mQuery()->layTable(function () {
           $this->title = '{{Title}}';
       }, function (QueryHelper $query) {
           $query->where(['deleted_time' => 0, 'status' => intval($this->type === 'index')]);
{{ControlData}}
       });
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_page_filter(&$data){
        {{TableName}}Service::instance()->bind($data);
    }

    /**
     * 添加
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add(){
        $this->title = '添加';
        $this->getModel()::mForm('form');
    }

    /**
     * 编辑
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(){
        $this->title = '编辑';
        $this->getModel()::mForm('form');
    }

    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function _form_filter(&$data){
    }

    /**
     * 修改状态
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function state(){
        $this->getModel()::mSave($this->_vali([
            'status.in:0,1'  => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 删除
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove(){
        $this->getModel()::mDelete();
    }

}