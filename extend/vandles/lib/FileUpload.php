<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/8/9
 * Time: 14:35
 */

namespace vandles\lib;

use think\admin\Storage;

class FileUpload {

    private static $instance;

    public static function instance(): FileUpload {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function save($file) {
        $exts = ["png","jpg","jpeg","gif"];
        $extension = strtolower($file->getOriginalExtension());
        $saveFileName = input('key') ?: Storage::name($file->getPathname(), $extension, '', 'md5_file');

        // 检查文件名称是否合法
        if (strpos($saveFileName, '..') !== false) {
            VException::runtime('文件路径不能出现跳级操作！');
        }
        // 检查文件后缀是否被恶意修改
        if (strtolower(pathinfo(parse_url($saveFileName, PHP_URL_PATH), PATHINFO_EXTENSION)) !== $extension) {
            VException::runtime('文件后缀异常，请重新上传文件！');
        }
        // 屏蔽禁止上传指定后缀的文件
        if (!in_array($extension, str2arr(sysconf('storage.allow_exts|raw')))) {
            VException::runtime('文件类型受限，请在后台配置规则！');
        }
        // 前端用户上传后缀检查处理
        if (!in_array($extension, $exts)) {
            VException::runtime('文件类型受限，请上传允许的文件类型！');
        }
        if (in_array($extension, ['sh', 'asp', 'bat', 'cmd', 'exe', 'php'])) {
            VException::runtime('文件安全保护，禁止上传可执行文件！');
        }
        $bina = file_get_contents($file->getPathname());
        $info = Storage::instance("local")->set($saveFileName, $bina, false, $file->getOriginalName());
        return $info;
    }
}