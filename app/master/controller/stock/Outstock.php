<?php

namespace app\master\controller\stock;

use think\admin\helper\QueryHelper;
use think\admin\model\SystemUser;
use think\facade\Db;
use vandles\controller\MasterCrudBaseController;
use vandles\lib\Tool;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CustomerModel;
use vandles\service\BuyerMoneyLogService;
use vandles\service\DepotService;
use vandles\service\GoodsService;
use vandles\service\GoodsStockService;
use vandles\service\InStockService;
use vandles\service\OutStockService;
use vandles\service\OutStockSubService;
use vandles\service\CustomerService;
use vandles\service\PayInService;
use vandles\service\UserInfoService;
use vandles\service\WaterStationService;

/**
 * 商品出库管理
 */
class Outstock extends MasterCrudBaseController {

    public $isProduceget = 0;

    public function getModel(): BaseSoftDeleteModel {
        return OutStockService::instance()->getModel();
    }

    /**
     * 商品出库管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index() {
        $this->type = input('get.type', '9');

        if ($this->isProduceget) {
            $this->title = "商品出库管理 (原料申领)";

            $where       = ['out_stock_type' => "produceget"];
            $this->total = [
                9  => $this->getModel()->where($where)->count(),
                0  => $this->getModel()->where($where)->where('status', 0)->count(),
                1  => $this->getModel()->where($where)->where('status', 1)->count(),
                -1 => $this->getModel()->where($where)->where('status', -1)->count()
            ];
        } else {
            $this->title = '商品出库管理';
            $this->total = [
                9  => $this->getModel()->count(),
                0  => $this->getModel()->where('status', 0)->count(),
                1  => $this->getModel()->where('status', 1)->count(),
                -1 => $this->getModel()->where('status', -1)->count()
            ];
        }

        $this->getModel()::mQuery()->layTable(function () {
            [$companyList, $partnerList] = CustomerService::instance()->getCompanyOrPartnerOptsByList();
            $this->buyers = array_merge($companyList, $partnerList);
            $this->depots    = DepotService::instance()->getDepotListAndWaterStationList(true);

        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0]);
            if ($this->type !== '9') $query->where('status', $this->type);
            $query->like('sn')->equal("out_stock_type,buyer_gid,depot_gid")->dateBetween('create_at');

            if ($this->isProduceget) $query->where("out_stock_type", "produceget");

        }, "index");
    }

    protected function _index_page_filter(&$list) {
        OutStockService::instance()->bind($list);
    }

    /**
     * 原料申领管理
     * @menu true
     * @auth true
     * @return void
     */
    public function produceget() {
        $this->isProduceget = 1;
        $this->index();
    }

    protected function _produceget_page_filter(&$list) {
        OutStockService::instance()->bind($list);
    }

    /**
     * 新建出库单
     * @auth true
     * @return void
     */
    public function add() {
        $this->mode = "add";
        if ($this->isGet()) {
            $this->depotList = DepotService::instance()->getDepotListAndWaterStationList();
            $this->getModel()::mForm("form");
        }
    }

