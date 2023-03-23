<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Connection;

use Benit8\Ampqp\Connection;
use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class TuneOk extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelMax
	 * @var int $frameMax
	 * @var int $heartbeat
	 */
	public function __construct(
		public readonly int $channelMax = 0,
		public readonly int $frameMax = 0,
		public readonly int $heartbeat = 0,
	) {
		parent::__construct(Connection::CHANNEL_ID, ClassId::Connection, Method::TuneOk);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$buffer->consumeInt16(),
			$buffer->consumeInt32(),
			$buffer->consumeInt16(),
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt16($this->channelMax)
			->appendInt32($this->frameMax)
			->appendInt16($this->heartbeat);
	}
}
