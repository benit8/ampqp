<?php declare(strict_types=1);

require_once \dirname(__DIR__) . '/vendor/autoload.php';

use Benit8\Ampqp\Connection;
use Benit8\Ampqp\Message;

$config = new Connection\Config();
$connection = new Connection($config);

$channel = $connection->channel();

$exchange = $channel->exchange('test', autoDelete: true);

$queue = $channel->queue(autoDelete: true);
$queue->bind($exchange);


$exchange->publish(new Message('.'));

$message = $queue->get(noAck: true);
dump($message->body);

// Amp\delay(3);
$connection->close();
