<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Basic;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class ReturnFrame extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var int $replyCode
	 * @var string $replyText
	 * @var string $exchange
	 * @var string $routingKey
	 */
	public function __construct(
		int $channelId,
		public readonly int $replyCode,
		public readonly string $replyText,
		public readonly string $exchange,
		public readonly string $routingKey,
	) {
		parent::__construct($channelId, ClassId::Basic, Method::Return);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$channelId,
			$buffer->consumeInt16(),
			$buffer->consumeString(),
			$buffer->consumeString(),
			$buffer->consumeString(),
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt16($this->replyCode)
			->appendString($this->replyText)
			->appendString($this->exchange)
			->appendString($this->routingKey);
	}
}
