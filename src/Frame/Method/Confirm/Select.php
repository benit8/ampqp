<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Confirm;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Select extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var bool $noWait
	 */
	public function __construct(
		int $channelId,
		public readonly bool $noWait = false,
	) {
		parent::__construct($channelId, ClassId::Confirm, Method::Select);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$channelId,
			$buffer->consumeBits(1)[0],
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendBits([$this->noWait]);
	}
}
