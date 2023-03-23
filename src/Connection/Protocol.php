<?php declare(strict_types=1);

namespace Benit8\Ampqp\Connection;

use Amp\DeferredFuture;
use Amp\Socket\Socket;
use Benit8\Ampqp\Exchange\Type as ExchangeType;
use Benit8\Ampqp\Frame;
use Benit8\Ampqp\Frame\Method\Access;
use Benit8\Ampqp\Frame\Method\Basic;
use Benit8\Ampqp\Frame\Method\Channel;
use Benit8\Ampqp\Frame\Method\ClassId;
use Benit8\Ampqp\Frame\Method\Confirm;
use Benit8\Ampqp\Frame\Method\Connection;
use Benit8\Ampqp\Frame\Method\Exchange;
use Benit8\Ampqp\Frame\Method\Queue;
use Benit8\Ampqp\Frame\Method\Tx;
use Benit8\Ampqp\Queue\Message;
use Benit8\EventEmitter\EventEmitterTrait;

/**
 * This class should interact with frames, implementing the AMQP methods and
 * their replies.
 */
final class Protocol
{
	use EventEmitterTrait;

	/** @var string Version 0-9-1 */
	private const HEADER = "AMQP\x00\x00\x09\x01";

	/** @var Frame[] */
	private array $frameQueue = [];

	/** @var array{int, class-string[], DeferredFuture}[] */
	private array $awaiting = [];

	private ?object $properties = null;

	/**
	 * Constructor.
	 */
	public function __construct(
		private readonly Socket $socket,
	) {
	}

	/**
	 * Handle all the handshakes required to open a connection.
	 *
	 * @param Config $config
	 *
	 * @return Connection\Config
	 */
	public function connect(Config $config): Connection\OpenOk
	{
		$this->socket->write(self::HEADER);

		$this->on('frame', function ($frame): void {
			foreach ($this->awaiting as $i => [$id, $frameClasses, $deferred]) {
				if ($frame->channelId === $id && in_array($frame::class, $frameClasses)) {
					$deferred->complete($frame);
					unset($this->awaiting[$i]);
					return;
				}
			}

			$this->frameQueue[] = $frame;
		});

		$this->on(Basic\Deliver::class, function (Basic\Deliver $frame): void {
			\Amp\async(function () use ($frame) {
				$message = $this->awaitMessage($frame->channelId, $frame);
				$this->emit('message.' . $frame->consumerTag, $message);
			});
		});

		$onOpen = new DeferredFuture();

		$this->once(Connection\Start::class, fn ($f) => $this->onConnectionStart($f, $config, $onOpen));

		return $onOpen->getFuture()->await();
	}

	/**
	 * Requets to close the connection.
	 *
	 * @return void
	 */
	public function close(int $code, string $reason): void
	{
		$this->send(new Connection\Close($code, $reason));
	}

	/**
	 * @param Connection\Start $frame
	 * @param Config $config
	 * @param DeferredFuture $onOpen
	 *
	 * @return void
	 */
	private function onConnectionStart(Connection\Start $frame, Config $config, DeferredFuture $onOpen): void
	{
		$this->properties = (object) $frame->serverProperties;

		$response = (new Buffer())
			->appendTable(['LOGIN' => $config->user, 'PASSWORD' => $config->password])
			->discard(4);

		$this->once(Connection\Tune::class, fn ($f) => $this->onConnectionTune($f, $config, $onOpen));

		$this->send(new Connection\StartOk(response: $response->bytes()));
	}

	/**
	 * @param Connection\Tune $frame
	 * @param Config $config
	 * @param DeferredFuture $onOpen
	 *
	 * @return void
	 */
	private function onConnectionTune(Connection\Tune $frame, Config $config, DeferredFuture $onOpen): void
	{
		$maxChannel = min($config->maxChannels,   $frame->channelMax);
		$maxFrame   = min($config->maxFrameSize,  $frame->frameMax);
		$heartbeat  = min($config->heartbeatRate, $frame->heartbeat);

		$this->properties->maxChannels = $maxChannel;
		$this->properties->maxFrameSize = $maxFrame;

		// $this->setupHeartbeat($heartbeat);

		$this->send(new Connection\TuneOk($maxChannel, $maxFrame, $heartbeat));

		$this->once(Connection\OpenOk::class, function ($f) use ($onOpen): void {
			$this->once(Connection\Close::class, $this->onConnectionClose(...));
			$onOpen->complete($f);
		});

		$this->send(new Connection\Open($config->vhost));
	}

	/**
	 * @param Connection\Close $frame
	 *
	 * @return void
	 */
	private function onConnectionClose(Connection\Close $frame): void
	{
		$this->send(new Connection\CloseOk());
		$this->emit('close');
	}

	/// Content header & body -------------------------------------------------

