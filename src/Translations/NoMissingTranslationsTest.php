<?php declare(strict_types=1);

namespace Becklyn\TestHelpers\Translations;

use Becklyn\TestHelpers\Translations\Diff\CatalogueDiffer;
use Becklyn\TestHelpers\Translations\Loader\TranslationsLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 *
 */
abstract class NoMissingTranslationsTest extends TestCase
{
    /**
     * Tests that there are no missing translations
     */
    public function testNoMissingTranslations () : void
    {
        $locales = $this->getLocales();
        $required = $this->loadTranslations();

        $existingTranslationLoader = new TranslationsLoader();
        $existing = $existingTranslationLoader->loadTranslations($this->getDirectoriesWithTranslations(), $locales);

        $differ = new CatalogueDiffer();
        $missing = $differ->diff(
            $locales,
            $required,
            $existing,
            $this->getIgnoredKeys()
        );

        $log = \json_encode($missing, \JSON_PRETTY_PRINT);
        self::assertEmpty($missing, "Ensure that there are no missing translations, missing are:\n{$log}");
    }


    /**
     *
     */
    private function loadTranslations ()
    {
        $root = $this->getRootDir();
        $translationExtractorExecutable = "{$root}/vendor/bin/extract-translations";

        if (!\is_file($translationExtractorExecutable))
        {
            throw new \RuntimeException("Could not find `extract-translations` executable. Have you installed the composer package `becklyn/translations-extractor` (preferrably via the composer-bin-plugin)?");
        }

        $arguments = [
            "{$root}/vendor/bin/extract-translations",
        ];

        foreach ($this->getDirectoriesWithUsages() as $dir)
        {
            $arguments[] = $dir;
        }

        foreach ($this->getMockedFunctions() as $name)
        {
            $arguments[] = "--mock-function";
            $arguments[] = $name;
        }

        foreach ($this->getMockedFilters() as $name)
        {
            $arguments[] = "--mock-filter";
            $arguments[] = $name;
        }

        foreach ($this->getMockedTests() as $name)
        {
            $arguments[] = "--mock-test";
            $arguments[] = $name;
        }

        $process = new Process($arguments);
        $process->mustRun();
        $output = $process->getOutput();
        $data = \json_decode($output, true);

        if (!\is_array($data))
        {
            throw new \RuntimeException("Could not parse output of `extract-translations`:\n{$output}");
        }

        return $data;
    }

    /**
     * Returns the locales to check for.
     */
    protected function getLocales () : array
    {
        return ["en", "de"];
    }


    /**
     * Returns the directories from where the translations should be extracted.
     */
    protected function getDirectoriesWithUsages () : array
    {
        $root = \rtrim($this->getRootDir(), "/");

        return [
            "{$root}/src",
        ];
    }


    /**
     * Returns the list of directories containing translation files.
     */
    protected function getDirectoriesWithTranslations () : array
    {
        $root = \rtrim($this->getRootDir(), "/");

        return [
            "{$root}/src/Resources/translations",
            "{$root}/translations",
        ];
    }


    /**
     * Returns the path to the root dir.
     */
    protected function getRootDir () : string
    {
        $r = new \ReflectionClass($this);
        return \dirname($r->getFileName(), 2);
    }


    /**
     * Ignore these keys. These will not be reported as missing, even if they are used.
     *
     * Return type: array
     *
     *  -> key: regular expression matching the key(s)
     *  -> value: true     -> ignore in every domain
     *            string[] -> ignore in these specific domains
     */
    protected function getIgnoredKeys () : array
    {
        return [];
    }


    /**
     * Returns the names of the functions to mock.
     *
     * @return string[]
     */
    protected function getMockedFunctions () : array
    {
        return [];
    }


    /**
     * Returns the names of the filters to mock.
     *
     * @return string[]
     */
    protected function getMockedFilters () : array
    {
        return [];
    }


    /**
     * Returns the names of the tests to mock.
     *
     * @return string[]
     */
    protected function getMockedTests () : array
    {
        return [];
    }
}
