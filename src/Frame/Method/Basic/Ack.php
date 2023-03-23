<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Basic;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Ack extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var int $deliveryTag
	 * @var bool $multiple
	 */
	public function __construct(
		int $channelId,
		public readonly int $deliveryTag = 0,
		public readonly bool $multiple = false,
	) {
		parent::__construct($channelId, ClassId::Basic, Method::Ack);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$channelId,
			$buffer->consumeInt64(),
			$buffer->consumeBits(1)[0],
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt64($this->deliveryTag)
			->appendBits([$this->multiple]);
	}
}
