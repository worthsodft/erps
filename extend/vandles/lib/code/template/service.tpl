<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace {{Namespace}};

use think\admin\helper\QueryHelper;
use vandles\model\BaseSoftDeleteModel;
use {{UseModel}}\{{TableName}}Model;

use vandles\service\BaseService;

class {{ClassName}} extends BaseService {
    protected static $instance;


    public static function instance(): {{ClassName}} {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init() {

        dd('init');
    }

    /**
     * @return BaseSoftDeleteModel|{{TableName}}Model
     */
    public function getModel() {
        return {{TableName}}Model::mk();
    }
    public function mQuery():QueryHelper {
        return {{TableName}}Model::mQuery();
    }

    public function bind(&$data) {
        {{BindDataOpts}}
        foreach ($data as &$v){
            {{BindDataTxt}}
        }
    }

    public function bindOne(&$v) {
        {{BindDataOpts}}
        {{BindDataTxt}}
    }


}