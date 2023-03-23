<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Connection;

use Benit8\Ampqp\Connection;
use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class Start extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var int $versionMajor
	 * @var int $versionMinor
	 * @var array{
	 *     platform: string,
	 *     product: string,
	 *     version: string,
	 *     capabilities: array<string, bool>
	 * } $serverProperties
	 * @var string $mechanisms
	 * @var string $locales
	 */
	public function __construct(
		public readonly int $versionMajor = 0,
		public readonly int $versionMinor = 9,
		public readonly array $serverProperties = ['platform' => '', 'product' => '', 'version' => '', 'capabilities' => []],
		public readonly string $mechanisms = 'AMQPLAIN',
		public readonly string $locales = 'en_US',
	) {
		parent::__construct(Connection::CHANNEL_ID, ClassId::Connection, Method::Start);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$buffer->consumeUint8(),
			$buffer->consumeUint8(),
			$buffer->consumeTable(),
			$buffer->consumeText(),
			$buffer->consumeText(),
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendUint8($this->versionMajor)
			->appendUint8($this->versionMinor)
			->appendTable($this->serverProperties)
			->appendText($this->mechanisms)
			->appendText($this->locales);
	}
}
