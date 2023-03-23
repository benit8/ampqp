<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Confirm;

enum Method: int
{
	case Select   = 10;
	case SelectOk = 11;
}
