<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2015 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ArrayFactory;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

/**
 * FactoryServiceProvider bootstraps array factory to Laravel application.
 *
 * This service provider registers array factory as a singleton, facilitating functioning of the
 * {@see \Illuminatech\ArrayFactory\Facades\Factory} facade.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class FactoryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton(FactoryContract::class, function ($app) {
            return new Factory($app);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [
            FactoryContract::class,
        ];
    }
}
