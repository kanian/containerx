<?php

namespace Kanian\ContainerX\Tests\Unit;

use stdClass;
use ReflectionException;
use PHPUnit\Framework\TestCase;
use Kanian\ContainerX\Container;
use Kanian\ContainerX\Exceptions\DependencyNotRegisteredException;
use Kanian\ContainerX\Exceptions\DependencyIsNotInstantiableException;
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\ConstructorLessClass;

class ContainerTest extends TestCase
{

    protected $instances = [];

    protected function setUp(): void
    {
        $this->dummyDependency = new stdClass();
        $this->container = new Container;
        $this->cantInstantiate = function () {$doNothing = 'nothing';};
    }

    public function testSetInstantialbleDependencyWithNoArgumentConstructorAndRetrieveIt()
    {
        $this->container->set('dummyDependency', stdClass::class);
        $this->assertEquals(
            $this->container->get('dummyDependency'),
            $this->dummyDependency
        );
    }
    public function testSetInstantiableDependencyWithNoConstructorAndRetrieveIt()
    {
        $this->container->set('dependency', ConstructorLessClass::class);
        $this->assertEquals(
            $this->container->get('dependency'),
            new ConstructorLessClass
        );
    }
    public function testExceptionThrownWhenDependencyNotRegistered()
    {
        $this->expectException(DependencyNotRegisteredException::class);
        $this->container->get('stdClass');
    }

    public function testExceptionThrownWhenGettingNonexistentClass()
    {
        $this->container->set('dummyDependency', 'dummyDependency');
        $this->expectException(ReflectionException::class);
        $this->container->get('dummyDependency');
    }
    public function testExceptionThrownWhenGettingNonInstantiableDependency()
    {
        $this->container->set('CANT_BE_INSTANTIATE', $this->cantInstantiate);
        $this->expectException(DependencyIsNotInstantiableException::class);
        $this->container->getConstructor('CANT_BE_INSTANTIATE');
    }
    /*public function testSetObjectCanBeRetrieved(){
$this->container->set('dummyDependency', $this->dummyDependency);
$this->assertEquals(
$this->container->get('dummyDependency'),
$this->dummyDependency
);
}*/
}
