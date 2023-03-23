<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Queue;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Bind extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $queue
	 * @var string $exchange
	 * @var string $routingKey
	 * @var bool $noWait
	 * @var array $arguments
	 */
	public function __construct(
		int $channelId,
		public readonly string $queue,
		public readonly string $exchange,
		public readonly string $routingKey = '',
		public readonly bool $noWait = false,
		public readonly array $arguments = [],
	) {
		parent::__construct($channelId, ClassId::Queue, Method::Bind);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		$reserved1 = $buffer->consumeInt16();

		return new self(
			$channelId,
			$buffer->consumeString(),
			$buffer->consumeString(),
			$buffer->consumeString(),
			$buffer->consumeBits(1)[0],
			$buffer->consumeTable(),
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt16(0 /* reserved1 */)
			->appendString($this->queue)
			->appendString($this->exchange)
			->appendString($this->routingKey)
			->appendBits([$this->noWait])
			->appendTable($this->arguments);
	}
}
