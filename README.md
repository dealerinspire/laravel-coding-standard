# Laravel Coding Standard

Dealer Inspire Laravel Coding Standard provides sniffs that help developers write better Laravel code.

## Installation

Install the package by requiring it with Composer.

```bash
composer require dealerinspire/laravel-coding-standard
```

## Usage

First you need to make sure that `vendor/dealerinspire/laravel-coding-standard` is in your `phpcs.xml` file's `installed_paths` setting.

```xml
<config name="installed_paths" value="vendor/dealerinspire/laravel-coding-standard"/>
```

Then you can use any of the sniffs provided in this package.

```xml
<rule ref="DealerInspireLaravelCodingStandard.Providers.DeferredProviders"/>
```

## Provided Sniffs

### DealerInspireLaravelCodingStandard.Models.NoGuardedAttributes

Checks that no classes use the `protected $guarded` attribute. Useful for any projects that strictly enforce the use of explicitly whitelisting fillable attributes.

### DealerInspireLaravelCodingStandard.Providers.DeferredProviders

Checks all deferred service providers to ensure that any bindings in the file are also included in the `provides` array.
Note that your service provider class must end with the conventional suffix `ServiceProvider`. e.g. `FooServiceProvider.php`

## License

MIT © [Dealer Inspire](https://www.dealerinspire.com/)
