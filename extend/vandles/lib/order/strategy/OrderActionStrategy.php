<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/12/31
 * Time: 15:09
 */

namespace vandles\lib\order\strategy;

interface OrderActionStrategy {

    public function execute($data);
}