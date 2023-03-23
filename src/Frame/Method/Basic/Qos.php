<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Basic;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Qos extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var int $prefetchSize
	 * @var int $prefetchCount
	 * @var bool $global
	 */
	public function __construct(
		int $channelId,
		public readonly int $prefetchSize = 0,
		public readonly int $prefetchCount = 0,
		public readonly bool $global = false,
	) {
		parent::__construct($channelId, ClassId::Basic, Method::Qos);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$channelId,
			$buffer->consumeInt32(),
			$buffer->consumeInt16(),
			$buffer->consumeBits(1)[0],
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt32($this->prefetchSize)
			->appendInt16($this->prefetchCount)
			->appendBits([$this->global]);
	}
}
