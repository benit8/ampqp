<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Access;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Request extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $channelId
	 * @var string $realm
	 * @var bool $exclusive
	 * @var bool $passive
	 * @var bool $active
	 * @var bool $write
	 * @var bool $read
	 */
	public function __construct(
		int $channelId,
		public readonly string $realm = '/data',
		public readonly bool $exclusive = false,
		public readonly bool $passive = true,
		public readonly bool $active = true,
		public readonly bool $write = true,
		public readonly bool $read = true,
	) {
		parent::__construct($channelId, ClassId::Access, Method::Request);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		$realm = $buffer->consumeString();
		[$exclusive, $passive, $active, $write, $read] = $buffer->consumeBits(5);

		return new self(
			$channelId,
			$realm,
			$exclusive, $passive, $active, $write, $read,
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendString($this->realm)
			->appendBits([$this->exclusive, $this->passive, $this->active, $this->write, $this->read]);
	}
}
