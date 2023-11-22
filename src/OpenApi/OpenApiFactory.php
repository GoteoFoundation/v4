<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{

    public function __construct(private OpenApiFactoryInterface $decorated)
    {
    }

    private function updateOperation(Operation $operation): Operation
    {
        $idParts = explode('_', $operation->getOperationId());
        $idResource = $operation->getTags()[0];
        $idOperation = array_slice($idParts, -1)[0];

        switch ($idOperation) {
            case 'collection':
                $operationId = sprintf('List all %ss', $idResource);
                break;
            case 'post':
                $operationId = sprintf('Create one %s', $idResource);
                break;
            case 'get':
                $operationId = sprintf('Retrieve one %s', $idResource);
                break;
            case 'put':
                $operationId = sprintf('Update one %s', $idResource);
                break;
            case 'delete':
                $operationId = sprintf('Delete one %s', $idResource);
                break;
            case 'patch':
                $operationId = sprintf('Patch one %s', $idResource);
                break;
            default:
                $operationId = $operation->getOperationId();
        }

        return $operation
            ->withOperationId($operationId)
            ->withSummary($operationId);
    }

    private function updatePathItemOperations(PathItem $pathItem): PathItem
    {
        if ($pathItem->getGet()) {
            $pathItem = $pathItem->withGet(
                $this->updateOperation($pathItem->getGet())
            );
        }

        if ($pathItem->getPost()) {
            $pathItem = $pathItem->withPost(
                $this->updateOperation($pathItem->getPost())
            );
        }

        if ($pathItem->getPut()) {
            $pathItem = $pathItem->withPut(
                $this->updateOperation($pathItem->getPut())
            );
        }

        if ($pathItem->getDelete()) {
            $pathItem = $pathItem->withDelete(
                $this->updateOperation($pathItem->getDelete())
            );
        }

        if ($pathItem->getPatch()) {
            $pathItem = $pathItem->withPatch(
                $this->updateOperation($pathItem->getPatch())
            );
        }

        return $pathItem;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        $openApi = $openApi->withInfo(
            $openApi
                ->getInfo()
                ->withDescription(
                    \file_get_contents(sprintf(
                        '%s%s%s',
                        __DIR__,
                        DIRECTORY_SEPARATOR,
                        'OpenApiDescription.md'
                    ))
                )
        );

        $tags = [];
        foreach ($openApi->getComponents()->getSchemas() as $name => $schema) {
            if (\preg_match('/.*\.json/', $name)) continue;
            if (empty($schema['description'])) continue;

            $tags[] = [
                'name' => $name,
                'description' => $schema['description']
            ];
        }

        $openApi = $openApi->withTags($tags);

        $paths = new Paths();
        foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
            $pathItem = $this->updatePathItemOperations($pathItem);

            $paths->addPath($path, $pathItem);
        }

        $openApi = $openApi->withPaths($paths);

        $openApi = $openApi->withServers([
            new Model\Server(sprintf('%s://%s', $_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST']))
        ]);

        return $openApi;
    }
}
