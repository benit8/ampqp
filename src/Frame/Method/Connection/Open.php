<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Connection;

use Benit8\Ampqp\Connection;
use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Open extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var string $virtualHost
	 * @var string $capabilities
	 * @var bool $insist
	 */
	public function __construct(
		public readonly string $virtualHost = '/',
		public readonly string $capabilities = '',
		public readonly bool $insist = false,
	) {
		parent::__construct(Connection::CHANNEL_ID, ClassId::Connection, Method::Open);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$buffer->consumeString(),
			$buffer->consumeString(),
			[$buffer->consumeBits(1)]
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendString($this->virtualHost)
			->appendString($this->capabilities)
			->appendBits([$this->insist]);
	}
}
