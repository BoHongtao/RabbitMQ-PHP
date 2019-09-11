<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 2019/9/10
 * Time: 15:25
 */

//生产者

$config = array(
    'host' => '127.0.0.1',
    'vhost' => '/',
    'port' => 5672,
    'login' => 'guest',
    'password' => 'guest'
);
$cnn = new AMQPConnection($config);
if (!$cnn->connect()) {
    echo "Cannot connect to the broker";
    exit();
}
$ch = new AMQPChannel($cnn);
$ex = new AMQPExchange($ch);
//消息的路由键，一定要和消费者端一致
$routingKey = 'SMSMQ';
//交换机名称，一定要和消费者端一致
$exchangeName = 'exchange';
$ex->setName($exchangeName);
$ex->setType(AMQP_EX_TYPE_DIRECT);
$ex->setFlags(AMQP_DURABLE);
$ex->declareExchange();
//创建10个消息
for ($i=1;$i<=10000;$i++){
    //消息内容(比如用户手机号，邮件，文件id等)
    $msg = array(
        'data'  => 'file id:'.$i,
    );
    //发送消息到交换机，并返回发送结果
    //delivery_mode:2声明消息持久，持久的队列+持久的消息在RabbitMQ重启后才不会丢失
    $ex->publish(json_encode($msg), $routingKey, AMQP_NOPARAM, array('delivery_mode' => 2));
    echo "要处理的文件". $i ."放入消息队列\n";
//    echo "Send Message:".$ex->publish(json_encode($msg), $routingKey, AMQP_NOPARAM, array('delivery_mode' => 2))."\n";
    //代码执行完毕后进程会自动退出
}
