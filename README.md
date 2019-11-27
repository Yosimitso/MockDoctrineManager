Yosimitso/DoctrineManagerMock
=============================

Usage
---------
Mock of Doctrine\ORM\EntityManager, this bundle logs persisted and flushed entities, and basic operations like transactions
and enable you to check if entities are correctly registered and if your workflow is respected
Please note that this bundle is new and focus on most current operations, including :
- persist
- flush
- remove
- beginTransaction
- commit
- rollback

Feel free to submit a PR to expand the features

Example
---------------
Simple example to check if your entity was actually persisted and with good data,

Example of class to test :

````php
<?php

namespace App\Services;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\EntityToTest;

class ClassToTest
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
namespace App\Entity;
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

Test of EntityToTest
```php
<?php
use Yosimitso\MockDoctrineManager\EntityManagerMock;
use App\Entity\EntityToTest;
use PHPUnit\Framework\TestCase;                     // ASSUMING YOU'RE USING PHPUNIT, BUT IT WORKS WITH ANY TESTING FRAMEWORK

class MyTest extends TestCase {
    public function testMethodToTest()
    {
        $entityManagerMock = new EntityManagerMock();
        $testedClass = new ClassToTest($entityManagerMock);
        $testedClass->methodToTest(10);

        $this->assertTrue($entityManagerMock->hasBegunTransaction());
        // ASSERT WE PERSISTED THE GOOD ENTITY WITH THE GOOD DATA
        $this->assertEquals(10, $entityManagerMock->getPersistedEntity(EntityToTest::class)->getNb());

        // ASSERT WE FLUSHED THE GOOD ENTITY WITH THE GOOD DATA
        $this->assertEquals(10, $entityManagerMock->getFlushedEntity(EntityToTest::class)->getNb());

        // ASSERT WE COMITTED
        $this->assertTrue($entityManagerMock->hasCommitted());

        // ASSERT WE DIDN'T ROLLBACK
        $this->assertFalse($entityManagerMock->hasRollbacked());
    }
}
````

**What if I persisted two or more instance of a class ?**
Use the 'position' argument of getPersistedEntity, example if I want to get the second persisted "YourClass" : getPersistedEntity(YourClass::class, 2);


