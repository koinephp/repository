<?php

namespace KoineTest\Repository;

use Koine\Repository\Repository;
use Koine\Repository\Test\DbTestCase;
use Zend\Hydrator\ClassMethods;
use Dummy\TestTableEntity;

class RepositoryTest extends DbTestCase
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
        $this->repository = new Repository($this->persitence);
        $this->hydrator = new ClassMethods();
        $this->entityPrototype = new TestTableEntity();

        $this->repository->setHydrator($this->hydrator);
        $this->repository->setEntityPrototype($this->entityPrototype);
    }

    /**
     * @test
     */
    public function testImplementsInterface()
    {
        $this->assertInstanceOf('Koine\Repository\RepositoryInterface', $this->repository);
    }

    /**
     * @test
     */
    public function canGetHydrator()
    {
        $hydrator = $this->repository->getHydrator();
        $this->assertSame($this->hydrator, $hydrator);
    }

    /**
     * @test
     * @expectedException \DomainException
     * @expectedExceptionMessage Hydrator was not set
     */
    public function getHydratorThrowsExceptionWhenHydratorIsNotSet()
    {
        $repository = new Repository($this->persitence);
        $repository->getHydrator();
    }

    /**
     * @test
     */
    public function canGetEntityPrototype()
    {
        $entity = $this->repository->getEntityPrototype();
        $this->assertSame($this->entityPrototype, $entity);
    }

    /**
     * @test
     * @expectedException \DomainException
     * @expectedExceptionMessage Entity Prototype was not set
     */
    public function getEntityPrototypeThrowsExceptionWhenEntityPrototypeIsNotSet()
    {
        $repository = new Repository($this->persitence);
        $repository->getEntityPrototype();
    }

    /**
     * @test
     */
    public function findOneByReturnsAHydratorEntity()
    {
        $params = array('foo' => 'bar');

        $attributes = array(
            'name'  => 'foo',
            'value' => 'bar',
        );

        $this->persitence
            ->expects($this->once())
            ->method('findOneBy')
            ->with($params)
            ->will($this->returnValue($attributes));

        $expectedEntity = new TestTableEntity();
        $expectedEntity->setName('foo');
        $expectedEntity->setValue('bar');

        $entity = $this->repository->findOneBy($params);

        $this->assertEquals($this->createEntity(), $entity);
    }

    /**
     * @test
     */
    public function findAllByReturnsCollection()
    {
        $params = array('foo' => 'bar');

        $resultSet = array(
            array(
                'name'  => 'foo',
                'value' => 'bar',
            ),
        );

        $this->persitence
            ->expects($this->once())
            ->method('findAllBy')
            ->with($params)
            ->will($this->returnValue($resultSet));

        $entities = $this->repository->findAllBy($params);

        $this->assertEquals(array($this->createEntity()), $entities);
    }

    private function createEntity()
    {
        $entity = new TestTableEntity();
        $entity->setName('foo');
        $entity->setValue('bar');
        return $entity;
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function doesNotImplementPersist()
    {
        $this->repository->persist($this->createEntity());
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function doesNotImplementRemove()
    {
        $this->repository->remove($this->createEntity());
    }
}
