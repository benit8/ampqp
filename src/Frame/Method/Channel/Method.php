<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Channel;

enum Method: int
{
	case Open    = 10;
	case OpenOk  = 11;
	case Flow    = 20;
	case FlowOk  = 21;
	case Close   = 40;
	case CloseOk = 41;
}