	/**
	 * FIXME: Refactor this, or not; it's an internal class.
	 */
	public function contentHeader(int $id, ClassId $classId, int $weight, int $bodySize, int $flags, ?string $contentType, ?string $contentEncoding, array $headers, ?int $deliveryMode, ?int $priority, ?string $correlationId, ?string $replyTo, ?string $expiration, ?string $messageId, ?\DateTimeInterface $timestamp, ?string $typeHeader, ?string $userId, ?string $appId, ?string $clusterId): void
	{
		$this->send(new Frame\ContentHeader($id, $classId, $weight, $bodySize, $flags, $contentType, $contentEncoding, $headers, $deliveryMode, $priority, $correlationId, $replyTo, $expiration, $messageId, $timestamp, $typeHeader, $userId, $appId, $clusterId));
	}

	public function contentBody(int $id, string $body): void
	{
		$chunks = array_filter(str_split($body, $this->properties->maxFrameSize));
		foreach ($chunks as $chunk) {
			$this->send(new Frame\ContentBody($id, new Buffer($chunk)));
		}
	}

	/// Channels --------------------------------------------------------------

	public function channelOpen(int $id): Channel\OpenOk
	{
		$this->send(new Channel\Open($id));
		return $this->await($id, Channel\OpenOk::class);
	}

	public function channelClose(int $id, int $code, string $reason): Channel\CloseOk
	{
		$this->send(new Channel\Close($id, $code, $reason));
		return $this->await($id, Channel\CloseOk::class);
	}

	public function channelFlow(int $id, bool $active): Channel\FlowOk
	{
		$this->send(new Channel\Flow($id, $active));
		return $this->await($id, Channel\FlowOk::class);
	}

	/// Access ----------------------------------------------------------------

	public function accessRequest(int $id, string $realm, bool $exclusive, bool $passive, bool $active, bool $write, bool $read): Access\RequestOk
	{
		$this->send(new Access\Request($id, $realm, $exclusive, $passive, $active, $write, $read));
		return $this->await($id, Access\RequestOk::class);
	}

	/// Exchange --------------------------------------------------------------

	public function exchangeDeclare(int $id, string $name, ExchangeType $type, bool $passive, bool $durable, bool $autoDelete, bool $internal, bool $noWait, array $arguments): Exchange\DeclareOk
	{
		$this->send(new Exchange\DeclareFrame($id, $name, $type, $passive, $durable, $autoDelete, $internal, $noWait, $arguments));
		return $this->await($id, Exchange\DeclareOk::class);
	}

	public function exchangeDelete(int $id, string $name, bool $ifUnused, bool $noWait): Exchange\DeleteOk
	{
		$this->send(new Exchange\Delete($id, $name, $ifUnused, $noWait));
		return $this->await($id, Exchange\DeleteOk::class);
	}

	public function exchangeBind(int $id, string $destination, string $source, string $routingKey, bool $noWait, array $arguments): Exchange\BindOk
	{
		$this->send(new Exchange\Bind($id, $destination, $source, $routingKey, $noWait, $arguments));
		return $this->await($id, Exchange\BindOk::class);
	}

	public function exchangeUnbind(int $id, string $destination, string $source, string $routingKey, bool $noWait, array $arguments): Exchange\UnbindOk
	{
		$this->send(new Exchange\Unbind($id, $destination, $source, $routingKey, $noWait, $arguments));
		return $this->await($id, Exchange\UnbindOk::class);
	}

	/// Queue -----------------------------------------------------------------

	public function queueDeclare(int $id, string $name, bool $passive, bool $durable, bool $exclusive, bool $autoDelete, bool $noWait, array $arguments): Queue\DeclareOk
	{
		$this->send(new Queue\DeclareFrame($id, $name, $passive, $durable, $exclusive, $autoDelete, $noWait, $arguments));
		return $this->await($id, Queue\DeclareOk::class);
	}

	public function queueBind(int $id, string $name, string $exchange, string $routingKey, bool $noWait, array $arguments): Queue\BindOk
	{
		$this->send(new Queue\Bind($id, $name, $exchange, $routingKey, $noWait, $arguments));
		return $this->await($id, Queue\BindOk::class);
	}

	public function queuePurge(int $id, string $name, bool $noWait): Queue\PurgeOk
	{
		$this->send(new Queue\Purge($id, $name, $noWait));
		return $this->await($id, Queue\PurgeOk::class);
	}

	public function queueDelete(int $id, string $name, bool $ifUnused, bool $ifEmpty, bool $noWait): Queue\DeleteOk
	{
		$this->send(new Queue\Delete($id, $name, $ifUnused, $ifEmpty, $noWait));
		return $this->await($id, Queue\DeleteOk::class);
	}

	public function queueUnbind(int $id, string $name, string $exchange, string $routingKey, array $arguments): Queue\UnbindOk
	{
		$this->send(new Queue\Unbind($id, $name, $exchange, $routingKey, $arguments));
		return $this->await($id, Queue\UnbindOk::class);
	}

	/// Basic -----------------------------------------------------------------

	public function basicQos(int $id, int $prefetchSize, int $prefetchCount, bool $global): Basic\QosOk
	{
		$this->send(new Basic\Qos($id, $prefetchSize, $prefetchCount, $global));
		return $this->await($id, Basic\QosOk::class);
	}

