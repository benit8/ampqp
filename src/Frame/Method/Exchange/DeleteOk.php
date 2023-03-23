<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Exchange;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class DeleteOk extends MethodFrame
{
	public function __construct(int $channelId)
	{
		parent::__construct($channelId, ClassId::Exchange, Method::DeleteOk);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self($channelId);
	}
}
