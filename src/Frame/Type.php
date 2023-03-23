<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame;

enum Type: int
{
	case Method    = 1;
	case Header    = 2;
	case Body      = 3;
	case Heartbeat = 8;
}
