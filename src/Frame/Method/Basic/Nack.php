<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Basic;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Nack extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var int $deliveryTag
	 * @var bool $multiple
	 * @var bool $requeue
	 */
	public function __construct(
		int $channelId,
		public readonly int $deliveryTag = 0,
		public readonly bool $multiple = false,
		public readonly bool $requeue = true,
	) {
		parent::__construct($channelId, ClassId::Basic, Method::Nack);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		$deliveryTag = $buffer->consumeInt64();
		[$multiple, $requeue] = $buffer->consumeBits(2);

		return new self(
			$channelId,
			$deliveryTag,
			$multiple, $requeue,
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendInt64($this->deliveryTag)
			->appendBits([$this->multiple, $this->requeue]);
	}
}
