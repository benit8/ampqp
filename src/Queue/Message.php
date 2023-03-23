<?php declare(strict_types=1);

namespace Benit8\Ampqp\Queue;

use Benit8\Ampqp\Frame\ContentHeader;

/**
 * Application data passed from server to client.
 */
class Message
{
	public function __construct(
		private readonly \WeakReference $protocol,
		private readonly int $channelId,
		public readonly ContentHeader $headers,
		public readonly string $body,
		public readonly string $exchange,
		public readonly string $routingKey,
		public readonly ?string $consumerTag = null,
		public readonly ?int $deliveryTag = null,
		public readonly bool $redelivered = false,
		public readonly bool $returned = false,
		public readonly int $remainingCount = 0,
	) {
	}

	public function ack(bool $multiple = false): void
	{
		$this->protocol->get()?->basicAck($this->channelId, $this->deliveryTag, $multiple);
	}

	public function nack(bool $multiple = false, bool $requeue = true): void
	{
		$this->protocol->get()?->basicNack($this->channelId, $this->deliveryTag, $multiple, $requeue);
	}

	public function reject(bool $requeue = true): void
	{
		$this->protocol->get()?->basicReject($this->channelId, $this->deliveryTag, $requeue);
	}
}
