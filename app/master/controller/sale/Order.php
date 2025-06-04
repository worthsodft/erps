<?php

namespace app\master\controller\sale;

use Exception;
use think\admin\helper\QueryHelper;
use think\facade\Db;
use think\migration\UsePhinx;
use vandles\controller\MasterBaseController;
use vandles\lib\order\OrderStatusMachine;
use vandles\lib\order\strategy\CancelMasterStrategy;
use vandles\lib\order\strategy\PickupMasterStrategy;
use vandles\lib\order\strategy\RefundMasterStrategy;
use vandles\lib\order\strategy\VerifyMasterStrategy;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\ConfigService;
use vandles\service\CustomerService;
use vandles\service\OrderService;
use vandles\service\OrderSubService;
use vandles\service\UserInfoService;
use vandles\service\WaterStationService;

/**
 * 小程序订单管理
 */
class Order extends MasterBaseController {

    /**
     * 支付方式
     * @var array
     */
    protected $payments = [];

    /**
     * 订单来源
     * @var string
     */
    protected $from; // mini:小程序用户，company:企业用户.partner:经销商



    /** @var OrderService */
    private $orderService;

    /** @var OrderSubService */
    private $orderSubService;

    public $mode = "";

    /**
     * 控制器初始化
     */
    protected function initialize(){
        parent::initialize();
        $this->payments = config("a.order_pay_types");

        $this->orderService = OrderService::instance();
        $this->orderSubService = OrderSubService::instance();
    }

