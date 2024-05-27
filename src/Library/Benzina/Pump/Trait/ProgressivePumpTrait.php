<?php

namespace App\Library\Benzina\Pump\Trait;

use Doctrine\ORM\EntityManagerInterface;

/**
 * A progressive pump behaves with idempotency with the pumping data.
 * 
 * To do so the pump must check matching, already pumped, data for the currently pumping data and skip it.
 * 
 * This is an example:
 * ```
 * $alreadyPumped = $this->getPumped(Entity::class, $data, ['entityId' => 'dataId']);
 * ...
 * if ($this->isPumped($record, $alreadyPumped)) continue;
 * ```
 */
trait ProgressivePumpTrait
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Retrieve already pumped data from the pumping data batch
     * @param string $entityClass The class to pump the data to
     * @param array $pumpingBatch The pumping data batch
     * @param array $matchCriteria An array with the matching criteria between the entity key and the data key
     */
    public function getPumped(
        string $entityClass,
        array $pumpingBatch,
        array $matchCriteria
    ): array {
        $repository = $this->entityManager->getRepository($entityClass);

        $entityKey = \array_keys($matchCriteria)[0];
        $pumpingKey = $matchCriteria[$entityKey];

        $pumped = $repository->findBy([$entityKey => \array_map(function ($data) use ($pumpingKey) {
            return $data[$pumpingKey];
        }, $pumpingBatch)]);

        $pumpedByKey = [];
        foreach ($pumped as $pumped) {
            $getter = sprintf("get%s", ucfirst($entityKey));
            $pumpedByKey[$pumped->$getter()] = $pumped;
        }

        return $pumpedByKey;
    }

    /**
     * Determine if a pumping data record is already in a pumped data batch
     * @param array $pumpingRecord The data record to be pumped
     * @param array $pumpedBatch The pumped data batch
     */
    public function isPumped(array $pumpingRecord, array $pumpedBatch): bool
    {
        if (
            $this->config !== null &&
            \array_key_exists('progressive', $this->config) &&
            $this->config['progressive'] === false
        ) {
            return false;
        }

        return \array_key_exists($pumpingRecord['id'], $pumpedBatch);
    }
}
