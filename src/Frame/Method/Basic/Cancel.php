<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Basic;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Cancel extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $consumerTag
	 * @var bool $noWait
	 */
	public function __construct(
		int $channelId,
		public readonly string $consumerTag,
		public readonly bool $noWait = false,
	) {
		parent::__construct($channelId, ClassId::Basic, Method::Cancel);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$channelId,
			$buffer->consumeString(),
			$buffer->consumeBits(1)[0],
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendString($this->consumerTag)
			->appendBits([$this->noWait]);
	}
}
