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


