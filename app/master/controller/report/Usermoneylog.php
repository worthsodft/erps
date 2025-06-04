<?php

namespace app\master\controller\report;

use think\admin\helper\QueryHelper;
use vandles\controller\MasterBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\RechargeWayService;
use vandles\service\UserInfoService;
use vandles\service\UserMoneyLogService;

/**
 * 余额变动记录
 * @package app\master\controller\report
 */
class Usermoneylog extends MasterBaseController {
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
        $this->rechargeWayOpts = RechargeWayService::instance()->getOpts();
        $this->getModel()::mQuery()->layTable(function () {
            $this->title = '余额变动记录';
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
            $query->whereRaw("delta != 0");
            $query->equal("id,log_type,recharge_way_gid")->like('openid')->dateBetween('create_at');

            $db = UserInfoService::instance()->getModel()::mQuery()->field("openid")->like("realname|nickname#username,phone");
            if($db->getOptions("where")) $query->whereRaw("openid in {$db->buildSql()}");
        });
    }

    public function _index_page_filter(&$data) {
        $openids = array_unique(array_column($data, 'openid'));
        $userInfos = UserInfoService::instance()->getUserInfoByOpenids($openids, "*", "openid");

        UserMoneyLogService::instance()->bind($data, $userInfos, $this->rechargeWayOpts);
    }

    public function getModel(): BaseSoftDeleteModel {
        return UserMoneyLogService::instance()->getModel();
    }


}