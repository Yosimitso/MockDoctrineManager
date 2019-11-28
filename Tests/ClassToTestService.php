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
    }

    public function methodToTest($nb)
    {
        $newEntity = new EntityToTest;
        $newEntity->setNb($nb);

        /** CLASSIC WORKFLOW */
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
