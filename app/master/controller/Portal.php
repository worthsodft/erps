<?php

namespace app\master\controller;

use app\data\model\BaseUserUpgrade;
use app\data\model\DataUser;
use app\data\model\DataUserBalance;
use app\data\model\DataUserRebate;
use app\data\model\ShopGoods;
use app\data\model\ShopOrder;
use think\admin\Controller;
use vandles\model\GoodsModel;
use vandles\model\OrderModel;
use vandles\model\OrderSubModel;
use vandles\model\OutStockSubModel;
use vandles\model\UserInfoModel;
use vandles\model\UserMoneyLogModel;
use vandles\service\GoodsService;
use vandles\service\RechargeWayService;
use vandles\service\UserMoneyLogService;

/**
 * 数据统计看板
 */
class Portal extends Controller {
    /**
     * 数据统计看板
     * @auth true
     * @menu true
     */
    public function index() {
        $expire            = 60; // 缓存有效期秒数
        $this->dayCount    = 30; // 统计天数
        $this->usersTotal  = UserInfoModel::mk()->cache(true, $expire)->count();
        $this->goodsTotal  = GoodsModel::mk()->cache(true, $expire)->where(['deleted' => 0])->count();
        $this->orderTotal  = OrderModel::mk()->cache(true, $expire)->whereRaw('status >= 1')->count();
        $this->amountTotal = OrderModel::mk()->cache(true, $expire)->whereRaw('status >= 1 and refund_status != 2')->sum('pay_amount');
        if ($this->amountTotal > 10000) $this->amountTotal = round($this->amountTotal / 10000, 2) . ' 万';
        $key = "portals_index";
        // 近dayCount天的用户及交易趋势
        $this->days = $this->app->cache->get($key, []);
        if (empty($this->days)) {
            for ($i = $this->dayCount; $i >= 0; $i--) {
                $date         = date('Y-m-d', strtotime("-{$i}days"));
                $this->days[] = [
                    '当天日期' => date('m-d', strtotime("-{$i}days")),
                    '增加用户' => UserInfoModel::mk()->whereLike('create_at', "{$date}%")->count(),
                    '订单数量' => OrderModel::mk()->whereLike('create_at', "{$date}%")->whereRaw('status>=1')->count(),
                    '订单金额' => OrderModel::mk()->whereLike('create_at', "{$date}%")->whereRaw('status >= 1 and refund_status != 2')->sum('pay_amount'),
                    '余额充值' => UserMoneyLogModel::mk()->whereLike('create_at', "{$date}%")->whereRaw('deleted=0 and (log_type="recharge" or log_type="give")')->sum('delta'),
                    '余额消费' => UserMoneyLogModel::mk()->whereLike('create_at', "{$date}%")->whereRaw('deleted=0 and log_type="order"')->sum('delta'),
                    '累计余额' => UserMoneyLogModel::mk()->whereRaw("create_at<='{$date} 23:59:59' and deleted=0")->sum('delta'),
                ];
            }
            $this->app->cache->set($key, $this->days, $expire);
        }

        // 充值次数统计
        $this->rechargeData = UserMoneyLogService::instance()->totalCount();

        $this->fetch();
    }


    /**
     * 历史销量统计
     * @auth true
     * @menu true
     * @return void
     */
    public function goods_sale_history() {
        $expire         = 60; // 缓存有效期秒数
        $this->dayCount = 60; // 统计天数
        $key            = "portals_goods_sale_history";
        // 近dayCount天的商品销售趋势
        cache($key, null);
        $this->data = $this->app->cache->get($key, []);
        if (!empty($this->data)) $this->fetch();

        // 分类是生产商品的所有商品
        $db = GoodsService::instance()->getListByCateIdQuery(1, "sn");

        // 小程序销售
        $goodsListMini = OrderSubModel::mk()->alias("a")->whereBetween('b.pay_at', [date('Y-m-d', strtotime("-{$this->dayCount}days")), date('Y-m-d 23:59:59')])
            ->fieldRaw("a.goods_sn, a.goods_name,sum(a.goods_number) goods_number, date(b.pay_at) pay_at")
            ->join("a_order b", 'a.order_sn=b.sn')
            ->whereRaw("b.status = 1 or b.status = 2 or (b.status = 9 and b.refund_status != 2)")
            ->whereRaw("a.goods_sn in {$db->buildSql()}")
            ->orderRaw("date(b.pay_at)")
            ->group("a.goods_sn,a.goods_name,date(b.pay_at)")->select();

        // 企业用户销售
        $goodsListCus = OutStockSubModel::mk()->alias("a")->whereBetween('b.create_at', [date('Y-m-d', strtotime("-{$this->dayCount}days")), date('Y-m-d 23:59:59')])
            ->fieldRaw("a.goods_sn, a.goods_name,sum(a.goods_number) goods_number, date(b.create_at) pay_at")
            ->join("a_out_stock b", 'a.out_stock_sn=b.sn')
            ->whereRaw("b.paid_amount = b.amount")
            ->whereRaw("a.goods_sn in {$db->buildSql()}")
            ->orderRaw("date(b.create_at)")
            ->group("a.goods_sn,a.goods_name,date(b.create_at)")->select();;
        // dd($goodsListCus->toArray());

        // 二者合并
        $goodsList = array_merge($goodsListMini->toArray(), $goodsListCus->toArray());

        $dates     = [];
        $goodsData = [];

        foreach ($goodsList as $item) {
            $date        = $item['pay_at'];
            $goodsSn     = $item['goods_sn'];
            $goodsName   = $item['goods_name'];
            $goodsNumber = $item['goods_number'];

            if (!isset($goodsData[$goodsSn])) {
                $goodsData[$goodsSn] = [
                    'name' => $goodsName,
                    'data' => []
                ];
            }

            if (!in_array($date, $dates)) {
                $dates[] = $date;
            }

            $goodsData[$goodsSn]['data'][$date] = $goodsNumber;
        }


        // 补全缺失的日期数据
        foreach ($goodsData as &$good) {
            foreach ($dates as $date) {
                if (!isset($good['data'][$date])) {
                    $good['data'][$date] = 0;
                }
            }
            ksort($good['data']);
        }

        $data['goodsNames'] = array_column($goodsData, 'name');
        $data['dates']      = $dates;
        $data['goodsData']  = array_values(array_map(function ($good) {
            return [
                'name' => $good['name'],
                'data' => array_values($good['data']),
            ];
        }, $goodsData));

        // 没有销售数据
        if (empty($data['goodsNames'])) {
            $data['goodsNames'][] = '暂无销售数据';
            $data['dates']        = [];
            for ($i = $this->dayCount; $i >= 0; $i--) {
                $data['dates'][] = date('Y-m-d', strtotime("-{$i}days"));
            }
            $data['goodsData'][] = [
                'name' => '暂无销售数据',
                'data' => array_fill(0, $this->dayCount, 0),
            ];
        }

        $this->data = $data;
        $this->app->cache->set($key, $this->data, $expire);

        $this->fetch();
    }
}