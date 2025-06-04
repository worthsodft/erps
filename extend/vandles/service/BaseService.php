<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/29 20:09
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\Service;
use think\db\Query;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\UserInfoModel;

abstract class BaseService{
    protected static $instance;

    protected $limit = 10;

    abstract protected static function instance();

    /**
     *
     * @return BaseSoftDeleteModel
     */
    abstract public function getModel();

    public function getById($id, string $field=null) {
        $query = $this->getModel()->where('id', $id);
        if($field) $query->field($field);
        return $query->find();
    }
    public function getByGid($gid, string $field="*") {
        $query = $this->getModel()->field($field)->where('gid', $gid);
        return $query->find();
    }

    /**
     * 得到一个有效的
     * @param $id
     * @param $field
     * @return array|mixed|Query|\think\Model|BaseSoftDeleteModel|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOneById($id, $field=null) {
        $query = $this->search(['status' => 1]);
        if($field) $query->field($field);
        return $query->find($id);
    }
    public function getOneByGid($gid, $field=null) {
        $query = $this->search(['status' => 1]);
        if($field) $query->field($field);
        return $query->where("gid", $gid)->find();
    }

    /**
     * 创建
     * @param $data
     * @return \think\Model|BaseSoftDeleteModel
     */
    public function create($data) {
        return $this->getModel()->create($data);
    }

    /**
     * 批量创建或保存
     * @param $data
     * @return \think\Collection
     * @throws \Exception
     */
    public function saveAll($data) {
        return $this->getModel()->saveAll($data);
    }

    /**
     * 根据id删除
     * @param $id
     * @return BaseSoftDeleteModel
     */
    public function deleteById($id) {
        $model = $this->getModel();
        return $this->getModel()->where($model->pk, $id)->update(['deleted'=>1]);
    }



    /**
     * 根据id更新
     * @param $id
     * @param $data
     * @return BaseSoftDeleteModel
     */
    public function updateById($id, $data) {
        $model = $this->getModel();
        if(isset($data[$model->pk])) unset($data[$model->pk]);
        return $model->where($model->pk, $id)->update($data);
    }

    /**
     * 查找1个
     * @param $id
     * @param null $field
     * @return array|mixed|\think\Model|BaseSoftDeleteModel|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function find($id, $field=null) {
        $model = $this->getModel();
        $query = $model->order($model->_order)->field($field);
        return $query->find($id);
    }

    /**
     * 根据ids得到数据列表
     * @param $ids
     * @param null $field
     * @param null $asKey
     * @return array|\think\Collection|Query[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getListByIds($ids, $field=null, $asKey=null) {
        $model = $this->getModel();
        $query = $model->whereIn($model->pk, $ids);
        if($field) $query->field($field);
        $list = $query->select();

        if(empty($asKey) || empty($list)) return $list;
        else return array_column($list->toArray(), null, $asKey);
    }

    /**
     * 得到列表数据
     * @param array $where
     * @param string $field
     * @param null $limit
     * @param null $asKey
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList($where=[], $field=null, $limit=null, $asKey=null) {
        $model = $this->getModel();
        $query = $model->where($where);
        if($limit) $query->limit($limit);
        if($field) $query->field($field);
        $list = $query->select();

        if(empty($asKey) || empty($list)) return $list;
        else return array_column($list->toArray(), null, $asKey);
    }

    /**
     * @param array $where
     * @return Query|BaseSoftDeleteModel
     */
    public function search($where=[]) {
        $model = $this->getModel();
        $query = $model->where($where);
        return $query;
    }

    /**
     * 用于验证消息的确来自微信服务器
     *
     * $token 与服务器配置中的token一致
     * @param $token
     */
    protected static function signature($token) {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        
        // $token = '与服务器配置中的token一致';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            echo $_GET['echostr'];
//            echo 1;
        }else{
            echo 0;
        }
    }

    /**
     * 得到文件的文件名
     * @param $pic_id_cards
     * @return false|string
     */
    protected function getFileName($url) {
        return substr($url, strrpos($url, '/') + 1);
    }

    /**
     * 得到文件的扩展名
     * @param $url
     * @return false|string
     */
    protected function getFileExtname($url) {
        return substr($url, strrpos($url, '.') + 1);
    }
}