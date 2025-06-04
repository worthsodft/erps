<?php

namespace app\master\controller;

use think\admin\helper\QueryHelper;
use vandles\controller\MasterBaseController;
use vandles\controller\MasterCrudBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CouponTplModel;
use vandles\service\ArticleService;
use vandles\service\CouponTplService;
use vandles\service\RechargeWayService;
use vandles\service\WaterStationService;

/**
 * 文章管理
 */
class Article extends MasterCrudBaseController {
//    private $articleType;
    public function initialize() {
        $this->articleType = input('article_type', "");
    }

    public function getModel(): BaseSoftDeleteModel {
        return ArticleService::instance()->getModel();
    }

    /**
     * 列表
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
        $this->type = input('get.type', 'index');
        $this->getModel()::mQuery()->layTable(function () {
            $this->title = config("a.article_types.{$this->articleType}")?:"文章管理";
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
            if($this->articleType) $query->where("article_type", $this->articleType);
            $query->like('title')->dateBetween('create_at');
        });
    }
    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_page_filter(&$data){
        ArticleService::instance()->bind($data);
    }

    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function _form_filter(&$data){
        if($this->isPost()){
            $data['article_type'] = $this->articleType?:"notice";
            if(empty($data['id'])) $data['gid'] = uuid();
            if(empty($data['title'])) $data['title'] = "无标题";

            if($data['article_type'] == 'about'){
                if(!empty($data['desc'])){
                    $data['desc'] = str_replace('<div><img', '<div style="font-size: 0;"><img style="display:block;width:100%;"', $data['desc']);
                    $data['desc'] = str_replace('<p><img', '<p style="font-size: 0;"><img style="display:block;width:100%;"', $data['desc']);
                }
            }
        }
    }

}