<?php


namespace app\master\controller;


use think\helper\Str;
use vandles\controller\MasterBaseController;
use vandles\controller\MasterCrudBaseController;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\ConfigModel;
use vandles\service\ConfigService;

/**
 * 系统配置维护
 */
class Config extends MasterCrudBaseController{


    /**
     * 系统配置列表
     * @auth true
     * @menu true
     */
    public function index() {
        $this->title = '系统配置维护';

        $query = $this->getModel()::mQuery();
        // 加载对应数据
        $this->type = $this->request->get('type', 'index');
        if ($this->type === 'index') $query->where(['status' => 1]);
        elseif ($this->type === 'hide') $query->where(['status' => 0]);
        else $this->error("无法加载 {$this->type} 数据列表！");

        // 列表排序并显示
        $query->like('title|name#name');
        $query->equal('status,input_type,xtype')->order('xtype asc,sort desc,id asc')->page();

    }

    /**
     * 系统参数配置
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function config(){
        $this->skey = 'config';
        $this->title = '系统参数配置';
        $this->list = ConfigService::instance()->search(['status'=>1])->whereIn('xtype', ['app','sys'])->order('xtype asc,sort desc,id asc')->select();
        $this->__sysdata($this->skey);
    }

    /**
     * 显示并保存数据
     * @param string $template 模板文件
     * @param string $history 跳转处理
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function __sysdata(string $template, string $history = ''){
        if ($this->request->isGet()) {
            $this->data = sysdata($this->skey);
            $this->fetch($template);
        }
        if ($this->request->isPost()) {
            if (is_string(input('data'))) {
                $data = json_decode(input('data'), true) ?: [];
            } else {
                $data = $this->request->post();
            }

            if (sysdata($this->skey, $data) !== false) {
                $this->success('内容保存成功！', $history);
            } else {
                $this->error('内容保存失败，请稍候再试!');
            }
        }
    }

    public function getModel(): BaseSoftDeleteModel {
        return ConfigService::instance()->getModel();

    }
}