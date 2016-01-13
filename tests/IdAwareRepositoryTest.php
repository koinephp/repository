<?php

namespace KoineTest\Repository;

use Koine\Repository\IdAwareRepository;
use Koine\Repository\Test\DbTestCase;
use Zend\Hydrator\ClassMethods;
use Dummy\TestTableEntity;

class IdAwareRepositoryTest extends DbTestCase
{
    /** @var Repository */
    protected $repository;
    /** @var TestTableHydrator */
    protected $hydrator;
    /** @var TestTable */
    protected $entityPrototype;
    /** @var MySql */
    protected $persitence;

    public function setUp()
    {
        parent::setUp();
        $this->persitence = $this->getMock('Koine\Repository\Storage\StorageInterface');
        $this->repository = new IdAwareRepository($this->persitence);
        $this->hydrator = new ClassMethods();
        $this->entityPrototype = new TestTableEntity();

        $this->repository->setHydrator($this->hydrator);
        $this->repository->setEntityPrototype($this->entityPrototype);
    }

    /**
     * @test
     */
    public function canInsertAnEntity()
    {
        $params = array(
            'id'    => null,
            'name'  => 'foo',
            'value' => 'bar',
        );

        $this->persitence
            ->expects($this->once())
            ->method('insert')
            ->with($params)
            ->will($this->returnValue(1));

        $entity = $this->createEntity();
        $this->repository->persist($entity);
        $this->assertEquals(1, $entity->getId());
    }

    /**
     * @test
     */
    public function canUpdateAnEntity()
    {
        $expectedConditions = array('id' => 1);

        $expectedParams = array(
            'id'    => 1,
            'name'  => 'foo',
            'value' => 'bar',
        );

        $this->persitence
            ->expects($this->once())
            ->method('updateWhere')
            ->with($expectedConditions, $expectedParams)
            ->will($this->returnValue(1));

        $entity = $this->createEntity();
        $entity->setId(1);
        $this->repository->persist($entity);
    }

    /**
     * @test
     */
    public function canRemoveEntity()
    {
        $expectedConditions = array('id' => 1);

        $this->persitence
            ->expects($this->once())
            ->method('deleteWhere')
            ->with($expectedConditions)
            ->will($this->returnValue(1));

        $entity = $this->createEntity();
        $entity->setId(1);
        $this->repository->remove($entity);
        $this->assertEquals(null, $entity->getId());
    }

    private function createEntity()
    {
        $entity = new TestTableEntity();
        $entity->setName('foo');
        $entity->setValue('bar');
        return $entity;
    }
}
