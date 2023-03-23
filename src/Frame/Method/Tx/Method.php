<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame\Method\Tx;

enum Method: int
{
	case Select     = 10;
	case SelectOk   = 11;
	case Commit     = 20;
	case CommitOk   = 21;
	case Rollback   = 30;
	case RollbackOk = 31;
}
