<?php
/*
 * 
 * Author: vandles
 * Date: 2022/4/11 15:45
 * Email: <vandles@qq.com>
 */

use Ramsey\Uuid\Uuid;
use think\facade\Log;
use vandles\lib\JwtUtil;

function d($arg='==='){
    dump($arg);
}
function dd($arg='==='){
    halt($arg);
}
function vd($arg='==='){
    if(isDev()) dump($arg);
}
function vdd($arg='==='){
    if(isDev()) halt($arg);
}
function sdd($arg='==='){
    if(session("user.id") == 10000){
        dd($arg);
    }
}
function sd($arg='==='){
    if(session("user.id") == 10000){
        d($arg);
    }
}
function isAdmin(){
    return session("user.id") == 10000;
}
function isDev($openid=null){
    if(empty($openid)) $openid = openid();
    return session("user.id") == 10000 || $openid == "obzAZ7c85NvxGhEXyOEXnP6uY0JM" || $openid == 'obzAZ7eSb81qMLsW-4Lor6oPlkU4';
}
function openid(){
    $token = request()->header('token');
    if(empty($token)) return null;
    try{
        $userInfo = JwtUtil::instance()->decode($token);
    }catch (\Exception $e){
        return null;
    }
    $userInfo = json_encode($userInfo);
    $userInfo = json_decode($userInfo, true);
    $userInfo['iat_txt'] = date("Y-m-d H:i:s", $userInfo['iat']);
    $userInfo['exp_txt'] = date("Y-m-d H:i:s", $userInfo['exp']);
    $userInfo['nbf_txt'] = date("Y-m-d H:i:s", $userInfo['nbf']);
    return $userInfo['openid']??null;

}

function alert(...$args){
    Log::alert(...$args);
}
function error(...$args){
    Log::error(...$args);
}

function domain($port = false) {
    return request()->domain($port);
}

function now($format = 'Y-m-d H:i:s', $time = null) {
    empty($format) && $format = 'Y-m-d H:i:s';
    empty($time) && $time = time();
    return date($format, $time);
}

function today($format = 'Y-m-d', $time = null) {
    empty($format) && $format = 'Y-m-d';
    empty($time) && $time = time();
    return date($format, $time);
}
/**
 * 检查字符串中是否包含某些字符串
 * @param string       $haystack
 * @param string|array $needles
 * @return bool
 */
function contains(string $haystack, $needles)
{
	foreach ((array) $needles as $needle) {
		if ('' != $needle && mb_strpos($haystack, $needle) !== false) {
			return true;
		}
	}

	return false;
}

/**
 * 获取字符串的长度
 *
 * @param  string $value
 * @return int
 */
function length_(string $value): int
{
    return mb_strlen($value);
}

/**
 * 截取字符串
 *
 * @param  string   $string
 * @param  int      $start
 * @param  int|null $length
 * @return string
 */
function substr_(string $string, int $start, int $length = null): string
{
    return mb_substr($string, $start, $length, 'UTF-8');
}

/**
 * 检查字符串是否以某些字符串结尾
 *
 * @param  string       $haystack
 * @param  string|array $needles
 * @return bool
 */
function endsWith($haystack, $needles)
{
	foreach ((array) $needles as $needle) {
		if ((string) $needle === substr_($haystack, -length_($needle))) {
			return true;
		}
	}

	return false;
}

/**
 * 检查字符串是否以某些字符串开头
 *
 * @param  string       $haystack
 * @param  string|array $needles
 * @return bool
 */
function startsWith($haystack, $needles)
{
	foreach ((array) $needles as $needle) {
		if ('' != $needle && mb_strpos($haystack, $needle) === 0) {
			return true;
		}
	}

	return false;
}

/**
 * 获取指定长度的随机字母数字组合的字符串
 *
 * @param  int $length
 * @param  int $type
 * @param  string $addChars
 * @return string
 */
function random($length = 6, $type = null, $addChars = ''){
	$str = '';
	switch ($type) {
		case 0:
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz23456789' . $addChars;
			break;
		case 1:
			$chars = str_repeat('0123456789', 3);
			break;
		case 2:
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
			break;
		case 3:
			$chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		case 4:
			$chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书" . $addChars;
			break;
		case 5:
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		default:
			$chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
			break;
	}
	if ($length > 10) {
		$chars = $type == 1 ? str_repeat($chars, $length) : str_repeat($chars, 5);
	}
	if ($type != 4) {
		$chars = str_shuffle($chars);
		$str = substr($chars, 0, $length);
	} else {
		for ($i = 0; $i < $length; $i++) {
			$str .= mb_substr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
		}
	}
	return $str;
}
// 安全编码
function urlsafe_b64encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
    return $data;
}

