<?php
namespace Kanian\ContainerX;

use Kanian\ContainerX\Exceptions\DependencyIsNotInstantiableException;
use Kanian\ContainerX\Exceptions\DependencyNotRegisteredException;
use ReflectionClass;

class Container
{
    /**
     * Registered dependencies
     *
     * @var array
     */
    private $instances = [];

    /**
     * Undocumented function
     *
     * @param [type] $abstract
     * @param [type] $concrete
     * @return void
     */
    public function set($abstract, $concrete = null)
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }
        $this->instances[$abstract] = $concrete;
    }

    public function get($dependency, $parameters = [])
    {
        if (!isset($this->instances[$dependency])) {
            throw new DependencyNotRegisteredException($dependency);
        }
        return $this->resolve($dependency, $parameters);
    }

    public function resolve($dependency, $parameters = [])
    {
        $reflector = $this->getConstructor($dependency, $parameters);

        return $this->concretize($reflector, $parameters);

    }
    public function getConstructor($dependency, $parameters = [])
    {
        $concrete = $this->instances[$dependency];
        $reflector = new ReflectionClass($concrete);
        // check if class is instantiable
        if (!$reflector->isInstantiable()) {
            throw new DependencyIsNotInstantiableException($dependency);
        }
        // get class constructor
        return $reflector;
    }
    public function concretize($reflector, $parameters = [])
    {
        $constructor = $reflector->getConstructor();
        $parameters = !is_null($constructor) ? $constructor->getParameters() : [];
        if (is_null($constructor) || empty($parameters)) {
            // get new instance from class
            return $reflector->newInstance();
        }

        // We are faced with a dependency with a constructor taking arguments
        // Hence, we resolve the dependencies of the parameters, if any.
        //$dependencies = $this->getDependencies($parameters);  
    }
}
