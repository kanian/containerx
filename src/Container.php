<?php
namespace Kanian\ContainerX;

use ReflectionClass;
use ReflectionParameter;
use Psr\Container\ContainerInterface;
use Kanian\ContainerX\Exceptions\DependencyNotRegisteredException;
use Kanian\ContainerX\Exceptions\DependencyHasNoDefaultValueException;
use Kanian\ContainerX\Exceptions\DependencyIsNotInstantiableException;

class Container implements ContainerInterface
{
    /**
     * Registered dependencies
     *
     * @var array
     */
    protected $instances = [];

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
     * Finds a dependency by its identifier and returns it.
     *
     * @param string $dependency Identifier of the dependency to look for.
     *
     * @throws NotFoundExceptionInterface  No dependency was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the dependency.
     *
     * @return mixed dependency.
     */
    public function get($dependency)
    {
        if (!$this->has($dependency)) {
            throw new DependencyNotRegisteredException($dependency);
        }
        return $this->resolve($dependency);
    }
    /**
     * Resolves a dependency.
     *
     * @param string $dependency
     * @return the resolved entry
     */
    public function resolve(string $dependency)
    {

        $reflector = $this->getReflector($dependency);
        return $this->concretize($reflector);
    }
    /**
     * Returns a ReflectionClass object representing the dependency's class
     *
     * @param string $dependency
     * @return ReflectionClass
     */
    public function getReflector(string $dependency)
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
    /**
     * Returns an instance of the dependency.
     *
     * @param ReflectionClass $reflector
     * @return any the concrete dependency.
     */
    public function concretize(ReflectionClass $reflector)
    {
        $resolved = [];
        $constructor = $reflector->getConstructor();
        $dependencies = !is_null($constructor) ? $constructor->getParameters() : [];
        if (is_null($constructor) || empty($dependencies)) {
            // get new instance from class
            return $reflector->newInstance();
        }

        foreach ($dependencies as $dependency) {
            if ($dependency->getClass() !== null) { // The dependency is a class
                $typeName = $dependency->getType()->__toString();
                if (!$this->isUserDefined($dependency)) {

                    $this->set($typeName);
                }
                $resolved[] = $this->get($typeName);
            } else { // The dependency is a built-in primitive type
                // check if default value for a parameter is available
                if ($dependency->isDefaultValueAvailable()) {
                    // get default value of parameter
                    $resolved[] = $dependency->getDefaultValue();
                } else {
                    throw new DependencyHasNoDefaultValueException($dependency->name);
                }
            }
        }
        // get new instance with dependencies resolved
        return $reflector->newInstanceArgs($resolved);
    }
    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($dependency)` returning true does not mean that `get($dependency)` will not throw an exception.
     * It does however mean that `get($dependency)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $dependency Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($dependency)
    {
        return isset($this->instances[$dependency]);
    }
    public function unset($dependency){
        unset($this->instances[$dependency]);
    }
    /**
     * Checks if the dependency is an internal PHP class or a user defined one
     *
     * @param ReflectionParameter $parameter
     * @return boolean
     */
    private function isUserDefined(ReflectionParameter $parameter)
    {
        if ($parameter->getType()->isBuiltin()) {
            return false;
        }
        $class = $parameter->getClass();
        $isUserDefined = !$class->isInternal();
        return $isUserDefined;
    }
}