    /**
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
        if(empty($this->from)){
            $this->title = '小程序订单管理';
            $this->from = 'mini';
        }
        $this->type = trim(input('type', 'ta'), 't');
        // $get = $this->get();
        // 状态数据统计
        $this->total = ['ta' => 0, 't0' => 0, 't10' => 0, 't11' => 0, 't2' => 0, 't9' => 0];
        $data = $this->getModel()->where("from", $this->from)->field('status,deliver_status,count(1) total')->group('status, deliver_status')->cursor();
        foreach ($data as $vo) {
            $this->total["ta"] += $vo['total'];
            if ($vo['status'] == 1) $this->total['t1'.$vo['deliver_status']] = ($this->total['t1'.$vo['deliver_status']]??0) + $vo['total'];
            else $this->total["t{$vo['status']}"] += $vo['total'];
        }
        // 水站选择
        $this->stations =  OrderService::instance()->getStationOptsByOrder();
        // 收货区域
        $this->districts = OrderService::instance()->getTakeDistrictOptsByOrder();


        // 订单列表查询
        $query = OrderService::instance()->mQuery()->where("from", $this->from);
        $query->like('sn,openid,take_name|take_phone|take_address#take_info,station_link_name|station_link_phone|station_address#station_info,transaction_id');
        $query->equal('station_gid,take_district,deliver_status,take_type,pay_type,refund_status')->dateBetween('create_at,pay_at,pick_at,take_at');

        // 用户搜索查询
        $db = UserInfoService::instance()->mQuery()->like('phone|nickname|realname#user_info')->db();
        if ($db->getOptions('where')) $query->whereRaw("openid in {$db->field('openid')->buildSql()}");

        // 核销人搜索查询
        $db = UserInfoService::instance()->mQuery()->like('phone|nickname|realname#deliver_info')->db();
        if ($db->getOptions('where')) $query->whereRaw("take_by in {$db->field('openid')->buildSql()}");

        // 列表选项卡 ta,t0,t10,t11,t2,t9
        if (is_numeric($this->type)) {
            if($this->type == '10') $query->where(['status' => 1, 'deliver_status'=> 0]);
            elseif($this->type == '11') $query->where(['status' => 1, 'deliver_status'=> 1]);
            else $query->where(['status' => $this->type]);
        }

        // 分页排序处理
        $query->order('id desc')->cusLimit()->page(true,true,false,0,'index');
    }

    /**
     * 小程序订单管理
     * @auth true
     * @menu true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function indexmini(){
        $this->title = '小程序订单管理';
        $this->from = 'mini';
        // 订单支付方式
        $this->orderPayTypes = config("a.order_pay_types");
        $this->index();
    }
    protected function _indexmini_page_filter(&$data) {
        $this->_index_page_filter($data);
    }

    /**
     * 企业订单管理
     * @auth true
     * @menu true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function indexcompany(){
        $this->title = '企业订单管理';
        $this->from = 'company';
        // 订单支付方式
        $this->orderPayTypes = config("a.com_order_pay_types");
        $this->index();
    }
    protected function _indexcompany_page_filter(&$data) {
        $this->_index_page_filter($data);
    }

    /**
     * 经销商订单管理
     * @auth true
     * @menu true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function indexpartner(){
        $this->title = '经销商订单管理';
        $this->from = 'partner';
        // 订单支付方式
        $this->orderPayTypes = config("a.com_order_pay_types");
        $this->index();
    }
    protected function _indexpartner_page_filter(&$data) {
        $this->_index_page_filter($data);
    }

    /**
     * 订单列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function _index_page_filter(&$data){
        // 用户信息
        $openids1 = array_unique(array_filter(array_column($data, 'openid')));
        // 核销/配送人员
        $openids2 = array_unique(array_filter(array_column($data, 'take_by')));
        // 配货人员
        $openids3 = array_unique(array_filter(array_column($data, 'pick_by')));
        $openids = array_unique(array_merge($openids1, $openids2, $openids3));

        $userInfos = UserInfoService::instance()->getListByOpenids($openids, "id,openid,nickname,realname,avatar,phone");
        $userInfos = array_column($userInfos->toArray(), null, 'openid');

        // 商品信息
        $sns = array_unique(array_column($data, 'sn'));
        $items = OrderSubService::instance()->getSubsBySns($sns, "order_sn,goods_sn,goods_name,goods_self_price,goods_number,goods_amount,goods_cover,goods_unit");
        $subs = [];
        foreach ($items as $item) {
            $subs[$item['order_sn']][] = $item;
        }

        // 配送人
//        $ids = array_unique(array_column($data, 'take_by'));

        // 订单数据
        $refundStatus = config("a.order_refund_status");
        $deliverStatus = config("a.order_deliver_status");
        $status = config("a.order_status");
        $status = array_column($status, 'title', 'status');
        $payTypes      = array_merge(config("a.order_pay_types"),config("a.com_order_pay_types"));

        if($this->from == 'company'){
            $buyers = CustomerService::instance()->getListByGids($openids1, 'id,gid,title', 'gid');
        }elseif($this->from == 'partner'){
            $buyers = WaterStationService::instance()->getListByGids($openids1, 'id,gid,title', 'gid');
        }else{
            $buyers1 = CustomerService::instance()->getListByGids($openids1, 'id,gid,title', 'gid');
            $buyers2 = WaterStationService::instance()->getListByGids($openids1, 'id,gid,title', 'gid');
            $buyers = array_merge($buyers1, $buyers2);
        }

        $froms = config("a.froms");

        foreach ($data as &$vo) {
            $vo['user'] = $userInfos[$vo['openid']] ?? [];
            $vo['openid_txt'] = $vo['user']['nickname']??"";
            $vo['subs'] = $subs[$vo['sn']] ?? [];
            $vo['status_txt'] = $status[$vo['status']]??"未知状态";
            $vo['refund_status_txt'] = $refundStatus[$vo['refund_status']]??"";
            $vo['deliver_status_txt'] = $deliverStatus[$vo['deliver_status']]??"";
            $vo['picker'] = $userInfos[$vo['pick_by']] ?? [];
            $vo['taker'] = $userInfos[$vo['take_by']] ?? [];
            if($vo['deliver_images']) $vo['deliver_images'] = explode("|", $vo['deliver_images']);

            $vo['pay_type_txt'] = $payTypes[$vo['pay_type']]??"未知";

            if($vo['from'] == 'company'){
                $vo['buyer'] = $buyers[$vo['openid']] ?? [];
                $vo['openid_txt'] = $vo['buyer']['title']??'';
            }elseif($vo['from'] == 'partner'){
                $vo['buyer'] = $buyers[$vo['openid']] ?? [];
                $vo['openid_txt'] = $vo['buyer']['title']??'';
            }

            $vo['from_txt'] = $froms[$vo['from']]??'未知';
        }
    }

    /**
     * 添加订单
     * @auth true
     * @return void
     */
    public function add() {
        $this->from = $this->param('from', 'company');
        if($this->isGet()){
            $this->mode = 'add';
            $this->fetch('form');
        }else{
            $post = $this->post();
            $vo = $post['vo']??null;
            $subs = $post['subs']??[];

            if(empty($vo)) $this->error("订单信息不能为空");
            if(empty($subs)) $this->error("请选择商品");

            try{
                OrderService::instance()->createFromComOrPartner($vo, $subs);
            }catch(\Exception $e){
                $this->error($e->getMessage());
            }

            $this->success("订单创建成功");
        }
    }

