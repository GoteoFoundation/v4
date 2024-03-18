<?php

namespace App\Library\Benzina\Stream;

class Stream implements StreamInterface
{
    /** @var resource */
    private $stream;

    public function __construct(string $data)
    {
        try {
            $this->stream = \fopen('php://temp', 'r+');

            \fwrite($this->stream, $data);
            \fseek($this->stream, 0);
        } catch (\Exception $e) {
            throw new \RuntimeException("Unable to open stream");
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function eof(): bool
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException(self::MESSAGE_ERROR_DETACHED);
        }

        return \feof($this->stream);
    }

    public function read(?int $size = null): string
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException(self::MESSAGE_ERROR_DETACHED);
        }

        if ($size < 0) {
            throw new \RuntimeException("Length parameter cannot be negative");
        }

        if ($size === 0) {
            return '';
        }

        try {
            $string = \fgets($this->stream, $size);
        } catch (\Exception $e) {
            throw new \RuntimeException(self::MESSAGE_ERROR_UNABLE_TO_READ, 0, $e);
        }

        if ($string === false) {
            throw new \RuntimeException(self::MESSAGE_ERROR_UNABLE_TO_READ);
        }

        return $string;
    }

    public function tell(): int
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException(self::MESSAGE_ERROR_DETACHED);
        }

        return ftell($this->stream);
    }

    public function size(): int
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException(self::MESSAGE_ERROR_DETACHED);
        }

        return \fstat($this->stream)['size'];
    }

    public function close(): void
    {
        if (isset($this->stream)) {
            if (\is_resource($this->stream)) {
                \fclose($this->stream);
            }

            unset($this->stream);
        }
    }

    public function rewind(): void
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException(self::MESSAGE_ERROR_DETACHED);
        }

        \fseek($this->stream, 0);
    }
}
