<?php declare(strict_types=1);

namespace Becklyn\TestHelpers\Translations\Diff;

/**
 * Diffs the existing keys with the required translations
 */
class CatalogueDiffer
{
    /**
     * @return array
     */
    public function diff (
        array $locales,
        array $requiredKeys,
        array $existingTranslations,
        array $ignoredKeys
    )
    {
        $missing = [];

        foreach ($locales as $locale)
        {
            foreach ($requiredKeys as $domain => $keys)
            {
                foreach ($keys as $key)
                {
                    if ($this->isIgnored($ignoredKeys, $domain, $key))
                    {
                        continue;
                    }

                    if (!isset($existingTranslations[$locale][$domain][$key]))
                    {
                        $missing[$locale][$domain][] = $key;
                    }
                }

                if (isset($missing[$locale][$domain]))
                {
                    \usort($missing[$locale][$domain], "strnatcasecmp");
                }
            }

            if (isset($missing[$locale]))
            {
                \uksort($missing[$locale], "strnatcasecmp");
            }
        }

        \uksort($missing, "strnatcasecmp");
        return $missing;
    }


    /**
     *
     */
    private function isIgnored (array $ignores, string $domain, string $key) : bool
    {
        foreach ($ignores as $pattern => $ignoredDomains)
        {
            if (\preg_match($pattern, $key) && (true === $ignoredDomains || \in_array($domain, $ignoredDomains, true)))
            {
                return true;
            }
        }

        return false;
    }
}
