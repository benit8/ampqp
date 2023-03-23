<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame;

use Benit8\Ampqp\Connection;
use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame;

final class Heartbeat extends Frame
{
	public function __construct()
	{
		parent::__construct(Type::Heartbeat, Connection::CHANNEL_ID);
	}

	protected static function unpack(int $channelId, Buffer $payload): self
	{
		return new self();
	}

	protected function pack(): Buffer
	{
		return new Buffer();
	}
}
