<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Queue;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Delete extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $queue
	 * @var bool $ifUnused
	 * @var bool $ifEmpty
	 * @var bool $noWait
	 */
	public function __construct(
		int $channelId,
		public readonly string $queue = '',
		public readonly bool $ifUnused = false,
		public readonly bool $ifEmpty = false,
		public readonly bool $noWait = false,
	) {
		parent::__construct($channelId, ClassId::Queue, Method::Delete);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		$reserved1 = $buffer->consumeInt16();
		$queue = $buffer->consumeString();
		[$ifUnused, $ifEmpty, $noWait] = $buffer->consumeBits(3);

		return new self(
			$channelId,
			$queue,
			$ifUnused, $ifEmpty, $noWait,
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt16(0 /* reserved1 */)
			->appendString($this->queue)
			->appendBits([$this->ifUnused, $this->ifEmpty, $this->noWait]);
	}
}
