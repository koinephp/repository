<?php

namespace Koine\Repository;

use Koine\Repository\Exception\RecordNotFoundException;
use Koine\Repository\Storage\StorageInterface;
use Zend\Hydrator\HydratorInterface;

abstract class RepositoryAbstract implements RepositoryInterface
{
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var object
     */
    private $entityPrototype;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return MySql
     */
    protected function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param array $params
     *
     * @return object
     *
     * @throws RecordNotFoundException
     */
    public function findOneBy(array $params)
    {
        $rawData = $this->getStorage()->findOneBy($params);

        return $this->createEntity($rawData);
    }

    /**
     * @param array $rawData
     *
     * @return object
     */
    private function createEntity(array $rawData = array())
    {
        $entity = clone $this->getEntityPrototype();

        if ($rawData) {
            $this->getHydrator()->hydrate($rawData, $entity);
        }

        return $entity;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function findAllBy(array $params = array())
    {
        $rawCollection = $this->getStorage()->findAllBy($params);
        $collection = array();

        foreach ($rawCollection as $rawData) {
            $collection[] = $this->createEntity($rawData);
        }

        return $collection;
    }

    /**
     * @param HydratorInterface $hydrator
     *
     * @return self
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    /**
     * @return HydratorInterface
     */
    public function getHydrator()
    {
        if ($this->hydrator === null) {
            throw new \DomainException('Hydrator was not set');
        }

        return $this->hydrator;
    }

    /**
     * @param object $entityPrototype
     *
     * @return self
     */
    public function setEntityPrototype($entityPrototype)
    {
        $this->entityPrototype = $entityPrototype;

        return $this;
    }

    /**
     * @return object
     */
    public function getEntityPrototype()
    {
        if ($this->entityPrototype === null) {
            throw new \DomainException('Entity Prototype was not set');
        }

        return $this->entityPrototype;
    }
}
