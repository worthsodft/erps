<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 16:02
 * Email: <vandles@qq.com>
 **/

namespace vandles\model;

use think\admin\Model;
use think\model\concern\SoftDelete;

class BaseSoftDeletedModel extends Model{
    use SoftDelete;
    protected $defaultSoftDelete = 0;
    protected $deleteTime = 'deleted_time';
    public $pk = 'id';

}