<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Interface\LocalizedEntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Translation;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EntityStateProcessor implements ProcessorInterface
{
    use LocalizedStateProcessorTrait;

    public function __construct(
        #[Autowire(service: RemoveProcessor::class)]
        private ProcessorInterface $deleteProcessor,
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @return T2
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($this->isLocalizedData($data)) {
            return $this->processLocalizedData($data, $operation, $uriVariables, $context);
        }

        if ($operation instanceof DeleteOperationInterface) {
            return $this->deleteProcessor->process($data, $operation, $uriVariables, $context);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function processLocalizedData(
        LocalizedEntityInterface $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): mixed {
        $languages = $this->getContentLanguages($context);

        if ($operation instanceof DeleteOperationInterface) {
            if ($this->isLocalizedRequest($context)) {
                return $this->deleteLocalizedContent($data, $operation, $uriVariables, $context, $languages);
            }

            return $this->deleteProcessor->process($data, $operation, $uriVariables, $context);
        }

        $language = $languages[0] ?? $this->localizationService->getDefaultLanguage();

        $data->setTranslatableLocale($language);

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function deleteLocalizedContent(
        LocalizedEntityInterface $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
        array $languages = [],
    ) {
        $translationRepository = $this->entityManager->getRepository(Translation::class);

        if (!\array_diff($data->getLocales(), $languages)) {
            throw new \Exception('Cannot leave empty resource. Must have at least one available localization.');
        }

        $nonLocalizedLanguages = \array_diff($languages, $data->getLocales());
        if (!empty($nonLocalizedLanguages)) {
            throw new \Exception(\sprintf(
                "Could not find requested localizations '%s' of the resource.",
                \join(', ', $nonLocalizedLanguages)
            ));
        }

        foreach ($languages as $language) {
            $translations = $translationRepository->findBy([
                'locale' => $language,
                'objectClass' => $data::class,
                'foreignKey' => $data->getId(),
            ]);

            foreach ($translations as $translation) {
                $this->entityManager->remove($translation);
            }

            $data->removeLocale($language);
        }

        $this->entityManager->flush();

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
