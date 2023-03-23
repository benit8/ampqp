<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Exchange;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Delete extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $exchange
	 * @var bool $ifUnused
	 * @var bool $noWait
	 */
	public function __construct(
		int $channelId,
		public readonly string $exchange,
		public readonly bool $ifUnused = true,
		public readonly bool $noWait = false,
	) {
		parent::__construct($channelId, ClassId::Exchange, Method::Delete);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		$reserved1 = $buffer->consumeInt16();
		$exchange = $buffer->consumeString();
		[$ifUnused, $noWait] = $buffer->consumeBits(2);

		return new self(
			$channelId,
			$exchange,
			$ifUnused, $noWait,
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt16(0 /* reserved1 */)
			->appendString($this->exchange)
			->appendBits([$this->ifUnused, $this->noWait]);
	}
}
