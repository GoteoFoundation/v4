<?php

namespace App\Service;

use Locale;
use Symfony\Component\Intl\Languages;

class LocalizationService
{
    public const ERROR_TAG_INVALID_LANG = "The tag '%s' does not match to any language.";

    public function __construct(
        private string $defaultLocale,
    ) {}

    /**
     * @param string $tags A string of language tags
     *
     * @return string The primary language of the first supplied tag
     *
     * @see https://www.rfc-editor.org/rfc/bcp/bcp47.txt
     */
    public function getLanguage(string $tags): string
    {
        $tag = $this->parseLanguageTags($tags)[0];

        return $this->getPrimaryLanguage($tag);
    }

    /**
     * @param string $tags A string of language tags
     *
     * @return array The primary language of the supplied tags
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
        $language = Locale::getPrimaryLanguage($tag);
        if ($language === null) {
            throw new \Exception(\sprintf(self::ERROR_TAG_INVALID_LANG, $tag));
        }

        if (!\in_array($language, Languages::getLanguageCodes())) {
            throw new \Exception(\sprintf(self::ERROR_TAG_INVALID_LANG, $tag));
        }

        return $language;
    }

    private function parseLanguageTags(?string $tags = null): array
    {
        if ($tags === null) {
            return [$this->defaultLocale];
        }

        $languages = \array_map(function (string $tag) {
            $tag = \explode(';', $tag);

            return \trim($tag[0]);
        }, \explode(',', $tags));

        if (empty($languages)) {
            return [$this->defaultLocale];
        }

        return $languages;
    }
}
