<?php declare(strict_types=1);

namespace Benit8\Ampqp\Connection;

final class Config
{
	/**
	 * Constructor.
	 */
	public function __construct(
		public readonly string $host = 'localhost',
		public readonly int    $port = 5672,
		public readonly string $user = 'guest',
		public readonly string $password = 'guest',
		public readonly string $vhost = '/',
		public readonly int    $maxChannels = 0xFFFF,
		public readonly int    $maxFrameSize = 0xFFFF,
		public readonly int    $heartbeatTimeout = 60,
		public readonly float  $channelTimeout = 0.0,
	) {
	}
}