    /**
     * 修改出库单
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit() {
        $this->mode = "edit";
        $id         = input('get.id');
        if ($this->isGet()) {
            $this->assignGetForm($id);
            $this->fetch("form");
        }
    }


    /**
     * 保存出库单
     * @auth true
     * @return void
     */
    public function save() {
        if ($this->isPost()) {
            $outStockService = OutStockService::instance();
            $post            = $this->post();
            // 只有原料申领（有post['sn'] && out_stock_type == 'produceget'），不用选择客户
            if (!$outStockService->isProduceType($post['sn'] ?? null) && empty($post['buyer_gid'])) $this->error("请选择客户");
            if ($outStockService->isProduceType($post['sn'] ?? null) && !empty($post['buyer_gid'])) $this->error("原料申领无需选择客户");
            if (empty($post['depot_gid'])) $this->error("请选择出库仓库");

            $subs = $post['subs'] ?? [];
            if (empty($subs)) $this->error("请添加商品明细");

            $items = [];
            foreach ($subs as $sub) {
                if (empty($sub['goods_number'])) $this->error("请输入商品数量：" . $sub['goods_name']);
                // $items[] = [
                //     "goods_sn"     => $sub['goods_sn'],
                //     "goods_number" => $sub['goods_number'],
                //     "goods_name"   => $sub['goods_name']
                // ];
            }

            try {
                // 检查商品库存
                GoodsStockService::instance()->checkStock($subs, $post['depot_gid']);
                // 新建或更新
                $outStockService->saveWithSubs($post);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success("创建成功", "javascript:$.reload()");
        }
    }

    /**
     * 新建原料申领
     * @auth true
     */
    public function addget() {
        $this->mode = "addget";
        if ($this->isGet()) {
            $this->getModel()::mForm("form_get");
        } else {
            $outStockService        = OutStockService::instance();
            $post                   = $this->post();
            $post['out_stock_type'] = "produceget";
            try {
                // 新建或更新
                $outStockService->saveWithSubs($post);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success("创建成功");
        }
    }

    /**
     * 修改原料申领
     * @auth true
     */
    public function editget() {
        $this->mode = "editget";
        $id         = input('get.id');
        if ($this->isGet()) {
            $this->assignGetForm($id);
            $this->fetch("form_get");
        } else {
            $outStockService        = OutStockService::instance();
            $post                   = $this->post();
            $post['out_stock_type'] = "produceget";
            try {
                // 新建或更新
                $outStockService->saveWithSubs($post);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success("修改成功", "javascript:$.form.reload()");
        }
    }

    /**
     * 审核出库单
     * @auth true
     * @return void
     */
    public function pass() {
        $this->mode = "pass";
        $id         = input('get.id');
        if ($this->isGet()) {
            $this->assignGetForm($id);
            $this->fetch("form");
        } else {
            $post = $this->_vali([
                "depot_gid.default"   => "",
                "sn.require"          => "出库单号不能为空",
                "type.require"        => "审核类型不能为空",
                "pass_remark.default" => "",
            ]);
            $suid = session("user.id");
            if (empty($suid)) $this->error("请登录系统后操作");

            if ($post['type'] == -1 && empty($post['pass_remark'])) $this->error("请填写驳回原因");
            if ($post['type'] == 1 && empty($post['depot_gid'])) $this->error("请选择仓库");

            try {
                OutStockService::instance()->pass($post);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }

            $this->success("出库单" . config("a.out_stock_status." . $post['type']), "javascript:$.form.reload()");
        }
    }

    /**
     * 查看
     * @auth true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function show() {
        $this->mode = "show";
        $id         = input('get.id');
        if (empty($id)) $id = input('get.sn');
        if ($this->isGet()) {
            $this->assignGetForm($id);
            $this->fetch("form");
        }
    }

    /**
     * 详情
     * @auth true
     * @return void
     */
    public function detail() {
        $this->mode = "detail";
        $id         = input('get.id');
        if (empty($id)) $id = input('get.sn');
        if ($this->isGet()) {
            $this->assignGetForm($id);
            $this->fetch("detail");
        }
    }

    private function assignGetForm($id) {
        $vo = $this->getModel()->find($id);
        if (empty($vo)) $vo = OutStockService::instance()->getBySn($id);
        if (empty($vo)) $this->error("单据不存在");

        $this->column_map = OutStockService::instance()->getColumnMap();

        OutStockService::instance()->bindOne($vo);
        $this->vo        = $vo;
        $this->depotList = DepotService::instance()->getDepotListAndWaterStationList();
        $this->subs      = OutStockSubService::instance()->search(['out_stock_sn' => $this->vo['sn']])->order("serial_no asc,id desc")->select();

        if($this->vo->from == 'company'){
            $this->buyer = CustomerService::instance()->getByGid($this->vo['buyer_gid']);
        }elseif($this->vo->from == 'partner'){
            $this->buyer = WaterStationService::instance()->getByGid($this->vo['buyer_gid']);
        }elseif($this->vo->from == 'mini'){
            $this->buyer = UserInfoService::instance()->getUserInfoByOpenid($vo['buyer_gid'], 'id,openid gid, nickname title');
        }

    }

    protected function _delete_result($result) {
        if ($result) $this->success("删除成功", "javascript:$.form.reload();");
    }


    /**
     * 选择出库单(用于创建收款单)
     * @auth true
     * @return void
     */
    public function select4payin() {
        $this->is_multi     = $this->get('is_multi', 0);
        $this->buyer_gid = $this->get("cgid", "");
        if (!$this->buyer_gid) $this->customer = null;
        else {
            $this->customer = CustomerService::instance()->getByGid($this->buyer_gid, "id,gid,title");
            $this->from = "company";
            if (empty($this->customer)){
                $this->customer = WaterStationService::instance()->getByGid($this->buyer_gid, "id,gid,title");
                $this->from = "partner";
            }
            if (empty($this->customer)) $this->error("企业客户不存在");
        }
        $this->getModel()::mQuery()->layTable(function () {
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => 1, 'out_stock_type' => 'sale'])->order("sort desc");
            $query->whereRaw('amount - paid_amount > 0');

            if (!empty($this->buyer_gid)) $query->where('buyer_gid', $this->buyer_gid);

            $query->like('sn')->dateBetween('create_at');
        });
    }

    protected function _select4payin_page_filter(&$list) {
        OutStockService::instance()->bind($list);
    }

    /**
     * 得到出库单未支付总金额
     * @return void
     */
    public function getUnPaidAmount() {
        $sns = $this->get("sns");
        if (empty($sns)) $this->error("参数错误");

        $amount = OutStockService::instance()->getUnPaidAmountBySns($sns);

        $this->success("未支付总金额", compact("amount"));
    }

}