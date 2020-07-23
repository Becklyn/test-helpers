<?php declare(strict_types=1);

namespace Becklyn\TestHelpers\Doctrine;

use Becklyn\Rad\Doctrine\Types\SerializedType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaValidator;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;

abstract class SchemaValidationTest extends TestCase
{
    private static $typesInitialized = false;

    /**
     * Tests that the Doctrine schema is valid
     */
    public function testSchemaIsValid () : void
    {
        $entityDirs = \array_filter($this->getEntityDirs(), "is_dir");

        if (empty($entityDirs))
        {
            self::markTestSkipped("No valid entity dirs found.");
            return;
        }

        $config = Setup::createAnnotationMetadataConfiguration(
            $this->getEntityDirs(),
            true,
            null,
            null,
            false
        );
        $entityManager = EntityManager::create([
            "url" => "sqlite:///:memory:",
        ], $config);

        if (!self::$typesInitialized)
        {
            if (\class_exists(SerializedType::class))
            {
                Type::addType(SerializedType::NAME, SerializedType::class);
            }

            self::$typesInitialized = true;
        }

        $validator = new SchemaValidator($entityManager);
        $issues = $validator->validateMapping();

        $log = \json_encode($issues, \JSON_PRETTY_PRINT);
        self::assertEmpty($issues, "Mapping errors should be empty, received:\n{$log}");
    }


    /**
     * @return string
     */
    protected function getEntityDirs () : array
    {
        $root = \rtrim($this->getRootDir(), "/");

        return  [
            "{$root}/src/Entity",
        ];
    }


    /**
     * Returns the path to the root dir
     */
    protected function getRootDir () : string
    {
        $r = new \ReflectionClass($this);
        return \dirname($r->getFileName(), 2);
    }
}
