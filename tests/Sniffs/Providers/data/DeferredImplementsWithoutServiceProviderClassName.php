<?php
declare(strict_types=1);

class SomeProviderClass extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(SomeServiceContract::class, function () {
            return new SomeService();
        });
    }

    /**
     * @return array the provided contracts for this deferred provider
     */
    public function provides(): array
    {
        return [
            AnotherServiceContract::class,
        ];
    }
}
