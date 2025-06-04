<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\helper\Str;
use think\Model;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CouponTplModel;
use vandles\model\DepotModel;
use vandles\model\DepotStationModel;
use vandles\model\DepotStationViewModel;
use vandles\model\UserInfoModel;

class DepotService extends BaseService {
    protected static $instance;


    public static function instance(): DepotService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);

        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|DepotModel
     */
    public function getModel() {
        return DepotModel::mk();
    }

    public function getDepotListAndWaterStationList($gidAsKey=false) {
        $field = "gid,title";
        $query1 = $this->search(['status'=>1])->field($field)->order("sort desc, id desc");
        $query2 = WaterStationService::instance()->search(['status'=>1,'is_open'=>1])->field($field)->order("sort desc, id desc");
        $list1 = $query1->select()->each(function ($item) {
            $item['title'] = "[仓库] " . $item['title'];
        });
        $list2 = $query2->select()->each(function ($item) {
            $item['title'] = "[水站] " . $item['title'];
        });
        $list1 = $list1->merge($list2);
        if($gidAsKey) $list1 = array_column($list1->toArray(), 'title', "gid");
        return $list1;
    }

    /**
     * 根据gid得到仓库或水站，编号以“DE_”开头为仓库，否则为水站
     * @param $gid
     * @return array|mixed|Model|BaseSoftDeleteModel|DepotModel|\vandles\model\WaterStationModel|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getDepotOrWaterStationByGid($gid) {
        if(empty($gid)) return null;
        $field = "gid,title";
        if($this->isDepot($gid)){
            $depot = $this->getModel()->where(['gid'=>$gid])->find();
            if(!empty($depot)) $depot->title = "[仓库] " . $depot->title;
            return $depot;
        }else{
            $waterStation = WaterStationService::instance()->getModel()->field($field)->where(['gid'=>$gid])->find();
            if(!empty($waterStation)) $waterStation->title = "[水站] " . $waterStation->title;
            return $waterStation;
        }
    }

    public function getDepotOrWaterStationListByGids(array $gids) {
        $field = "gid,title";
        $query1 = $this->search(['status'=>1])->whereIn('gid', $gids)->field($field)->order("sort desc, id desc");
        $query2 = WaterStationService::instance()->search(['status'=>1,'is_open'=>1])->whereIn('gid', $gids)->field($field)->order("sort desc, id desc");
        $list1 = $query1->select()->each(function ($item) {
            $item['title'] = "[仓库] " . $item['title'];
            $item['type'] = "depot";
        });
        $list2 = $query2->select()->each(function ($item) {
            $item['title'] = "[水站] " . $item['title'];
            $item['type'] = "station";
        });
        $list1 = $list1->merge($list2);
        return $list1;
    }

    /**
     * 得到默认仓库（水站）gid
     * @return string
     */
    public function getDefaultDepotOrStationGid() {
        $default = $this->getDefaultDepotOrStation();
        if(empty($default)) VException::throw("未设置默认仓库（水站）");
        return $default->gid;
    }

    /**
     * 得到默认仓库（水站）
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getDefaultDepotOrStation() {
        $list = DepotStationViewModel::where("is_default", 1)->select();
        if(($count=count($list)) == 0) return null;
        if($count > 1) VException::throw("存在多个默认仓库（水站）");
        $default = $list[0];
        $default->title = $this->isDepot($default->gid) ? "[仓库]" . $default->title : "[水站]" . $default->title;
        return $default;
    }

    /**
     * 设置默认仓库（水站）
     * @param $value
     * @param $is_default
     * @return bool
     */
    public function setDefaultDepotOrStation(string $gid, int $is_default) {
        // 如果为1，检查是否已设置默认仓库（水站）
        $isDepot = $this->isDepot($gid);
        if($is_default == 1){
            $default = DepotStationViewModel::where("is_default", 1)->find();
            if(!empty($default)) VException::throw("已有默认：". ($isDepot?"[仓库]":"[水站]") . $default->title . "，请先去取消");
        }

        $data = ["is_default"=>$is_default];
        if($isDepot){
            $this->getModel()->where("gid", $gid)->update($data);
        }else{
            WaterStationService::instance()->getModel()->where("gid", $gid)->update($data);
        }
        return true;
    }

    private function isDepot(string $gid) {
        return Str::startsWith($gid,'DE_');
    }

}