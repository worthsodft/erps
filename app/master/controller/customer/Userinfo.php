<?php

namespace app\master\controller\customer;

use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\admin\helper\QueryHelper;
use think\facade\Db;
use vandles\controller\MasterBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\MoneyCardService;
use vandles\service\UserAddressService;
use vandles\service\UserCouponService;
use vandles\service\UserInfoService;
use vandles\service\UserMoneyLogService;
use vandles\service\UserTempService;
use vandles\service\WaterStationService;

/**
 * 用户信息管理
 * @package app\master\controller\customer
 */
class Userinfo extends MasterBaseController {
    /**
     * 用户信息管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index() {
        $this->type = $this->get['type'] ?? 'index';
        $this->getModel()::mQuery()->layTable(function () {
            $this->title = '用户信息管理';
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
            $query->equal("id")->like('phone,email,username|realname|nickname#username,openid')->dateBetween('create_at');
        });
    }

    public function _index_page_filter(&$data) {
    }

    public function _form_filter(&$data) {
        if ($this->isPost()) {
            if (empty($data['phone'])) $data['phone'] = null;
            if (empty($data['username'])) $data['username'] = null;
        }
    }

    public function getModel(): BaseSoftDeleteModel {
        return UserInfoService::instance()->getModel();
    }

    /**
     * 选择用户
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function select() {
        $this->index();
    }

    /**
     * 用户详情
     * @auth true
     */
    public function detail() {
        $this->type     = $this->get('type', 0);
        $this->openid   = $this->get('openid', 0);
        $this->userInfo = UserInfoService::instance()->getOneByOpenid($this->openid);
        if (empty($this->userInfo)) $this->error('用户不存在');
        $this->total = $this->getSubListTotal();
        $this->title = config("a.user_info_tabs." . $this->type);
        switch ($this->type) {
            case "0": // 优惠券列表
                UserCouponService::instance()->mQuery()->where('openid', $this->openid)
                    ->where("status", 1)
                    ->order("id desc")
                    ->page(true, true, false, 10);
                break;
            case "1": // 余额记录
                UserMoneyLogService::instance()->mQuery()->where("openid", $this->openid)
                    ->where("status", 1)
                    ->order("id desc")
                    ->page(true, true, false, 10);
                break;
            case "2": // 收货地址
                UserAddressService::instance()->mQuery()->where("openid", $this->openid)
                    ->where("status", 1)
                    ->order("id desc")
                    ->page(true, true, false, 10);
                break;
            default:
                $this->error("未知的列表类型");
        }
    }

    protected function _detail_page_filter(&$data) {
        switch ($this->type) {
            case "0": // 优惠券列表
                UserCouponService::instance()->bind($data);
                break;
            case "1": // 余额记录
                UserMoneyLogService::instance()->bind($data);
                break;
            case "2": // 收货地址
                break;
            default:
                $this->error("未知的列表类型");
        }
    }

    private function getSubListTotal() {
        return [
            UserCouponService::instance()->getTotalByOpenid($this->openid),
            UserMoneyLogService::instance()->getTotalByOpenid($this->openid),
            UserAddressService::instance()->getTotalByOpenid($this->openid),
        ];
    }

    /**
     * 余额充值
     * @ auth true
     * @return void
     */
    public function money() {
        $openid = $this->param("openid");
        if (session("user.id") != 10000) $this->error("权限不足");
        $userInfo = UserInfoService::instance()->getModel()->where("openid", $openid)->lock(true)->find();
        if (!$userInfo) $this->error("用户不存在");
        if ($this->isGet()) {
            $this->vo = $userInfo;
            $this->fetch();
        } else {
            $post  = $this->post();
            $money = floatval($post['money'] ?? 0);
            if (empty($money)) $this->error("请填写充值金额");
            if ($money < 0) $this->error("充值金额必须大于0");

            $userInfo->money += $money;
            if ($userInfo->money < 0) $this->error("余额不能为负数");

            Db::transaction(function () use ($userInfo, $money) {
                $user = session("user");

                UserInfoService::instance()->incMoney($userInfo->openid, $money, UserMoneyLogService::MONEY_LOG_TYPE_RECHARGE, "", "", "后台手动充值（{$user['id']}）");
                MoneyCardService::instance()->addCard($userInfo->openid, $money);

                $userInfo->save();
            });
            $this->success("充值成功");
        }
    }

