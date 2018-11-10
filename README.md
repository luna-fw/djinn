# Luna Framework - DI Container

[![Latest Stable Version](https://poser.pugx.org/luna-fw/container/v/stable)](https://packagist.org/packages/luna-fw/container)
[![Total Downloads](https://poser.pugx.org/luna-fw/container/downloads)](https://packagist.org/packages/luna-fw/container)
[![License](https://poser.pugx.org/luna-fw/container/license)](https://packagist.org/packages/luna-fw/container)

This is the Luna Framework (Framework under development) dependency injection container.

This package is completely independent from the rest of the framework. 
This is a rule on Luna Framework packages: keep all the modules independent, 
so they can be used together (as a complete Framework), or independently,
as a complement to another framework or just a a third part package on a 
project.

## Usage

This package implements two contracts, listed below with their methods:
- `ContainerConfigContract` - Holds the methods related with the container config.
  - `bind` - Configures the resolution of a given interface
  - `singleton` - Configures the resolution of a given interface, always returning and immutable instance (singleton).
  - `contextual` - Same as bind, but the resolution depends on "who" is asking for the instance.
  - `contextualSingleton` - Same as contextual, but always return an immutable instance (singleton). 
- `ContainerContract` - Holds the methods related with the DI injection/resolution.
  - `get` - Returns an instance of the required class or interface, resolving all the dependencies, based on the 
  defined configs.
  - `run` - Executes a method, resolving all it's dependencies, based on the defined config.
  
### Examples

TODO

## Testing
To run the tests on the package, all you have to do is go to the root folder,
and run the command:
```
php phpunit
``` 

You can use all the phpunit options.

## Contribute

Luna framework is still a very premature project. I just started, and I'm alone so far.

If you want to contribute, contact me at jeferson@almeida.rocks, and we can discuss how you can help.

Right now, we don't have any process defined for pull requests, so you can help me to define the process and be
part of it.
