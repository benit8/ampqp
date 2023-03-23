<?php declare(strict_types=1);

namespace Benit8\Ampqp\Connection;

use PHPinnacle\Buffer\ByteBuffer;

final class Buffer extends ByteBuffer
{
	public function appendString(string $value): self
	{
		$this->appendUint8(\strlen($value))
			 ->append($value);
		return $this;
	}

	/**
	 * @throws \PHPinnacle\Buffer\BufferOverflow
	 */
	public function consumeString(): string
	{
		return $this->consume($this->consumeUint8());
	}

	public function appendText(string $value): self
	{
		$this
			->appendUint32(\strlen($value))
			->append($value);

		return $this;
	}

	/**
	 * @throws \PHPinnacle\Buffer\BufferOverflow
	 */
	public function consumeText(): string
	{
		return $this->consume($this->consumeUint32());
	}

	public function appendBits(array $bits): self
	{
		$value = 0;

		/**
		 * @var int $n
		 * @var bool $bit
		 */
		foreach ($bits as $n => $bit) {
			$bit = $bit ? 1 : 0;
			$value |= $bit << $n;
		}

		$this->appendUint8($value);

		return $this;
	}

	/**
	 * @throws \PHPinnacle\Buffer\BufferOverflow
	 */
	public function consumeBits(int $n): array
	{
		$bits = [];
		$value = $this->consumeUint8();

		for ($i = 0; $i < $n; ++$i) {
			$bits[] = ($value & (1 << $i)) > 0;
		}

		return $bits;
	}

	public function appendTimestamp(\DateTimeInterface $value): self
	{
		$this->appendUint64($value->getTimestamp());

		return $this;
	}

	public function consumeTimestamp(): \DateTimeInterface
	{
		return new \DateTimeImmutable(\sprintf('@%s', $this->consumeUint64()));
	}

	public function appendTable(array $table): self
	{
		$buffer = new self();

		/**
		 * @var string|ByteBuffer $k
		 * @var mixed $v
		 */
		foreach ($table as $k => $v) {
			$k = (string)$k;

			$buffer->appendUint8(\strlen($k));
			$buffer->append($k);
			$buffer->appendValue($v);
		}

		$this
			->appendUint32($buffer->size())
			->append($buffer);

		return $this;
	}

	/**
	 * @throws \PHPinnacle\Buffer\BufferOverflow
	 */
	public function consumeTable(): array
	{
		$buffer = $this->shift($this->consumeUint32());
		$data = [];

		while (!$buffer->empty()) {
			$data[$buffer->consume($buffer->consumeUint8())] = $buffer->consumeValue();
		}

		return $data;
	}

	public function appendArray(array $value): self
	{
		$buffer = new self();

		/** @var mixed $v */
		foreach ($value as $v) {
			$buffer->appendValue($v);
		}

		$this
			->appendUint32($buffer->size())
			->append($buffer);

		return $this;
	}

	/**
	 * @throws \PHPinnacle\Buffer\BufferOverflow
	 */
	public function consumeArray(): array
	{
		$buffer = $this->shift($this->consumeUint32());
		$data = [];

		while (!$buffer->empty()) {
			$data[] = $buffer->consumeValue();
		}

		return $data;
	}

	/**
	 * @throws \PHPinnacle\Buffer\BufferOverflow
	 */
	public function consumeDecimal(): int
	{
		$scale = $this->consumeUint8();
		$value = $this->consumeUint32();

		return $value * (10 ** $scale);
	}

	/**
	 * @throws \PHPinnacle\Buffer\BufferOverflow
	 * @throws \UnexpectedValueException
	 */
	private function consumeValue(): float|\DateTimeInterface|null|array|bool|int|string
	{
		$fieldType = FieldType::from($this->consumeUint8());

		return match ($fieldType) {
			FieldType::Boolean => $this->consumeUint8() > 0,
			FieldType::ShortShortInt => $this->consumeInt8(),
			FieldType::ShortShortUint => $this->consumeUint8(),
			FieldType::ShortInt => $this->consumeInt16(),
			FieldType::ShortUint => $this->consumeUint16(),
			FieldType::LongInt => $this->consumeInt32(),
			FieldType::LongUint => $this->consumeUint32(),
			FieldType::LongLongInt => $this->consumeInt64(),
			FieldType::LongLongUint => $this->consumeUint64(),
			FieldType::Float => $this->consumeFloat(),
			FieldType::Double => $this->consumeDouble(),
			FieldType::Decimal => $this->consumeDecimal(),
			FieldType::ShortString => $this->consume($this->consumeUint8()),
			FieldType::LongString => $this->consume($this->consumeUint32()),
			FieldType::Timestamp => $this->consumeTimestamp(),
			FieldType::Array => $this->consumeArray(),
			FieldType::Table => $this->consumeTable(),
			FieldType::Null => null,
		};
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	private function appendValue(mixed $value): void
	{
		if (\is_string($value)) {
			$this->appendUint8(FieldType::LongString->value);
			$this->appendText($value);
		} elseif (\is_int($value)) {
			$this->appendUint8(FieldType::LongInt->value);
			$this->appendInt32($value);
		} elseif (\is_bool($value)) {
			$this->appendUint8(FieldType::Boolean->value);
			$this->appendUint8((int)$value);
		} elseif (\is_float($value)) {
			$this->appendUint8(FieldType::Double->value);
			$this->appendDouble($value);
		} elseif (\is_array($value)) {
			if (\array_is_list($value)) {
				$this->appendUint8(FieldType::Array->value);
				$this->appendArray($value);
			} else {
				$this->appendUint8(FieldType::Table->value);
				$this->appendTable($value);
			}
		} elseif (\is_null($value)) {
			$this->appendUint8(FieldType::Null->value);
		} elseif ($value instanceof \DateTimeInterface) {
			$this->appendUint8(FieldType::Timestamp->value);
			$this->appendTimestamp($value);
		} else {
			throw new \UnexpectedValueException('Unknown value type');
		}
	}
}