    /**
     * 权限管理
     * @auth true
     * @return void
     */
    public function auth() {
        $openid   = $this->param("openid");
        $userInfo = UserInfoService::instance()->getModel()->where("openid", $openid)->lock(true)->find();
        if (empty($userInfo)) $this->error("用户不存在");
        $this->vo     = $userInfo;
        $this->auths  = UserInfoService::instance()->getAuthStrByOpenid($openid);
        $this->openid = $openid;
        if ($this->isGet()) {
            $this->stations = WaterStationService::instance()->getStationOpeningOpts("id,gid,title");

            if ($userInfo->districts) {
                $districts       = explode(",", $userInfo->districts);
                $this->districts = array_flip($districts);
            } else $this->districts = [];
            $this->fetch();
        } else {
            $post = $this->post();
            foreach ($post['auth'] as $k => $v) {
                $this->auths[$k] = empty($v) ? 0 : 1;
            }
            $userInfo->auth = bindec($this->auths);

            // 核销订单权限，需要设置所属水站
            if (isset($post['auth'][0]) && $post['auth'][0] == 1) {
                if (empty($post['station_gid'])) $this->error("请选择所属水站");
                $userInfo->station_gid = $post['station_gid'];
            } else $userInfo->station_gid = null;

            // 配送订单权限，需要设置配送区域
            if (isset($post['auth'][1]) && $post['auth'][1] == 1) {
                if (empty($post['districts'])) $this->error("请选择配送区域");
                $cities    = config("a.cities");
                $districts = [];
                foreach ($post['districts'] as $k => $v) {
                    if ($v == "on") $districts[] = $cities[$k]['name'];
                }
                if (empty($districts)) $this->error("请选择配送区域");
            }


            if (!empty($districts)) {
                $userInfo->districts = implode(",", $districts);
            } else $userInfo->districts = null;
            $userInfo->save();
            $this->success("权限更新成功");
        }
    }

    /**
     * 导入
     * @ auth true
     * @return void
     */
    /*
    public function import() {
        $file = $this->post("xurl");
        if (!$file) $this->error('文件不能为空');
        $file = '.' . str_replace($this->app->request->domain(), '', $file);
        if (!is_file($file)) $this->error('文件不存在');
        $cellName = [
            'A' => 'realname',
            'B' => 'phone',
            'E' => 'money',
            'F' => "card_no"
        ];
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();               // 获取表格
        $highestRow = $sheet->getHighestRow();                 // 取得总行数
        $sheetData = [];
        for ($row = 2; $row <= $highestRow; $row++) {          // $row表示从第几行开始读取
            foreach ($cellName as $cell => $field) {
                $value = $sheet->getCell($cell . $row)->getValue();
                $value = trim($value);
                $sheetData[$row][$field] = $value;
            }
        }
        $sheetData = array_values($sheetData);
        $userTempService = UserTempService::instance();
        $phones = array_column($sheetData, 'phone');
        $userTemps = $userTempService->getUserTempListByPhones($phones,"id,phone,realname")->toArray();
        $userTemps = array_column($userTemps, null, 'phone');
        $total = [0, 0, 0];
        Db::startTrans();
        try{
            foreach ($sheetData as $vo){
                if(empty($vo['phone'])) continue;
                $total[0]++;
                if(isset($userTemps[$vo['phone']])){
                    $res = $userTempService->updateByPhone($vo['phone'], $vo);
                    $res && $total[1]++;
                }else{
                    $vo['openid'] = $vo['phone'];
                    $userTempService->create($vo);
                    $total[2]++;
                }
            }
            Db::commit();
        }catch (Exception $e){
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success("导入成功，获取共：{$total[0]} 条: 修改：{$total[1]} 条，新增：{$total[2]} 条");
    }
    */
}