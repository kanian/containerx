<?php
namespace Kanian\ContainerX\Tests\Unit\RepresentativeDependencies;

use stdClass;
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\ConstructorLessClass;

class DependencyWithInjectedDependencies{
    public function __construct(stdClass $c1, ConstructorLessClass $c2){
        $this->c1 = $c1;
        $this->c2 = $c2;
    }
}
