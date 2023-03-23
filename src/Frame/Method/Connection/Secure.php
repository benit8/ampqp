<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Connection;

use Benit8\Ampqp\Connection;
use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Secure extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var string $challenge
	 */
	public function __construct(
		public readonly string $challenge,
	) {
		parent::__construct(Connection::CHANNEL_ID, ClassId::Connection, Method::Secure);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self($buffer->consumeText());
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendText($this->challenge);
	}
}
