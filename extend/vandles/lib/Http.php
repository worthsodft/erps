<?php
/**
 * Created by PhpStorm
 * User: vandles
 * Date: 2023/7/5
 * Time: 10:56
 * Email: windows_1122334@126.com
 */


namespace vandles\lib;


class Http {

    private static $instance;

    public static function instance(): Http {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param $url
     * @param array $params
     * @param array $header
     * @return array
     */
    public function post($url, $params = [], $header = []) {
        return static::request($url, 'post', $params, $header);
    }

    /**
     * @param $url
     * @param array $params
     * @param array $header
     * @return array
     */
    public function get($url, $params = [], $header = []) {
        return static::request($url, 'get', $params, $header);
    }

    /**
     * @param $url
     * @param string $method
     * @param array $params
     * @param array $header
     * @param array $option
     * @return array
     */
    public function request($url, $method = 'get', $params = [], $header = [], $option = []) {
        $method       = strtoupper($method);
        $protocol     = substr($url, 0, 5);
        $query_string = is_array($params) ? http_build_query($params) : $params;

        $config = [];
        $ch     = curl_init($url);

        if ($method == 'GET') {
            $url                 = $query_string ? $url . (stripos($url, '?') !== false ? '&' : '?') . $query_string : $url;
            $config[CURLOPT_URL] = $url;
        } else {
            $config[CURLOPT_URL] = $url;
            if ($method == 'POST') $config[CURLOPT_POST] = 1;
            else $config[CURLOPT_CUSTOMREQUEST] = $method;
            $config[CURLOPT_POSTFIELDS] = $params;
        }

        if (empty($header)) {
            $config[CURLOPT_HEADER] = false;
        } else {
            $config[CURLOPT_HTTPHEADER] = $header;
        }

        $config[CURLOPT_USERAGENT]      = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.67";
        $config[CURLOPT_FOLLOWLOCATION] = true;
        $config[CURLOPT_RETURNTRANSFER] = true;
        $config[CURLOPT_CONNECTTIMEOUT] = 3;
        $config[CURLOPT_TIMEOUT]        = 3;

        // 关闭 大于1kb时，100-continue 请求
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Except:']);

        if ($protocol == 'https') {
            $config[CURLOPT_SSL_VERIFYPEER] = false;
            $config[CURLOPT_SSL_VERIFYHOST] = false;
        }

        curl_setopt_array($ch, (array)$option + $config);

        $res = curl_exec($ch);
        $err = curl_error($ch);
        if ($res === false || !empty($err)) {
            $errno = curl_errno($ch);
            $info  = curl_getinfo($ch);
            curl_close($ch);
            return [
                'code' => $errno,
                'msg'  => $err,
                'data' => $info,
            ];
        }
        curl_close($ch);
        $data = [
            'code' => '0000',
            'msg'  => 'success',
            'data' => $res
        ];
        return $data;
    }

}