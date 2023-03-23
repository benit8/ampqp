<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame;
use Benit8\Ampqp\Frame\ContentHeader\Flags;
use Benit8\Ampqp\Frame\Method\ClassId;

final class ContentHeader extends Frame
{
	public function __construct(
		int $channelId,
		public readonly ClassId $classId,
		public readonly int $weight,
		public readonly int $bodySize,
		public readonly int $flags,
		public readonly ?string $contentType = null,
		public readonly ?string $contentEncoding = null,
		public readonly array $headers = [],
		public readonly ?int $deliveryMode = null,
		public readonly ?int $priority = null,
		public readonly ?string $correlationId = null,
		public readonly ?string $replyTo = null,
		public readonly ?string $expiration = null,
		public readonly ?string $messageId = null,
		public readonly ?\DateTimeInterface $timestamp = null,
		public readonly ?string $typeHeader = null,
		public readonly ?string $userId = null,
		public readonly ?string $appId = null,
		public readonly ?string $clusterId = null,
	) {
		parent::__construct(Type::Header, $channelId);
	}

	protected static function unpack(int $channelId, Buffer $buffer): self
	{
		return new self(
			$channelId,
			ClassId::from($buffer->consumeUint16()),
			$buffer->consumeUint16(),
			$buffer->consumeUint64(),
			$flags = $buffer->consumeUint16(),
			$flags & Flags::ContentType->value     ? $buffer->consumeString() : null,
			$flags & Flags::ContentEncoding->value ? $buffer->consumeString() : null,
			$flags & Flags::Headers->value         ? $buffer->consumeTable()  : [],
			$flags & Flags::DeliveryMode->value    ? $buffer->consumeUint8()  : null,
			$flags & Flags::Priority->value        ? $buffer->consumeUint8()  : null,
			$flags & Flags::CorrelationId->value   ? $buffer->consumeString() : null,
			$flags & Flags::ReplyTo->value         ? $buffer->consumeString() : null,
			$flags & Flags::Expiration->value      ? $buffer->consumeString() : null,
			$flags & Flags::MessageId->value       ? $buffer->consumeString() : null,
			$flags & Flags::Timestamp->value       ? $buffer->consumeTimestamp() : null,
			$flags & Flags::Type->value            ? $buffer->consumeString() : null,
			$flags & Flags::UserId->value          ? $buffer->consumeString() : null,
			$flags & Flags::AppId->value           ? $buffer->consumeString() : null,
			$flags & Flags::ClusterId->value       ? $buffer->consumeString() : null,
		);
	}

	protected function pack(): Buffer
	{
		$buffer = new Buffer();
		$buffer
			->appendUint16($this->classId->value)
			->appendUint16($this->weight)
			->appendUint64($this->bodySize);

		$buffer->appendUint16($this->flags);

		if ($this->flags & Flags::ContentType->value && $this->contentType !== null) {
			$buffer->appendString($this->contentType);
		}

		if ($this->flags & Flags::ContentEncoding->value && $this->contentEncoding !== null) {
			$buffer->appendString($this->contentEncoding);
		}

		if ($this->flags & Flags::Headers->value) {
			$buffer->appendTable($this->headers);
		}

		if ($this->flags & Flags::DeliveryMode->value && $this->deliveryMode !== null) {
			$buffer->appendUint8($this->deliveryMode);
		}

		if ($this->flags & Flags::Priority->value && $this->priority) {
			$buffer->appendUint8($this->priority);
		}

		if ($this->flags & Flags::CorrelationId->value && $this->correlationId !== null) {
			$buffer->appendString($this->correlationId);
		}

		if ($this->flags & Flags::ReplyTo->value && $this->replyTo !== null) {
			$buffer->appendString($this->replyTo);
		}

		if ($this->flags & Flags::Expiration->value && $this->expiration !== null) {
			$buffer->appendString($this->expiration);
		}

		if ($this->flags & Flags::MessageId->value && $this->messageId !== null) {
			$buffer->appendString($this->messageId);
		}

		if ($this->flags & Flags::Timestamp->value && $this->timestamp !== null) {
			$buffer->appendTimestamp($this->timestamp);
		}

		if ($this->flags & Flags::Type->value && $this->typeHeader !== null) {
			$buffer->appendString($this->typeHeader);
		}

		if ($this->flags & Flags::UserId->value && $this->userId !== null) {
			$buffer->appendString($this->userId);
		}

		if ($this->flags & Flags::AppId->value && $this->appId !== null) {
			$buffer->appendString($this->appId);
		}

		if ($this->flags & Flags::ClusterId->value && $this->clusterId !== null) {
			$buffer->appendString($this->clusterId);
		}

		return $buffer;
	}
}
