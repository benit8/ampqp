<?php declare(strict_types=1);

namespace Benit8\Ampqp\Message;

enum PublishMode
{
	/** Regular AMQP guarantees of published messages delivery.  */
	case Regular;
	/** Messages are published after 'tx.commit'. */
	case Transactional;
	/** Broker sends asynchronously 'basic.ack's for delivered messages. */
	case Confirm;
}
