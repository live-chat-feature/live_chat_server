<?php
require __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

$ws_worker = new Worker("websocket://192.168.1.14:8080");
$clients = [];

$ws_worker->onMessage = function($connection, $data) use (&$clients) {
    $msg = json_decode($data, true);

    if (isset($msg['type']) && $msg['type'] === 'login') {
        $connection->user = $msg['user'];
        $clients[$msg['user']] = $connection;
        return;
    }

    if (isset($msg['type']) && $msg['type'] === 'chat') {
        $to = $msg['to'];
        if (isset($clients[$to])) {
            $clients[$to]->send(json_encode($msg));
        }
        if (isset($clients[$msg['from']])) {
            $clients[$msg['from']]->send(json_encode($msg));
        }
    }
};

$ws_worker->onClose = function($connection) use (&$clients) {
    if (isset($connection->user)) {
        unset($clients[$connection->user]);
    }
};

Worker::runAll();