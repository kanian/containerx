<?php

namespace Kanian\ContainerX\Tests\Unit;

use stdClass;
use ReflectionException;
use PHPUnit\Framework\TestCase;
use Kanian\ContainerX\Container;
use Kanian\ContainerX\Exceptions\DependencyNotRegisteredException;
use Kanian\ContainerX\Exceptions\DependencyHasNoDefaultValueException;
use Kanian\ContainerX\Exceptions\DependencyIsNotInstantiableException;
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\ConstructorLessClass;
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\DependencyWithInjectedDependencies;
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\DependencyWithPrimitiveTypeDependencies;
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\DependencyWithPrimitiveTypeDependenciesWithoutDefault;

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
        $x = $this->container->get('dependency');
        $y =  new ConstructorLessClass;
        $this->assertEquals(
            $x,
            $y
        );
    }
    public function testInstantiateDependencyWithRegisteredDependecies()
    {
        $this->container->set(DependencyWithInjectedDependencies::class);
        $this->container->set(ConstructorLessClass::class);
        $x = $this->container->get(DependencyWithInjectedDependencies::class);
        $y = new DependencyWithInjectedDependencies(new stdClass, new ConstructorLessClass);
        $this->assertEquals( $x, $y);
    }
    public function testInstantiateDependencyWithPrimitiveTypeDependencies()
    {
        $this->container->set(DependencyWithPrimitiveTypeDependencies::class);
        $this->container->set(ConstructorLessClass::class);
        $x = $this->container->get(DependencyWithPrimitiveTypeDependencies::class);
        $y = new DependencyWithPrimitiveTypeDependencies(3, new ConstructorLessClass);
        $this->assertEquals( $x, $y);
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
        $this->container->get('CANT_BE_INSTANTIATE');
    }
    public function testExceptionThrownWhenGettingPrimitiveTypeDependencyWithNoDefault()
    {
        $this->container->set(DependencyWithPrimitiveTypeDependenciesWithoutDefault::class);
        $this->expectException(DependencyHasNoDefaultValueException::class);
        $this->container->get(DependencyWithPrimitiveTypeDependenciesWithoutDefault::class);
    }
}
