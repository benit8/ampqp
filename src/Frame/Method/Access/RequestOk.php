<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Access;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class RequestOk extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var int $ticketId
	 */
	public function __construct(
		int $channelId,
		public readonly int $ticketId,
	) {
		parent::__construct($channelId, ClassId::Access, Method::RequestOk);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$channelId,
			$buffer->consumeInt16(),
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt16($this->ticketId);
	}
}
