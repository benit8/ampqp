<?php declare(strict_types=1);

namespace Benit8\Ampqp;

/**
 * A bi-directional stream of communications between two AMQP peers. Channels are
 * multiplexed so that a single network connection can carry multiple channels.
 */
class Channel
{
	/**
	 * Constructor.
	 *
	 * @param \WeakReference $protocol The connection from which came the channel.
	 * @param int            $id
	 * @param float          $timeout
	 */
	public function __construct(
		private readonly \WeakReference $protocol,
		public readonly int $id,
		public readonly float $timeout,
	) {
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		// $this->close();
	}

	/**
	 * Request a channel close.
	 *
	 * @return void
	 */
	public function close(int $code = 0, string $reason = ''): void
	{
		$this->protocol->get()?->channelClose($this->id, $code, $reason);
	}

	/**
	 * Enable/disable flow from peer.
	 *
	 * @return bool
	 */
	public function flow(bool $active): bool
	{
		return $this->protocol->get()?->channelFlow($this->id, $active)->active;
	}

	/**
	 * Quality Of Service.
	 *
	 * @return void
	 */
	public function qos(int $prefetchSize = 0, int $prefetchCount = 0, bool $global = false): void
	{
		$this->protocol->get()?->basicQos($this->id, $prefetchSize, $prefetchCount, $global);
	}

	/**
	 * Declare an exchange.
	 *
	 * @return Exchange
	 */
	public function exchange(
		string $name,
		Exchange\Type $type = Exchange\Type::Direct,
		bool $passive = false,
		bool $durable = false,
		bool $autoDelete = false,
		bool $internal = false,
		bool $noWait = false,
		array $arguments = [],
	): Exchange {
		$this->protocol->get()?->exchangeDeclare($this->id, $name, $type, $passive, $durable, $autoDelete, $internal, $noWait, $arguments);
		return new Exchange($this->protocol, $this->id, $name, $type, $passive, $durable, $autoDelete, $internal, $noWait, $arguments);
	}

	/**
	 * Declare a queue.
	 *
	 * @return Queue
	 */
	public function queue(
		string $name = '',
		bool $passive = false,
		bool $durable = false,
		bool $exclusive = false,
		bool $autoDelete = false,
		bool $noWait = false,
		array $arguments = [],
	): Queue {
		$ok = $this->protocol->get()?->queueDeclare($this->id, $name, $passive, $durable, $exclusive, $autoDelete, $noWait, $arguments);
		return new Queue($this->protocol, $this->id, $ok->queue, $passive, $durable, $exclusive, $autoDelete, $noWait, $arguments);
	}
}
