<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Basic;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class GetOk extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var int $messageCount
	 */
	public function __construct(
		int $channelId,
		public readonly int $deliveryTag,
		public readonly bool $redelivered,
		public readonly string $exchange,
		public readonly string $routingKey,
		public readonly int $messageCount,
	) {
		parent::__construct($channelId, ClassId::Basic, Method::GetOk);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$channelId,
			$buffer->consumeInt64(),
			$buffer->consumeBits(1)[0],
			$buffer->consumeString(),
			$buffer->consumeString(),
			$buffer->consumeInt32(),
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt64($this->deliveryTag)
			->appendBits([$this->redelivered])
			->appendString($this->exchange)
			->appendString($this->routingKey)
			->appendInt32($this->messageCount);
	}
}
