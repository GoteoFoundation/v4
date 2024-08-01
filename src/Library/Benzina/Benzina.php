<?php

namespace App\Library\Benzina;

use App\Library\Benzina\Pump\PumpInterface;
use App\Library\Benzina\Stream\StreamInterface;

class Benzina
{
    /** @var Pump\PumpInterface[] */
    private array $availablePumps = [];

    public function __construct(
        iterable $instanceof
    ) {
        $this->availablePumps = \iterator_to_array($instanceof);
    }

    /**
     * Get the Pumps that can process a sample in the stream data.
     *
     * @var mixed
     *
     * @return PumpInterface[]
     */
    public function getPumps(StreamInterface $stream, int $sampleSize = 1): array
    {
        $sample = $stream->read($sampleSize);
        $stream->rewind();

        $pumps = [];
        foreach ($this->availablePumps as $pump) {
            if (!$pump->supports($sample)) {
                continue;
            }

            $pumps[] = $pump;
        }

        return $pumps;
    }
}
