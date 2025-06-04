<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use addon\base\BaseAddon;
use think\admin\helper\QueryHelper;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\MyAddonModel;

use vandles\service\BaseService;

class MyAddonService extends BaseService {
    protected static $instance;


    public static function instance(): MyAddonService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init() {

        dd('init');
    }

    /**
     * @return BaseSoftDeleteModel|MyAddonModel
     */
    public function getModel() {
        return MyAddonModel::mk();
    }
    public function mQuery():QueryHelper {
        return MyAddonModel::mQuery();
    }

    public function bind(&$data) {
        if(!$data) return;
        $addonIds = array_column($data, 'id');
        $myAddons = MyAddonModel::mk()->whereIn('id', $addonIds)->select()->toArray();

        $myAddons = array_column($myAddons, null, 'id');
        foreach ($data as &$one){
            $one['download'] = class_exists($this->getHandlerClassNameByName($one['name']))?1:0;
            if(isset($myAddons[$one['id']])){
                $one['status']  = $myAddons[$one['id']]['status'];
                $one['install'] = $myAddons[$one['id']]['install'];
            }else{
                $one['status']  = 0;
                $one['install'] = 0;
            }
        }
    }

    public function bindOne(&$one) {
        if(!$one) return;
        $myAddon = MyAddonModel::mk()->where("id", $one['id'])->find();

        $one['download'] = class_exists($this->getHandlerClassNameByName($one['name']))?1:0;
        if($myAddon){
            $one['status'] = $myAddon['status'];
            $one['install'] = $myAddon['install'];
        }else{
            $one['status']  = 0;
            $one['install'] = 0;
        }
    }

    // 过滤小程序插件菜单项
    public function menuAuthFilter(array &$list) {
        // 获取插件的小程序菜单项
        $alist = array_filter($list, function ($v){return !empty($v['addon']);});
        $aNames = array_column($alist, 'addon');

        // 获取系统已启用的插件
        $bNames = MyAddonModel::mk()->whereIn('name', $aNames)
            ->where("status = 1 and install = 1")->column('name', 'name');

        foreach ($list as $k=>$v){
            // 如果是插件菜单，并且插件未在系统中启用，则删除该菜单
            if(!empty($v['addon']) && !isset($bNames[$v['addon']])) unset($list[$k]);
        }
    }

    public function softDeleteByName($name) {
        MyAddonModel::mk()->where("name", $name)->update([
            'deleted_time' => time()
        ]);
    }

    public function getByName($name, $field="*") {
        return MyAddonModel::mk()->where("name", $name)->field($field)->find();
    }

    public function getAddonHandler($name):  BaseAddon {
        $class = "app\\addon{$name}\\" . ucfirst($name);
        return app()->make($class);
    }

    public function createIfNotExist(array $data) {
        if(empty($data['id'])) VException::throw("插件ID不能为空");
        if(empty($data['name'])) VException::throw("插件名称不能为空");
        $addon = $this->getByName($data['name']);
        if(!$addon) return $this->create($data);
        return null;
    }

    public function createOrUpdate(array $data) {
        if(empty($data['id'])) VException::throw("插件ID不能为空");
        if(empty($data['name'])) VException::throw("插件名称不能为空");
        $addon = $this->getByName($data['name']);
        if(!$addon) return $this->create($data);
        if($addon['id'] != $data['id']) VException::throw("本地插件ID与云插件ID不一致");
        return $this->updateByName($data['name'], $data);

    }

    public function updateByName($name, array $data) {
        return $this->getModel()->where('name', $name)->update($data);
    }

    private function getRootByName($name) {
        return app()->getRootPath() . "app/addon{$name}/";
    }
    private function getHandlerClassNameByName($name) {
        $class = "app\\addon{$name}\\" . ucfirst($name);
        return $class;
    }


}