<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\ContentHeader;

enum Flags: int
{
	case ContentType     = 0x8000;
	case ContentEncoding = 0x4000;
	case Headers         = 0x2000;
	case DeliveryMode    = 0x1000;
	case Priority        = 0x0800;
	case CorrelationId   = 0x0400;
	case ReplyTo         = 0x0200;
	case Expiration      = 0x0100;
	case MessageId       = 0x0080;
	case Timestamp       = 0x0040;
	case Type            = 0x0020;
	case UserId          = 0x0010;
	case AppId           = 0x0008;
	case ClusterId       = 0x0004;
}
