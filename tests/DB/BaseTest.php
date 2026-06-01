<?php

declare(strict_types=1);

namespace App\Tests\DB;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\EntityManagerInterface;

use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
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

        $connection = DriverManager::getConnection($params);

        $connection->executeStatement(
            sprintf('CREATE DATABASE `%s`', $this->dbName)
        );


        $this->entityManager = new EntityManager($connection,$config);

        // Reconfigure Doctrine to use $this->dbName
        // Then create schema here
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
