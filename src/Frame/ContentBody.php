<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame;

final class ContentBody extends Frame
{
	public function __construct(
		int $channelId,
		public readonly Buffer $payload,
	) {
		parent::__construct(Type::Body, $channelId);
	}

	protected static function unpack(int $channelId, Buffer $payload): self
	{
		return new self($channelId, $payload);
	}

	protected function pack(): Buffer
	{
		return $this->payload;
	}
}
