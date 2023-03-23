<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Connection;

use Benit8\Ampqp\Connection;
use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class OpenOk extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var string $knownHosts
	 */
	public function __construct(
		public readonly string $knownHosts = '',
	) {
		parent::__construct(Connection::CHANNEL_ID, ClassId::Connection, Method::OpenOk);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self($buffer->consumeString());
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendString($this->knownHosts);
	}
}
