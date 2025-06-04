<?php
/**
 * User: vandles
 * Date: 2025/3/11
 * Time: 16:36
 */

namespace app\addon{{name}}\controller;

use addon\base\AddonBaseController;
use app\addon{{name}}\{{Name}};

class Index extends AddonBaseController {


    public function initialize() {
        $this->addonHandler = {{Name}}::instance();
        $this->name = $this->addonHandler->getName();
    }

    public function index() {

        $this->fetch();
    }

    public function install() {
        // $this->success('install开发中...');
        try{
            $res = $this->addonHandler->install();
        }catch (\Exception $e){
            $this->error($e->getMessage());
        }
        if($res) $this->success('安装成功');
        else $this->error("安装失败");
        // $this->fetch('index/index');
    }
    public function uninstall() {
        // $this->success('uninstall开发中...');
        try{
            $res = $this->addonHandler->uninstall();
        }catch(\Exception $e){
            $this->error($e->getMessage());
        }
        if($res) $this->success('卸载成功');
        else $this->error("卸载失败");
        // $this->fetch('index/index');
    }
    public function menu() {
        try{
            $res = $this->addonHandler->initMenu();
        }catch(\Exception $e){
            $this->error($e->getMessage());
        }
        if($res) $this->success('初始化菜单成功');
        else $this->error("插件菜单已存在");
        // $this->fetch('index/index');
    }
}