    /**
     * 编辑订单
     * @auth true
     * @return void
     */
    public function edit() {
        $this->from = $this->param('from');
        if($this->isGet()){
            $this->mode = 'edit';

            dd($this->mode);
            $this->fetch('form');
        }else{
            $post = $this->post();
            dd($post);
        }

    }

    /**
     * 订单支付
     * @auth true
     * @return void
     */
    public function pay() {
        if($this->isGet()){
            $this->mode = 'pay';
            $sn = $this->get("sn");
            $vo = $this->orderService->getOneBySn($sn);
            if(empty($vo)) $this->error("订单不存在");
            $this->orderService->bindOne($vo);

            $this->vo = $vo;
            $this->subs = $this->orderSubService->getSubsBySn($sn);

            $this->fetch();
        }else{
            $post = $this->post();
            if(empty($post['sn'])) $this->error("请输入订单编号");
            if(empty($post['pay_type'])) $this->error("请选择支付方式");
            $order           = $this->orderService->getOneBySn($post['sn']);

            // if($post['pay_type'] == 'com_credit') $this->error("暂不支持挂账，请更换其他方式支付");

            Db::startTrans();
            try{
                if (!$this->orderService->checkBeforePay($order)) VException::throw("订单不满足支付条件");
                $this->orderService->payOrderComSimple($order, $post['pay_type'], $post['pay_remark']);
                $res = $this->orderService->payOrderSuccess($order, $post['pay_type']);

                if($res) Db::commit();
                else VException::throw("订单支付失败");
            }catch(\Exception $e){
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success("支付成功");
        }
    }

    /**
     * 确认退款
     * @auth true
     */
    public function refund() {
        $sn = $this->param("sn");
        $orderService = OrderService::instance();
        $order = $orderService->getOneBySn($sn);
        if(empty($order)) $this->error("订单不存在");
        if($order['status'] == OrderService::ORDER_STATUS_UNPAY) $this->error("订单未支付");
        if(empty($order['pay_at'])) $this->error("订单未支付");
        if($order['status'] != OrderService::ORDER_STATUS_REFUND) $this->error("订单未申请退款");
        if($order['refund_status'] == OrderService::REFUND_APPLY_STATUS_UNREFUND) $this->error("订单未申请退款");
        if($order['refund_status'] == OrderService::REFUND_APPLY_STATUS_REFUNDFINISHED) $this->error("订单已退款");
        if($order['refund_status'] == OrderService::REFUND_APPLY_STATUS_REFUNDFAIL) $this->error("订单退款已驳回");
        if($this->isGet()){
            $this->vo = $order;
            $this->subs = OrderSubService::instance()->getSubsBySns([$order['sn']], "order_sn,goods_sn,goods_name,goods_price,goods_number,goods_cover,goods_unit");

            if($order->pay_type == 'weixin' && isAdmin()){
                if($orderService->isWxRefundAll($order['sn'])) {
                    $orderService->refundSuccess($order, "退款时更新订单状态失败，补录订单状态");
                    $this->success("订单已全部退款，状态补录成功");
                }
            }

            $this->fetch();
        }else{
            $post = $this->post();
            if($post['is_pass'] == 1){ // 同意
                try {
                    $orderService->refund($order, $order->pay_amount, $order->refund_reason, $post['refund_feedback_msg']);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success("申请已通过，退款成功");
            }elseif($post['is_pass'] == 0){ // 驳回
                if(empty($post['refund_feedback_msg'])) $this->error("请填写驳回理由");
                $order->refund_status = OrderService::REFUND_APPLY_STATUS_REFUNDFAIL;
                $order->refund_feedback_msg = $post['refund_feedback_msg'];
                $order->refund_feedback_at = now();
                $order->status = $order->refund_before_status ?? 1;
                $order->save();
                $this->success("申请已驳回");
            }
        }
    }

    /**
     * 导出(配送用)
     * @auth true
     * @return void
     */
    public function export_deliver() {}

    /**
     * 导出(财务用)
     * @auth true
     * @return void
     */
    public function export_money() {}


    /**
     * 后台退款
     * @auth true
     * @return void
     * @throws Exception
     */
    public function refundMaster() {
        $this->mode = "refundMaster";
        // dd($this->mode);
        if($this->isGet()){
            $this->detailFetch();
        }else {
            $post = $this->post();
            $refundReason = $post['refund_reason']??'';
            $order = OrderService::instance()->getOneBySn($post['sn']);
            if(empty($order)) $this->error("订单不存在");
            try{
                OrderStatusMachine::instance()->applyAction(new RefundMasterStrategy(), compact("order", 'refundReason'));
            }catch (\Exception $e){
                $this->error($e->getMessage());
            }
            $this->success("后台退款成功");
        }
    }
    /**
     * 后台配货
     * @auth true
     * @return void
     * @throws Exception
     */
    public function pickupMaster() {
        if($this->isGet()){
            $this->detailFetch();
        }else {
            $post = $this->post();
            $order = OrderService::instance()->getOneBySn($post['sn']);
            if(empty($order)) $this->error("订单不存在");
            OrderStatusMachine::instance()->applyAction(new PickupMasterStrategy(), compact("order"));
            $this->success("后台配货成功");
        }
    }

    /**
     * 后台取消
     * @auth true
     * @return void
     */
    public function cancelMaster() {
        $sn = $this->post("sn");
        $order = OrderService::instance()->getOneBySn($sn);
        if(empty($order)) $this->error("订单不存在");
        OrderStatusMachine::instance()->applyAction(new CancelMasterStrategy(), compact("order"));
        $this->success("后台取消订单成功");
    }

    /**
     * 后台核销
     * @auth true
     * @return void
     * @throws Exception
     */
    public function verifyMaster() {
        if($this->isGet()){
            $this->detailFetch();
        }else{
            $post = $this->post();
            $order = OrderService::instance()->getOneBySn($post['sn']);
            if(empty($order)) $this->error("订单不存在");
            OrderStatusMachine::instance()->applyAction(new VerifyMasterStrategy(), compact("order"));
            $this->success("后台核销成功");
        }
    }

    private function detailFetch(){
        $sn = $this->param("sn");
        $order = OrderService::instance()->getOneBySn($sn);
        if(empty($order)) $this->error("订单不存在");
        OrderService::instance()->bindOne($order);
        $this->vo = $order;
        $this->subs = OrderSubService::instance()->getSubsBySns([$order['sn']], "order_sn,goods_sn,goods_name,goods_price,goods_number,goods_cover,goods_unit");
        $this->fetch("detail");
    }

    /**
     * 选择企业(或经销商)订单
     * @auth true
     * @return void
     */
    public function selectCom() {
        $this->title = '选择订单';
        $this->companies = CustomerService::instance()->getListOptsByGids();
        $this->partners = WaterStationService::instance()->getListOptsByGids();
        $this->buyers = array_merge($this->companies, $this->partners);

        $this->getModel()::mQuery()->layTable(function () {
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => 1])->whereIn("from", ['company', 'partner'])->order("sort desc, id desc");
            $query->equal("openid")->like("sn")->dateBetween('create_at,pay_at');
        });
    }
    protected function _selectCom_page_filter(&$list) {
        $this->_index_page_filter($list);
    }

    /**
     * 查看销售单
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function show() {
        $this->mode = "show";
        $id         = input('get.id');
        if ($this->isGet()) {
            $this->assignGetForm($id);
            $this->fetch();
        }
    }

    private function assignGetForm($id) {
        $vo = $this->getModel()->find($id);
        OrderService::instance()->bindOne($vo);
        $this->vo        = $vo;
        $this->subs      = OrderSubService::instance()->getList(['order_sn' => $this->vo['sn']]);
        // dd($vo->toArray());
        // $this->depotList = DepotService::instance()->getDepotListAndWaterStationList();
        // $this->customer  = CustomerService::instance()->getByGid($this->vo['customer_gid']);
    }


    /**
     * 清理订单数据
     * @ auth true
     */
//    public function clean(){
//        $this->_queue('定时清理无效订单数据', "vandles:OrderClean", 0, [], 0, 60);
//    }

    public function getModel(): BaseSoftDeleteModel {
        return OrderService::instance()->getModel();
    }
}