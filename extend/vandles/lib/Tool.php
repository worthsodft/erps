<?php
/*
 * 
 * Author: vandles
 * Date: 2022/10/11 14:19
 * Email: <vandles@qq.com>
 */


namespace vandles\lib;


use think\Exception;
use think\facade\Db;
use vandles\model\SnModel;

class Tool {

    /**
     * 获取指定长度的随机字母数字组合的字符串
     *
     * @param  int $length
     * @param  int $type
     * @param  string $addChars
     * @return string
     */
    public static function randStr($length = 6, $type = null, $addChars = ''){
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

    public static function httpGet($url, $params=null, $header=null) {
        $header_ = ['Content-Type:application/json', 'Accept:application/json',"User-Agent:".$_SERVER['HTTP_USER_AGENT']];
        if($header) $header_ = array_merge($header_, $header);
        if($params){
            if(strpos($url, '?') !== false)
                $url .= '&'.http_build_query($params);
            else
                $url .= '?'.http_build_query($params);
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }

    public static function httpPost($url, $params, $header=null) {
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

    /**
     * 解析XML内容到数组
     * @param string $xml
     * @return array
     */
    public static function xml2arr($xml){
        if (PHP_VERSION_ID < 80000) {
            $backup = libxml_disable_entity_loader(true);
            $data = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            libxml_disable_entity_loader($backup);
        } else {
            $data = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return json_decode(json_encode($data), true);
    }

    /**
     * 解析XML文本内容
     * @param string $xml
     * @return array|false
     */
    public static function xml3arr($xml){
        $state = xml_parse($parser = xml_parser_create(), $xml, true);
        return xml_parser_free($parser) && $state ? self::xml2arr($xml) : false;
    }

    /**
     * 数组转xml内容
     * @param array $data
     * @return null|string
     */
    public static function arr2json($data){
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $json === '[]' ? '{}' : $json;
    }

    /**
     * 解析JSON内容到数组
     * @param string $json
     * @return array
     * @throws Exception
     */
    public static function json2arr($json){
        $result = json_decode($json, true);
        if (empty($result)) {
            throw new Exception('invalid response.', '0');
        }
        if (!empty($result['errcode'])) {
            throw new Exception($result['errmsg'], $result['errcode'], $result);
        }
        return $result;
    }
    /**
     * 生成uuid字符串 69451ce5-5c1e-40cf-85f0-ecd70bab2700
     * @return string
     */
    public static function uuid() {
        return uuid("-");
        // return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        //     mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        //     mt_rand( 0, 0xffff ),
        //     mt_rand( 0, 0x0fff ) | 0x4000,
        //     mt_rand( 0, 0x3fff ) | 0x8000,
        //     mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        // );
    }
	
	/**
	 * base64转存为png文件
	 *
	 */
	public function b64ToImage($content, $filename, $topic='topic', $upload='upload') {
        $path = app()->getRootPath() . "public/$upload/" . $topic;
        if (!is_dir($path)) mkdir($path, 777, true);
        $fullname = $path . '/' . $filename;
        $url      = getDomain() . "/$upload/" . $topic . '/' . $filename;
        if (is_file($fullname)) return $url;;

        // 去掉文件头并解码base64编码的数据
        $img_data   = str_replace('data:image/png;base64,', '', $content);
        $img_data   = str_replace(' ', '+', $img_data);
        $img_binary = base64_decode($img_data);

        // 将数据保存为文件
        file_put_contents($fullname, $img_binary);
        return $url;
    }

    public static function genSn($prefix){
        $lock = FileLock::instance("gen_sn_prefix_$prefix");
        if(!$lock->lock(true)) VException::throw("系统繁忙，请稍后访问！");
        $entity = SnModel::where("prefix", $prefix)->field("prefix, date, current")->find();
        $date = date('Ymd');
        if(empty($entity)){
            $current = 1;
            SnModel::create(['prefix'=>$prefix, 'date'=>$date, 'current'=>$current]);
        } else{
            if($entity['date'] == $date){
                $current = $entity['current'] + 1;
                $entity->current = $current;
            }else{
                $current = 1;
                $entity->current = $current;
                $entity->date = $date;
            }
            $entity->save();
        }
        $sn = str_pad($current, 5, '0', STR_PAD_LEFT);
        return $prefix . date('Ymd') . $sn;
    }
}