<?php
namespace Yosimitso\MockDoctrineManager\Tests\Test;

use Yosimitso\MockDoctrineManager\EntityManagerMock;
use Yosimitso\MockDoctrineManager\Tests\Entity\EntityToTest;
use Yosimitso\MockDoctrineManager\Tests\ClassToTestService;
use PHPUnit\Framework\TestCase;  // ASSUMING YOU'RE USING PHPUNIT, BUT IT WORKS WITH ANY TESTING FRAMEWORK

class ClassToTestServiceTest extends TestCase {
    public function testMethodToTest()
    {
        $entityManagerMock = new EntityManagerMock();
        $testedClass = new ClassToTestService($entityManagerMock);
        $testedClass->methodToTest(10);

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

    public function testTransactionnalToTest()
    {
        $entityManagerMock = new EntityManagerMock();
        $testedClass = new ClassToTestService($entityManagerMock);
        $testedClass->transactionnalToTest();

        $this->assertTrue($entityManagerMock->hasBegunTransaction());
        // ASSERT WE PERSISTED THE GOOD ENTITY WITH THE GOOD DATA
        $this->assertEquals(20, $entityManagerMock->getPersistedEntity(EntityToTest::class)->getNb());

        // ASSERT WE FLUSHED THE GOOD ENTITY WITH THE GOOD DATA
        $this->assertEquals(20, $entityManagerMock->getFlushedEntity(EntityToTest::class)->getNb());

        // ASSERT WE COMITTED
        $this->assertTrue($entityManagerMock->hasCommitted());

        // ASSERT WE DIDN'T ROLLBACK
        $this->assertFalse($entityManagerMock->hasRolledback());
    }
}
