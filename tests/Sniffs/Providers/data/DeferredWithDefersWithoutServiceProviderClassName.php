<?php
declare(strict_types=1);

class SomeProviderClass extends ServiceProvider
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
