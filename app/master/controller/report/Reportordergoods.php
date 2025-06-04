<?php

namespace app\master\controller\report;

use think\admin\helper\QueryHelper;
use think\exception\HttpResponseException;
use think\helper\Str;
use vandles\controller\MasterBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\OrderSubModel;
use vandles\service\OrderService;
use vandles\service\OrderSubService;
use vandles\service\RechargeWayService;
use vandles\service\UserInfoService;
use vandles\service\UserMoneyLogService;

/**
 * 订单商品报表
 * @package app\master\controller\report
 */
class Reportordergoods extends MasterBaseController {
    /**
     * 列表
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index() {
        $this->type = $this->get['type'] ?? 'index';

        // 水站选择
        $this->stations =  OrderService::instance()->getStationOptsByOrder();
        // 收货区域
        $this->districts = OrderService::instance()->getTakeDistrictOptsByOrder();

        $this->getModel()::mQuery()->layTable(function () {
            $this->title = '订单商品报表';
        }, function (QueryHelper $query) {
            $query = $this->getModel()::mQuery()->alias("a")->where(['b.deleted' => 0])
                ->field("a.goods_name,a.goods_number,a.goods_self_price,a.goods_amount goods_amount_goods, b.*")
                ->leftJoin("a_order b", "b.sn = a.order_sn");

            $query->equal("b.id#id,b.take_type#take_type,b.station_gid#station_gid,b.take_district#take_district");
            $query->like("b.sn#sn,a.goods_name#goods_name");
            $query->dateBetween("b.create_at#create_at, b.pay_at#pay_at");

            $pageData = $query->paginate(input('limit'))->toArray();
            $data     = $pageData['data'];
            $this->_index_page_filter($data);

            $result = [
                'code'  => 0,
                'count' => $pageData['total'],
                'data'  => $data,
                'info'  => '',
            ];
            if (input("output") == "json") {
                $result['code'] = 1;
                $result['data'] = [
                    "list" => $data,
                    "page" => [
                        "current" => $pageData['current_page'],
                        "pages"   => $pageData['last_page'],
                    ],
                ];
                throw new HttpResponseException(json($result));
            } else {
                throw new HttpResponseException(json($result));
            }
        });
    }

    public function _index_page_filter(&$data) {
        OrderService::buildTableData($data);

    }

    public function getModel(): BaseSoftDeleteModel {
        return OrderSubService::instance()->getModel();
    }


}