<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Queue;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class DeclareFrame extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $queue
	 * @var bool $passive
	 * @var bool $durable
	 * @var bool $exclusive
	 * @var bool $autoDelete
	 * @var bool $noWait
	 * @var array $arguments
	 */
	public function __construct(
		int $channelId,
		public readonly string $queue = '',
		public readonly bool $passive = false,
		public readonly bool $durable = false,
		public readonly bool $exclusive = false,
		public readonly bool $autoDelete = false,
		public readonly bool $noWait = false,
		public readonly array $arguments = [],
	) {
		parent::__construct($channelId, ClassId::Queue, Method::Declare);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		$reserved1 = $buffer->consumeInt16();
		$queue = $buffer->consumeString();
		[$passive, $durable, $exclusive, $autoDelete, $noWait] = $buffer->consumeBits(5);
		$arguments = $buffer->consumeTable();

		return new self(
			$channelId,
			$queue,
			$passive, $durable, $exclusive, $autoDelete, $noWait,
			$arguments,
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt16(0 /* reserved1 */)
			->appendString($this->queue)
			->appendBits([$this->passive, $this->durable, $this->exclusive, $this->autoDelete, $this->noWait])
			->appendTable($this->arguments);
	}
}
