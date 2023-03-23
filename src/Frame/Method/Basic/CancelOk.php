<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Basic;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class CancelOk extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $consumerTag
	 */
	public function __construct(
		int $channelId,
		public readonly string $consumerTag,
	) {
		parent::__construct($channelId, ClassId::Basic, Method::CancelOk);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$channelId,
			$buffer->consumeString(),
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendString($this->consumerTag);
	}
}
