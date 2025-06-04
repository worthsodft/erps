<?php

namespace app\master\controller\stock;

use think\admin\helper\QueryHelper;
use think\admin\model\SystemUser;
use think\facade\Db;
use think\facade\Request;
use vandles\controller\MasterCrudBaseController;
use vandles\lib\Tool;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\SupplierModel;
use vandles\service\DepotService;
use vandles\service\GoodsService;
use vandles\service\GoodsStockService;
use vandles\service\InStockService;
use vandles\service\InStockSubService;
use vandles\service\SupplierService;
use vandles\service\WaterStationService;

/**
 * 商品入库管理
 */
class Instock extends MasterCrudBaseController {

    public $isProduceback = 0;

    public function getModel(): BaseSoftDeleteModel {
        return InStockService::instance()->getModel();
    }

    /**
     * 商品入库管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index() {
        $this->type = input('get.type', '9');
        // 固定查询条件的sessionKey(避免刷新时重置查询条件)
        $this->searchKey = $this->request->method()."_".$this->request->controller()."_".$this->request->action()."_".$this->type."_".session("user.id");

        if ($this->isProduceback) {
            if ($this->isProduceback) $this->title = '商品入库管理 (原料退还)';

            $where       = ['in_stock_type' => "produceback"];
            $this->total = [
                9  => $this->getModel()->where($where)->count(),
                0  => $this->getModel()->where($where)->where('status', 0)->count(),
                1  => $this->getModel()->where($where)->where('status', 1)->count(),
                -1 => $this->getModel()->where($where)->where('status', -1)->count()
            ];

        } else {
            $this->title = '商品入库管理';
            $this->total = [
                9  => $this->getModel()->count(),
                0  => $this->getModel()->where('status', 0)->count(),
                1  => $this->getModel()->where('status', 1)->count(),
                -1 => $this->getModel()->where('status', -1)->count()
            ];
        }


        $this->getModel()::mQuery()->layTable(function () {

            $this->suppliers = SupplierService::instance()->getListOptsByGids();
            $this->depots    = DepotService::instance()->getDepotListAndWaterStationList(true);

        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0]);
            if ($this->type !== '9') $query->where('status', $this->type);
            $query->like('sn')->equal("in_stock_type,supplier_gid,depot_gid")->dateBetween('create_at');

            if ($this->isProduceback) $query->where("in_stock_type", "produceback");

            // 固定查询条件
            // session($this->searchKey, $this->get());

        }, "index");
    }

    protected function _index_page_filter(&$list) {
        InStockService::instance()->bind($list);
    }

    /**
     * 原料退还管理
     * @menu true
     * @auth true
     * @return void
     */
    public function produceback() {
        $this->isProduceback = 1;
        $this->index();
    }

    protected function _produceback_page_filter(&$list) {
        InStockService::instance()->bind($list);
    }

