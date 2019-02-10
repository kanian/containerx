<?php
namespace Kanian\ContainerX\Tests\Unit\RepresentativeDependencies;

use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\ConstructorLessClass;

class DependencyWithPrimitiveTypeDependencies{
    public function __construct(int $c1 = 3, ConstructorLessClass $c2){
        $this->c1 = $c1;
        $this->c2 = $c2;
    }
}