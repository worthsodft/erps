<?php

namespace vandles\lib;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

class JwtUtil{
    private static $obj;
    private $key;
    private $algorithm;

    public function __construct($key, $algorithm = 'HS256'){
        $this->key = $key;
        $this->algorithm = $algorithm;
    }

    public static function instance() {
        $key = "VANDLES_HS256_~!@";
        if(!self::$obj) {
            self::$obj = new self($key);
        }
        return self::$obj;
    }

    public function encode(array $payload, $validityPeriod = 7200, $notBefore = null) {
        $issuedAt = time();
        $expirationTime = $issuedAt + $validityPeriod; // 使用有效期秒数计算过期时间
        if ($notBefore === null) {
            $notBefore = $issuedAt;
        }

        $payload['iss'] = "VANDLES"; // 签发组织
        $payload['aud'] = "VANDLES"; // 签发作者
        $payload['iat'] = $issuedAt;
        $payload['exp'] = $expirationTime;
        $payload['nbf'] = $notBefore;

        return JWT::encode($payload, $this->key, $this->algorithm);
    }

    public function decode($token) {
        try {
            return JWT::decode($token, new Key($this->key, $this->algorithm));
        } catch (ExpiredException $e) {
            throw new \Exception("令牌已过期", 401);
        } catch (SignatureInvalidException $e) {
            throw new \Exception("签名无效", 401);
        } catch (BeforeValidException $e) {
            throw new \Exception("令牌尚未生效", 401);
        } catch (\Exception $e) {
            throw new \Exception("无效的令牌", 401);
        }
    }

    // 添加静态示例方法
    public static function exampleUsage() {
        $key = 'example_key';
        $jwtUtil = new JwtUtil($key);

        // 示例：编码一个JWT
        $payload = ['user_id' => 123];
        $token = $jwtUtil->encode($payload, 3600); // 使用有效期秒数
        echo "Encoded Token: " . $token . "\n";

        // 示例：解码一个JWT
        try {
            $decoded = $jwtUtil->decode($token);
            echo "Decoded Payload: ";
            print_r($decoded);
        } catch (\Exception $e) {
            echo "Error decoding token: " . $e->getMessage();
        }
    }
}