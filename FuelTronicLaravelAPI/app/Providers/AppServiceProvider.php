<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\App\Interfaces\SiteRepositoryInterface::class, \App\Repositories\SiteRepository::class);
        $this->app->bind(\App\Interfaces\GradeRepositoryInterface::class, \App\Repositories\GradeRepository::class);
        $this->app->bind(\App\Interfaces\TankRepositoryInterface::class, \App\Repositories\TankRepository::class);
        $this->app->bind(\App\Interfaces\PumpRepositoryInterface::class, \App\Repositories\PumpRepository::class);
        $this->app->bind(\App\Interfaces\HoseRepositoryInterface::class, \App\Repositories\HoseRepository::class);
        $this->app->bind(\App\Interfaces\AttendantRepositoryInterface::class, \App\Repositories\AttendantRepository::class);
        $this->app->bind(\App\Interfaces\CustomerRepositoryInterface::class, \App\Repositories\CustomerRepository::class);
        $this->app->bind(\App\Interfaces\VehicleRepositoryInterface::class, \App\Repositories\VehicleRepository::class);
        $this->app->bind(\App\Interfaces\SupplierRepositoryInterface::class, \App\Repositories\SupplierRepository::class);
        $this->app->bind(\App\Interfaces\TagRepositoryInterface::class, \App\Repositories\TagRepository::class);
        $this->app->bind(\App\Interfaces\SiteusersRepositoryInterface::class, \App\Repositories\SiteusersRepository::class);
        $this->app->bind(\App\Interfaces\AtgReadingsRepositoryInterface::class, \App\Repositories\AtgReadingsRepository::class);
        $this->app->bind(\App\Interfaces\JobRepositoryInterface::class, \App\Repositories\JobRepository::class);
        $this->app->bind(\App\Interfaces\AtgDataRepositoryInterface::class, \App\Repositories\AtgDataRepository::class);
        $this->app->bind(\App\Interfaces\FuelDropRepositoryInterface::class, \App\Repositories\FuelDropRepository::class);
        $this->app->bind(\App\Interfaces\FuelTransferRepositoryInterface::class, \App\Repositories\FuelTransferRepository::class);
        $this->app->bind(\App\Interfaces\CustomerTransactionRepositoryInterface::class, \App\Repositories\CustomerTransactionRepository::class);
        $this->app->bind(\App\Interfaces\PaymentRepositoryInterface::class, \App\Repositories\PaymentRepository::class);
        $this->app->bind(\App\Interfaces\AtgTransactionRepositoryInterface::class, \App\Repositories\AtgTransactionRepository::class);
        $this->app->bind(\App\Interfaces\FuelAdjustmentRepositoryInterface::class, \App\Repositories\FuelAdjustmentRepository::class);
        $this->app->bind(\App\Interfaces\LocationRepositoryInterface::class, \App\Repositories\LocationRepository::class);
    }
}
