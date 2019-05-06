<?php
declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Services\SomeNameSpace\SomeServiceContract;
use App\Contracts\Services\SomeOtherNameSpace\SomeOtherServiceContract;
use App\Contracts\Services\AnotherNameSpace\AnotherServiceContract;
use App\Services\SomeNameSpace\SomeService;
use App\Services\SomeOtherNameSpace\SomeOtherService;
use App\Services\AnotherNameSpace\AnotherService;

class SomeServiceProvider extends ServiceProvider
{
    /**
     * @var bool true defer till needed
     */
    protected $defer = true;

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(SomeServiceContract::class, SomeService::class);
        $this->app->bind(SomeOtherServiceContract::class, SomeOtherService::class);
        $this->app->bind(AnotherServiceContract::class, AnotherService::class);
    }

    /**
     * @return array the provided contracts for this deferred provider
     */
    public function provides(): array
    {
        return [
            SomeServiceContract::class,
            SomeOtherServiceContract::class,
            AnotherServiceContract::class,
        ];
    }
}
