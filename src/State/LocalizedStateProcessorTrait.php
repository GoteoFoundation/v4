<?php

namespace App\State;

use App\Entity\Interface\LocalizedEntityInterface;
use App\Service\LocalizationService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;

trait LocalizedStateProcessorTrait
{
    protected RequestStack $requestStack;

    protected LocalizationService $localizationService;

    #[Required]
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Required]
    public function setLocalizationService(LocalizationService $localizationService)
    {
        $this->localizationService = $localizationService;
    }

    private function isLocalizedData(mixed $data): bool
    {
        if ($data instanceof LocalizedEntityInterface) {
            return true;
        }

        return false;
    }

    private function isLocalizedRequest(array $context): bool
    {
        if (\array_key_exists('request', $context)) {
            return $context['request']->headers->has('Content-Language');
        }

        return false;
    }

    private function getContentLanguages(array $context): ?array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (\array_key_exists('request', $context)) {
            $request = $context['request'];
        }

        $tags = $request->headers->get('Content-Language');

        if ($tags === null) {
            return null;
        }

        return $this->localizationService->getLanguages($tags);
    }
}
