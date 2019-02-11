<?php
namespace Kanian\ContainerX\Tests\Unit;

use stdClass;
use PHPUnit\Framework\TestCase;
use Kanian\ContainerX\ContainerX;

class ContainerXTest extends TestCase
{

    public function setUp():void
    {
        $this->container = new ContainerX;
    }

    public function testGivenNullOffsetButAValueInsertsDependency()
    {
        $this->container[] = stdClass::class;
        $this->assertTrue($this->container->has(stdClass::class));
    }

    public function testGivenBothOffsetAndValueInsertsDependency()
    {
        $this->container['aClass'] = stdClass::class;
        $this->assertTrue($this->container->has('aClass'));
    }

    public function testCanRetrieveDependency()
    {
        $this->container[] = stdClass::class;
        $retrieved = $this->container[stdClass::class];
        $this->assertTrue($retrieved  instanceof stdClass);
    }

    public function testGivenBothOffsetAndValueCanRetrieveDependency()
    {
        $this->container['aClass'] = stdClass::class;
        $retrieved = $this->container['aClass'];
        $this->assertTrue($retrieved  instanceof stdClass);
    }

    public function testUnsetDependencyIsNotInContainerAnymore()
    {
        $this->container['aClass'] = stdClass::class;
        $retrieved = $this->container['aClass'];
        $this->assertTrue($retrieved  instanceof stdClass);
        unset($this->container['aClass']);
        $this->assertTrue(!isset($this->container['aClass']));
    }

}
