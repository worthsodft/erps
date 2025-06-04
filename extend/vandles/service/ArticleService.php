<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\helper\QueryHelper;
use vandles\model\ArticleModel;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CouponTplModel;

class ArticleService extends BaseService {
    protected static $instance;


    public static function instance(): ArticleService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $data = [
            [
                "gid"           => uuid(),
                "title"         => "国民品牌 品质上选 欢迎光临凯得美",
            ],[
                "gid"           => uuid(),
                "title"         => "凯得美 送水到家上线了！！！",
            ]
        ];

        $res  = $this->getModel()->saveAll($data);
        $list = $this->getModel()->select();
        dd($list->toArray());
    }

    /**
     * @return BaseSoftDeleteModel|ArticleModel
     */
    public function getModel() {
        return ArticleModel::mk();
    }
    public function mQuery():QueryHelper {
        return ArticleModel::mQuery();
    }

    public function bind(array &$list) {
        $articleTypes = config("a.article_types");
        foreach ($list as &$item) {
            $this->bindOne($item, $articleTypes);
        }
    }

    public function bindOne(&$one, array $articleTypes) {
        $one['article_type_txt'] = $articleTypes[$one['article_type']] ?? "未知类型";
    }

    public function getSwiperList() {
        return $this->getListByArticleType("index_swiper", "gid,cover");
    }
    public function getNoticeList() {
        return $this->getListByArticleType("notice", "gid,title");
    }
    public function getAboutContent() {
        $one = $this->getLastOneByArticleType("about", "gid,desc");
        if(empty($one['desc'])) return "";
        return $one['desc'];
    }

    public function getListByArticleType(string $articleType, $field="*") {
        return $this->getModel()->where("article_type", $articleType)
            ->field($field)
            ->where("status", 1)
            ->order("sort desc,id desc")
            ->select();
    }

    public function getLastOneByArticleType(string $articleType, $field="*") {
        return $this->getModel()->where("article_type", $articleType)
            ->field($field)
            ->where("status", 1)
            ->order("sort desc,id desc")
            ->find();
    }


}