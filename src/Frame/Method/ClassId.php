<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method;

enum ClassId: int
{
	case Connection = 10;
	case Channel    = 20;
	case Access     = 30;
	case Exchange   = 40;
	case Queue      = 50;
	case Basic      = 60;
	case Confirm    = 85;
	case Tx         = 90;
}
