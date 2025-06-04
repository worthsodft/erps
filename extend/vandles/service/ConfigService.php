<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;


use vandles\model\BaseSoftDeleteModel;
use vandles\model\ConfigModel;

class ConfigService extends BaseService {
    protected static $instance;

    public static function instance(): ConfigService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $datas = [
            [
                "title" => "小程序appid",
                "name"  => "miniappid"
            ], [
                "title" => "小程序secret",
                "name"  => "minisecret"
            ]
        ];

        $this->getModel()->saveAll($datas);
    }

    /**
     * 微信小程序配置
     * @return array
     * @throws \think\admin\Exception
     */
    public function getConfigMini($key=null) {
        $config = sysdata('config');
        $config = [
            'appid'       => $config['mini_appid'] ?? '',
            'appsecret'   => $config['mini_secret'] ?? '',
            'share_title' => $config['share_title'] ?? '',
            'share_image' => $config['share_image'] ?? '',
            'mch_id'      => $config['mch_id'] ?? '',
            'mch_key'     => $config['mch_key'] ?? '',
            "notify_url"  => request()->domain() . "/api/v1/paySuccessNotify",
            "ssl_key"     => $config['ssl_key'] ?? '',
            "ssl_cer"     => $config['ssl_cer'] ?? '',
            "ssl_p12"     => app()->getRootPath() . "extend/safefile/apiclient_cert.p12",
        ];
        if($key) return $config[$key]??null;
        return $config;
    }

    /**
     * @return BaseSoftDeleteModel
     */
    public function getModel() {
        return ConfigModel::mk();
    }

    public function getAppConfig($key=null) {
        $config = sysdata('config');
        if($key) return $config[$key]??null;
        return [
            "shareData" => [
                "title"    => $config['share_title']??"分享标题",
                "content"  => $config['share_content']??"分享内容",
                "path"     => $config['share_path']??"/pages/index/index",
                "imageUrl" => $config['share_image']??"",
            ],
            "servicePhone" => $config['service_phone'],
            "free_deliver_money" => $config['free_deliver_money'] ?? 0,
        ];
    }

    public function getRechargeWayRemark() {
        $remarks = sysdata("config.recharge_way_remarks");
        $remarks = explode(PHP_EOL, $remarks);
        return $remarks;
    }

    /**
     * 实物卡绑定页图片（730*460px）
     * @return mixed
     */
    public function getGiftCardBindBanner() {
        return config("a.gift_card_bind_banner");
    }
    public function getGiftCardBindBanners() {
        return config("a.gift_card_bind_banners");
    }

    /**
     * 实物卡使用说明
     * @return mixed
     */
    public function getGiftCardRemarks() {
        return config("a.gift_card_remarks");
    }

    /**
     * 是否开启库存严格模式
     * @return bool
     */
    public function isStockStrict() {
        $config = sysdata("config");
        return !empty($config['is_stock_strict']);
    }
}