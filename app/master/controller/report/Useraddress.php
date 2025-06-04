<?php

namespace app\master\controller\report;

use think\admin\helper\QueryHelper;
use vandles\controller\MasterBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\service\UserAddressService;
use vandles\service\UserInfoService;

/**
 * 用户地址报表
 * @package app\master\controller\report
 */
class Useraddress extends MasterBaseController {
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
            $this->title = '用户地址报表';
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
            $query->equal("id,district,is_default")->like('openid,detail,link_name|link_phone#link_info')->dateBetween('create_at');

            $db = UserInfoService::instance()->getModel()::mQuery()->field("openid")->like("realname|nickname#username,phone");
            if($db->getOptions("where")) $query->whereRaw("openid in {$db->buildSql()}");
        });
    }

    public function _index_page_filter(&$data) {
        $openids = array_unique(array_column($data, 'openid'));
        $userInfos = UserInfoService::instance()->getUserInfoByOpenids($openids, "*", "openid");
        UserAddressService::instance()->bind($data, $userInfos);
    }


    public function getModel(): BaseSoftDeleteModel {
        return UserAddressService::instance()->getModel();
    }


}