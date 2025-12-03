
# Jardis Factory
![Build Status](https://github.com/jardiscore/factory/actions/workflows/ci.yml/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.2-777BB4.svg)](https://www.php.net/)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![PSR-4](https://img.shields.io/badge/autoload-PSR--4-blue.svg)](https://www.php-fig.org/psr/psr-4/)
[![PSR-11](https://img.shields.io/badge/PSR--11-Container-blue.svg)](https://www.php-fig.org/psr/psr-11/)
[![PSR-12](https://img.shields.io/badge/code%20style-PSR--12-blue.svg)](https://www.php-fig.org/psr/psr-12/)
[![Coverage](https://img.shields.io/badge/coverage->95%25-brightgreen)](https://github.com/jardiscore/factory)

## Purpose
The `Factory` serves as a flexible instantiation and access factory for classes, supporting optional versioning and dependency injection (DI).

## Description for Developers
The class provides methods to dynamically create instances of classes or retrieve them from a provided container. It supports:
- **Class versioning** through a `ClassVersionInterface` (optional).
- **Dependency Injection** via a `Psr\Container\ContainerInterface` (optional).
- Dynamic construction of classes with parameter support using reflection.

### Key Features
- **PSR-11 Container Integration**: Automatically uses the container to resolve dependencies if available.
- **Optional Class Versioning**: Supports versioned class resolution through `ClassVersionInterface`.
- **Flexible Parameter Passing**: Supports both variadic and array-based parameter passing.
- **Fallback to Reflection**: If no container is provided, or the class is not in the container, instances are created via reflection.

### Notes
- Both `ContainerInterface` and `ClassVersionInterface` are optional dependencies.
- If a container is provided and has the requested class, it will be retrieved from the container (ignoring parameters).
- If no container is available or the class is not registered, the instance is created dynamically using reflection with provided parameters.
- The `ClassVersionInterface` can return a class name (string), an object instance, or null.

### Basic Usage
```php
use JardisCore\Factory\Factory;

// Simple usage without dependencies
$factory = new Factory();
$instance = $factory->get(MyClass::class);

// With parameters (variadic)
$instance = $factory->get(MyClass::class, null, $param1, $param2);

// With parameters (array)
$instance = $factory->get(MyClass::class, null, [$param1, $param2]);
```

## Example code without ClassVersion and without DI container

```php
use JardisCore\Factory\Factory;

$factory = new Factory();

// Simple instantiation
$myClassInstance = $factory->get(MyClass::class);

// With constructor parameters
$myClassInstance = $factory->get(MyClassWithTwoParameters::class, null, $var1, $var2);
```

## Example code with ClassVersion and without DI container

```php
use JardisCore\Factory\Factory;
use JardisCore\Contract\ClassVersion\ClassVersionInterface;

// Your ClassVersionInterface implementation
$classVersion = new YourClassVersionImplementation();

$factory = new Factory(null, $classVersion);

// ClassVersion may return a different class based on versioning logic
$myClassInstance = $factory->get(MyClass::class);

// With specific version parameter
$myClassInstance = $factory->get(MyClass::class, 'v2.0', $var1, $var2);
```

## Example code with ClassVersion and DI container

```php
use JardisCore\Factory\Factory;
use JardisCore\Contract\ClassVersion\ClassVersionInterface;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$container = new YourContainer();

/** @var ClassVersionInterface $classVersion */
$classVersion = new YourClassVersionImplementation();

$factory = new Factory($container, $classVersion);

// ClassVersion determines the actual class, container provides the instance
$myClassInstance = $factory->get(MyClass::class);

// With version parameter
$myClassInstance = $factory->get(MyClass::class, 'v2.0');
```

## Installation

### Composer

```bash
composer require jardiscore/factory
```

### GitHub

```bash
git clone https://github.com/jardiscore/factory.git
cd factory
make install
```

---

## Contents in the GitHub Repository

- **Source Files**:
    - src
    - tests
- **Support**:
    - Docker Compose
    - .env
    - pre-commit-hook.sh
    - `Makefile` Simply run `make` in the console
- **Documentation**:
    - README.md
