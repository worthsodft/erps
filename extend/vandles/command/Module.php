<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2025/3/14
 * Time: 14:06
 */

namespace vandles\command;

use addon\base\BaseAddon;
use think\admin\model\SystemMenu;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;
use vandles\lib\code\CodeBuilder;
use vandles\lib\VException;

/**
 * 模块
 */
class Module extends Command {
    protected function configure() {
        $this->setName('vandles:module');
        $this->setDescription('模块管理，参数：插件名称');
        $this->addArgument('action', Argument::REQUIRED, "操作类型");
        $this->addOption('prefix', 'p', Option::VALUE_REQUIRED, "控制器前缀");
        $this->addOption('tableName', 'e', Option::VALUE_REQUIRED, "表名称");
        $this->addOption('title', 't', Option::VALUE_REQUIRED, "模块标题");
    }

    protected function execute(Input $input, Output $output) {
        $action     = trim($input->getArgument('action'));
        $operations = [
            'createAddon'       => '创建插件',
            'createAddonMaster' => '创建插件后台模块',
            'createAddonMenu'   => '创建插件后台菜单',
            'createMaster'      => '创建普通后台模块'
        ];
        if (!isset($operations[$action])) exit("暂不支持除了 " . implode(', ', array_keys($operations)) . " 以外的操作");
        $prefix    = strtolower(trim($input->getOption('prefix')));
        $tableName = strtolower(trim($input->getOption('tableName')));
        $title     = strtolower(trim($input->getOption('title')));

        echo "开始{$action}操作: " . PHP_EOL;
        try {
            if ($action == 'createAddon') $this->doActionCreateAddon($prefix, $title);
            else if ($action == 'createAddonMaster') $this->doActionCreateAddonMaster($prefix, $tableName, $title);
            else if ($action == 'createAddonMenu') $this->doActionCreateAddonMenu($prefix);
            else if ($action == 'createMaster') $this->doActionCreateMaster($prefix, $tableName, $title);
            else exit("暂不支持除了 " . implode(', ', array_keys($operations)) . " 以外的操作");
        } catch (\Exception $e) {
            exit("操作失败: " . $e->getMessage());
        }
        exit("{$action}操作成功: ");
    }

    private function doActionCreateAddonMenu($prefix) {
        if (!$prefix) exit("prefix参数错误");
        echo "插件名称: {$prefix}" . PHP_EOL;

        $res = $this->getAddonInstance($prefix)->initMenu();
        if ($res) exit("初始化菜单成功");
        else exit("插件菜单已存在");
    }

    /**
     * @param $prefix
     * @return BaseAddon
     * @throws \Exception
     */
    private function getAddonInstance($prefix) {
        $className = "app\\addon{$prefix}\\" . ucfirst($prefix);
        if (!class_exists($className)) VException::throw("插件类不存在");
        return $className::instance();
    }

    /**
     * 创建插件及前台模块
     * @param $prefix
     * @return void
     */
    private function doActionCreateAddon($prefix, $title) {
        if (!$prefix) exit("prefix参数错误");
        if (!$title) exit("title参数错误");
        echo "插件名称: {$prefix}" . PHP_EOL;

        $codeBuilder = CodeBuilder::instance()->setIsAddon(true)->setIsForce(false)->setControlPrefix($prefix);

        $files = $codeBuilder->buildAddon($title);
        foreach ($files as $k => $file) {
            echo("addon {$k} ：" . $file . PHP_EOL);
        }

        // 创建插件后台根菜单
        $res = $this->getAddonInstance($prefix)->createAddonRootMenu();
        echo $res . PHP_EOL;
        exit("插件创建完成：{$prefix}");
    }

    /**
     * 创建插件后台模块
     * @param $prefix
     * @param $tableName
     * @param $title
     * @return void
     */
    private function doActionCreateAddonMaster($prefix, $tableName, $title) {
        if (!$prefix) exit("prefix参数错误");
        if (!$tableName) exit("tableName参数错误");
        if (!$title) exit("title参数错误");
        echo "后台模块前缀: {$prefix}" . PHP_EOL;
        echo "表名称: {$tableName}" . PHP_EOL;
        echo "后台模块标题: {$title}" . PHP_EOL;

        // 检查插件类是否存在，不存在，此方法会抛出异常，保证程序不再往下执行
        $this->getAddonInstance($prefix);

        $codeBuilder = CodeBuilder::instance()->setIsAddonMaster(true)->setIsForce(false)->setControlPrefix($prefix);

        try {

            // 生成model
            $file = $codeBuilder->buildModel($tableName);
            echo("model ：" . $file . PHP_EOL);

            // 生成service
            $file = $codeBuilder->buildService($tableName);
            echo("service ：" . $file . PHP_EOL);

            // 生成controller
            $file = $codeBuilder->buildController($tableName, $title);
            echo("controller ：" . $file . PHP_EOL);

            // 生成view
            $files = $codeBuilder->buildViews($tableName);
            foreach ($files as $k => $file) {
                echo("view {$k} ：" . $file . PHP_EOL);
            }

            // 创建插件后台模块菜单
            $res = $this->getAddonInstance($prefix)->createAddonMasterMenu($title, $tableName);
            echo $res . PHP_EOL;

            exit("后台模块创建完成：{$prefix}");
        } catch (\Exception $e) {
            exit("操作失败: " . $e->getMessage());
        }
    }

    /**
     * 创建普通后台模块
     * @param string $prefix
     * @return void
     */
    private function doActionCreateMaster(string $prefix, string $tableName, string $title) {
        // if (!$prefix) exit("prefix参数错误");
        if (!$tableName) exit("tableName参数错误");
        if (!$title) exit("title参数错误");
        echo "后台模块前缀: {$prefix}" . PHP_EOL;
        echo "表名称: {$tableName}" . PHP_EOL;
        echo "后台模块标题: {$title}" . PHP_EOL;

        $codeBuilder = CodeBuilder::instance()->setIsForce(false);
        if($prefix) $codeBuilder->setControlPrefix($prefix);

        try {

            // 生成model
            $file = $codeBuilder->buildModel($tableName);
            echo("model ：" . $file . PHP_EOL);

            // 生成service
            $file = $codeBuilder->buildService($tableName);
            echo("service ：" . $file . PHP_EOL);

            // 生成controller
            $file = $codeBuilder->buildController($tableName, $title);
            echo("controller ：" . $file . PHP_EOL);

            // 生成view
            $files = $codeBuilder->buildViews($tableName);
            foreach ($files as $k => $file) {
                echo("view {$k} ：" . $file . PHP_EOL);
            }

            // 创建后台模块菜单
            // $res = $this->createMasterMenu($title, $tableName);
            // echo $res . PHP_EOL;

            exit("后台模块创建完成：{$prefix}");
        } catch (\Exception $e) {
            exit("操作失败: " . $e->getMessage());
        }
    }
}