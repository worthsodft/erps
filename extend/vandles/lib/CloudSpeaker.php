<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\lib;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Darabonba\OpenApi\Models\Config;
use think\App;
use think\Exception;
use vandles\lib\Result;
use WeChat\Contracts\Tools;


class CloudSpeaker {

    private $config;
    private $gateway = 'https://speaker.17laimai.cn';

    private const PRICE_TYPE = [
        'pay'      => [
            'ali'   => 1,
            'wx'    => 2,
            'flash' => 3,
            'card'  => 8,
            'yi'    => 10,
        ],
        'refund'   => [
            '0'   => 11,
            'ali' => 12,
            'wx'  => 13,
            'jd'  => 19,
        ],
        'recharge' => [
            'card' => 9,
        ],
        'cancel'   => [
            0 => 20
        ]
    ];

    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * @param array $config
     * @param bool $new
     * @return CloudSpeaker
     */
    public static function instance(array $config, bool $new = false) {
        return app()->make(CloudSpeaker::class, ['config' => $config], $new);
    }


    /**
     * 播报语音
     * @param string $platform 支付平台：wx,ali
     * @param float $money 单位：分
     * @param string $traceNo 支付交易号
     * @return array
     * @throws Exception
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    public function speak(string $platform, float $money, string $traceNo = null) {
        $url  = '/add.php';
        $opts = [
            'token'   => $this->config['token'],
            'version' => (int)$this->config['version'],
            'id'      => $this->config['id'],
            'uid '    => $this->config['uid'] ?? '', // 商户id(可选)

            'pt'       => self::PRICE_TYPE['pay'][$platform] ?? 2,
            'price' => (int)bcmul($money, 100),
            'trace_no' => $traceNo,
        ];
        $res = Tools::post($this->gateway . $url, $opts);
        $res = json_decode($res, true);
        return $res;
    }

    public function mer(?\think\Model $mer) {
        $this->mer = $mer;
        return $this;
    }

}