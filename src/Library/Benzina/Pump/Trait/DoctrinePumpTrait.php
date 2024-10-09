<?php

namespace App\Library\Benzina\Pump\Trait;

use App\Library\Benzina\Pump\PumpInterface;
use Doctrine\ORM\EntityManagerInterface;

trait DoctrinePumpTrait
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Skip already pumped records from a pumping stream batch.
     *
     * @param array  $batch  The batch of records being pumped
     * @param array  $batchKey The key in the batch records to match against the entity key
     * @param string $entityClass   The class where the data is being pumped to
     * @param string $entityKey The property of the pumped to class to match against the batch key
     * @return array The batch of records being pumped, sliced out of already pumped records
     */
    public function skipPumped(
        array $batch,
        string $batchKey,
        string $entityClass,
        string $entityKey,
    ): array {
        if (!$this instanceof PumpInterface) {
            throw new \Exception("DoctrinePumpTrait can only be used with PumpInterface classes");
        }

        if (!$this->getConfig('skip-pumped')) {
            return $batch;
        }

        $repository = $this->entityManager->getRepository($entityClass);

        $pumped = $repository->findBy([$entityKey => \array_map(function ($record) use ($batchKey) {
            return $record[$batchKey];
        }, $batch)]);

        $pumpedKeys = [];
        foreach ($pumped as $key => $entity) {
            $entityKeyGetter = sprintf('get%s', \ucfirst($entityKey));

            $pumpedKeys[] = $entity->$entityKeyGetter();
        }

        return \array_filter($batch, function (array $record) use ($batchKey, $pumpedKeys) {
            return !\in_array($record[$batchKey], $pumpedKeys);
        });
    }
}
