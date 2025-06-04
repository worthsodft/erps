<?php

namespace app\master\controller\base;

use think\admin\helper\QueryHelper;
use vandles\controller\MasterCrudBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\CustomerService;
use vandles\service\UserAddressService;
use vandles\service\WaterStationService;

/**
 * 客户收货地址管理
 */
class Useraddress extends MasterCrudBaseController {

    public function getModel(): BaseSoftDeleteModel {
        return UserAddressService::instance()->getModel();
    }

    /**
     * 客户收货地址管理
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
        if($this->isGet()){
            $this->openid = $this->get("openid");
        }else{
            if(empty($data['id'])){
                $data['gid'] = uuid();
            }
        }
    }

    /**
     * 修改默认状态
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function is_default(){
        $this->getModel()::mSave($this->_vali([
            'is_default.in:0,1'  => '状态值范围异常！',
            'is_default.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 选择地址
     * @auth true
     * @return void
     */
    public function select() {
        $this->title = '选择地址';
        $this->type = 'index';
        $this->openid = $this->get("openid", "");
        if(empty($this->openid)) $this->error("请选择地址");
        $this->getModel()::mQuery()->layTable(function () {
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => 1])->order("sort desc");

            $query->where("openid", $this->openid);
            $query->like('title')->dateBetween('create_at');
        });
    }

    /**
     * 添加明细时，查询地址
     * @return void
     */
    public function getAddressByGid() {
        $gid    = $this->get("gid");
        $one = UserAddressService::instance()->getOneByGid($gid, "id,gid,link_name,link_phone,detail");
        if (empty($one)) $this->error("地址不存在");
        $this->success("获取成功", compact('one'));
    }

}