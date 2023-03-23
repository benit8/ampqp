<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Basic;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Publish extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $exchange
	 * @var string $routingKey
	 * @var bool $mandatory
	 * @var bool $immediate
	 */
	public function __construct(
		int $channelId,
		public readonly string $exchange = '',
		public readonly string $routingKey = '',
		public readonly bool $mandatory = false,
		public readonly bool $immediate = false,
	) {
		parent::__construct($channelId, ClassId::Basic, Method::Publish);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		$reserved1 = $buffer->consumeInt16();
		$exchange = $buffer->consumeString();
		$routingKey = $buffer->consumeString();
		[$mandatory, $immediate] = $buffer->consumeBits(2);

		return new self(
			$channelId,
			$exchange,
			$routingKey,
			$mandatory, $immediate,
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt16(0 /* reserved1 */)
			->appendString($this->exchange)
			->appendString($this->routingKey)
			->appendBits([$this->mandatory, $this->immediate]);
	}
}