// 安全解码
function urlsafe_b64decode($string) {
    $data = str_replace(array('-', '_'), array('+', '/'), $string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
}

/**
 *
 * 字符串分隔，回车
 *
 */
function split_enter($str) {
    return preg_split("/\r\n/", $str);
}
/**
 * 字符串分隔成数组，按指定字符分隔
 * @param string $preg
 * @param $str
 * @return array[]|false|string[]
 */
function split_char($str, $preg="/\r\n/") {
    return preg_split($preg, $str);
}

/**
 * 是否为字母
 * @param $str
 * @return false|int
 */
function is_letter($str){
    return preg_match('/[a-zA-Z]/', $str);
}
/**
 * 是否为数字
 * @param $str
 * @return false|int
 */
//function is_number($str){
//    return preg_match('/[0-9]/', $str);
//}
/**
*	文本的回车换行符替换成html的<br/>
*
*/
function toHtmlBr($str){
    return str_replace(PHP_EOL,'<br/>',$str);
}

/*
 * 生成唯一标志
 * 标准的UUID格式为：xxxxxxxx-xxxx-xxxx-xxxxxx-xxxxxxxxxx(8-4-4-4-12)
 */
function _uuid($separator='') {
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr($chars, 0, 8) . $separator
        . substr($chars, 8, 4) . $separator
        . substr($chars, 12, 4) . $separator
        . substr($chars, 16, 4) . $separator
        . substr($chars, 20, 12);
    return $uuid;
}

function uuid($separator=''){
    $res = Uuid::uuid1();
    return str_replace('-', $separator, $res->toString());
}

// 字符串长度超出length, 用省略号代替
function elips($text, $length) {
    if (mb_strlen($text, 'utf8') > $length) {
        return mb_substr($text, 0, $length, 'utf8') . '...';
    } else {
        return $text;
    }
}
//加密函数
function vencode($txt, $key = 'vandles') {
	if(empty($txt)) return '';
    $txt = $txt . $key;
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_";
    $nh = rand(0, 62);
    $ch = $chars[$nh];
    $mdKey = md5($key . $ch);
    $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
    $txt = base64_encode($txt);
    $tmp = '';
    $k = 0;
    for ($i = 0; $i < strlen($txt); $i++) {
        $k = $k == strlen($mdKey) ? 0 : $k;
        $j = ($nh + strpos($chars, $txt[$i]) + ord($mdKey[$k++])) % 62;
        $tmp .= $chars[$j];
    }
    return urlencode(base64_encode($ch . $tmp));
}
//解密函数
function vdecode($txt, $key = 'vandles') {
	if(empty($txt)) return '';
    $txt = base64_decode(urldecode($txt));
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_";
    $ch = $txt[0];
    $nh = strpos($chars, $ch);
    $mdKey = md5($key . $ch);
    $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
    $txt = substr($txt, 1);
    $tmp = '';
    $k = 0;
    for ($i = 0; $i < strlen($txt); $i++) {
        $k = $k == strlen($mdKey) ? 0 : $k;
        $j = strpos($chars, $txt[$i]) - $nh - ord($mdKey[$k++]);
        while ($j < 0) $j += 62;
        $tmp .= $chars[$j];
    }
    $ss = trim(base64_decode($tmp));
    return substr($ss,0,strlen($ss)-strlen($key));
}
/**
 *
 * @param string $string 需要加密的字符串
 * @param string $secret
 * @return string
 */
function ensha1($string, $secret='vandles'){
	$key = substr(openssl_digest(openssl_digest($secret, 'sha1', true), 'sha1', true), 0, 16);
	// openssl_encrypt 加密不同Mcrypt，对秘钥长度要求，超出16加密结果不变
	$data = openssl_encrypt($string, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
	$encrypted = strtolower(bin2hex($data));
	return $encrypted;
}

/**
 * @param string $string 需要解密的字符串
 * @param string $secret
 * @return string
 */
function desha1($string, $secret='vandles'){
	$key = substr(openssl_digest(openssl_digest($secret, 'sha1', true), 'sha1', true), 0, 16);
	$decrypted = openssl_decrypt(hex2bin($string), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
	return $decrypted;
}
if(!function_exists('encryptId')){
    /**
     * ID加密
     * @param int $id
     * @param int $scale
     * @return string
     * @throws Exception
     */
    function encryptId(int $id, int $scale=33){
        if($id > 32775567890) throw new \Exception('id 大于适用范围');
        $source_string = 'E5FCDG3HQA4B1NO7PI6J2RST9UVM6WXKLYZ';
        if($scale>35 || $scale <30) $scale = 35;
        else $source_string = substr($source_string, 0, $scale);
        $code = '';
        while ( $id > 0) {
            $mod = $id % $scale;
            $id = ($id - $mod) / $scale;
            $code = $source_string[$mod].$code;
        }
        if(empty($code[3])){
            $code = str_pad($code,4,'0',STR_PAD_LEFT);
            return $code;
        }
        //加密
        $lastChar = substr($code, -1);
        $step = strpos($source_string,$lastChar) - 1 ;
        $strLen = strlen($code);
        for ($i=0;$i<$strLen-1;$i++){
            if($step%2)
                $local = strpos($source_string,$code[$i])+$step-$i;
            else
                $local = strpos($source_string,$code[$i])+$step+$i;
            if ($local < 0)
                $local = $scale + $local;

            if($local >= $scale ){
                $local = $local - $scale;
            }
            $code[$i] = $source_string[$local];
        }
        return $code;
    }
}

if(!function_exists('decryptId')){
    /**
     * ID解密
     * @param string $code
     * @param int $scale
     * @return float|int
     * @throws Exception
     */
    function decryptId(string $code, int $scale = 33){
        $source_string = 'E5FCDG3HQA4B1NO7PI6J2RST9UVM6WXKLYZ';
        if($scale>35 || $scale <30) $scale = 35;
        else $source_string = substr($source_string, 0, $scale);
        $lastChar = substr($code, -1);
        $step = strpos($source_string,$lastChar) - 1 ;
        $strLen = strlen($code);

        for ($i=0;$i<$strLen-1;$i++){
            if($step%2)
                $local = strpos($source_string,$code[$i])-$step+$i;
            else
                $local = strpos($source_string,$code[$i])-$step-$i;

            if ($local < 0)
                $local = $scale + $local;

            if($local >= $scale ){
                $local = $local - $scale;
            }
            $code[$i] = $source_string[$local];
        }
        //进制转换为10进制
        if (strrpos($code, '0') !== false)
            $code = substr($code, strrpos($code, '0')+1);
        $len = strlen($code);
        $code = strrev($code);
        $num = 0;
        for ($i=0; $i < $len; $i++) {
            $num += strpos($source_string, $code[$i]) * pow($scale, $i);
        }

        if($num > 32775567890) throw new \Exception('解密后的 id 大于适用范围');
        return $num;
    }
}
function httpGet($url, $params=null) {
	if($params){
		if(strpos($url, '?') !== false)
			$url .= '&'.http_build_query($params);
		else
			$url .= '?'.http_build_query($params);
	}
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	$output = curl_exec($ch);
	curl_close($ch);
	return json_decode($output, true);
}
function httpPost($url, $params, $header=null) {
	$params = json_encode($params);
	$header_ = ['Content-Type:application/json', 'Accept:application/json',"User-Agent:".$_SERVER['HTTP_USER_AGENT']];
	if($header) $header_ = array_merge($header_, $header);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header_);
	curl_setopt($ch, CURLOPT_POSTFIELDS , $params);
	$output = curl_exec($ch);
	curl_close($ch);
	return json_decode($output, true);
}
function getSign($data) {
	ksort($data);
	$sign = sha1(http_build_query($data));
	return $sign;
}
/**
 * 将base64图片数据保存成图片
 * @param string $image base64 字符串
 * @param string $path  保存路径
 * @return bool|string  保存成功返回路径否则返回flase
 */
function base64ToImgFile($image,$path='/public/tmp/', $imageName='a.png'){
//        $imageName = "b64_".date("His",time())."_".rand(1111,9999).'.png';
    if (strstr($image,",")){
        $image = explode(',',$image);
        $image = $image[1];
    }
//        $path = $path.date("Ym",time());
    if (!is_dir($_SERVER['DOCUMENT_ROOT'].$path)){ //判断目录是否存在 不存在就创建
        mkdir($_SERVER['DOCUMENT_ROOT'].$path,0777,true);
    }
    $imageSrc=  $_SERVER['DOCUMENT_ROOT'].$path."/". $imageName;  //图片名字
    $r = file_put_contents($imageSrc, base64_decode($image));//返回的是字节数
    if (!$r) {
        return false;
    }else{
        return $path."/". $imageName;
    }
}
// 转换编码，将可以浏览的utf-8编码转换成unicode编码
function unicode_encode($name) {
    $name = iconv('utf-8', 'ucs-2', $name);
    $len = strlen($name);
    $str = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2) {
        $c = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0){    // 两个字节的文字
            $str .= '\u'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);
        }else $str .= $c2;
    }
    return $str;
}

