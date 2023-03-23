<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Connection;

use Benit8\Ampqp\Connection;
use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame\Method as MethodFrame;
use Benit8\Ampqp\Frame\Method\ClassId;

final class StartOk extends MethodFrame
{
	/**
	 * Constructor.
	 *
	 * @var array $clientProperties
	 * @var string $mechanism
	 * @var string $response
	 * @var string $locale
	 */
	public function __construct(
		public readonly array $clientProperties = [],
		public readonly string $mechanism = 'AMQPLAIN',
		public readonly string $response = '',
		public readonly string $locale = 'en_US',
	) {
		parent::__construct(Connection::CHANNEL_ID, ClassId::Connection, Method::StartOk);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$buffer->consumeTable(),
			$buffer->consumeString(),
			$buffer->consumeText(),
			$buffer->consumeString(),
		);
	}

	protected function pack(): Buffer
	{
		return parent::pack()
			->appendTable($this->clientProperties)
			->appendString($this->mechanism)
			->appendText($this->response)
			->appendString($this->locale);
	}
}
