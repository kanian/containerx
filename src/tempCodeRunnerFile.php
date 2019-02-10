<?php
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\ConstructorLessClass;
use Kanian\ContainerX\Tests\Unit\RepresentativeDependencies\DependencyWithInjectedDependencies;
use stdClass;

$x = new DependencyWithInjectedDependencies(new stdClass, new ConstructorLessClass);
echo $x->isUserDefined();