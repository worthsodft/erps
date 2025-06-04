<?php
/**
 * User: vandles
 * Date: 2025/3/11
 * Time: 17:45
 */

namespace app\addon{{name}};

use addon\base\BaseAddon;
use think\admin\model\SystemMenu;
use think\facade\Db;
use vandles\lib\VException;

class {{Name}} extends BaseAddon {

    public static function instance(): {{Name}} {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    /**
     * 安装
     * @return bool
     */
    public function install() {
        if($this->isInstalled()) VException::throw("插件已安装");

        Db::startTrans();
        try{
            // 1. 初始化后台菜单
            $this->initMenu();

            // 2. 创建表
            $this->importFromSql();

            // 3. 初始货数据
            $this->importFromSql('test.sql');

            $this->lockInstall();
            Db::commit();
        }catch(\Exception $e){
            Db::rollback();
            VException::throw($e->getMessage());
        }

        return true;
    }

    /**
     * 卸载
     * @return bool
     */
    public function uninstall() {
        // 1. 删除菜单
        SystemMenu::mk()->where("addon", $this->getName())->delete();



        $this->unlockInstall();
        return true;
    }
    /**
     * 启用
     * @return bool
     */
    public function enable(){
        return true;
    }

    /**
     * 禁用
     * @return bool
     */
    public function disable(){
        return true;
    }

    /**
     * 初货化后台菜单
     * @return bool
     */
    public function initMenu() {
        Db::startTrans();
        try{
            $this->createAddonRootMenu();
            $this->createAddonMasterMenu("海报管理", "user_poster");
            $this->createAddonMasterMenu("用户二维码", "user_qrcode");
            $this->createAddonMasterMenu("海报模板", "user_poster_tpl");
            Db::commit();
        }catch(\Exception $e){
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }
}