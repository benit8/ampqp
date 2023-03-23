<?php declare(strict_types=1);

namespace Benit8\Ampqp\Queue;

class Consumer
{
	/**
	 * Not to be used directly. See Queue->consume().
	 */
	public function __construct(
		private readonly \WeakReference $protocol,
		private readonly int $channelId,
		private readonly \Closure $callback,
		public readonly string $tag,
	) {
		$this->protocol->get()?->on('message.' . $this->tag, $this->callback);
	}

	public function cancel(bool $noWait = false): void
	{
		$this->protocol->get()?->basicCancel($this->channelId, $this->tag, $noWait);
		$this->protocol->get()?->removeListener('message.' . $this->tag, $this->callback);
	}
}
