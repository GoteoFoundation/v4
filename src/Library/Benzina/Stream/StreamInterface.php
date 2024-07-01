<?php

namespace App\Library\Benzina\Stream;

interface StreamInterface
{
    public const MESSAGE_ERROR_DETACHED = 'Stream is detached';
    public const MESSAGE_ERROR_UNABLE_TO_READ = 'Unable to read from stream';

    /**
     * Returns true if the current position is at the end of the Stream.
     */
    public function eof(): bool;

    /**
     * Read data from the Stream.
     *
     * @param int|null $length Read up to $length positions from the current position of the Stream
     */
    public function read(?int $length = null): mixed;

    /**
     * Get the current position.
     */
    public function tell(): int;

    /**
     * Get the total length of the Stream.
     */
    public function size(): int;

    /**
     * Close the Stream.
     */
    public function close(): void;

    /**
     * Seek the Stream to position 0.
     */
    public function rewind(): void;
}
