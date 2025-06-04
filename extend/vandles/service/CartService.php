<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CartModel;
use vandles\model\CouponTplModel;

class CartService extends BaseService {
    protected static $instance;


    public static function instance(): CartService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $data = [
            [
                "gid"           => uuid(),
                "openid"        => uuid(),
            ],[
                "gid"           => uuid(),
                "openid"        => uuid(),
            ]
        ];

        $res  = $this->getModel()->saveAll($data);
        $list = $this->getModel()->select();
        dd($list->toArray());
    }

    /**
     * @return BaseSoftDeleteModel|CartModel
     */
    public function getModel() {
        return CartModel::mk();
    }

    /**
     * 删除到购物车
     * @param $openid
     * @param $sn
     * @param $number
     * @return array|mixed|\think\Model|BaseSoftDeleteModel|CartModel|null
     */
    public function del2cart($openid, $sn) {
        $cart = $this->getOneByOpenid($openid);
        if(empty($cart)) return null;

        $goodsSnap = json_decode($cart->goods_snap, true);
        if(isset($goodsSnap[$sn]))unset($goodsSnap[$sn]);
        $cart->goods_snap = json_encode($goodsSnap);
        $cart->save();
        return $cart;
    }

    /**
     * 选择购物车
     * @param $openid
     * @param $sn
     * @param $number
     * @param $type
     * @return array|mixed|\think\Model|BaseSoftDeleteModel|CartModel|null
     */
    public function check2cart($openid, $sn, $is_checked) {
        $cart = $this->getOneByOpenid($openid);
        if(empty($cart)){
            VException::throw("购物车不存在");
        }else{
            $goodsSnap = json_decode($cart->goods_snap, true);
            if($sn == -1){
                foreach ($goodsSnap as $item) {
                    $goodsSnap[$item['sn']]['is_checked'] = $is_checked;
                }
                $cart->is_checked = $is_checked;
            }elseif(isset($goodsSnap[$sn])){
                $goodsSnap[$sn]['is_checked'] = $is_checked;
            }else{
                VException::throw("购物车商品不存在");
            }
            $cart->goods_snap = json_encode($goodsSnap);
            $cart->save();
        }
        return $cart;
    }

    /**
     * 操作到购物车
     * @param $openid
     * @param $sn
     * @param $number
     * @return array|mixed|\think\Model|BaseSoftDeleteModel|CartModel|null
     */
    public function operate2cart($openid, $sn, $number, $type="add") {
        $cart = $this->getOneByOpenid($openid);
        if(empty($cart)){
            $snap = [
                $sn => [
                    'sn'         => $sn,
                    'number'     => $number,
                    'is_checked' => true,
                ]
            ];
            $data = [
                "openid" => $openid,
                "gid" => uuid(),
                "goods_snap" => json_encode($snap)
            ];
            $cart = $this->create($data);
        }else{
            $goodsSnap = json_decode($cart->goods_snap, true);
            if(isset($goodsSnap[$sn])){
                if($type == "add") {
                    $goodsSnap[$sn]['number'] += $number;
                    $goodsSnap[$sn]['is_checked'] = true;
                }
                elseif($type == "update" && $number > 0) {
                    $goodsSnap[$sn]['number']     = $number;
                    $goodsSnap[$sn]['is_checked'] = true;
                } elseif($type == "update" && $number <= 0) unset($goodsSnap[$sn]);
            }else{
                $goodsSnap[$sn] = [
                    'sn'         => $sn,
                    'number'     => $number,
                    'is_checked' => true,
                ];
            }
            $cart->goods_snap = json_encode($goodsSnap);
            $cart->save();
        }
        return $cart;
    }

    public function getOneByOpenid($openid, $field="*") {
        return $this->getModel()->field($field)->where("openid", $openid)->find();
    }

    /**
     * 绑定商品、数量、金额等数据
     * @param $cart
     * @return void
     */
    public function bind(&$cart) {
        if(empty($cart['goods_snap'])){
            $cart['total'] = 0;
            $cart['amount'] = 0;
            $cart['list'] = [];
            return;
        }
        $goodsSnap = json_decode($cart['goods_snap'], true);
        unset($cart->goods_snap);
        $sns = array_keys($goodsSnap);
        $goodsList = GoodsService::instance()->getShowListBySns($sns, "id,sn,name,cover,unit,min_buy_number,self_price,deliver_fee,market_price,stock,goods_type", "sn");

        $total = 0;
        $amount = 0;
        $list = [];
        $is_checked_all = true;
        foreach ($goodsSnap as $sn=>$vo) {
            if(empty($goodsList[$sn])) continue;
            $goods = $goodsList[$sn];
            $goods['is_checked'] = !empty($vo['is_checked']);
            $sub = bcmul($vo['number'], $goods['self_price'], 2);
            if($goods['is_checked']){
                $total += $vo['number'];
                $amount = bcadd($amount, $sub, 2);
            }else {
                $is_checked_all = false;
            }

            $goods['number'] = $vo['number'];
            $goods['amount'] = $sub;
            $list[] = $goods;
        }

//        if(empty($list)) VException::runtime("购买的商品不存在");
        $cart['total'] = $total;
        $cart['amount'] = $amount;
        $cart['list'] = $list;
        $cart['is_checked'] = $is_checked_all;
    }

    /**
     * 只得到购物车数量
     * @param $openid
     * @return int
     */
    public function getCartTotalByOpenid($openid) {
        $cart = $this->getOneByOpenid($openid);
        if(empty($cart)) return 0;
        $goodsSnap = json_decode($cart->goods_snap, true);

        $sns = array_keys($goodsSnap);
        $goodsList = GoodsService::instance()->getShowListBySns($sns, "id,sn,name", "sn");

        $total = 0;
        foreach ($goodsSnap as $sn=>$vo) {
            if(empty($goodsList[$sn])) continue;
            if(empty($vo['is_checked'])) continue;
            $total += $vo['number'];
        }
        return $total;
    }

    public function clearCart($openid) {
        $this->getModel()->where("openid", $openid)->update(['deleted'=>1]);
    }

    /**
     * 清除购物车中已选中的商品
     * @param $openid
     * @return void
     */
    public function clearCheckedCart($openid) {
        $one = $this->getModel()->where("openid", $openid)->where("status", 1)
            ->order("id desc")->find();
        if(empty($one)) return;
        $goodsSnap = json_decode($one->goods_snap, true);
        $list = [];
        foreach ($goodsSnap as $k=>$item) {
            if(!$item['is_checked'])$list[$k] = $item;
        }

        if(empty($list)) {
            $this->getModel()->where("id", $one['id'])->update(['deleted'=>1]);
        }else{
            $one->goods_snap = json_encode($list);
            $one->save();
        }
    }


}