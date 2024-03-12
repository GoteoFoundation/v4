<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Model\SecurityScheme;
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

        $operationDescription = $operation->getDescription();

        switch ($idOperation) {
            case 'collection':
                $operationId = sprintf('List all %ss', $idResource);
                break;
            case 'post':
                $operationId = sprintf('Create one %s', $idResource);
                $operationDescription = sprintf('Creates a new %s resource.', $idResource);
                break;
            case 'get':
                $operationId = sprintf('Retrieve one %s', $idResource);
                $operationDescription = sprintf('Retrieves one %s resource.', $idResource);
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
            ->withSummary($operationId)
            ->withOperationId($operationId)
            ->withDescription($operationDescription);
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

        $openApi = $openApi->withServers([
            new Model\Server(sprintf('%s://%s', $_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST']))
        ]);

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
            if (\preg_match('/.*\.jsonld/', $name)) continue;
            if (empty($schema['description'])) continue;

            if (\preg_match('/.*Dto/', $name)) {
                $name = preg_replace('/\..*Dto/', '', $name);
            }

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

        $securitySchemes = $openApi->getComponents()->getSecuritySchemes() ?: new \ArrayObject();
        $securitySchemes['access_token'] = new SecurityScheme(
            type: 'http',
            scheme: 'bearer',
        );

        return $openApi;
    }
}
