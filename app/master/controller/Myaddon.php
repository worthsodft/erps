<?php

namespace app\master\controller;

use think\admin\helper\QueryHelper;
use think\exception\ClassNotFoundException;
use think\exception\HttpResponseException;
use vandles\controller\MasterBaseController;
use vandles\model\BaseSoftDeletedModel;
use vandles\service\AddonCenterService;
use vandles\service\MyAddonService;


/**
 * 我的插件
 */
class Myaddon extends MasterBaseController {

    public function getModel(): BaseSoftDeletedModel {
        return MyAddonService::instance()->getModel();
    }

    /**
     * 插件管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
        MyAddonService::instance()->mQuery()->layTable(function () {
           $this->title = '插件管理';
       }, function (QueryHelper $query) {

            // 从插件中心获取列表
            $page = $this->get('page', 1);
            $limit = $this->get('limit', 20);
            $pageData = AddonCenterService::instance()->getAddon($page, $limit);

            $this->_index_page_filter($pageData['data']);
            $result = ['msg' => '', 'code' => 0, 'count' => $pageData['total'], 'data' => $pageData['data']];
            throw new HttpResponseException(json($result));
       });
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_page_filter(&$data){
        MyAddonService::instance()->bind($data);
    }

    // /**
    //  * 添加
    //  * @auth true
    //  * @throws \think\db\exception\DataNotFoundException
    //  * @throws \think\db\exception\DbException
    //  * @throws \think\db\exception\ModelNotFoundException
    //  */
    // public function add(){
    //     $this->title = '添加';
    //     $this->getModel()::mForm('form');
    // }
    //
    // /**
    //  * 编辑
    //  * @auth true
    //  * @throws \think\db\exception\DataNotFoundException
    //  * @throws \think\db\exception\DbException
    //  * @throws \think\db\exception\ModelNotFoundException
    //  */
    // public function edit(){
    //     $this->title = '编辑';
    //     $this->getModel()::mForm('form');
    // }

    // /**
    //  * 表单数据处理
    //  * @param array $data
    //  * @throws \think\db\exception\DataNotFoundException
    //  * @throws \think\db\exception\DbException
    //  * @throws \think\db\exception\ModelNotFoundException
    //  */
    // protected function _form_filter(&$data){
    //     $addon = MyAddonService::instance()->getByName($data['name']);
    //     if(empty($data['id']) && !empty($addon)) $this->error("插件名称不能重复");
    //     elseif(!empty($data['id']) && !empty($addon) && $data['id'] != $addon->id) $this->error("插件名称不能重复");
    // }

    /**
     * 下载插件包并解压源文件
     * @auth true
     * @return void
     */
    public function download() {
        $id = $this->post('id');
        $addon = AddonCenterService::instance()->getAddonById($id);
        if(!$addon) $this->error("插件不存在");

        // 1. 远程获取压缩包内容
        $name = $addon['name'];
        $path = app()->getRootPath()  . "app/addon" . $name . '/';
        if(!file_exists($path)) mkdir($path, 0755, true);
        $binContent = AddonCenterService::instance()->getZipContentById($id);
        if(empty($binContent)) $this->error("插件包不存在");

        $binContent = base64_decode($binContent);
        // 2. 将压缩包内容转存入zip文件
        $fullname = $path . $name . '.zip';
        file_put_contents($fullname, $binContent);
        if(!is_file($fullname)) $this->error("插件包下载失败");

        // 3. 解压zip文件
        $zip = new \ZipArchive();
        if($zip->open($fullname) !== true) $this->error("插件包打开失败");
        if (!is_writable($path)) $this->error("目标目录不可写，请检查权限");
        $zip->extractTo($path);
        $zip->close();

        // 4. 删除zip文件
        unlink($fullname);

        // 5. 移动静态文件
        $toPath = app()->getRootPath() . "public/static/addon/";
        if(!file_exists($toPath)) mkdir($toPath, 0755, true);
        $path = $path . 'static/';
        if(is_dir($path)){
            $files = scandir($path);
            foreach($files as $file){
                if($file == '.' || $file == '..') continue;
                $filePath = $path . $file;
                if(is_dir($filePath)){
                    rename($filePath, $toPath . $file);
                }else{
                    copy($filePath, $toPath . $file);
                }
            }
        }
        rmdir($path);

        $this->success("插件包下载成功");
    }

    /**
     * 修改状态
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function state(){
        $this->getModel()::mSave($this->_vali([
            'status.in:0,1'  => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 安装插件
     * @auth true
     * @return void
     */
    public function install() {
        $id = $this->post('id');
        $addon = AddonCenterService::instance()->getAddonById($id);
        if(empty($addon)) $this->error("插件不存在");
        MyAddonService::instance()->bindOne($addon);
        if($addon['install']) $this->error("插件已安装");

        try{
            $handler = MyAddonService::instance()->getAddonHandler($addon['name']);
            $handler->install($addon['id']);
        }catch (ClassNotFoundException $e){
            $this->error("插件未下载，请先下载插件");
        }catch (\Exception $e){
            $this->error($e->getMessage());
        }
        $this->success("安装成功");
    }

    /**
     * 卸载插件
     * @auth true
     * @return void
     */
    public function uninstall() {
        $id = $this->post('id');
        $addon = AddonCenterService::instance()->getAddonById($id);
        if(empty($addon)) $this->error("插件不存在");
        MyAddonService::instance()->bindOne($addon);
        if(!$addon['install']) $this->error("插件未安装");

        try{
            MyAddonService::instance()->getAddonHandler($addon['name'])->uninstall();
        }catch(\Exception $e){
            $this->error($e->getMessage());
        }
        $this->success("卸载成功");
    }

}