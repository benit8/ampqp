<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Connection;

use Benit8\Ampqp\Connection;
use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Blocked extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var string $reason
	 */
	public function __construct(
		public readonly string $reason = '',
	) {
		parent::__construct(Connection::CHANNEL_ID, ClassId::Connection, Method::Blocked);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self($buffer->consumeString());
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendString($this->reason);
	}
}
