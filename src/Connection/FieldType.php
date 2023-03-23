<?php declare(strict_types=1);

namespace Benit8\Ampqp\Connection;

enum FieldType: int
{
	case Boolean        = 0x74; // 't'
	case ShortShortInt  = 0x62; // 'b'
	case ShortShortUint = 0x42; // 'B'
	case ShortInt       = 0x55; // 'U'
	case ShortUint      = 0x75; // 'u'
	case LongInt        = 0x49; // 'I'
	case LongUint       = 0x69; // 'i'
	case LongLongInt    = 0x4C; // 'L'
	case LongLongUint   = 0x6C; // 'l'
	case Float          = 0x66; // 'f'
	case Double         = 0x64; // 'd'
	case Decimal        = 0x44; // 'D'
	case ShortString    = 0x73; // 's'
	case LongString     = 0x53; // 'S'
	case Array          = 0x41; // 'A'
	case Timestamp      = 0x54; // 'T'
	case Table          = 0x46; // 'F'
	case Null           = 0x56; // 'V'
}
