# Djinn - DI Container

[![Build Status](https://travis-ci.org/luna-fw/container.svg?branch=master)](https://travis-ci.org/luna-fw/container)
[![Latest Stable Version](https://poser.pugx.org/luna-fw/container/v/stable)](https://packagist.org/packages/luna-fw/container)
[![Total Downloads](https://poser.pugx.org/luna-fw/container/downloads)](https://packagist.org/packages/luna-fw/container)
[![License](https://poser.pugx.org/luna-fw/container/license)](https://packagist.org/packages/luna-fw/container)

Djinn is a full featured DI container for PHP. We call it Djinn, because a DI container is pretty much as a Djinn: It
 grants wishes (dependencies) to his master (the caller).

## Install

The package must be installed using composer:
```
composer require luna-fw/djinn
```

Or include `luna-fw/djinn` on your `composer.json` dependencies.

## Usage

This package implements the PSR-11 interfaces and Exceptions:
- `Psr\Container\ContainerInterface`
  - `has(key)` - Return an instance based on a string key
  - `get(key)` - Return true if there is an entry for the given instance
- `Psr\Container\ContainerExceptionInterface`
- `Psr\Container\NotFoundExceptionInterface`

For further details, please refer to: https://www.php-fig.org/psr/psr-11/

We consider the PSR-11 specification too much simple, so we added some additional interfaces to the implementation
 (those extended implementations don't prevent someone to use the container as a PSR-11 compatible container). 

Here are the extended interfaces:
- `ContainerConfigContract` - Holds the methods related with the container config.
  - `bind` - Configures the resolution of a given key
  - `singleton` - Configures the resolution of a given key, always returning and immutable instance (singleton).
  - `contextual` - Same as bind, but the resolution depends on "who" is asking for the instance.
  - `contextualSingleton` - Same as contextual, but always return an immutable instance (singleton). 
- `ExtendedContainerContract` - Holds the methods related with the DI injection/resolution.
  - `getFor` - Returns an instance of the required class or interface, resolving all the dependencies, based on the 
  defined configs. Allow contextual binding.
  - `run` - Executes a method, resolving all it's dependencies, based on the defined config.
  
To use it, you need to have the `vendor/autoload.php` file included. Then all you have to do 
is to instantiate the `Luna\Djinn` class and star using it.

```php
<?php
require 'vendor/autoload.php';

// keep the container instance available globally
$djinn = new \Luna\Djinn();

// configure the bindings
$djinn->bind(FooContract::class, function() {
    return new FooClass('paramether'); 
});

// every time you need an instance that implements FooContract, you call the get method
$foo = $djinn->get(FooContract::class);

echo get_class($foo); // prints FooClass
```

This is the simplest use of the DI container. In a practical environment, you'll want to split all the config for the
container on a specific file (like dependencies.php, or something like that), and you'll try to push all the calls to
the container methods to the "borders" of the system (probably to the router), since PSR-11 recommends to don't
pass the container object inside other objects.

Pay attention that we always have two steps on the resolution:
1. Configure the container, letting him know how it should resolve your dependencies.
1. Use the container

Some important considerations:
- If your wish is a valid class, that don't take any parameters on it's constructor, it's instantiated automatically.
- If your wish is a valid class, and all it's constructor parameters are resolvable by the container, they'll be
 resolved recursively.
- A common  use, is to use an interface as the wish, and a class name as the granted, at this case, the granted class
 will be resolved and granted.
- It's possible to have multiple binding to the same wish (i.e multiple implementations for the same interface
), granting based on who is wishing. Ie. if you call the same interface from different places, you can get different
results based on the context.
- When automatically resolving parameters for a constructor or method, we always try to resolve using the variable
 name first, then by the type hint.
- We also allow to run a function/method through Djinn, that will try to resolve all the dependencies. This is
 specially useful when building a router, then you don't have to manually inject everything through the constructor.
- We also have the singleton binding option, that always return the same instance for the same wish.
 
## Configuration/Binding Examples

Simple binding:
```php
<?php
$djinn->bind(FooClass::class, function() {
    return new FooClass('paramether'); 
});

$djinn->bind('barservice', function() {
    return new BarClass($djinn->get(FooClass::class)); 
});
```

Binding a singleton (the same, but Djinn will always return the same object):
```php
<?php
$djinn->singleton(FooClass::class, function() {
    return new FooClass('paramether'); 
});
```

Binding interfaces to implementations:
```php
<?php
$djinn->bind(FileUploaderInterface::class, YoutubeUploader::class);
```

Contextual binding (lets say that you want to upload photos to google photos and videos to youtube):
```php
<?php
$djinn->contextual(PhotoContoller::class, FileUploaderInterface::class, GooglePhotosUploader::class);
$djinn->contextual(VideoController::class, FileUploaderInterface::class, YoutubeUploader::class);
```

Binding a primitive (and running a method):
```php
<?php
$djinn->contextual(FooClass::class.':sum', '$value1', 3);
$djinn->contextual(FooClass::class.':sum', '$value2', 7);

$djinn->run('sum', $djinn->get(FooClass::class)); // returns 10 
```

To get more examples on this container usage, please refer to the tests under `tests` folder.

## Testing
To run the tests on the package, first run `composer install`, and then go to the root folder,
and run one of the commands below:

On Linux/Unix:
```
./vendor/bin/phpunit
```

On Windows:
```
.\vendor\bin\phpunit.bat
```

Or even (any platform):
```
php ./vendor/phpunit/phpunit/phpunit
```

You can use all the phpunit options.

## Contribute

Luna framework is still a very premature project. I just started, and I'm alone so far.

If you want to contribute, contact me at jeferson@webage.solutions, and we can discuss how you can help.

Right now, we don't have any process defined for pull requests, so you can help me to define the process and be
part of it.
