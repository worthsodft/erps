<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 16:02
 * Email: <vandles@qq.com>
 **/

namespace vandles\controller;

use vandles\model\BaseSoftDeleteModel;

abstract class MasterCrudBaseController extends BaseController {

    protected $model;


    /**
     * @return BaseSoftDeleteModel
     */
    abstract public function getModel():BaseSoftDeleteModel;

    /**
     * 列表
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
//        $this->type = input('get.type', 'index');
//        BasePostageCompany::mQuery()->layTable(function () {
//            $this->title = '列表';
//        }, function (QueryHelper $query) {
//            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
//            $query->like('name,code_1|code_3#code')->equal('status')->dateBetween('create_at');
//        });
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_page_filter(&$data){
//        foreach ($data as &$vo) {
//        }
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
//        if ($this->request->isGet()) {
//        }
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