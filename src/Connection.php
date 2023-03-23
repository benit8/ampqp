<?php declare(strict_types=1);

namespace Benit8\Ampqp;

use Amp\DeferredFuture;
use Amp\Parser\Parser;
use Amp\Socket\InternetAddress;
use Amp\Socket\Socket;
use Revolt\EventLoop;

use function Amp\async;
use function Amp\Socket\connect;

class Connection
{
	/**
	 * The channel ID for all connection related operations.
	 *
	 * @var int
	 */
	public const CHANNEL_ID = 0;

	/** @var ?Socket */
	protected ?Socket $socket = null;

	/** @var Parser */
	protected Parser $parser;

	/** @var Connection\Protocol */
	protected Connection\Protocol $protocol;

	/** @var Channel[] */
	protected array $channels = [];

	/**
	 * Constructor.
	 *
	 * @param Connection\Config $config
	 */
	public function __construct(
		protected Connection\Config $config,
	) {
		$this->socket = connect($config->host . ':' . $config->port);
		$this->parser = new Parser($this->parseFrame());

		// Let's give the protocol a reference to the socket so it can write its replies
		$this->protocol = new Connection\Protocol($this->socket);
		$this->protocol->on(Frame\Method\Connection\CloseOk::class, function (): void {
			$this->socket->close();
			$this->parser->cancel();
		});

		async($this->read(...));

		$this->protocol->connect($this->config);
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		// $this->close();
	}

	/**
	 * Close the connection.
	 *
	 * @return void
	 */
	public function close(int $code = 0, string $reason = ''): void
	{
		foreach ($this->channels as $channel) {
			$channel->close($code, $reason);
		}

		$this->protocol->close($code, $reason);
	}

	/**
	 * Get or open a channel.
	 *
	 * @return Channel
	 */
	public function channel(?int $id = null): Channel
	{
		if ($id !== null && isset($this->channels[$id])) {
			return $this->channels[$id];
		}

		$id ??= $this->getFreeChannelId();
		$reply = $this->protocol->channelOpen($id);

		$this->channels[$id] = new Channel(\WeakReference::create($this->protocol), $id, $this->config->channelTimeout);
		// $this->channels[$id]->once('close', function () use ($id) { unset($this->channels[$id]); });
		return $this->channels[$id];
	}

	/**
	 * Get a free channel ID, or null if you can't have more.
	 *
	 * @return ?int
	 *
	 * @throws \RuntimeException
	 */
	protected function getFreeChannelId(): int
	{
		for ($i = 1; $i <= $this->config->maxChannels; ++$i) {
			if (!isset($this->channels[$i])) {
				return $i;
			}
		}

		throw new \RuntimeException('No available channel ID');
	}

	/**
	 * @return void
	 */
	private function read(): void
	{
		while (($data = $this->socket->read()) !== null) {
			$this->parser->push($data);
		}
	}

	/**
	 * @return \Generator<int, int, string, void>
	 *
	 * @throws \UnexpectedValueException
	 */
	private function parseFrame(): \Generator
	{
		while (true) {
			$frameHeader = yield Frame::HEADER_SIZE;
			extract(unpack('Ctype/nchannel/Nsize', $frameHeader));

			$payload = yield $size;

			$end = yield 1;
			if (ord($end) !== Frame::END_MAGIC) {
				throw new \UnexpectedValueException('Invalid frame end');
			}

			$frame = Frame::deserialize(Frame\Type::from($type), $channel, new Connection\Buffer($payload));
			$frameSlug = strtolower(preg_replace('/([a-z])([A-Z])([A-Z](?![a-z]))*/', '$1-$2',
				str_replace('\\', '-', substr($frame::class, 0, strlen('Benit8\\Ampqp\\Frame\\')))
			));

			$this->protocol->emit("frame.{$channel}.{$frameSlug}", $frame);
			$this->protocol->emit($frame::class, $frame);
		}
	}
}
