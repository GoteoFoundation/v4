<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Interface\LocalizedContentInterface;
use App\Service\LocalizationService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

class EntityStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: RemoveProcessor::class)]
        private ProcessorInterface $deleteProcessor,
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private LocalizationService $localizationService,
    ) {}

    /**
     * @return T2
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof LocalizedContentInterface) {
            /** @var Request */
            $request = $context['request'];

            $data->setTranslatableLocale($this->localizationService->getLanguage(
                $request->headers->get('Content-Language'),
            ));
        }

        if ($operation instanceof DeleteOperationInterface) {
            return $this->deleteProcessor->process($data, $operation, $uriVariables, $context);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
