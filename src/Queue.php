<?php declare(strict_types=1);

namespace Benit8\Ampqp;

use Benit8\Ampqp\Queue\Consumer;
use Benit8\Ampqp\Queue\Message;

/**
 * A named entity that holds messages and forwards them to consumer applications.
 */
class Queue
{
	/**
	 * Not to be used directly. See Channel->queue().
	 */
	public function __construct(
		private readonly \WeakReference $protocol,
		private readonly int $channelId,
		public readonly string $name,
		public readonly bool $passive,
		public readonly bool $durable,
		public readonly bool $exclusive,
		public readonly bool $autoDelete,
		public readonly bool $noWait,
		public readonly array $arguments,
	) {
	}

	public function __destruct()
	{
		// if (!$this->autoDelete) {
		// 	$this->delete();
		// }
	}

	public function bind(string|Exchange $exchange, string $routingKey = '', bool $noWait = false, array $arguments = []): void
	{
		if ($exchange instanceof Exchange) {
			$exchange = $exchange->name;
		}

		$this->protocol->get()?->queueBind($this->channelId, $this->name, $exchange, $routingKey, $noWait, $arguments);
	}

	public function purge(bool $noWait = false): void
	{
		$this->protocol->get()?->queuePurge($this->channelId, $this->name, $noWait);
	}

	public function delete(bool $ifUnused = false, bool $ifEmpty = false, bool $noWait = false): void
	{
		$this->protocol->get()?->queueDelete($this->channelId, $this->name, $ifUnused, $ifEmpty, $noWait);
	}

	public function unbind(string|Exchange $exchange, string $routingKey = '', array $arguments = []): void
	{
		if ($exchange instanceof Exchange) {
			$exchange = $exchange->name;
		}

		$this->protocol->get()?->queueUnbind($this->channelId, $this->name, $exchange, $routingKey, $arguments);
	}

	/**
	 * @param Closure(Message): void $consumer
	 */
	public function consume(
		\Closure $callback,
		string $consumerTag = '',
		bool $noLocal = false,
		bool $noAck = false,
		bool $exclusive = false,
		bool $noWait = false,
		array $arguments = [],
	): Consumer {
		$ok = $this->protocol->get()?->basicConsume($this->channelId, $this->name, $consumerTag, $noLocal, $noAck, $exclusive, $noWait, $arguments);
		return new Consumer($this->protocol, $this->channelId, $callback, $ok->consumerTag);
	}

	public function get(bool $noAck = false): ?Message
	{
		return $this->protocol->get()?->basicGet($this->channelId, $this->name, $noAck);
	}
}
