<?php
declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Services\SomeNameSpace\SomeServiceContract;
use App\Contracts\Services\SomeOtherNameSpace\SomeOtherServiceContract;
use App\Contracts\Services\AnotherNameSpace\AnotherServiceContract;
use App\Services\SomeNameSpace\SomeService;
use App\Services\SomeOtherNameSpace\SomeOtherService;
use App\Services\AnotherNameSpace\AnotherService;

class DeferrableWithExtraBindingsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public $bindings = [
        SomeServiceContract::class => SomeService::class,
        SomeOtherServiceContract::class => SomeService::class,
        AnotherServiceContract::class => SomeService::class,
    ];

    public function provides(): array
    {
        return [
            SomeServiceContract::class,
            AnotherServiceContract::class,
        ];
    }
}
