<?php

namespace Kanian\ContainerX\Tests\Unit;

use Kanian\ContainerX\Container;
use Kanian\ContainerX\Exceptions\DependencyClassDoesNotExistException;
use Kanian\ContainerX\Exceptions\DependencyHasNoDefaultValueException;
use Kanian\ContainerX\Exceptions\DependencyIsNotInstantiableException;
use Kanian\ContainerX\Exceptions\DependencyNotRegisteredException;
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\ClassThatMarksDateOfInstantiation;
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\ConstructorLessClass;
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\DependencyWithInjectedDependencies;
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\DependencyWithPrimitiveTypeDependencies;
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\DependencyWithPrimitiveTypeDependenciesWithoutDefault;
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\NonInstantiableDependency;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class ContainerTest extends TestCase
{

    protected $instances = [];

    protected function setUp(): void
    {
        $this->dummyDependency = new stdClass();
        $this->container = new Container;
        $this->cantInstantiate = NonInstantiableDependency::class;
        $this->factoryOne = function () {return new ConstructorLessClass;};
        $this->factoryOfDobClass = function ($container) {
            return new ClassThatMarksDateOfInstantiation;
        };

    }

    public function testSingletonize()
    {
        $this->container->singletonize('DobLcass', $this->factoryOfDobClass);
        $dob = $this->container->get('DobLcass');
        $dob2 = $this->container->get('DobLcass');
        $this->assertEquals($dob, $dob2);
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
        $y = new ConstructorLessClass;
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
        $this->assertEquals($x, $y);
    }
    public function testSetThenGetClosure()
    {
        $this->container->set('factoryOne', $this->factoryOne);
        $x = $this->container->get('factoryOne');
        $y = new ConstructorLessClass;
        $this->assertEquals($x, $y);
    }
    public function testCanExecuteClosureChain()
    {
        $this->container->set('factoryStd', function ($c = null) {
            return new stdClass;
        });
        $this->container->set('factoryConstructorLess', function ($c = null) {
            return new ConstructorLessClass;
        });
        $this->container->set('factoryComposed', function ($c) {
            return new DependencyWithInjectedDependencies(
                $c->get('factoryStd'),
                $c->get('factoryConstructorLess')
            );
        });
        $x = $this->container->get('factoryComposed');
        $y = new DependencyWithInjectedDependencies(new stdClass, new ConstructorLessClass);
        $this->assertEquals($x, $y);
    }
    public function testInstantiateDependencyWithPrimitiveTypeDependencies()
    {
        $this->container->set(DependencyWithPrimitiveTypeDependencies::class);
        $this->container->set(ConstructorLessClass::class);
        $x = $this->container->get(DependencyWithPrimitiveTypeDependencies::class);
        $y = new DependencyWithPrimitiveTypeDependencies(3, new ConstructorLessClass);
        $this->assertEquals($x, $y);
    }
    public function testConcretize()
    {
        $this->assertEquals(
            $this->container->concretize(stdClass::class),
            $this->dummyDependency
        );
    }
    public function testHas()
    {
        $this->container->set('dummyDependency', stdClass::class);
        $has = $this->container->has('dummyDependency');
        $this->assertTrue(
            $has
        );
        $this->container->unset('dummyDependency');
        $has = $this->container->has('dummyDependency');
        $this->assertTrue(
            !$has
        );
    }
    public function testResolveParameter()
    {
        $reflector = new ReflectionClass(DependencyWithInjectedDependencies::class);
        $this->container->set(ConstructorLessClass::class);
        $constructor = $reflector->getConstructor();
        $parameters = $constructor->getParameters();
        $this->assertEquals(
            $this->container->resolveParameter($parameters[0]),
            new stdClass
        );
        $this->assertEquals(
            $this->container->resolveParameter($parameters[1]),
            new ConstructorLessClass
        );
    }
    public function testUnsetDependencyIsNotInContainerAnymore()
    {
        $this->container->set('dummyDependency', stdClass::class);
        $this->assertEquals(
            $this->container->get('dummyDependency'),
            $this->dummyDependency
        );
        $this->container->unset('dummyDependency');
        $this->assertTrue(!$this->container->has('dummyDependency'));
    }
    public function testGetReflector()
    {
        $this->container->set('dummyDependency', stdClass::class);
        $this->assertEquals(
            $this->container->getReflector('stdClass'),
            new ReflectionClass('stdClass')
        );
    }
    public function testIsUserDefined()
    {
        $reflector = new ReflectionClass(DependencyWithInjectedDependencies::class);
        $constructor = $reflector->getConstructor();
        $parameters = $constructor->getParameters();
        $this->assertTrue(
            !$this->container->isUserDefined($parameters[0]) // stdClass
        );
        $this->assertTrue(
            $this->container->isUserDefined($parameters[1]) // ConstructorLessClass
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
        $this->expectException(DependencyClassDoesNotExistException::class);
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
