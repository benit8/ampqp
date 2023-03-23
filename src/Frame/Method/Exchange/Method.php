<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Exchange;

enum Method: int
{
	case Declare   = 10;
	case DeclareOk = 11;
	case Delete    = 20;
	case DeleteOk  = 21;
	case Bind      = 30;
	case BindOk    = 31;
	case Unbind    = 40;
	case UnbindOk  = 51;
}
