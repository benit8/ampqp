<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Queue;

enum Method: int
{
	case Declare   = 10;
	case DeclareOk = 11;
	case Bind      = 20;
	case BindOk    = 21;
	case Purge     = 30;
	case PurgeOk   = 31;
	case Delete    = 40;
	case DeleteOk  = 41;
	case Unbind    = 50;
	case UnbindOk  = 51;
}
