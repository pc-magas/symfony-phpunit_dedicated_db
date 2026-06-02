<?php

declare(strict_types=1);

namespace App\Tests\DB;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

class TestBase extends TestCase
{
    private string $dbName;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [__DIR__ . '/../../src'],
            isDevMode: true,
        );

        $this->dbName = 'test_' . uniqid();

        $params = [
            'dbname'   => null,
            'user'     => getenv("TEST_DB_USERNAME"),
            'password' => getenv("TEST_DB_PASSWORD"),
            'host'     => getenv("TEST_DB_HOST"),
            'driver'   => 'pdo_mysql',
        ];

        $tmpConnection = DriverManager::getConnection($params);

        $tmpConnection->executeStatement(
            sprintf('CREATE DATABASE `%s`', $this->dbName)
        );

        $newParams = array_merge($params, ['dbname' => $this->dbName]);
        $newConnection = DriverManager::getConnection($newParams);

        $this->entityManager = new EntityManager($newConnection,$config);

        // Reconfigure Doctrine to use $this->dbName
        // Then create schema here

        $schemaTool = new SchemaTool($this->entityManager);

        $metadata = $this->entityManager
            ->getMetadataFactory()
            ->getAllMetadata();

        $schemaTool->createSchema($metadata);

        $emConnection = $this->entityManager->getConnection();

        $tables = $emConnection->fetchFirstColumn('SHOW TABLES');

        dump($tables);
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement(
            sprintf('DROP DATABASE IF EXISTS `%s`', $this->dbName)
        );
        $connection->close();

        parent::tearDown();
    }
}
