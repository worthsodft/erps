<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/6/28
 * Time: 13:36
 */

$ws = new Swoole\WebSocket\Server("0.0.0.0", 9100);

//监听WebSocket连接打开事件。
$ws->on('Open', function ($ws, $request) {
    echo "连接已打开：{$request->fd} is in!\n";
});

//监听WebSocket消息事件。
$ws->on('Message', function ($ws, $frame) {
    $data = $frame->data;
    echo "收到消息：$data \n";
//    $ws->push($frame->fd, $data);
});

//监听WebSocket连接关闭事件。
$ws->on('Close', function ($ws, $fd) {
    echo "连接已关闭：{$fd} is closed\n";
});

$ws->start();