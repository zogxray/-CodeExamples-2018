<?php
/**
 * Created by PhpStorm.
 * User: zogxray
 * Date: 3/19/18
 * Time: 10:12 PM
 */

namespace App\Providers;


use App\Registry\Content\Instagram;
use App\Registry\Content\Vkontakte;
use App\Registry\ContentGatewayRegistry;
use Illuminate\Support\ServiceProvider;

/**
 * Class ContentServiceProvider
 * @package App\Providers
 */
class ContentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    function boot () {
        $this->app->make(ContentGatewayRegistry::class)
            ->add(new Instagram())
            ->add(new Vkontakte());
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    function register () {
        $this->app->singleton(ContentGatewayRegistry::class);
    }
}