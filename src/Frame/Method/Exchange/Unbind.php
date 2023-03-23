<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Exchange;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Unbind extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $destination
	 * @var string $source
	 * @var string $routingKey
	 * @var bool $noWait
	 * @var array $arguments
	 */
	public function __construct(
		int $channelId,
		public readonly string $destination,
		public readonly string $source,
		public readonly string $routingKey = '',
		public readonly bool $noWait = false,
		public readonly array $arguments = [],
	) {
		parent::__construct($channelId, ClassId::Exchange, Method::Unbind);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		$reserved1 = $buffer->consumeInt16();

		return new self(
			$channelId,
			$buffer->consumeString(),
			$buffer->consumeString(),
			$buffer->consumeString(),
			$buffer->consumeBits(1)[0],
			$buffer->consumeTable(),
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt16(0 /* reserved1 */)
			->appendString($this->destination)
			->appendString($this->source)
			->appendString($this->routingKey)
			->appendBits([$this->noWait])
			->appendTable($this->arguments);
	}
}