// 转换编码，将unicode编码转换成可以浏览的utf-8编码
function unicode_decode($name) {
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $name, $matches);
    if (!empty($matches)){
        $name = '';
        for ($j = 0; $j < count($matches[0]); $j++){
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0){
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c = chr($code).chr($code2);
                $c = iconv('ucs-2', 'utf-8', $c);
                $name .= $c;
            }else $name .= $str;
        }
    }
    return $name;
}

// 去掉所有空格
function atrim($str){
	return preg_replace('# #', '', $str);
}
// A~Z的数组
function getA2Z(){
    $arr = [];
    for($i=65; $i<65+26; $i++){
        $arr[] = chr($i);
    }
    return $arr;
}
/**
 * 根据经纬度算距离，返回结果单位是公里，后经度，先纬度
 * @param $lat1
 * @param $lng1
 * @param $lat2
 * @param $lng2
 * @return float|int
 */
function getDistance($lng1, $lat1, $lng2, $lat2) {
    if($lng1 > 180 || $lng1 < -180 || $lng2 > 180 || $lng2 < -180)
        throw new Exception('经度必须在-180~180之间');
    if($lat1 > 90 || $lat1 < -90 || $lat2 > 90 || $lat2 < -90)
        throw new Exception('纬度必须在-90~90之间');
    $EARTH_RADIUS = 6378.137;
    $radLat1 = rad($lat1);
    $radLat2 = rad($lat2);
    $a = $radLat1 - $radLat2;
    $b = rad($lng1) - rad($lng2);
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $s = $s * $EARTH_RADIUS;
    $s = round($s * 100) / 100;
    return $s;
}

