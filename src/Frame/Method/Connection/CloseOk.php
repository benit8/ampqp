<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Connection;

use Benit8\Ampqp\Connection;
use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class CloseOk extends MethodFrame
{
	public function __construct()
	{
		parent::__construct(Connection::CHANNEL_ID, ClassId::Connection, Method::CloseOk);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self();
	}
}
