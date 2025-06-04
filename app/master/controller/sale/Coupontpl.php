<?php

namespace app\master\controller\sale;

use think\admin\helper\QueryHelper;
use vandles\controller\MasterCrudBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CouponTplModel;
use vandles\service\CouponTplService;

/**
 * 优惠券模板管理
 */
class Coupontpl extends MasterCrudBaseController {

    public function getModel(): BaseSoftDeleteModel {
        return CouponTplService::instance()->getModel();
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
            }
        }
    }

    /**
     * 优惠券选择器
     * @login true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function select(){
        $query = CouponTplModel::mQuery();
        $query->like('title#tpl_title');
        $query->where(['deleted' => 0, 'status'=> 1])->order('sort desc,id desc');
        $query->page();
    }

}