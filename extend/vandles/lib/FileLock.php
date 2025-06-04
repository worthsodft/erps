<?php


namespace vandles\lib;


/**
 * Class FileLock
 * @package vandles
 *
 * $lock = FileLock::instance('lockKey');
 * if(!$lock->lock()){
 *  $this->error("系统繁忙，请稍后访问！");
 * }
 * d('执行正常的逻辑');
 * sleep(5);
 * $lock->unlock();
 *
 */

class FileLock{

    //文件锁存放路径
    private $path = '';
    //文件句柄
    private $fp = null;
    //锁文件
    private $lockFile = '';

    /**
     * 构造函数
     * @param string $path 锁的存放目录
     * @param string $key 锁 KEY
     */
    public function __construct($key, $path = ''){
        if (empty($path)){
            $dir = app()->getRuntimePath() . '.lock/';
            $this->path = $dir;
        }
        else $this->path = $path . '.lock/';
        !is_dir($this->path) && mkdir($this->path);
        $this->lockFile = $this->path . md5($key) . '.lock';
    }

    public static function instance($key, $path = '') {
        return new self($key, $path);
    }

    /**
     * 加锁
     *
     */
    public function lock($isWait=false){
        $this->fp = fopen($this->lockFile, 'w+');
        if ($this->fp === false) return false;

        // LOCK_EX 获取独占锁, LOCK_NB 非阻塞模式（只要当前文件有锁存在，那么直接返回）
        if($isWait) $operation = LOCK_EX;
        else $operation = LOCK_EX | LOCK_NB;
        return flock($this->fp, $operation);
    }

    /**
     * 解锁
     */
    public function unlock(){
        if ($this->fp !== false) {
            @flock($this->fp, LOCK_UN);
            clearstatcache();
        }
        @fclose($this->fp);
        @unlink($this->lockFile);
    }
}