// 按二维数组的值排序
function vsort($arr, $val, $sort = SORT_ASC){
    $key = array_column($arr,$val);
    array_multisort($key,$sort, $arr);
    return $arr;
}


/**
 * 计算距离的附加函数
 * @param $d
 * @return float
 */
function rad($d){
    return $d * M_PI / 180.0;
}

/**
 * 二维数组按照键值降序排序
 * @param array $arr 待排序数组
 * @param string $key 键值
 * @param string $sort
 * @return mixed
 */
function sortByKey($arr, $key, $sort='asc') {
    array_multisort(array_column($arr, $key), $sort=='desc'?SORT_DESC:SORT_ASC, $arr);
    return $arr;
}

/**
 * 毫秒时间戳
 * @return string
 */
function mtime(){
    list($mtime, $time) = explode(' ', microtime());
    $mtime = sprintf("%.0f", (floatval($mtime) + floatval($time)) * 1000);
    return intval($mtime);
}

function cnval($str, $in="gbk"){
    return iconv($in, "utf-8", $str);
}
function g2u($str, $in="gbk"){
    return cnval($str, $in);
}
function utf8val($str, $in="utf-8"){
    return iconv($in, "gbk", $str);
}
function u2g($str, $in="utf-8"){
    return utf8val($str, $in);
}

/**
 * 时间字符串
 * 500秒 => 00:08:20
 */
