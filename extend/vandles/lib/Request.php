<?php
/*
 * 
 * Author: vandles
 * Date: 2022/11/15 10:35
 * Email: <vandles@qq.com>
 */


namespace vandles\lib;


use Spatie\Macroable\Macroable;

/**
 * @method uid()
 * @method openid()
 */
class Request extends \think\Request {
    use Macroable;

}