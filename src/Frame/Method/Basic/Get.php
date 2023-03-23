<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Basic;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Get extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $queue
	 * @var bool $noAck
	 */
	public function __construct(
		int $channelId,
		public readonly string $queue = '',
		public readonly bool $noAck = false,
	) {
		parent::__construct($channelId, ClassId::Basic, Method::Get);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		$reserved1 = $buffer->consumeInt16();

		return new self(
			$channelId,
			$buffer->consumeString(),
			$buffer->consumeBits(1)[0],
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt16(0 /* reserved1 */)
			->appendString($this->queue)
			->appendBits([$this->noAck]);
	}
}
