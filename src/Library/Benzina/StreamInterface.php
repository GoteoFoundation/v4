<?php

namespace App\Library\Benzina;

interface StreamInterface
{
    public const MESSAGE_ERROR_DETACHED = 'Stream is detached';
    public const MESSAGE_ERROR_UNABLE_TO_READ = 'Unable to read from stream';

    public function eof(): bool;

    public function read(?int $length = null): mixed;

    public function close(): void;

    public function length(): int;
}