	public function basicConsume(int $id, string $queue, string $consumerTag, bool $noLocal, bool $noAck, bool $exclusive, bool $noWait, array $arguments): Basic\ConsumeOk
	{
		$this->send(new Basic\Consume($id, $queue, $consumerTag, $noLocal, $noAck, $exclusive, $noWait, $arguments));
		return $this->await($id, Basic\ConsumeOk::class);
	}

	public function basicCancel(int $id, string $consumerTag, bool $noWait): Basic\CancelOk
	{
		$this->send(new Basic\Cancel($id, $consumerTag, $noWait));
		return $this->await($id, Basic\CancelOk::class);
	}

	public function basicPublish(int $id, string $exchange, string $routingKey, bool $mandatory, bool $immediate): void
	{
		$this->send(new Basic\Publish($id, $exchange, $routingKey, $mandatory, $immediate));
	}

	public function basicGet(int $id, string $queue, bool $noAck): ?Message
	{
		$this->send(new Basic\Get($id, $queue, $noAck));

		$frame = $this->await($id, [Basic\GetOk::class, Basic\GetEmpty::class]);
		if ($frame instanceof Basic\GetEmpty) {
			return null;
		}

		return $this->awaitMessage($id, $frame);
	}

	public function basicAck(int $id, int $deliveryTag, bool $multiple): void
	{
		$this->send(new Basic\Ack($id, $deliveryTag, $multiple));
	}

	public function basicReject(int $id, int $deliveryTag, bool $requeue): void
	{
		$this->send(new Basic\Reject($id, $deliveryTag, $requeue));
	}

	/**
	 * This method is deprecated in favor of @basicRecover().
	 */
	public function basicRecoverAsync(int $id, bool $requeue): Basic\RecoverOk
	{
		$this->send(new Basic\RecoverAsync($id, $requeue));
		return $this->await($id, Basic\RecoverOk::class);
	}

	public function basicRecover(int $id, bool $requeue): Basic\RecoverOk
	{
		$this->send(new Basic\Recover($id, $requeue));
		return $this->await($id, Basic\RecoverOk::class);
	}

	public function basicNack(int $id, int $deliveryTag, bool $multiple, bool $requeue): void
	{
		$this->send(new Basic\Nack($id, $deliveryTag, $multiple, $requeue));
	}

	/// Confirm ---------------------------------------------------------------

	public function confirmSelect(int $id, bool $noWait): Confirm\SelectOk
	{
		$this->send(new Confirm\Select($id, $noWait));
		return $this->await($id, Confirm\SelectOk::class);
	}

	/// Tx (transaction) ------------------------------------------------------

	public function txSelect(int $id): Tx\SelectOk
	{
		$this->send(new Tx\Select($id));
		return $this->await($id, Tx\SelectOk::class);
	}

	public function txCommit(int $id): Tx\CommitOk
	{
		$this->send(new Tx\Commit($id));
		return $this->await($id, Tx\CommitOk::class);
	}

	public function txRollback(int $id): Tx\RollbackOk
	{
		$this->send(new Tx\Rollback($id));
		return $this->await($id, Tx\RollbackOk::class);
	}

	/// Utils -----------------------------------------------------------------

	/**
	 * @param Frame $frame
	 *
	 * @return void
	 */
	private function send(Frame $frame): void
	{
		$buffer = $frame->serialize()->flush();
		$this->socket->write($buffer);
	}

	/**
	 * @param int                         $id           The Channel ID to await for.
	 * @param class-string|class-string[] $frameClasses
	 *
	 * @return Frame
	 */
	private function await(int $id, string|array $frameClasses): Frame
	{
		if (is_string($frameClasses)) {
			$frameClasses = [$frameClasses];
		}

		// Look through the queue'd frames
		foreach ($this->frameQueue as $i => $f) {
			if ($f->channelId === $id && in_array($f::class, $frameClasses)) {
				unset($this->frameQueue[$i]);
				return $f;
			}
		}

		// Await and scan the next frames
		$deferred = new DeferredFuture();
		$this->awaiting[] = [$id, $frameClasses, $deferred];
		return $deferred->getFuture()->await();
	}

	/**
	 * Await the different parts of a Message and construct it.
	 *
	 * @param int                       $id
	 * @param Basic\GetOk|Basic\Deliver $frame
	 *
	 * @return Message
	 */
	private function awaitMessage(int $id, Basic\GetOk|Basic\Deliver $frame): Message
	{
		$header = $this->await($id, Frame\ContentHeader::class);

		$buffer = new Buffer();
		for ($remaining = $header->bodySize; $remaining > 0;) {
			$body = $this->await($id, Frame\ContentBody::class);
			$buffer->append((string)$body->payload);

			$remaining -= (int)$body->payload->size();
			if ($remaining < 0) {
				throw new \RuntimeException(sprintf('Body overflow of %d bytes', -$remaining));
			}
		}

		return new Message(
			\WeakReference::create($this),
			$id,
			$header,
			$buffer->flush(),
			$frame->exchange,
			$frame->routingKey,
			$frame?->consumerTag,
			$frame->deliveryTag,
			$frame->redelivered,
			false,
			$frame?->messageCount ?? 0,
		);
	}
}
