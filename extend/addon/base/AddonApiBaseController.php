<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 16:02
 * Email: <vandles@qq.com>
 **/

namespace addon\base;

use vandles\controller\ApiBaseController;

/**
 * 接口控制器基类
 */
abstract class AddonApiBaseController extends ApiBaseController {
    use AddonBaseControllerTrait;

}