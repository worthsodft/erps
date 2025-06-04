<?php

namespace app\master\controller\report;

use think\admin\helper\QueryHelper;
use vandles\controller\MasterBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\MoneyCardLogService;
use vandles\service\MoneyCardService;
use vandles\service\RechargeWayService;
use vandles\service\UserInfoService;
use vandles\service\UserMoneyLogService;

/**
 * 余额消费明细
 * @package app\master\controller\report
 */
class Moneycardlog extends MasterBaseController {
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
        $this->getModel()::mQuery()->layTable(function () {
            $this->title = '余额消费明细';
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
            $query->equal("money_card_gid,log_type");
            $query->like('openid')->dateBetween('create_at');
//
            $db = UserInfoService::instance()->getModel()::mQuery()->field("openid")->like("realname|nickname#username,phone,id");
            if($db->getOptions("where")) $query->whereRaw("openid in {$db->buildSql()}");
        });
    }

    public function _index_page_filter(&$data) {
        $openids = array_unique(array_column($data, 'openid'));
        $userInfos = UserInfoService::instance()->getUserInfoByOpenids($openids, "*", "openid");

        $types = config("a.money_card_loy_types");
        foreach($data as &$vo){
            $userInfo = $userInfos[$vo['openid']] ?? [];
            if(empty($userInfo)) $vo['username'] = "不存在或已删除";
            else{
                $username = "[{$userInfo['id']}] " . ($userInfo['realname']??"") . " / " . ($userInfo['nickname']??"");
                $vo['username'] = $username;
            }

            $vo['log_type_txt'] = $types[$vo['log_type']] ?? "未知";
        }
    }

    public function getModel(): BaseSoftDeleteModel {
        return MoneyCardLogService::instance()->getModel();
    }


}