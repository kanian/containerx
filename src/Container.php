<?php
namespace Kanian\ContainerX;

use ReflectionClass;
use Psr\Container\ContainerInterface;
use Kanian\ContainerX\Exceptions\DependencyNotRegisteredException;
use Kanian\ContainerX\Exceptions\DependencyIsNotInstantiableException;


class Container implements ContainerInterface
{
    /**
     * Registered dependencies
     *
     * @var array
     */
    private $instances = [];

    /**
     * Sets a dependency.
     *
     * @param string $abstract
     * @param  $concrete
     * @return void
     */
    public function set($abstract, $concrete = null)
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }
        $this->instances[$abstract] = $concrete;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($dependency)
    {
        if (!$this->has($dependency)) {
            throw new DependencyNotRegisteredException($dependency);
        }
        return $this->resolve($dependency);
    }

    public function resolve($dependency)
    {
        $reflector = $this->getConstructor($dependency);

        return $this->concretize($reflector);

    }
    public function getConstructor($dependency)
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
    public function concretize($reflector)
    {
        $resolved = [];
        $constructor = $reflector->getConstructor();
        $dependencies = !is_null($constructor) ? $constructor->getParameters() : [];
        if (is_null($constructor) || empty($dependencies)) {
            // get new instance from class
            return $reflector->newInstance();
        }

        foreach($dependencies as $dependency){
            $resolved = $this->get($dependency);
        }
        // We are faced with a dependency with a constructor taking arguments
        // Hence, we resolve the dependencies of the parameters, if any.
        //$dependencies = $this->getDependencies($parameters);  
    }
    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id){
        return isset($this->instances[$id]);
    }
}