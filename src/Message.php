<?php declare(strict_types=1);

namespace Benit8\Ampqp;

use Benit8\Ampqp\Frame\ContentHeader\Flags;

/**
 * Application data passed from client to server.
 */
class Message
{
	public readonly int $flags;

	public function __construct(
		public readonly string $body = '',
		public readonly array $headers = [],
		public readonly ?string $contentType = null,
		public readonly ?string $contentEncoding = null,
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
		$flags = 0;
		if ($contentType !== null)     $flags |= Flags::ContentType->value;
		if ($contentEncoding !== null) $flags |= Flags::ContentEncoding->value;
		if (!empty($headers))          $flags |= Flags::Headers->value;
		if ($deliveryMode !== null)    $flags |= Flags::DeliveryMode->value;
		if ($priority !== null)        $flags |= Flags::Priority->value;
		if ($correlationId !== null)   $flags |= Flags::CorrelationId->value;
		if ($replyTo !== null)         $flags |= Flags::ReplyTo->value;
		if ($expiration !== null)      $flags |= Flags::Expiration->value;
		if ($messageId !== null)       $flags |= Flags::MessageId->value;
		if ($timestamp !== null)       $flags |= Flags::Timestamp->value;
		if ($typeHeader !== null)      $flags |= Flags::Type->value;
		if ($userId !== null)          $flags |= Flags::UserId->value;
		if ($appId !== null)           $flags |= Flags::AppId->value;
		if ($clusterId !== null)       $flags |= Flags::ClusterId->value;
		$this->flags = $flags;
	}
}