    /**
     * 新建入库单
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
     * 修改入库单
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
     * 保存入库单
     * @auth true
     * @login true
     * @return void
     */
    public function save() {
        if ($this->isPost()) {
            $inStockService = InStockService::instance();
            $post           = $this->post();
            // 只有生产入库（有post['sn'] && in_stock_type == 'produce'），不用选择供应商
            if (!$inStockService->isProduceType($post['sn'] ?? null) && empty($post['supplier_gid'])) $this->error("请选择供应商");
            if ($inStockService->isProduceType($post['sn'] ?? null) && !empty($post['supplier_gid'])) $this->error("生产入库（或原料退还）单无需选择供应商");
            if (empty($post['depot_gid'])) $this->error("请选择入库仓库");

            $subs = $post['subs'] ?? [];
            if (empty($subs)) $this->error("请添加商品明细");

            foreach ($subs as $sub) {
                if (empty($sub['goods_number'])) $this->error("请输入商品数量：" . $sub['goods_name']);
            }

            try {
                // 新建或更新
                $inStockService->saveWithSubs($post);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success("创建成功", "javascript:$.form.reload()");
        }
    }

    /**
     * 新建原料退还
     * @auth true
     */
    public function addback() {
        $this->mode = "addback";
        if ($this->isGet()) {
            $this->getModel()::mForm("form_back");
        } else {
            $inStockService        = InStockService::instance();
            $post                  = $this->post();
            $post['in_stock_type'] = "produceback";
            try {
                // 新建或更新
                $inStockService->saveWithSubs($post);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success("创建成功", "javascript:$.form.reload()");
        }
    }

    /**
     * 修改原料退还
     * @auth true
     */
    public function editback() {
        $this->mode = "editback";
        $id         = input('get.id');
        if ($this->isGet()) {
            $this->assignGetForm($id);
            $this->fetch("form_back");
        } else {
            $inStockService        = InStockService::instance();
            $post                  = $this->post();
            $post['in_stock_type'] = "produceback";
            try {
                // 新建或更新
                $inStockService->saveWithSubs($post);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success("修改成功");
        }
    }

    /**
     * 审核入库单
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
                "sn.require"          => "入库单号不能为空",
                "type.require"        => "审核类型不能为空",
                "pass_remark.default" => "",
            ]);
            $suid = session("user.id");
            if (empty($suid)) $this->error("请登录系统后操作");

            if ($post['type'] == -1 && empty($post['pass_remark'])) $this->error("请填写驳回原因");
            if ($post['type'] == 1 && empty($post['depot_gid'])) $this->error("请选择仓库");

            $data = [
                'status'      => $post['type'],
                'pass_remark' => $post['pass_remark'],
                'pass_by'     => $suid,
                'pass_at'     => now(),
                'depot_gid'   => $post['depot_gid']??null
            ];

            Db::startTrans();
            try {
                // 更新入库单
                InStockService::instance()->updateBySn($post['sn'], $data);
                // 审核通过，更新商品库存
                if ($post['type'] == 1) {
                    GoodsStockService::instance()->updateByInStockSn($post['sn']);
                }

                Db::commit();
            }catch(\Exception $e){
                Db::rollback();
                $this->error($e->getMessage());
            }

            $this->success("入库单" . config("a.in_stock_status." . $post['type']), "javascript:$.form.reload()");
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
        if(empty($id)) $id = input('get.sn');
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
        if(empty($vo)) $vo = InStockService::instance()->getBySn($id);
        if(empty($vo)) $this->error("单据不存在");

        $this->column_map = InStockService::instance()->getColumnMap();

        InStockService::instance()->bindOne($vo);
        $this->vo        = $vo;
        $this->depotList = DepotService::instance()->getDepotListAndWaterStationList();
        $this->subs      = InStockSubService::instance()->getList(['in_stock_sn' => $this->vo['sn']]);
        $this->supplier  = SupplierService::instance()->getByGid($this->vo['supplier_gid']);
    }
    protected function _delete_result($result){
        if($result) $this->success("删除成功", "javascript:$.form.reload();");
    }

    /**
     * 选择入库单(用于创建付款单)
     * @auth true
     * @return void
     */
    public function select4payout() {
        $this->is_multi     = $this->get('is_multi', 0);
        $this->supplier_gid = $this->get("sgid", "");
        if (!$this->supplier_gid) $this->supplier = null;
        else {
            $this->supplier = SupplierService::instance()->getByGid($this->supplier_gid, "id,gid,title");
            if (empty($this->supplier)) $this->error("供应商不存在");
        }
        $this->getModel()::mQuery()->layTable(function () {
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => 1, 'in_stock_type' => 'purchase'])->order("sort desc");
            $query->whereRaw('amount - paid_amount > 0');

            if (!empty($this->supplier_gid)) $query->where('supplier_gid', $this->supplier_gid);

            $query->like('sn')->dateBetween('create_at');
        });
    }

    protected function _select4payout_page_filter(&$list) {
        InStockService::instance()->bind($list);
    }

    /**
     * 得到入库单未支付总金额
     * @return void
     */
    public function getUnPaidAmount() {
        $sns = $this->get("sns");
        if (empty($sns)) $this->error("参数错误");

        $amount = InStockService::instance()->getUnPaidAmountBySns($sns);

        $this->success("未支付总金额", compact("amount"));
    }
}