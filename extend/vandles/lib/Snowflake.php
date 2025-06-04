<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2025/1/23
 * Time: 14:10
 */

namespace vandles\lib;

/**
 * 雪花算法生成ID
 * 使用示例
 * $snowflake = new Snowflake(1); // 假设机器ID为1, 0~1023
 * echo $snowflake->generateId();
 */
class Snowflake {
    const EPOCH = 1609459200000; // 自定义起始时间戳，这里使用2021-01-01 00:00:00 UTC

    const SEQUENCE_BITS = 12; // 序列号占用的位数
    const NODE_ID_BITS = 10; // 机器ID占用的位数
    const TIMESTAMP_BITS = 41; // 时间戳占用的位数

    const MAX_SEQUENCE = -1 ^ (-1 << self::SEQUENCE_BITS); // 序列号最大值
    const MAX_NODE_ID = -1 ^ (-1 << self::NODE_ID_BITS); // 机器ID最大值

    const SEQUENCE_SHIFT = 0; // 序列号左移位数
    const NODE_ID_SHIFT = self::SEQUENCE_BITS; // 机器ID左移位数
    const TIMESTAMP_SHIFT = self::SEQUENCE_BITS + self::NODE_ID_BITS; // 时间戳左移位数

    protected $nodeId; // 机器ID
    protected $sequence = 0; // 序列号
    protected $lastTimestamp = -1; // 上一次的时间戳

    public function __construct($nodeId=1) {
        if ($nodeId > self::MAX_NODE_ID || $nodeId < 0) {
            throw new \Exception("Node ID can't be greater than " . self::MAX_NODE_ID . " or less than 0");
        }
        $this->nodeId = $nodeId;
    }

    public static function instance($nodeId=1): Snowflake {
        return new self($nodeId);
    }

    public function generateId() {
        $timestamp = $this->getCurrentTimestamp();

        if ($timestamp < $this->lastTimestamp) {
            throw new \Exception("Clock moved backwards. Refusing to generate id for " . ($this->lastTimestamp - $timestamp) . " milliseconds");
        }

        if ($timestamp == $this->lastTimestamp) {
            // 同一毫秒内，序列号自增
            $this->sequence = ($this->sequence + 1) & self::MAX_SEQUENCE;
            if ($this->sequence == 0) {
                // 序列号达到最大值，等待下一毫秒
                $timestamp = $this->tilNextMillis($this->lastTimestamp);
            }
        } else {
            // 不同毫秒内，序列号重置
            $this->sequence = 0;
        }

        $this->lastTimestamp = $timestamp;

        // 组合生成ID
        return (($timestamp - self::EPOCH) << self::TIMESTAMP_SHIFT) |
               ($this->nodeId << self::NODE_ID_SHIFT) |
               $this->sequence;
    }

    protected function getCurrentTimestamp() {
        return floor(microtime(true) * 1000);
    }

    protected function tilNextMillis($lastTimestamp) {
        $timestamp = $this->getCurrentTimestamp();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->getCurrentTimestamp();
        }
        return $timestamp;
    }
}
