<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Connection;

enum Method: int
{
	case Start     = 10;
	case StartOk   = 11;
	case Secure    = 20;
	case SecureOk  = 21;
	case Tune      = 30;
	case TuneOk    = 31;
	case Open      = 40;
	case OpenOk    = 41;
	case Close     = 50;
	case CloseOk   = 51;
	case Blocked   = 60;
	case Unblocked = 61;
}
