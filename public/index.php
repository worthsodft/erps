<?php

// +----------------------------------------------------------------------
// | Static Plugin for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2024 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/think-plugs-static
// | github 代码仓库：https://github.com/zoujingli/think-plugs-static
// +----------------------------------------------------------------------

use think\admin\Library;
use think\admin\service\RuntimeService;
use think\admin\support\Route;
use think\admin\support\Url;
use think\App;
use think\Container;
use think\Request;

// 加载基础文件
require __DIR__ . '/../vendor/autoload.php';

// WEB应用初始化
RuntimeService::doWebsiteInit();

// Library::$sapp = Container::getInstance()->make(App::class);
// Library::$sapp->bind('think\Route', Route::class);
// Library::$sapp->bind('think\route\Url', Url::class);
// // 初始化运行配置位置
// $envFile = syspath('runtime/.env');
//
// if (empty($envs = sysvar('think-library-runtime') ?: [])) {
//     // 读取默认配置
//     is_file($envFile) && Library::$sapp->env->load($envFile);
//     // 动态判断赋值
//     $envs['mode'] = Library::$sapp->env->get('RUNTIME_MODE') ?: 'debug';
//     $envs['appmap'] = Library::$sapp->env->get('RUNTIME_APPMAP') ?: [];
//     $envs['domain'] = Library::$sapp->env->get('RUNTIME_DOMAIN') ?: [];
//     sysvar('think-library-runtime', $envs);
// }
// $isDebug = $envs['mode'] !== 'product';
//
// $http = Library::$sapp->debug($isDebug)->http;
// $request = Library::$sapp->make(Request::class);
// Library::$sapp->instance('request', $request);
// ($response = $http->run($request))->send();
// $http->end($response);