<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2025/3/13
 * Time: 14:02
 */

namespace addon\base;


use Symfony\Component\Yaml\Yaml;
use think\admin\model\SystemMenu;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\helper\Str;
use think\Model;
use vandles\lib\VException;
use vandles\service\MyAddonService;

/**
 * 插件主类基类
 */
abstract class BaseAddon {

    protected static $instance;

    abstract public function install(int $addonId);

    abstract public function uninstall();

    abstract public function initMenu();

    public function getAddonRoot() {
        $reflection = new \ReflectionClass($this);
        $filePath   = $reflection->getFileName();
        $addonPath  = dirname($filePath) . '/';
        return $addonPath;
    }
    public function getName() {
        return $this->getInfo('name');
    }
    public function getTitle() {
        return $this->getInfo('title');
    }

    public function getSkey() {
        return $this->getName() . '_' . 'index';
    }

    public function getConfig($name = null) {
        $config = sysdata($this->getSkey());
        if ($name == null) return $config;
        return $config[$name] ?? null;
    }

    public function getInfo($k = null) {
        $file        = $this->getAddonRoot() . '/info.yml';
        $yamlContent = file_get_contents($file);
        $info        = Yaml::parse($yamlContent);
        if ($k) return $info[$k] ?? null;
        return $info;
    }

    public function importFromSql($sqlFile = 'install.sql') {
        $sqlFile = $this->getAddonRoot() . $sqlFile;
        if (!is_file($sqlFile)) return false;

        $lines   = file($sqlFile);
        if (empty($lines)) return false;

        $tmpLine = '';
        Db::startTrans();
        try {
            foreach ($lines as $line) {
                $line = trim($line);
                $line = strtolower($line);
                if(empty($line)) continue;
                if (Str::startsWith($line, ['--', '/*', '#', '//'])) continue;
                $tmpLine .= $line . " ";


                if (Str::endsWith($line, ';')) {
                    if (Str::contains($tmpLine, 'insert into')) {
                        $tmpLine = str_replace("insert into", "insert ignore into", $tmpLine);
                    }
                    // if(Str::endsWith($sqlFile, 'test.sql')) {
                    //     exit($tmpLine);
                    // }
                    Db::execute($tmpLine);
                    $tmpLine = '';
                }
            }
            Db::commit();
        } catch (\PDOException $e) {
            Db::rollback();
            throw $e;
        }
        Db::commit();
        return true;
    }

    public function isInstalled() {
        $addon = MyAddonService::instance()->getByName($this->getName());
        if (!$addon) VException::throw("插件不存在");
        MyAddonService::instance()->bindOne($addon);
        return $addon->install == 1;
    }



    /**
     * 得到项目根菜单
     * @return array|mixed|SystemMenu|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getRootMenu() {
        return SystemMenu::mk()->where("pid = 0 and title = '控制台'")->order("sort desc, id desc")->field("id,pid,title,sort")->find();
    }

    /**
     * 创建插件后台根菜单
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function createAddonRootMenu() {
        $rootMenu = $this->getRootMenu();
        $data     = [
            'pid'   => $rootMenu['id'],
            'title' => $this->getTitle(),
            'node'  => '',
            'url'   => '#',
            'addon' => $this->getName(),
        ];
        if (SystemMenu::where($data)->count() == 0) {
            $res = SystemMenu::create($data);
            if ($res) $res = "后台菜单已创建：" . $data['title'] . PHP_EOL;
            else $res = "后台菜单未创建：" . $data['title'] . PHP_EOL;
        } else $res = "后台菜单已存在：" . $data['title'] . PHP_EOL;
        return $res;
    }

    public function createAddonMasterMenu($title, $tableName, $sort=0) {
        $name     = $this->getName();
        $tableName = str_replace('_', '', $tableName);
        $rootMenu = $this->getRootMenu();
        $data     = [
            'pid'   => $rootMenu['id'],
            'node'  => '',
            'url'   => '#',
            'addon' => $name,
        ];
        $myRoot   = SystemMenu::where($data)->select();
        if (($count = count($myRoot)) > 1) return "插件根目录存在多个，请删除多余目录，再操作";
        elseif ($count == 0) return "插件根目录不存在，请先创建插件根目录";
        $myRoot = $myRoot[0];
        $data   = [
            'pid'   => $myRoot['id'],
            'title' => $title,
            'node'  => "addon{$name}/master.{$tableName}/index",
            "url"   => "addon{$name}/master.{$tableName}/index",
            'addon' => $name,

        ];
        if (SystemMenu::where($data)->count() == 0) {
            $data['sort'] = $sort;
            $res = SystemMenu::create($data);
            if ($res) $res = "后台模块菜单已创建：" . $title . PHP_EOL;
            else $res = "后台模块菜单未创建：" . $title . PHP_EOL;
        } else $res = "后台模块菜单已存在：" . $title . PHP_EOL;
        return $res;
    }

    /**
     * 得到插件的静态资源路径
     * @return string
     */
    public function getStaticPath() {
        return $this->staticPath = app()->getRootPath() . 'public/static/addon/' . $this->getName() . '/';
    }

    /**
     * 启用
     * @return bool
     */
    public function enable() {
        $addon = MyAddonService::instance()->getByName($this->getName());
        if (!$addon) VException::throw("插件不存在");
        if ($addon->status == 1) VException::throw("插件已启用");
        $res = $addon->save(['status' => 1]);
        if (!$res) VException::throw("数据未改变");
        return true;
    }


    /**
     * 禁用
     * @return bool
     */
    public function disable() {
        $addon = MyAddonService::instance()->getByName($this->getName());
        if (!$addon) VException::throw("插件不存在");
        if ($addon->status == 0) VException::throw("插件已禁用");
        $res = $addon->save(['status' => 0]);
        if (!$res) VException::throw("数据未改变");
        return true;
    }

    /**
     * 是否已启用
     * @return bool
     * @throws \Exception
     */
    public function isEnable() {
        $addon = MyAddonService::instance()->getByName($this->getName());
        if (!$addon) VException::throw("插件不存在");
        return $addon->status == 1;
    }
    /**
     * 是否已禁用
     * @return bool
     * @throws \Exception
     */
    public function isDisable() {
        $addon = MyAddonService::instance()->getByName($this->getName());
        if (!$addon) VException::throw("插件不存在");
        return $addon->status == 0;
    }

    public function setAddon(?array $addon) {
        $this->addon = $addon;
    }

    protected function saveMyAddon($addonId) {
        MyAddonService::instance()->createOrUpdate([
            'id'      => $addonId,
            'name'    => $this->getName(),
            'title'   => $this->getTitle(),
            'status'  => 1,
            'install' => 1
        ]);
    }

}