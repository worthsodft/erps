<?php

namespace app\master\controller\sale;

use think\admin\helper\QueryHelper;
use vandles\controller\MasterCrudBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\CouponPublishService;
use vandles\service\CouponTplService;

/**
 * 优惠券发布
 */
class Couponpublish extends MasterCrudBaseController {

    public function getModel(): BaseSoftDeleteModel {
        return CouponPublishService::instance()->getModel();
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
        CouponPublishService::instance()->bind($data);
    }

    /**
     * 发布优惠券
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add(){
        $this->title = '发布优惠券';
        $this->getModel()::mForm('form');
    }

    /**
     * 查看
     * @auth true
     */
    public function show(){
        if($this->isGet()){
            $this->getModel()::mForm();
        }
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
            if(empty($data['id'])) $data['gid'] = guid();
            if(empty($data['expire_at'])) $data['expire_at'] = null;
            if(empty($data['fetch_openids'])) $data['fetch_openids'] = null;

            // 默认有效期30天
            if(empty($data['expire_at']) && empty($data['expire_days'])){
                $data['expire_days'] = 30;
            }
        }
    }


    public function getCouponTplByGid() {
        $tplGid = $this->get('tplGid');
        $tpl = CouponTplService::instance()->getOneByGid($tplGid, 'id,title,money,discount,min_use_money');
        if(!$tpl) $this->error("优惠券不存在");
        $this->success("得到一个优惠券", compact('tpl'));
    }

}