<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2015 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ArrayFactory;

use Illuminate\Support\ServiceProvider;

/**
 * FactoryServiceProvider bootstraps array factory to Laravel application.
 *
 * This service provider registers array factoryas a singleton, facilitating functioning of the
 * {@link \Illuminatech\ArrayFactory\Facades\Factory} facade.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class FactoryServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected $defer = true;

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton(FactoryContract::class, function () {
            return new Factory($this->app);
        });
    }
}
