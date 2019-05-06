<?php
declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Services\BindNameSpace\BindContract;
use App\Contracts\Services\BindIfNameSpace\BindIfContract;
use App\Contracts\Services\SingletonNameSpace\SingletonContract;
use App\Contracts\Services\InstanceNameSpace\InstanceContract;
use App\Contracts\Services\ExtendNameSpace\ExtendContract;
use App\Contracts\Services\BindMethodNameSpace\BindMethodContract;
use App\Contracts\Services\RefreshNameSpace\RefreshContract;
use App\Contracts\Services\RebindingNameSpace\RebindingContract;
use App\Contracts\Services\SomeOtherBindingMethodNameSpace\SomeOtherBindingMethodContract;
use App\Services\BindNameSpace\Bind;
use App\Services\BindIfNameSpace\BindIf;
use App\Services\SingletonNameSpace\Singleton;
use App\Services\InstanceNameSpace\Instance;
use App\Services\ExtendNameSpace\Extend;
use App\Services\BindMethodNameSpace\BindMethod;
use App\Services\RefreshNameSpace\Refresh;
use App\Services\RebindingNameSpace\Rebinding;
use App\Services\SomeOtherBindMethodNameSpace\SomeOtherBindingMethod;


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
        $this->app->bind(BindNameSpaceContract::class, BindNameSpace::class);
        $this->app->bindIf(BindIfNameSpaceContract::class, BindIfNameSpace::class);
        $this->app->singleton(SingletonNameSpaceContract::class, SingletonNameSpace::class);
        $this->app->instance(InstanceNameSpaceContract::class, InstanceNameSpace::class);
        $this->app->extend(ExtendNameSpaceContract::class, ExtendNameSpace::class);
        $this->app->bindMethod(BindMethodNameSpaceContract::class, BindMethodNameSpace::class);
        $this->app->refresh(RefreshNameSpaceContract::class, RefreshNameSpace::class);
        $this->app->rebinding(RebindingNameSpaceContract::class, RebindingNameSpace::class);
        $this->app->someOtherBindingMethod(SomeOtherBindingMethodContract::class, SomeOtherBindingMethod::class);
    }

    /**
     * @return array the provided contracts for this deferred provider
     */
    public function provides(): array
    {
        return [];
    }
}
