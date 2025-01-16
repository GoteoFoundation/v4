<?php

namespace App\State;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Metadata as API;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\EmbeddedResource;
use App\ApiResource\Version;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResourceVersionStateProvider implements ProviderInterface
{
    private LogEntryRepository $versionRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private IriConverterInterface $iriConverter,
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
            throw new NotFoundHttpException('Not Found');
        }

        $entity = $this->entityManager->find($log->getObjectClass(), $log->getObjectId());

        return new Version($log, $entity, $this->iriConverter->getIriFromResource($entity));
    }

    /**
     * @return Version[]
     */
    private function getVersions(string $resourceName, int $resourceId): array
    {
        $resourceClass = \sprintf('App\\Entity\\%s', ucfirst($resourceName));

        try {
            $entity = $this->entityManager->find($resourceClass, $resourceId);
        } catch (MappingException $e) {
            throw new NotFoundHttpException(sprintf("Resource '%s' does not exist", $resourceName));
        }

        if (!$entity) {
            throw new NotFoundHttpException(sprintf("Resource '%s' with ID '%s' not found", $resourceName, $resourceId));
        }

        $logs = $this->versionRepository->getLogEntries($entity);

        $versions = [];
        foreach ($logs as $key => $log) {
            $resource = new EmbeddedResource();
            $resource->id = $entity->getId();
            $resource->iri = $this->iriConverter->getIriFromResource($entity);
            $resource->resource = $this->reconstructEntity($entity, \array_slice($logs, 0, $key));

            $version = new Version();
            $version->id = $log->getId();
            $version->version = $log->getVersion();
            $version->action = $log->getAction();
            $version->changes = $log->getData();
            $version->resource = $resource;
            $version->dateCreated = $log->getLoggedAt();

            $versions[] = $version;
        }

        return $versions;
    }

    /**
     * @param LogEntry[] $logs
     */
    private function reconstructEntity(object $entity, array $logs): object
    {
        foreach ($logs as $log) {
            $data = $log->getData();
            foreach ($data as $property => $value) {
                $setter = \sprintf('set%s', \ucfirst($property));
                $entity->$setter($value);
            }
        }

        return $entity;
    }
}
