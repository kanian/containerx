<?php
namespace Kanian\ContainerX;

use Closure;
use ReflectionClass;
use ReflectionParameter;
use ReflectionException;
use Psr\Container\ContainerInterface;
use Kanian\ContainerX\Exceptions\DependencyNotRegisteredException;
use Kanian\ContainerX\Exceptions\DependencyClassDoesNotExistException;
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
        return $this->concretize($this->instances[$dependency]);
    }
    /**
     * Returns an instance of the entry.
     *
     * @param mixed $entry
     * @return any the concrete entry.
     */
    protected function concretize($entry)
    {

        //We use closures in order to enable factory composition
        if ($entry instanceof Closure) {
            return $entry($this);
        }

        $resolved = [];
        $reflector = $this->getReflector($entry);
        $constructor = $reflector->getConstructor();
        $dependencies = !is_null($constructor) ? $constructor->getParameters() : [];
        if (is_null($constructor) || empty($dependencies)) {
            // get new instance from class
            return $reflector->newInstance();
        }

        foreach ($dependencies as $parameter) {
            if ($parameter->getClass() !== null) { // The parameter is a class
                $typeName = $parameter->getType()->__toString();
                if (!$this->isUserDefined($parameter)) {

                    $this->set($typeName);
                }
                $resolved[] = $this->get($typeName);
            } else { // The parameter is a built-in primitive type
                // check if default value for a parameter is available
                if ($parameter->isDefaultValueAvailable()) {
                    // get default value of parameter
                    $resolved[] = $parameter->getDefaultValue();
                } else {
                    throw new DependencyHasNoDefaultValueException($parameter->name);
                }
            }
        }
        // get new instance with dependencies resolved
        return $reflector->newInstanceArgs($resolved);
    }
    /**
     * Returns a ReflectionClass object representing the entry's class
     *
     * @param string $entry
     * @return ReflectionClass
     */
    protected function getReflector(string $entry)
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
    public function unset($dependency) {
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
