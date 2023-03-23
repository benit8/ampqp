<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Basic;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Deliver extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $consumerTag
	 */
	public function __construct(
		int $channelId,
		public readonly string $consumerTag,
		public readonly int $deliveryTag,
		public readonly bool $redelivered,
		public readonly string $exchange,
		public readonly string $routingKey,
	) {
		parent::__construct($channelId, ClassId::Basic, Method::Deliver);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$channelId,
			$buffer->consumeString(),
			$buffer->consumeInt64(),
			$buffer->consumeBits(1)[0],
			$buffer->consumeString(),
			$buffer->consumeString(),
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendString($this->consumerTag)
			->appendInt64($this->deliveryTag)
			->appendBits([$this->redelivered])
			->appendString($this->exchange)
			->appendString($this->routingKey);
	}
}