function sec2str($sec){
    if(is_numeric($sec)){
        $value = array(
            "years" => 0, "days" => 0, "hours" => 0,
            "minutes" => 0, "seconds" => 0,
        );
        if($sec >= 31556926){
            $value["years"] = floor($sec/31556926);
            $sec = ($sec%31556926);
        }
        if($sec >= 86400){
            $value["days"] = floor($sec/86400);
            $sec = ($sec%86400);
        }
        if($sec >= 3600){
            $value["hours"] = floor($sec/3600);
            $sec = ($sec%3600);
        }
        if($sec >= 60){
            $value["minutes"] = floor($sec/60);
            $sec = ($sec%60);
        }
        $value["seconds"] = floor($sec);
        if($value['years']) $y = $value["years"] <= 9 ? '0'.$value["years"] : $value["years"] . "年";
        else $y = '';
        if($value['days']) $d = $value["days"] <= 9 ? '0'.$value["days"] : $value["days"] . "天";
        else $d = '';

        $h = $value["hours"] <= 9 ? '0'.$value["hours"] : $value["hours"];
        $m = $value["minutes"] <= 9 ? '0'.$value["minutes"] : $value["minutes"];
        $s = $value["seconds"] <= 9 ? '0'.$value["seconds"] : $value["seconds"];

        $t = $y . $d . $h . ":" . $m .":" . $s;
        return $t;
    }else{
        return (bool) false;
    }
}
/**
 * 给定秒数，输出多长时间以前
 * @param $datetime
 * @return string
 * @throws Exception
 */
function timeAgo($datetime) {
    // 计算时间差
    $interval = (new DateTime($datetime))->diff(new DateTime());

    // 输出时间差
    if ($interval->y > 0) {
        return $interval->y . '年前';
    } elseif ($interval->m > 0) {
        return $interval->m . '个月前';
    } elseif ($interval->d > 0) {
        return $interval->d . '天前';
    } elseif ($interval->h > 0) {
        return $interval->h . '小时前';
    } elseif ($interval->i > 0) {
        return $interval->i . '分钟前';
    } else {
        return '刚刚';
    }
}
// 字符串中间加*
function cstar($str, $start=0, $len=4, $star='*'){
    $s1 = substr($str, 0, $start);
    if(($end=$start + $len - strlen($str)) < 0){
        $s2 = substr($str, $end);
    }elseif($end == 0){
        $s2 = '';
    }else $s2 = '-';
    return $s1 . str_repeat($star, $len) . $s2;
}

function star_cut($str, $prefixNum=4, $repeat=3, $suffixNum=4, $suffixStar='*'){
    if(empty($str)) return "";
    $prefix = substr($str, 0, $prefixNum);
    $suffix = substr($str, -$suffixNum);
    $repeatStr = str_repeat($suffixStar, $repeat);
    return $prefix . $repeatStr . $suffix;
}

/**
 * 敏感字符串脱敏，指定位置用*代替
 * @param $str
 * @param int $start 开始替换的位置
 * @param int $length 要替换的字符数量
 * @param string $star
 * @return mixed|string
 */
function repeatStart($str, $start=0, $length=0, $star="*"){
    mb_internal_encoding("UTF-8"); // 设置内部字符编码为UTF-8
    if(empty($str))  return '';
    if(!is_string($str)) return '';

    if($start < 0) $start = mb_strlen($str) + $start;
    if($length == 0){
        $length = mb_strlen($str) - $start;
    }
    $replacement = str_repeat($star, $length); // 生成与要替换的字符数量相同的星号字符串

    $prefix = mb_substr($str, 0, $start); // 获取替换位置之前的部分
    $suffix = mb_substr($str, $start + $length); // 获取替换位置之后的部分

    $result = $prefix . $replacement . $suffix; // 构建替换后的字符串
    return $result;
}

/**
* 正则表达式匹配中国手机号码格式
*/
function isPhone($phone) {
    if(empty($phone)) return false;
	// 正则表达式匹配中国手机号码格式
	$pattern = '/^1[3-9]\d{9}$/';
	return preg_match($pattern, $phone);
}
/**
 * 生成GUID
 */
function guid($separator='') {
    return uuid($separator);
    // if (function_exists('com_create_guid') === true) {
    //     return strtolower(str_replace('-', $separator, trim(com_create_guid(), '{}')));
    // }
    // return sprintf("%04x%04x$separator%04x$separator%04x$separator%04x$separator%04x%04x%04x", mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

function oplog(string $action, string $content):bool{
    return sysoplog("操作日志:".$action, $content);
}