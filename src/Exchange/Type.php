<?php declare(strict_types=1);

namespace Benit8\Ampqp\Exchange;

enum Type: string
{
	case Direct  = 'direct';
	case Topic   = 'topic';
	case Fanout  = 'fanout';
	case Headers = 'headers';
}
