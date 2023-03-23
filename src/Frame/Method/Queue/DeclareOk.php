<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Queue;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class DeclareOk extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $queue
	 * @var int $messageCount
	 * @var int $consumerCount
	 */
	public function __construct(
		int $channelId,
		public readonly string $queue,
		public readonly int $messageCount,
		public readonly int $consumerCount,
	) {
		parent::__construct($channelId, ClassId::Queue, Method::DeclareOk);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$channelId,
			$buffer->consumeString(),
			$buffer->consumeInt32(),
			$buffer->consumeInt32(),
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendString($this->queue)
			->appendInt32($this->messageCount)
			->appendInt32($this->consumerCount);
	}
}
