<?php
namespace Kanian\ContainerX\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class DependencyIsNotInstantiableException extends Exception implements ContainerExceptionInterface
{

    public function __construct($dependency, $code = 0, Exception $previous = null)
    {
        // some code
        $message = "Dependency {$dependency} is not instantiable";
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}
