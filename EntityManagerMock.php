<?php

namespace Yosimitso\MockDoctrineManager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMapping;

class EntityManagerMock implements EntityManagerInterface
{
    /**
     * Logs persisted entities during the workflow
     * @var array
     */
    protected $persistedEntities;

    /**
     * Logs removed entities during the workflow
     * @var array
     */
    protected $removedEntities;

    /**
     * Logs flushed entities
     * @var array
     */
    protected $flushedEntities;

    /**
     * @var bool
     */
    protected $beganTransaction;

    /**
     * @var bool
     */
    protected $comitted;

    /**
     * @var bool
     */
    protected $rolledback;

    /**
     * EntityManagerMock constructor.
     */
    public function __construct()
    {
        $this->persistedEntities = [];
        $this->flushedEntities = [];
        $this->removedEntities = [];
        $this->beganTransaction = false;
        $this->comitted = false;
        $this->rolledback = false;
    }


    public function getRepository($className) {
        return $this;
    }

    /**
     * Persist an entity, this method inserts or updates the entity
     * @param $entity
     */
    public function persist($entity)
    {
        foreach ($this->persistedEntities as $index => $entityPersisted) {
            if (spl_object_id($entityPersisted) === spl_object_id($entity)) { // UPDATE OBJECT IF IT EXISTS
                $this->persistedEntities[$index] = $entity;
                return;
            }
        }

        $this->persistedEntities[] = $entity; // ENTITY WASN'T PERSISTED YET
    }

    /**
     * @param $entity
     */
    public function remove($entity)
    {
        $this->removedEntities[] = $entity;
    }

    /**
     *
     */
    public function flush() // WARNING : ARRAY PERSISTED ENTITIES AND REMOVED ENTITIES AREN'T EMPTY AFTER FLUSH
    {
        array_push($this->flushedEntities, ...$this->persistedEntities, ...$this->removedEntities);
    }

    /**
     * Returns if a persisted entity has been flushed
     * @param $entity
     * @throws \Exception
     * @return bool
     */
    public function hasBeenFlushed($entity)
    {
        $splId = null;
        foreach ($this->persistedEntities as $persistedEntity) {
            if (spl_object_id($entity) === spl_object_id($persistedEntity)) {
                $splId = spl_object_id($entity);
            }
        }

        if (is_null($splId)) {
            throw new \Exception('The entity '.get_class($entity).' must be persisted before testing if it has been flushed');
        }

        foreach ($this->flushedEntities as $flushedEntity) {
            if (spl_object_id($entity) === spl_object_id($flushedEntity)) {
                return true;
            }
        }

        return false;       // HASN'T BEEN FOUND
    }

    /**
     * Get all persisted entities
     * @return array
     */
    public function getPersistedEntities()
    {
        return $this->persistedEntities;
    }

    /**
     * Get all removed entities
     * @return array
     */
    public function getRemovedEntities()
    {
        return $this->removedEntities;
    }

    /**
     * Get all flushed entities
     * @return array
     */
    public function getFlushedEntities()
    {
        return $this->flushedEntities;
    }

    /**
     * Get the first persisted entity by its class
     * @param $className
     * @return mixed
     * @throws \Exception
     */
    public function getPersistedEntity($className, $position = 1)
    {
        if ($position < 1) {
            throw new \Exception('getPersistedEntity : position can\'t be inferior to 1');
        }
        $i = 0;
        foreach ($this->persistedEntities as $entity) {
            if (get_class($entity) === $className) {
                $i++;
                if ($i === $position) {
                    return $entity;
                }

            }
        }

        throw new \Exception('getPersistedEntity : class '.$className.' not found in persisted entities');
    }

    /**
     * Get the first flushed entity by its class
     * @param $className
     * @return mixed
     * @throws \Exception
     */
    public function getFlushedEntity($className, $position = 1)
    {
        if ($position < 1) {
            throw new \Exception('getFlushedEntity : position can\'t be inferior to 1');
        }
        $i = 0;
        foreach ($this->flushedEntities as $entity) {
            if (get_class($entity) === $className) {
                $i++;
                if ($i === $position) {
                    return $entity;
                }
            }
        }

        throw new \Exception('getFlushedEntity : class '.$className.' not found in flushed entities');
    }

    /**
     * Get removed entity by its class
     * @param $className
     * @return mixed
     * @throws \Exception
     */
    public function getRemovedEntity($className, $position = 1)
    {
        if ($position < 1) {
            throw new \Exception('getRemovedEntity : position can\'t be inferior to 1');
        }
        $i = 0;
        foreach ($this->removedEntities as $entity) {
            if (get_class($entity) === $className) {
                $i++;
                if ($i === $position) {
                    return $entity;
                }

            }
        }

        throw new \Exception('getRemovedEntity : '.$className.' not found in persisted entities');
    }

    /**
     * @return void
     */
    public function beginTransaction()
    {
        $this->beganTransaction = true;
    }

    /**
     * @return void
     */
    public function commit()
    {
        $this->comitted = true;
    }

    /**
     * @return void
     */
    public function rollback()
    {
        $this->rolledback = true;
    }

    /**
     * @return bool
     */
    public function hasBegunTransaction()
    {
        return $this->beganTransaction;
    }

    /**
     * @return bool
     */
    public function hasCommitted()
    {
        return $this->comitted;
    }

    /**
     * Returns if the entity manager has rolledback
     * @return bool
     */
    public function hasRolledback()
    {
        return $this->rolledback;
    }

    public function find($className, $id) {
        throw new \Exception('You need to mock the "find" method to fits your needs about the entity returned');
    }

    public function transactional($func) {
        $this->beginTransaction();
        try {
            $func();
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
        }
    }

    //see contains - lock - close

    /** INACTIVE METHOD FROM DOCTRINE MANAGER INTERFACE */
    public function getCache() {}
    public function getConnection() {}
    public function getExpressionBuilder() {}
    public function createQuery($dql = '') {}
    public function createNamedQuery($name) {}
    public function createNativeQuery($sql, ResultSetMapping $rsm) {}
    public function createNamedNativeQuery($name) {}
    public function createQueryBuilder() {}
    public function getReference($entityName, $id) {}
    public function getPartialReference($entityName, $identifier) {}
    public function close() {}
    public function copy($entity, $deep = false) {}
    public function lock($entity, $lockMode, $lockVersion = null) {}
    public function getEventManager() {}
    public function getConfiguration() {}
    public function isOpen() {}
    public function getUnitOfWork() {}
    public function getHydrator($hydrationMode) {}
    public function newHydrator($hydrationMode) {}
    public function getProxyFactory() {}
    public function getFilters() {}
    public function isFiltersStateClean() {}
    public function hasFilters() {}
    public function clear($objectName = null) {}
    public function detach($object) {}
    public function refresh($object) {}
    public function getClassMetadata($className) {}
    public function getMetadataFactory() {}
    public function initializeObject($obj) {}
    public function contains($object) {}
    public function merge($object) {}
}
