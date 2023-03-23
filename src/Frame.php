<?php declare(strict_types=1);

namespace Benit8\Ampqp;

use Benit8\Ampqp\Connection\Buffer;

/**
 * A formally-defined package of connection data. Frames are always written and read
 * contiguously - as a single unit - on the connection.
 */
abstract class Frame
{
	/** @var int sizeof(u8) + sizeof(u16) + sizeof(u32) */
	public const HEADER_SIZE = 7;

	/** @var int */
	public const END_MAGIC = 206;

	/**
	 * Constructor.
	 *
	 * @var Frame\Type $type
	 * @var int $channelId
	 */
	public function __construct(
		public readonly Frame\Type $type,
		public readonly int $channelId,
	) {
	}

	abstract protected static function unpack(int $channelId, Connection\Buffer $payload): self;
	abstract protected function pack(): Connection\Buffer;

	public static function deserialize(Frame\Type $type, int $channelId, Connection\Buffer $payload): static
	{
		return match ($type) {
			Frame\Type::Method => Frame\Method::unpack($channelId, $payload),
			Frame\Type::Header => Frame\ContentHeader::unpack($channelId, $payload),
			Frame\Type::Body   => Frame\ContentBody::unpack($channelId, $payload),
			Frame\Type::Heartbeat => new Frame\Heartbeat(),
		};
	}

	public function serialize(): Buffer
	{
		$buffer = new Buffer();
		$payload = static::pack();

		return $buffer
			->appendUint8($this->type->value)
			->appendUint16($this->channelId)
			->appendUint32($payload->size())
			->append($payload)
			->appendUint8(self::END_MAGIC);
	}
}
