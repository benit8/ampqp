<?php declare(strict_types=1);

namespace Benit8\Ampqp;

use Benit8\Ampqp\Exchange\Type;
use Benit8\Ampqp\Frame\Method\ClassId;

/**
 * The entity within the server which receives messages from producer applications and
 * optionally routes these to message queues within the server.
 */
class Exchange
{
	/**
	 * Not to be used directly. See Channel->exchange().
	 */
	public function __construct(
		private readonly \WeakReference $protocol,
		private readonly int $channelId,
		public readonly string $name,
		public readonly Type $type,
		public readonly bool $passive,
		public readonly bool $durable,
		public readonly bool $autoDelete,
		public readonly bool $internal,
		public readonly bool $noWait,
		public readonly array $arguments,
	) {
	}

	public function delete(bool $ifUnused = true, bool $noWait = false): void
	{
		$this->protocol->get()?->exchangeDelete($this->channelId, $this->name, $ifUnused, $noWait);
	}

	public function bindFrom(string $source, string $routingKey = '', bool $noWait = false, array $arguments = []): void
	{
		$this->protocol->get()?->exchangeBind($this->channelId, $this->name, $source, $routingKey, $noWait, $arguments);
	}

	public function bindTo(string $destination, string $routingKey = '', bool $noWait = false, array $arguments = []): void
	{
		$this->protocol->get()?->exchangeBind($this->channelId, $destination, $this->name, $routingKey, $noWait, $arguments);
	}

	public function unbindFrom(string $source, string $routingKey = '', bool $noWait = false, array $arguments = []): void
	{
		$this->protocol->get()?->exchangeUnbind($this->channelId, $this->name, $source, $routingKey, $noWait, $arguments);
	}

	public function unbindTo(string $destination, string $routingKey = '', bool $noWait = false, array $arguments = []): void
	{
		$this->protocol->get()?->exchangeUnbind($this->channelId, $destination, $this->name, $routingKey, $noWait, $arguments);
	}

	public function publish(Message $message, string $routingKey = '', bool $mandatory = false, bool $immediate = false): void
	{
		$p = $this->protocol->get();

		$p?->basicPublish($this->channelId, $this->name, $routingKey, $mandatory, $immediate);

		$p?->contentHeader($this->channelId, ClassId::Basic, 0, strlen($message->body), $message->flags, $message->contentType, $message->contentEncoding, $message->headers, $message->deliveryMode, $message->priority, $message->correlationId, $message->replyTo, $message->expiration, $message->messageId, $message->timestamp, $message->typeHeader, $message->userId, $message->appId, $message->clusterId);
		$p?->contentBody($this->channelId, $message->body);
	}
}
