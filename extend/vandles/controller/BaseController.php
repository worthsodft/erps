<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 16:02
 * Email: <vandles@qq.com>
 **/

namespace vandles\controller;


use think\admin\Controller;

abstract class BaseController extends Controller {
    public function only(...$args) {
        return $this->request->only(...$args);
    }
    public function param(...$args) {
        return $this->request->param(...$args);
    }
    public function get(...$args) {
        return $this->request->get(...$args);
    }
    public function post(...$args) {
        return $this->request->post(...$args);
    }
    public function isGet() {
        return $this->request->isGet();
    }
    public function isPost() {
        return $this->request->isPost();
    }
    public function header(...$arg) {
        return $this->request->header(...$arg);
    }
}