<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Interface\LocalizedContentInterface;
use App\Service\LocalizationService;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Translation;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EntityStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: RemoveProcessor::class)]
        private ProcessorInterface $deleteProcessor,
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private LocalizationService $localizationService,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @return T2
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof LocalizedContentInterface) {
            return $this->processLocalizedContent($data, $operation, $uriVariables, $context);
        }

        if ($operation instanceof DeleteOperationInterface) {
            return $this->deleteProcessor->process($data, $operation, $uriVariables, $context);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function processLocalizedContent(
        LocalizedContentInterface $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): mixed {
        $languages = $this->getContextLanguages($context);

        if ($operation instanceof DeleteOperationInterface) {
            if ($languages === null) {
                return $this->deleteProcessor->process($data, $operation, $uriVariables, $context);
            }

            return $this->deleteLocalizedContent($data, $operation, $uriVariables, $context, $languages);
        }

        $language = $languages ? $languages[0] : $this->localizationService->getDefaultLanguage();

        $data->setTranslatableLocale($language);

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function getContextLanguages(array $context): ?array
    {
        $tags = $context['request']->headers->get('Content-Language', '');

        if (empty($tags)) {
            return null;
        }

        return $this->localizationService->getLanguages($tags);
    }

    private function deleteLocalizedContent(
        LocalizedContentInterface $data,
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
