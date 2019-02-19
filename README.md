# ContainerX

ContainerX is a little PHP dependency injection container.

# Installation
```bash
$ composer require kanian/containerx
```
# Usage
Let:

The **Car** class be:
```php
class Car {
	protected $driver;
    public function __construct(Driver $driver)
    {
    	$this -> driver = $driver;
    }
    \\\ ... more car code
}
```
And the **HumanDriver** class be:
```php
class HumanDriver implements Driver {
  public function drive()
  {
  	\\\ ... some driving code
  }
}
```

We can use:
## Container functionalities as object methods
In order to access the functionalities of the container as object methods:
```php
use Kanian\ContainerX\Container;

$container = new Container();
$container->set('chauffeur', function($c){ return new HumanDriver;});
$container->set('limo', function($c){ return new Car($c->get('chauffeur'));});

$limo = $container->get('limo');
```
We have used anonymous functions has factories.
Moreover, We could simply register the dependencies we need and let the container instantiate them:
```php
use Kanian\ContainerX\Container;
$container = new Container();
$container->set('chauffeur',HumanDriver::class);
$container->set('limo',Car::class);
$limo = $container->get('limo');
```
The container will know how to construct a Car instance for us.

Alternatively, we can use:
## Container functionalities through the ArrayAccess Interface
For example, we can achieve factory based registration by using the **Kanian\Container\ContainerX** class, which implements the ArrayAccess interface.

```php
use Kanian\ContainerX\ContainerX;

$container = new ContainerX();
$container['chauffeur'] =  HumanDriver::class;
$container['limo'] = Car::class;

$limo = $container['limo'];
```

# Accessing a dependency as a Singleton
In order to ensure that there is receive only one copy of a dependency in the system at a time, you will use the 
```php 
singletonize
``` 
method. like this:
```php 
$factoryOfLimo = function ($container) {
            return new Car($container['chauffeur']);
        };
$container->singletonize('limo', $factoryOfLimo);
$limo = $container['limo'];
``` 
Now you will always get the same instance of Car, but with different instances of HumanDriver.
You will have noticed that a closure is used instead of just the class name as in previous examples. In fact, singletonize only accepts closures. 