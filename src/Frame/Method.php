<?php declare(strict_types=1);

namespace Benit8\Ampqp\Frame;

use Benit8\Ampqp\Connection\Buffer;
use Benit8\Ampqp\Frame;

abstract class Method extends Frame
{
	/** @var array<int, array<int, class-string>> */
	private const CLASS_METHOD_MAP = [
		/* Connection */ 10 => [
			/* Start */     10 => Frame\Method\Connection\Start::class,
			/* StartOk */   11 => Frame\Method\Connection\StartOk::class,
			/* Secure */    20 => Frame\Method\Connection\Secure::class,
			/* SecureOk */  21 => Frame\Method\Connection\SecureOk::class,
			/* Tune */      30 => Frame\Method\Connection\Tune::class,
			/* TuneOk */    31 => Frame\Method\Connection\TuneOk::class,
			/* Open */      40 => Frame\Method\Connection\Open::class,
			/* OpenOk */    41 => Frame\Method\Connection\OpenOk::class,
			/* Close */     50 => Frame\Method\Connection\Close::class,
			/* CloseOk */   51 => Frame\Method\Connection\CloseOk::class,
			/* Blocked */   60 => Frame\Method\Connection\Blocked::class,
			/* Unblocked */ 61 => Frame\Method\Connection\Unblocked::class,
		],
		/* Channel */ 20 => [
			/* Open */    10 => Frame\Method\Channel\Open::class,
			/* OpenOk */  11 => Frame\Method\Channel\OpenOk::class,
			/* Flow */    20 => Frame\Method\Channel\Flow::class,
			/* FlowOk */  21 => Frame\Method\Channel\FlowOk::class,
			/* Close */   40 => Frame\Method\Channel\Close::class,
			/* CloseOk */ 41 => Frame\Method\Channel\CloseOk::class,
		],
		/* Exchange */ 40 => [
			/* Declare */   10 => Frame\Method\Exchange\DeclareFrame::class,
			/* DeclareOk */ 11 => Frame\Method\Exchange\DeclareOk::class,
			/* Delete */    20 => Frame\Method\Exchange\Delete::class,
			/* DeleteOk */  21 => Frame\Method\Exchange\DeleteOk::class,
			/* Bind */      30 => Frame\Method\Exchange\Bind::class,
			/* BindOk */    31 => Frame\Method\Exchange\BindOk::class,
			/* Unbind */    40 => Frame\Method\Exchange\Unbind::class,
			/* UnbindOk */  51 => Frame\Method\Exchange\UnbindOk::class,
		],
		/* Queue */ 50 => [
			/* Declare */   10 => Frame\Method\Queue\DeclareFrame::class,
			/* DeclareOk */ 11 => Frame\Method\Queue\DeclareOk::class,
			/* Bind */      20 => Frame\Method\Queue\Bind::class,
			/* BindOk */    21 => Frame\Method\Queue\BindOk::class,
			/* Purge */     30 => Frame\Method\Queue\Purge::class,
			/* PurgeOk */   31 => Frame\Method\Queue\PurgeOk::class,
			/* Delete */    40 => Frame\Method\Queue\Delete::class,
			/* DeleteOk */  41 => Frame\Method\Queue\DeleteOk::class,
			/* Unbind */    50 => Frame\Method\Queue\Unbind::class,
			/* UnbindOk */  51 => Frame\Method\Queue\UnbindOk::class,
		],
		/* Basic */ 60 => [
			/* Qos */          10 => Frame\Method\Basic\Qos::class,
			/* QosOk */        11 => Frame\Method\Basic\QosOk::class,
			/* Consume */      20 => Frame\Method\Basic\Consume::class,
			/* ConsumeOk */    21 => Frame\Method\Basic\ConsumeOk::class,
			/* Cancel */       30 => Frame\Method\Basic\Cancel::class,
			/* CancelOk */     31 => Frame\Method\Basic\CancelOk::class,
			/* Publish */      40 => Frame\Method\Basic\Publish::class,
			/* Return */       50 => Frame\Method\Basic\ReturnFrame::class,
			/* Deliver */      60 => Frame\Method\Basic\Deliver::class,
			/* Get */          70 => Frame\Method\Basic\Get::class,
			/* GetOk */        71 => Frame\Method\Basic\GetOk::class,
			/* GetEmpty */     72 => Frame\Method\Basic\GetEmpty::class,
			/* Ack */          80 => Frame\Method\Basic\Ack::class,
			/* Reject */       90 => Frame\Method\Basic\Reject::class,
			/* RecoverAsync */ 100 => Frame\Method\Basic\RecoverAsync::class,
			/* Recover */      110 => Frame\Method\Basic\Recover::class,
			/* RecoverOk */    111 => Frame\Method\Basic\RecoverOk::class,
			/* Nack */         120 => Frame\Method\Basic\Nack::class,
		],
		/* Confirm */ 85 => [
			/* Select */   10 => Frame\Method\Confirm\Select::class,
			/* SelectOk */ 11 => Frame\Method\Confirm\SelectOk::class,
		],
		/* Tx */ 90 => [
			/* Select */     10 => Frame\Method\Tx\Select::class,
			/* SelectOk */   11 => Frame\Method\Tx\SelectOk::class,
			/* Commit */     20 => Frame\Method\Tx\Commit::class,
			/* CommitOk */   21 => Frame\Method\Tx\CommitOk::class,
			/* Rollback */   30 => Frame\Method\Tx\Rollback::class,
			/* RollbackOk */ 31 => Frame\Method\Tx\RollbackOk::class,
		],
	];

	/**
	 * Constructor.
	 */
	public function __construct(
		int $channelId,
		public $classId,
		public $methodId,
	) {
		parent::__construct(Type::Method, $channelId);
	}

	protected static function unpack(int $channelId, Buffer $payload): self
	{
		$classId = $payload->consumeUint16();
		$methodId = $payload->consumeUint16();

		$frameClassName = self::CLASS_METHOD_MAP[$classId][$methodId] ?? null;
		if ($frameClassName === null) {
			throw new \UnexpectedValueException(sprintf('Unknown method frame "%d.%d"', $classId, $methodId));
		}

		return $frameClassName::unpack($channelId, $payload);
	}

	protected function pack(): Buffer
	{
		return (new Buffer())
			->appendUint16($this->classId->value)
			->appendUint16($this->methodId->value);
	}
}
