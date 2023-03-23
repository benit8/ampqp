<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Channel;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Close extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var int $replyCode
	 * @var string $replyText
	 * @var int $closeClassId
	 * @var int $closeMethodId
	 */
	public function __construct(
		int $channelId,
		public readonly int $replyCode = 0,
		public readonly string $replyText = '',
		public readonly int $closeClassId = 0,
		public readonly int $closeMethodId = 0,
	) {
		parent::__construct($channelId, ClassId::Channel, Method::Close);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$channelId,
			$buffer->consumeInt16(),
			$buffer->consumeString(),
			$buffer->consumeInt16(),
			$buffer->consumeInt16(),
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt16($this->replyCode)
			->appendString($this->replyText)
			->appendInt16($this->closeClassId)
			->appendInt16($this->closeMethodId);
	}
}
