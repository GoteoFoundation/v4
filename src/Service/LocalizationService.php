<?php

namespace App\Service;

use Symfony\Component\Intl\Languages;

class LocalizationService
{
    public const ERROR_TAG_INVALID_LANG = "The tag '%s' does not match to any language.";

    private string $defaultLanguage;

    public function __construct(
        private string $defaultLocale,
    ) {
        $this->defaultLanguage = $this->getPrimaryLanguage($defaultLocale);
    }

    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    /**
     * @param string $tags A string of language tags
     *
     * @return string The primary language of the first supplied tag, or the default language if empty
     *
     * @throws \Exception If the first tag specifies an invalid language
     *
     * @see https://www.rfc-editor.org/rfc/bcp/bcp47.txt
     */
    public function getLanguage(string $tags): string
    {
        $tags = $this->parseLanguageTags($tags);

        if (empty($tags[0])) {
            return $this->getDefaultLanguage();
        }

        return $this->getPrimaryLanguage($tags[0]);
    }

    /**
     * @param string $tags A string of language tags
     *
     * @return array The primary language of the supplied tags
     *
     * @throws \Exception If one of the supplied tags specifies an invalid language
     *
     * @see https://www.rfc-editor.org/rfc/bcp/bcp47.txt
     */
    public function getLanguages(string $tags): array
    {
        $tags = $this->parseLanguageTags($tags);

        foreach ($tags as $key => $tag) {
            $tags[$key] = $this->getPrimaryLanguage($tag);
        }

        return $tags;
    }

    private function getPrimaryLanguage(string $tag): string
    {
        $language = \Locale::getPrimaryLanguage($tag);
        if ($language === null) {
            throw new \Exception(\sprintf(self::ERROR_TAG_INVALID_LANG, $tag));
        }

        if (!\in_array($language, Languages::getLanguageCodes())) {
            throw new \Exception(\sprintf(self::ERROR_TAG_INVALID_LANG, $tag));
        }

        return $language;
    }

    private function parseLanguageTags(string $tagsString): array
    {
        $languages = \array_map(function (string $tag) {
            $tag = \explode(';', $tag);

            return \trim($tag[0]);
        }, \explode(',', $tagsString));

        return $languages;
    }
}
