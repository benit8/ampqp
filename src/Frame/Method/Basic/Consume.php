<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Basic;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Consume extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $queue
	 * @var string $consumerTag
	 * @var bool $noLocal
	 * @var bool $noAck
	 * @var bool $exclusive
	 * @var bool $noWait
	 * @var array $arguments
	 */
	public function __construct(
		int $channelId,
		public readonly string $queue = '',
		public readonly string $consumerTag = '',
		public readonly bool $noLocal = false,
		public readonly bool $noAck = false,
		public readonly bool $exclusive = false,
		public readonly bool $noWait = false,
		public readonly array $arguments = [],
	) {
		parent::__construct($channelId, ClassId::Basic, Method::Consume);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		$reserved1 = $buffer->consumeInt16();
		$queue = $buffer->consumeString();
		$consumerTag = $buffer->consumeString();
		[$noLocal, $noAck, $exclusive, $noWait] = $buffer->consumeBits(4);
		$arguments = $buffer->consumeTable();

		return new self(
			$channelId,
			$queue,
			$consumerTag,
			$noLocal, $noAck, $exclusive, $noWait,
			$arguments,
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt16(0 /* reserved1 */)
			->appendString($this->queue)
			->appendString($this->consumerTag)
			->appendBits([$this->noLocal, $this->noAck, $this->exclusive, $this->noWait])
			->appendTable($this->arguments);
	}
}
