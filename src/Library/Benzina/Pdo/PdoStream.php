<?php

namespace App\Library\Benzina\Pdo;

use App\Library\Benzina\Stream;
use App\Library\Benzina\StreamInterface;

class PdoStream implements StreamInterface
{
    private const BATCH_SIZE = 99;
    private int $currentBatch = 0;

    public function __construct(
        private Stream $stream,
        private \PDOStatement $pdo,
    ) {
    }

    public function eof(): bool
    {
        return $this->currentBatch > $this->length();
    }

    public function read(?int $length = null): mixed
    {
        $this->stream->read(self::BATCH_SIZE);
        $this->pdo->execute([self::BATCH_SIZE, $this->currentBatch]);

        $this->currentBatch += self::BATCH_SIZE;

        return $this->pdo->fetchAll();
    }

    public function close(): void
    {
        $this->stream->close();
    }

    public function tell(): int
    {
        return $this->currentBatch;
    }

    public function length(): int
    {
        return $this->stream->length();
    }
}
