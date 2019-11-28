Yosimitso/DoctrineManagerMock
=============================

Usage
---------
Mock of Doctrine\ORM\EntityManager which implements EntityManagerInterface, this bundle logs persisted, removed and flushed entities, and basic operations like transactions.
It enables you to check if entities are correctly registered and if your workflow is respected

Please note that this bundle is new and focus on most current operations, including :
- persist
- flush
- remove
- beginTransaction
- commit
- rollback

Feel free to submit a PR to expand the features

Installation
--------------
````
composer require "yosimitso/doctrinemanagermock" --dev
````

Example
---------------
Simple example to check if your entity was actually persisted and with good data,

Example from the actual test of this bundle :

````php
<?php

namespace Yosimitso\MockDoctrineManager\Tests;
use Doctrine\ORM\EntityManagerInterface;
use Yosimitso\MockDoctrineManager\Tests\Entity\EntityToTest;

class ClassToTestService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) // YOU CAN TYPE ENTITYMANAGERINTERFACE
    {
        $this->entityManager = $entityManager;
        $this->dataToTest = [];
    }

    public function methodToTest($nb)
    {
        $newEntity = new EntityToTest;
        $newEntity->setNb($nb);

        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
        }


    }
}
````

Example of entity
```php
<?php
namespace Yosimitso\MockDoctrineManager\Tests\Entity;
class EntityToTest
{
    private $nb;

    public function setNb($nb)      // CLASSIC SETTER
    {
        $this->nb = $nb;
    }

    public function getNb()         // CLASSIC GETTER
    {
        return $this->nb;
    }
}

````

Test
```php
<?php
namespace Yosimitso\MockDoctrineManager\Tests\Test;

use Yosimitso\MockDoctrineManager\EntityManagerMock;
use Yosimitso\MockDoctrineManager\Tests\Entity\EntityToTest;
use Yosimitso\MockDoctrineManager\Tests\ClassToTestService;
use PHPUnit\Framework\TestCase;  // ASSUMING YOU'RE USING PHPUNIT, BUT IT WORKS WITH ANY TESTING FRAMEWORK

class ClassToTestServiceTest extends TestCase {
    public function testMethodToTest()
    {
        $entityManagerMock = new EntityManagerMock(); // THIS BUNDLE 
        $testedClass = new ClassToTestService($entityManagerMock); // THE CLASS TO TEST
        $testedClass->methodToTest(10);     // THE METHOD TO TEST

        // ASSERT WE BEGAN THE TRANSACTION
        $this->assertTrue($entityManagerMock->hasBegunTransaction());

        // ASSERT WE PERSISTED THE GOOD ENTITY WITH THE GOOD DATA
        $this->assertEquals(10, $entityManagerMock->getPersistedEntity(EntityToTest::class)->getNb());

        // ASSERT WE FLUSHED THE GOOD ENTITY WITH THE GOOD DATA
        $this->assertEquals(10, $entityManagerMock->getFlushedEntity(EntityToTest::class)->getNb());

        // ASSERT WE COMITTED
        $this->assertTrue($entityManagerMock->hasCommitted());

        // ASSERT WE DIDN'T ROLLBACK
        $this->assertFalse($entityManagerMock->hasRolledback());
    }
}
````

**What if I persisted two or more instance of a class ?**

Use the 'position' argument of getPersistedEntity, example if I want to get the second persisted "YourClass" : getPersistedEntity(YourClass::class, 2);

**My code use 'findBy' methods**

This bundle can't guess what you're expecting from 'findBy',
so just create a mock of this bundle like any other class, in order to mock 'findBy'

Example with PHPUnit :
````php
$yourEntity = new Article(); // EXAMPLE OF AN ENTITY

$yourEntity->setName('hello');

$entityManager = $this->getMockBuilder(EntityManagerMock)
                    ->setMethods(['findBy']);

$entityManager->methods('findBy')->willReturn($yourEntity);
````

API
-------------------
````php
/* returns this entity persisted in n position (among its namespace) */
getPersistedEntity($className, $position = 1): mixed

/* returns this entity removed in n position (among its namespace) */
getRemovedEntity($className, $position = 1): mixed

/* returns this entity flushed in n position (among its namespace) */
getFlushedEntity($className, $position = 1): mixed

/** returns the list of persisted entites */
getPersistedEntities(): array

/** returns the list of flushed entites */
getFlushedEntities(): array

hasBegunTransaction(): boolean
hasComitted(): boolean
hasRolledback(): boolean
````
