<?php

namespace app\master\controller\product;

use think\admin\extend\HttpExtend;
use think\admin\helper\QueryHelper;
use vandles\controller\MasterCrudBaseController;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\GoodsCateModel;
use vandles\service\GiftCardService;
use vandles\service\GoodsService;

/**
 * 商品信息管理
 * @package app\master\controller\product
 */
class Goods extends MasterCrudBaseController {
    public $goods_type = 0; // 0销售商品，1原材料

    /**
     * 商品信息管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index() {
        if ($this->goods_type == 0 || $this->goods_type == 2) $this->type = $this->get['type'] ?? 'index';
        elseif ($this->goods_type == 1) $this->type = $this->get['type'] ?? 'recycle';


        $tpl = "index";
        if ($this->goods_type == 2) $tpl = "indexgiftcard";

        $this->getModel()::mQuery()->layTable(function () {
            if ($this->goods_type == 0) $this->title = '商品信息管理';
            elseif ($this->goods_type == 1) $this->title = '原材料管理';
            elseif ($this->goods_type == 2) $this->title = '实物卡商品管理';

            $this->cates = GoodsCateModel::treeTable(false, $this->goods_type);

        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'goods_type' => $this->goods_type, 'is_show' => intval($this->type === 'index')]);
            $query->like('name,sn|barcode#sn')->like('cateids', ",")->equal('goods_type,status')->dateBetween('create_at');
        }, $tpl);
    }

    public function _index_page_filter(&$data) {
        GoodsService::instance()->bindList($data);
    }

    /**
     * 原材料管理
     * @menu true
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index1() {
        $this->goods_type = 1;
        $this->index();
    }

    public function _index1_page_filter(&$data) {
        GoodsService::instance()->bindList($data);
    }

    /**
     * 实物卡商品管理
     * @menu true
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function indexgiftcard() {
        $this->goods_type = 2;
        $this->index();
    }

    public function _indexgiftcard_page_filter(&$data) {
        GoodsService::instance()->bindList($data);
    }

    /**
     * 添加
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add() {
        $this->title = '添加';
        $tpl         = 'form';
        if ($this->isGet()) {
            $this->goods_type = input("goods_type", 0);
            if ($this->goods_type == 2) $tpl = 'form_giftcard';
        }
        $this->getModel()::mForm($tpl);
    }

    /**
     * 编辑
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit() {
        $this->title = '编辑';
        $tpl         = 'form';
        if ($this->isGet()) {
            $this->goods_type = input("goods_type", 0);
            if ($this->goods_type == 2) $tpl = 'form_giftcard';
        }
        $this->getModel()::mForm($tpl);
    }

    public function _form_filter(&$data) {
        if ($this->isGet()) {
            $data['cateids'] = str2arr($data['cateids'] ?? '');
            $this->cates     = GoodsCateModel::treeTable(true, $this->goods_type);

            // 实物卡相关
            if ($this->goods_type == 2) {
                if (!empty($data['take_goods_sn'])) {
                    $take_goods            = GoodsService::instance()->getGoodsBySn($data['take_goods_sn']);
                    $this->take_goods_name = $take_goods['name'] ?? '';
                } else {
                    $this->take_goods_name = "";
                }
            }
        } elseif ($this->isPost()) {
            if (($data['goods_type'] == 0 || $data['goods_type'] == 2) && !$data['cover']) $this->error("销售商品封面不能为空");
            if (!$data['name']) $this->error("商品名称不能为空");
            if (empty($data['unit'])) $this->error("单位不能为空");
            if (empty($data['cateids'])) $this->error("商品分类不能为空");

            if (empty($data['id'])) $data['sn'] = GoodsService::instance()->genSn();
            else {
                unset($data['sn']);
                $data['update_at'] = date('Y-m-d H:i:s');
            }

            if (!$data['images']) $data['images'] = $data['cover'];

            if (!empty($data['desc'])) {
                $data['desc'] = str_replace('<div><img', '<div style="font-size: 0;"><img style="display:block;width:100%;"', $data['desc']);
                $data['desc'] = str_replace('<p><img', '<p style="font-size: 0;"><img style="display:block;width:100%;"', $data['desc']);
            }

            // 实物卡相关
            if ($data['goods_type'] == 2) {
                if ($data['use_type'] == 0) { // 金额
                    $data['take_goods_sn'] = null;
                    if (empty($data['init'])) $this->error("请输入面值金额");
                } elseif ($data['use_type'] == 1) { // 数量
                    if (empty($data['init'])) $this->error("请输入面值次数");
                    if (empty($data['take_goods_sn'])) $this->error("请选择计次商品");
                }
                if ($data['init'] <= 0) $this->error("面值必须大于0");

                if (empty($data['bind_expire_days'])) $this->error("请输入绑定有效期天数");
                if (empty($data['useful_days'])) $this->error("请输入使用有效期天数");

            }

            // dd($data);
        }
    }

    public function getModel(): BaseSoftDeleteModel {
        return GoodsService::instance()->getModel();
    }

    /**
     * 修改是否热销
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function is_hot() {
        $this->getModel()::mSave($this->_vali([
            'is_hot.in:0,1'  => '状态值范围异常！',
            'is_hot.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 修改是否上架
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function is_show() {
        $this->getModel()::mSave($this->_vali([
            'is_show.in:0,1'  => '状态值范围异常！',
            'is_show.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 选择商品
     * @auth true
     * @return void
     */
    public function select() {
        $this->title      = '选择商品';
        $this->type       = 'index';
        $this->is_multi   = $this->get('is_multi', 0);
        $this->goods_type = $this->get('goods_type', '-1');
        $this->cates      = GoodsCateModel::treeTable(false, $this->goods_type);

        $this->getModel()::mQuery()->layTable(function () {
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => 1])->order("sort desc");

            if ($this->goods_type == 1) $query->where("goods_type", 1); // 选择原材料

            $query->like('name,sn|barcode#barcode')->like('cateids', ",")->dateBetween('create_at');
        });
    }

    /**
     * 选择原材料
     * @auth true
     * @return void
     */
    public function selectMaterial() {
        $this->title      = '选择原材料';
        $this->type       = 'index';
        $this->is_multi   = $this->get('is_multi', 0);
        $this->goods_type = 1;
        $this->cates      = GoodsCateModel::treeTable(false, $this->goods_type);

        $this->getModel()::mQuery()->layTable(function () {
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => 1, 'goods_type' => '1'])->order("sort desc");
            $query->like('name,sn|barcode#barcode')->dateBetween('create_at');
        }, "select");
    }

    /**
     * 添加明细时，查询商品
     * @return void
     */
    public function getGoodsBySn() {
        $sn    = $this->get("sn");
        $p     = $this->get("p");
        $field = "id,sn goods_sn,name goods_name,produce_spec goods_spec,unit goods_unit";
        if (!empty($p)) $field .= ",self_price goods_price,cost goods_cost";
        $sn    = explode(",", $sn);
        $goods = GoodsService::instance()->getGoodsBySns($sn, $field);
        if (empty($goods)) $this->error("商品不存在");
        $this->success("获取成功", compact('goods'));
    }

    /**
     * 添加明细时，查询商品(用于盘点)
     * @auth true
     * @return void
     */
    public function getGoodsForTakingBySn() {
        $sn        = $this->post("sn");
        $depot_gid = $this->post("depot_gid");
        if (empty($depot_gid)) $this->error("请选择仓库");
        $goods = GoodsService::instance()->getGoodsListForTakingBySn($sn, $depot_gid);
        if (empty($goods)) $this->error("商品不存在");
        $this->success("获取成功", compact('goods'));
    }

    /**
     * 维护原材料
     * @auth true
     * @return void
     */
    public function material() {
        if ($this->isGet()) {
            $id = $this->get("id");
            $vo = GoodsService::instance()->getById($id, "id,sn,name,material");
            if (empty($vo)) $this->error("商品不存在");
            $material = $vo['material'] ?: '[]';
            unset($vo['material']);
            $this->material = $material;
            $this->vo       = $vo->toArray();
            $this->fetch();
        } else {
            $post = $this->post();
            if (empty($post['sn'])) $this->error("商品编号不能为空");
            if (empty($post['list'])) $this->error("请选择原材料");
            // 如果原材料重复，取排序中靠后的一个
            $material = array_values(array_column($post['list'], null, 'goods_sn'));
            $material = json_encode($material, JSON_UNESCAPED_UNICODE);
            GoodsService::instance()->updateBySn($post['sn'], ['material' => $material]);
            $this->success("保存成功");
        }
    }

    /**
     * 批量制卡
     * @auth true
     * @return void
     */
    public function batch() {
        $id = $this->param("id");
        if ($this->isGet()) {
            $goods = GoodsService::instance()->getGiftCardGoodsById($id);
            if (empty($goods)) $this->error("实物卡商品不存在");
            GoodsService::instance()->bindOne($goods);
            $this->goods = $goods;
            // dd($goods->toArray());
            $this->fetch();
        } else {
            $post = $this->post();
            if (empty($post['id'])) $this->error("请输入实物卡商品ID");
            if (empty($post['number'])) $this->error("请输入制卡数量");
            if ($post['number'] < 1) $this->error("制卡数量不能小于1");

            try {
                // 批量制卡
                $res = GiftCardService::instance()->batchGiftCardByGoodsId($post['id'], $post['number']);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }

            $this->success("操作成功");
        }
    }
}