<?php
namespace Kanian\ContainerX;

use Closure;
use Kanian\ContainerX\Exceptions\DependencyClassDoesNotExistException;
use Kanian\ContainerX\Exceptions\DependencyHasNoDefaultValueException;
use Kanian\ContainerX\Exceptions\DependencyIsNotInstantiableException;
use Kanian\ContainerX\Exceptions\DependencyNotRegisteredException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * A psr-11 compliant container
 */
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
        $entry = $this->instances[$dependency];
        //We use closures in order to enable factory composition
        if ($entry instanceof Closure) {
            return $entry($this);
        }
        return $this->concretize($entry);
    }
    /**
     * Returns an instance of the entry.
     *
     * @throws DependencyIsNotInstantiableException
     * @param string $entry
     * @return any the concrete entry.
     */
    public function concretize(string $entry)
    {
        $resolved = [];
        $reflector = $this->getReflector($entry);
        $constructor = null;
        $parameters = [];
        if ($reflector->isInstantiable()) {
            $constructor = $reflector->getConstructor();
            if (!is_null($constructor)) {
                $parameters = $constructor->getParameters();
            }
        } else {
            throw new DependencyIsNotInstantiableException($className);
        }
        if (is_null($constructor) || empty($parameters)) {
            // return new instance from class
            return $reflector->newInstance();
        }

        foreach ($parameters as $parameter) {
            $resolved[] = $this->resolveParameter($parameter);
        }
        // get new instance with dependencies resolved
        return $reflector->newInstanceArgs($resolved);
    }
    /**
     * Resolves the dependency's parameters
     *
     * @throws DependencyHasNoDefaultValueException
     * @param ReflectionParameter $parameter
     * @return mixed a resolved parameter
     */
    public function resolveParameter(ReflectionParameter $parameter)
    {
        if ($parameter->getClass() !== null) { // The parameter is a class
            $typeName = $parameter->getType()->__toString();
            if (!$this->isUserDefined($parameter)) { //The parameter is not user defined

                $this->set($typeName); // Register it
            }
            return $this->get($typeName); // Instantiate it
        } else { // The parameter is a built-in primitive type

            if ($parameter->isDefaultValueAvailable()) { // check if default value for a parameter is available

                return $parameter->getDefaultValue(); // get default value of parameter
            } else {
                throw new DependencyHasNoDefaultValueException($parameter->name);
            }
        }
    }
    /**
     * Returns a ReflectionClass object representing the entry's class
     *
     * @throws DependencyIsNotInstantiableException
     * @throws DependencyClassDoesNotExistException
     * @param string $entry
     * @return ReflectionClass
     */
    public function getReflector(string $entry)
    {
        try {
            $reflector = new ReflectionClass($entry);
            // check if class is instantiable
            if (!$reflector->isInstantiable()) {
                throw new DependencyIsNotInstantiableException($entry);
            }
            // get class constructor
            return $reflector;
        } catch (ReflectionException $ex) {
            throw new DependencyClassDoesNotExistException($entry);
        }

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
    /**
     * Removes an entry from the container
     *
     * @param string $dependency
     * @return void
     */
    function unset($dependency) {
        unset($this->instances[$dependency]);
    }
    /**
     * Checks if the dependency is an internal PHP class or a user defined one
     *
     * @param ReflectionParameter $parameter
     * @return boolean
     */
    public function isUserDefined(ReflectionParameter $parameter)
    {
        if ($parameter->getType()->isBuiltin()) {
            return false;
        }
        $class = $parameter->getClass();
        $isUserDefined = !$class->isInternal();
        return $isUserDefined;
    }
}
