<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Access;

enum Method: int
{
	case Request   = 10;
	case RequestOk = 11;
}
