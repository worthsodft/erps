<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use think\Service;

class Excel extends Service {

    /**
     * @param array $var
     * @param bool $new
     * @return Excel
     */
    public static function instance(array $var = [], bool $new = false) {
        return app()->make(Excel::class, $var, $new);
    }

    /**
     *
     * 从excel文件得到数据
     * @param $xlsFile
     * @param array $titles
     * @return array|null
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * $file = "/www/domain/public/upload/.../74bc1e15ed1f934426a10.xlsx";
     * $titles = ['no','realname','phone'];
     *
     */
    public function getDataFromExcel($xlsFile, $titles=[]) {
        if (!is_file($xlsFile)) return null;
        $sheet = IOFactory::load($xlsFile);
        $data = $sheet->getSheet(0)->toArray();
        if (!$data) return null;
        if(!is_numeric($data[0][0])) unset($data[0]);
        if(empty($titles)) return $data;
        $list = [];
        foreach ($data as $vo){
            $item = [];
            foreach($vo as $k => $cell){
                if(isset($titles[$k]))
                    $item[$titles[$k]] = trim($cell);
            }
            $list[] = $item;
        }
        return $list;
    }
}