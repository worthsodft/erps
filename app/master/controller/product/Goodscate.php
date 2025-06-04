<?php

// +----------------------------------------------------------------------
// | Shop-Demo for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2022~2023 Anyon <zoujingli@qq.com>
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// | 会员免费 ( https://thinkadmin.top/vip-introduce )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkAdmin
// | github 代码仓库：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\master\controller\product;

use app\data\model\ShopGoodsCate;
use think\admin\Controller;
use think\admin\extend\DataExtend;
use think\admin\helper\QueryHelper;
use vandles\controller\MasterBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\GoodsCateModel;
use vandles\service\GoodsCateService;

/**
 * 商品分类管理
 */
class Goodscate extends MasterBaseController
{
    /**
     * 最大级别
     * @var integer
     */
    protected $maxLevel = 3;

    /**
     * 商品分类管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
        GoodsCateModel::mQuery()->layTable(function () {
            $this->title = "商品分类管理";
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0]);
            $query->like('title')->equal('status')->dateBetween('create_at');
        });
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_page_filter(array &$data){
        $data = DataExtend::arr2table($data);
    }

    /**
     * 添加商品分类
     * @auth true
     */
    public function add(){
        GoodsCateModel::mForm('form');
    }

    /**
     * 编辑商品分类
     * @auth true
     */
    public function edit(){
        GoodsCateModel::mForm('form');
    }

    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function _form_filter(array &$data){
        if ($this->request->isGet()) {
            $data['pid'] = intval($data['pid'] ?? input('pid', '0'));
            if($data['pid'] == 0) $this->goods_type = -1;
            if($data['pid'] > 0){
                $this->goods_type = GoodsCateModel::mk()->where(['id'=>$data['pid']])->value('goods_type');
            }
            // dd($this->goods_type);
            $this->cates = GoodsCateModel::getParentData($this->maxLevel, $data, [
                'id' => '0', 'pid' => '-1', 'title' => '顶部分类',
            ]);
        }
    }

    /**
     * 修改商品分类状态
     * @auth true
     */
    public function state() {
        GoodsCateModel::mSave($this->_vali([
            'status.in:0,1'  => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 删除商品分类
     * @auth true
     */
    public function remove(){
        $ids = input('id');
        if(GoodsCateService::instance()->isUsing($ids)){
            $this->error("分类使用中，不允许删除");
        }
        GoodsCateModel::mDelete();
    }

    public function getModel(): BaseSoftDeleteModel {
        return GoodsCateModel::mk();
    }
}