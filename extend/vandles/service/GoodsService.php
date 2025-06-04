<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\helper\Str;
use think\Model;
use vandles\lib\Snowflake;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\GoodsCateModel;
use vandles\model\GoodsModel;
use vandles\model\UserInfoModel;

/**
 * Class GoodsService
 * @package vandles\service
 *
 */
class GoodsService extends BaseService {
    protected static $instance;


    public static function instance(): GoodsService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        dd("init");
    }

    /**
     * 生成商品编码
     * @return string
     */
    public function genSn() {
        return "GOODS" . strtoupper(Str::random(15));
    }

    /**
     * @return BaseSoftDeleteModel|GoodsModel
     */
    public function getModel() {
        return GoodsModel::mk();
    }

    public function bindList(&$data, $openid = null) {
        if ($openid) {
            $cart = CartService::instance()->getOneByOpenid($openid, "gid,goods_snap");
            if (empty($cart->goods_snap)) $goodsSnap = null;
            else $goodsSnap = json_decode($cart->goods_snap, true);
        } else $goodsSnap = null;

        $this->cates = GoodsCateModel::treeTable();
        foreach ($data as &$item) {
            $this->bindOne($item, $goodsSnap);
        }
    }

    public function bindOne(&$data, $goodsSnap = null) {
        if (!empty($data['goods_type'])) {
            $data['goods_type_txt'] = config("a.goods_types." . $data['goods_type']);
        }
        $data['slider'] = explode("|", $data['images']);
        if (isset($goodsSnap[$data['sn']])) $data['number'] = $goodsSnap[$data['sn']]['number'];
        else $data['number'] = 0;

        if (!empty($data['cateids'])) $data['cateids'] = str2arr($data['cateids']);
        else $data['cateids'] = [];


        if (empty($this->cates)) $this->cates = GoodsCateModel::treeTable();

        foreach ($this->cates as $cate) if (in_array($cate['id'], $data['cateids'])) $data['cateinfo'] = $cate;

        if (isset($data['cateinfo'])) {
            $data['cateids_txt'] = join(' ＞ ', $data['cateinfo']['titles']);
        }

        $material               = json_decode($data['material'] ?: '[]');
        $data['material_count'] = count($material);

        // 实物卡类型专用
        if ($data['goods_type'] == 2) {
            $data['use_type_txt'] = config("a.giftcard_use_types." . $data['use_type']) ?: "未知类型";
            if ($data['use_type'] == 1) {
                $takeGoods = $this->getGoodsBySn($data['take_goods_sn'], "id,sn,name");
                if ($takeGoods) {
                    $data['take_goods_sn_txt'] = $takeGoods->name;
                    $data['take_goods']        = $takeGoods;
                } else {
                    $data['take_goods_sn_txt'] = "";
                    $data['take_goods']        = null;
                }
            }
        }

    }

    public function getHotGoodsList($openid, $field = "*") {
        $list = $this->getModel()->whereRaw("goods_type = 0 and status = 1 and is_show = 1")->where("is_hot", 1)
            ->field($field)
            ->order("sort desc,id desc")
            ->select();
        $this->bindList($list, $openid);
        return $list;
    }

    /**
     * 热销实物卡
     * @param $openid
     * @param $field
     * @return array|\think\Collection|BaseSoftDeleteModel[]|GoodsModel[]
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getHotGiftCardList($openid, $field = "*") {
        $list = $this->getModel()->whereRaw("goods_type = 2 and status = 1 and is_show = 1")->where("is_hot", 1)
            ->field($field)
            ->order("sort desc,id desc")
            ->select();
        $this->bindList($list, $openid);
        return $list;
    }

    public function getListBySns(array $sns, $field = "*", $key = null) {
        $list = $this->getModel()->where("status", 1)->where("sn", "in", $sns)
            ->field($field)
            ->order("sort desc, id desc")
            ->select();
        $this->bindList($list);
        if ($key) $list = array_column($list->toArray(), null, $key);
        return $list;
    }

    public function getShowListBySns(array $sns, $field = "*", $key = null) {
        $list = $this->getModel()->whereRaw("status = 1 and is_show = 1")->where("sn", "in", $sns)
            ->field($field)
            ->order("sort desc, id desc")
            ->select();
        $this->bindList($list);
        if ($key) $list = array_column($list->toArray(), null, $key);
        return $list;
    }

    public function getOneBySn($sn, $field = "*") {
        $data = $this->getModel()->where("status", 1)->where("sn", $sn)
            ->field($field)
            ->find();
        $this->bindOne($data);
        return $data;
    }

    public function getShowOneBySn($sn, $field = "*") {
        $data = $this->getModel()->whereRaw("status = 1 and is_show = 1")->where("sn", $sn)
            ->field($field)
            ->find();
        $this->bindOne($data);
        return $data;
    }

    public function getGoodsBySn($sn, $field = "*") {
        $data = $this->getModel()->where("status", 1)->where("sn", $sn)
            ->field($field)
            ->find();
        return $data;
    }

    /**
     * 绑定购物车数据
     * @param $goods
     * @return void
     */
    public function bindCart(&$goods, $number) {
        $amount              = bcmul($number, $goods['self_price'], 2);
        $goods['number']     = $number;
        $goods['amount']     = $amount;
        $goods['is_checked'] = true;
    }

    public function mQuery() {
        return $this->getModel()::mQuery();
    }

    public function reduceStockBySn($goods_sn, $goods_number) {
        $this->getModel()->where("sn", $goods_sn)->dec("stock", $goods_number)->update();
    }

    public function addStockBySn($goods_sn, $goods_number) {
        $this->getModel()->where("sn", $goods_sn)->inc("stock", $goods_number)->update();
    }

    public function updateBySn(string $goodsSn, array $data) {
        return $this->getModel()->where("sn", $goodsSn)->update($data);
    }

    public function getBySn($sn, string $field = "*") {
        $data = $this->getModel()->where("status", 1)->where("sn", $sn)
            ->field($field)
            ->find();
        return $data;
    }

    public function getGoodsBySns(array $sns, string $field = "*") {
        $data = $this->getModel()->where("status", 1)->whereIn("sn", $sns)
            ->order("sort desc, id desc")
            ->field($field)
            ->select();
        $this->bindList($data);
        return $data;
    }

    public function getGoodsListForTakingBySn($sns, $depot_gid) {
        $field = "id, sn goods_sn, name goods_name, produce_spec goods_spec, unit goods_unit, self_price goods_price, cost goods_cost";
        // if (is_array($sns)) $sns = implode(",", $sns);
        $list      = $this->getModel()->where("status", 1)->whereIn("sn", $sns)
            ->order("sort desc, id desc")
            ->field($field)
            ->select();
        $stockList = GoodsStockService::instance()->getListByGoodsSnsAndDepotGid($sns, $depot_gid, "id,depot_gid,goods_sn,goods_number");
        $stockList = $stockList->toArray();
        $stockList = array_column($stockList, "goods_number", "goods_sn");

        $list = array_map(function ($item) use ($stockList) {
            $item['book_number'] = $stockList[$item['goods_sn']] ?? 0;
            return $item;
        }, $list->toArray());
        return $list;
    }

    /**
     * 按分类得到商品列表
     * @param int $int
     */
    public function getListByCateId(int $cateId, $field = "*") {
        $query = $this->getListByCateIdQuery($cateId, $field);
        $query->order("sort desc, id desc");
        return $query->select();
    }

    public function getListByCateIdQuery(int $cateId, string $field = "*") {
        $query = $this->getModel()->where("status", 1)->where("is_show", 1)->where("cateids", "like", "%,{$cateId},%");
        $query->field($field);
        return $query;
    }

    public function getWithMaterialBySn($goods_sn) {
        $goods = $this->getBySn($goods_sn);
        if (!$goods) VException::throw("商品未找到");
        if (empty($goods['material'])) VException::throw("商品未维护原材料信息，请先维护商品原材料信息");
        $goods->material = json_decode($goods['material'], true);
        return $goods;
    }


    /**
     * 计算损耗率
     * 公式：(周期内申领数量 - 周期内返还数量 - 周期内应使用数量) / 周期内应使用数量 * 100%
     *
     * @param $data
     * @param array $date
     * @return void
     */
    public function bindLossRatio(&$data, array $date) {
        // 1. 周期内原材料申领数量
        $getData = OutStockSubService::instance()->getProduceGetNumGroupByGoodsSn($date);

        // 2. 周期内原材料返还数量
        $backData = InStockSubService::instance()->getProduceBackNumGroupByGoodsSn($date);

        // 3. 周期内原材料应使用数量，每产品数量 * 周期使用数量
        // a. 周期内生产的商品及数量:productList
        $productList = InStockSubService::instance()->getSubsQueryByDate($date)->select();
        $productList = $productList->toArray();
        // b. 生产入库的产品用到的原材料
        $sns       = array_column($productList, "goods_sn");
        $goodsList = GoodsService::instance()->getGoodsBySns($sns, "id,sn,name,material");
        // c. 原材料应使用的数量
        $productList = array_column($productList, "goods_number", "goods_sn");
        $shouldData  = [];
        foreach ($goodsList as $goods) {
            $list = json_decode($goods['material'], true);
            if (isset($productList[$goods['sn']])) $should_number = $productList[$goods['sn']];
            else $should_number = 0;
            foreach ($list as $material) {
                if (!isset($shouldData[$material['goods_sn']])) {
                    $material['should_number']         = $material['goods_number'] * $should_number;
                    $shouldData[$material['goods_sn']] = $material;
                } else {
                    $shouldData[$material['goods_sn']]['should_number'] += $material['goods_number'] * $should_number;
                }
            }
        }
        // dd($getData);
        // dd($backData);
        // dd($shouldData);

        foreach ($data as &$vo) {
            $get    = $getData[$vo['sn']]['goods_number'] ?? 0; // 申领数量
            $back   = $backData[$vo['sn']]['goods_number'] ?? 0; // 返还数量
            $use    = $get - $back; // 实际使用数量
            $should = $shouldData[$vo['sn']]['should_number'] ?? 0; // 应使用数量

            if ($should == 0) $ratio = 0;
            else $ratio = round(($use - $should) / $should * 100, 2);

            $vo['get']    = $get;
            $vo['back']   = $back;
            $vo['use']    = $use;
            $vo['should'] = $should;
            $vo['ratio']  = $ratio;
        }
    }

    /**
     * 得到实物卡商品
     * @param $id
     * @return array|mixed|\think\db\Query|Model|BaseSoftDeleteModel|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getGiftCardGoodsById($id) {
        $field = "id,sn,name,self_price,stock,sale_number,goods_type,use_type,init,take_goods_sn,bind_expire_days,useful_days";
        $query = GoodsService::instance()->search([
            "id"         => $id,
            "status"     => 1,
            'goods_type' => 2,
        ])->field($field);
        return $query->find();
    }

    public function getListForCreateComOrderBySns(array $sns) {
        $field = "id,sn,name,cover,unit,min_buy_number,self_price,deliver_fee,market_price,stock,goods_type";
        $query = $this->getModel()->whereIn("sn", $sns);

        return $query->column($field, "sn");
    }


}