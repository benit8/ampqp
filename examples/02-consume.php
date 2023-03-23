<?php declare(strict_types=1);

require_once \dirname(__DIR__) . '/vendor/autoload.php';

use Benit8\Ampqp\Connection;
use Benit8\Ampqp\Message;
use Benit8\Ampqp\Queue;

$config = new Connection\Config();
$connection = new Connection($config);

$channel = $connection->channel();

$exchange = $channel->exchange('test', autoDelete: true);

$queue = $channel->queue(autoDelete: true);
$queue->bind($exchange);

$consumer = $queue->consume(function (Queue\Message $message) {
	echo "Consumed message: '{$message->body}'\n";
	$message->ack();
});

Amp\async(function () use ($exchange, $consumer) {
	Amp\delay(2);
	$exchange->publish(new Message('1'));

	Amp\delay(1);
	$exchange->publish(new Message('2'));

	Amp\delay(3);
	$exchange->publish(new Message('3'));
});

$timer = Revolt\EventLoop::repeat(1, function () {
	static $i;
	$i = $i ? ++$i : 1;
	echo "Demonstrating how alive the parent is for the {$i}th time.\n";
});

Amp\delay(8);
Revolt\EventLoop::cancel($timer);

$consumer->cancel();
$connection->close();
