<?php
namespace Kanian\ContainerX\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class DependencyHasNoDefaultValueException extends Exception implements ContainerExceptionInterface
{

    public function __construct($dependency, $code = 0, Exception $previous = null)
    {
        // some code
        $message = "Dependency {$dependency} can't be instatiated and yet has no default value";
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}
