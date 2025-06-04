<?php
/**
 * Created by vandles
 * Date: 2025/4/9
 * Time: 16:46
 */

namespace vandles\lib;

/**
 * 对象合法性验证器
 */
class Validator {


    /**
     * 统一社会信用代码验证
     * 1    登记管理部门代码：机构编制-1, 民政-5, 工商-9，其他-Y
     * 2    机构类别代码：
     *      机构编制（机关-1，事业单位-2，中央编办-3，其他-9）
     *      民政（社会团体-1，民办非企业单位-2，基金会-3，其他-9）
     *      工商（企业-1，个体-2，农民专业合作社-3）
     *      其他（1）
     * 3-8  登记管理机关行政区划码：6位数字，GB/T 2260 编码
     * 9-17 主体标识码(组织机构代码)：9位数字或大字字母，GB 11714 编码
     * 18   校验码：C18 = 31 - mod(∑(i=1-17)Ci * Wi, 31) GB/T 17710 编码
     *
     * @return bool
     */
    public static function taxno($code) {
        if(empty($code)) return false;
        // 统一社会信用代码长度必须为18位
        if (strlen($code) !== 18) return false;

        // 定义权重数组
        $w = [1, 3, 9, 27, 19, 26, 16, 17, 20, 29, 25, 13, 8, 24, 10, 30, 28];
        // 定义校验码字符集
        $checkCodes = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A'=>10, 'B'=>11, 'C'=>12, 'D'=>13, 'E'=>14, 'F'=>15, 'G'=>16, 'H'=>17, 'J'=>18, 'K'=>19, 'L'=>20, 'M'=>21, 'N'=>22, 'P'=>23, 'Q'=>24, 'R'=>25, 'T'=>26, 'U'=>27, 'W'=>28, 'X'=>29, 'Y'=>30];

        // 计算前17位的加权和
        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $char = $code[$i];
            if (!ctype_alnum($char)) return false;

            // 将字母转换为对应的数字
            $value = ctype_digit($char) ? (int)$char : $checkCodes[$char];
            $sum += $value * $w[$i];
        }

        // 计算校验码
        if(($mod = $sum % 31) == 0) $calculatedCheckCode = '0';
        else{
            $checkCodes_ = array_flip($checkCodes);
            $calculatedCheckCode = (string)$checkCodes_[31 - $mod];
        }
        return $calculatedCheckCode === $code[17];
    }

}