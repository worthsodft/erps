<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2025/1/20
 * Time: 17:24
 */

namespace vandles\lib;

class DateUtil {


    public static function dayStart(string $dateStr='') {
        $format = 'Y-m-d 00:00:00';
        return self::dayFormat($format, $dateStr);
    }

    public static function dayEnd(string $dateStr='') {
        $format = 'Y-m-d 23:59:59';
        return self::dayFormat($format, $dateStr);
    }

    public static function dayFormat($format, $dateStr='') {
        if(empty($dateStr)) $dateStr = date('Y-m-d');
        return date($format, strtotime($dateStr));
    }




}