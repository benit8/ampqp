<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Basic;

enum Method: int
{
	case Qos          = 10;
	case QosOk        = 11;
	case Consume      = 20;
	case ConsumeOk    = 21;
	case Cancel       = 30;
	case CancelOk     = 31;
	case Publish      = 40;
	case Return       = 50;
	case Deliver      = 60;
	case Get          = 70;
	case GetOk        = 71;
	case GetEmpty     = 72;
	case Ack          = 80;
	case Reject       = 90;
	case RecoverAsync = 100;
	case Recover      = 110;
	case RecoverOk    = 111;
	case Nack         = 120;
}
