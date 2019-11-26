Test Helpers
============

Installation
------------

```bash
composer require --dev becklyn/test-helpers 
```


Usage
-----

The bundle contains various base tests, that can be used in bundles / libraries.


### Schema Validation Test

This test receives a list of directories and tests that there are no mapping errors in doctrine annotations.

#### Usage

Extend it in your `tests` directory:

`tests/ValidateEntitySchemaTest`:

```php
use Becklyn\TestHelpers\Doctrine\SchemaValidationTest;

class ValidateEntitySchemaTest extends SchemaValidationTest
{
}
```

There are two extension points:

*   `getRootDir(): string` must return the absolute path to the root of your app. By default it assumes that your test is directly
    in the tests directory top level. Change it, if that isn't the case.

*   `getEntityDirs(): string[]` must return the list of absolute paths, where files with mapping info are stored.
    By default set to `getRootDir()/src/Entity`.




### No Missing Translations Test

This tests checks that there are no translations used that are missing in the library.

> You need to install the package `becklyn/translations-extractor`, preferably via the `composer-bin-plugin`:
> 
> ```bash
> composer bin test require becklyn/translations-extractor
> ```

Translations will be extracted from:

*   Validation constraint annotations in entities (on the class itself and its properties)
*   Form field options in `AbstractType`s
*   Usages of the `BackendTranslator` (Mayd-specific)
*   Usages of `ExecutionContext(Interface)` in `@Callback` methods
*   Symfony: `$this->get('translator')->trans('foobar')` in controllers
*   Symfony: `$this->get('translator')->transChoice('foobar')` in controllers
*   Symfony: `$this->addFlash()` and `$this->getFlashBag()->add()` in controllers
*   Symfony: Form type choices
*   Defaults messages from custom validation constraint message properties
*   Specific constructor parameters of special classes


#### Usage

Extend it in your `tests` directory:

`tests/ValidateEntitySchemaTest`:

```php
use Becklyn\PhpCs\Testing\NoMissingTranslationsTest;

class ValidateTranslationsTest extends NoMissingTranslationsTest
{
}
```

There are several extension points:

*   `getLocales(): string[]` returns the list of locales to validate. By default set to `["de", "en"]`.
*   `getDirectoriesWithUsages(): string[]` returns the absolute paths to where there is code that is using translations 
    (like forms, entities, templates, etc..). 
    By default set to `["getRootDir()/src"]`.
*   `getDirectoriesWithTranslations(): string[]`: returns the absolute paths to where the translations are stored.
    The translations files are expected to be in the `yaml` format.
    By default set to `["getRootDir()/src/Resources/translations"]`.
*   `getIgnoredKeys[]: (complex)`: returns the list of keys that should be ignored when validating for missing keys.
    See below for details.
    By default set to `[]`.
*   `getRootDir(): string[]`: must return the absolute path to the root of your app. By default it assumes that your 
    test is directly in the tests directory top level. Change it, if that isn't the case.
*   `registerTwigExtensions (Environment $twig): void`: extension point to register your own twig extensions
    (or mock core extensions), so that the twig parser can successfully parse your templates.


##### Defining Ignored Keys

The ignored keys are an indexed array, where

*   *the key* is a valid RegEx pattern. *Be sure to make it as specific as possible (e.g. by using `^` and `$`).
*   *the value* is the list of domains (`string[]`) where this ignore should apply. Instead of an array you can pass
    `true` to ignore it in any domain.
    
```php
return [
    "~^example\\..*$~" => ["form", "validators"], // ignore example.* in "form" and "validators" domain only 
                                                  // (i.e. will still be reported for "messages" for example)
                                                  
    "~^second\\..*$~" => true,                    // ignore second.* in every domain
];
```
