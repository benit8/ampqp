<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Exchange;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Exchange\Type;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class DeclareFrame extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $exchange
	 * @var Type $type
	 * @var bool $passive
	 * @var bool $durable
	 * @var bool $autoDelete
	 * @var bool $internal
	 * @var bool $noWait
	 * @var array $arguments
	 */
	public function __construct(
		int $channelId,
		public readonly string $exchange,
		public readonly Type $exchangeType = Type::Direct,
		public readonly bool $passive = false,
		public readonly bool $durable = false,
		public readonly bool $autoDelete = false,
		public readonly bool $internal = false,
		public readonly bool $noWait = false,
		public readonly array $arguments = [],
	) {
		parent::__construct($channelId, ClassId::Exchange, Method::Declare);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		$reserved1 = $buffer->consumeInt16();
		$exchange = $buffer->consumeString();
		$exchangeType = $buffer->consumeString();
		[$passive, $durable, $autoDelete, $internal, $noWait] = $buffer->consumeBits(5);
		$arguments = $buffer->consumeTable();

		return new self(
			$channelId,
			$exchange,
			Type::from($exchangeType),
			$passive, $durable, $autoDelete, $internal, $noWait,
			$arguments,
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt16(0 /* reserved1 */)
			->appendString($this->exchange)
			->appendString($this->exchangeType->value)
			->appendBits([$this->passive, $this->durable, $this->autoDelete, $this->internal, $this->noWait])
			->appendTable($this->arguments);
	}
}
