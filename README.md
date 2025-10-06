# Symfony DDD Toolkit Bundle

> Symfony messenger extension that makes Domain-Driven Design implementation effortless by providing essential building blocks that keep code understandable.

The core part is a lightweight framework-agnostic library you will use inside the domain layer.
On top of that, this package provides ready to use functionalities:

- ✅ **Library annotations**: Value Objects, Aggregates, Domain Events, Commands
- ✅ **Toolkit services**: Command handler, Process manager for handling domain events as workflow, ...

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
composer require symfony-ddd/toolkit
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require symfony-ddd/toolkit
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    SymfonyDDD\ToolkitBundle\ToolkitBundle::class => ['all' => true],
];
```