<?php
namespace Kanian\ContainerX\Tests\Unit\RepresentativeDependencies;

class ClassThatMarksDateOfInstantiation
{
    private $dob;
    
    public function __construct(){
        $this->dob = new \DateTimeImmutable;
    }

    /**
     * Get the value of dob
     */ 
    public function getDob()
    {
        return $this->dob;
    }
}