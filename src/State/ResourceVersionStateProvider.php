<?php

namespace App\State;

use ApiPlatform\Metadata as API;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Version;
use App\Service\ApiResourceNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResourceVersionStateProvider implements ProviderInterface
{
    private LogEntryRepository $versionRepository;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        $this->versionRepository = $this->entityManager->getRepository(LogEntry::class);
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        switch ($operation::class) {
            case API\Get::class:
                return $this->getVersion($uriVariables['id']);
            case API\GetCollection::class:
                return $this->getVersions(
                    $context['request']->query->get('resource'),
                    $context['request']->query->get('resourceId')
                );
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getVersion(int $id): Version
    {
        $log = $this->versionRepository->find($id);

        if (!$log) {
            throw new NotFoundHttpException("Not Found");
        }

        $entity = $this->entityManager->find($log->getObjectClass(), $log->getObjectId());

        return new Version($log, $entity);
    }

    /**
     * @return Version[]
     */
    private function getVersions(string $resourceName, int $resourceId): array
    {
        $resourceClass = ApiResourceNormalizer::toEntity($resourceName);

        $entity = $this->entityManager->find($resourceClass, $resourceId);
        $logs = $this->versionRepository->getLogEntries($entity);

        $versions = [];
        foreach ($logs as $log) {
            $versions[] = new Version($log, $entity);
        }

        return $versions;
    }
}
