<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Queue;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class PurgeOk extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var int $messageCount
	 */
	public function __construct(
		int $channelId,
		public readonly int $messageCount,
	) {
		parent::__construct($channelId, ClassId::Queue, Method::PurgeOk);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self($channelId, $buffer->consumeInt32());
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt32($this->messageCount);
	}
}
