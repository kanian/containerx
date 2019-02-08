<?php
namespace Kanian\ContainerX\Exceptions;

use Exception;

class DependencyIsNotInstantiableException extends Exception{

    public function __construct($dependency, $code = 0, Exception $previous = null) {
        // some code
        $message = "Dependency {$dependency} is not instantiable";
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}