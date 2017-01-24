<?php namespace Sirthxalot\Parse;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Parse\ParseClient;
use Sirthxalot\Parse\Auth\Providers\UserProvider;
use Sirthxalot\Parse\Console\ModelMakeCommand;

/**
 * Parse Service Provider
 * ==================================================================================
 *
 * Service providers are the central place of all Laravel application bootstrapping.
 * Your own application, as well as all of Laravel's core services are bootstrapped
 * via service providers.
 *
 * @package   Sirthxalot\Parse
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
class ParseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();

        $this->registerCommands();

        $this->setupParse();
    }


    /**
     * Provide the service configuration.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../../config/parse.php');

        $this->publishes([$source => config_path('parse.php')]);

        $this->mergeConfigFrom($source, 'parse');
    }


    /**
     * Setup the Parse driver.
     *
     * @return void
     */
    protected function setupParse()
    {
        $config = $this->app->config->get('parse');

        ParseClient::initialize($config['app_id'], $config['rest_key'], $config['master_key']);
        ParseClient::setStorage(new SessionStorage());

        if (isset($config['server_url']) && isset($config['mount_path'])):
            $serverURL = rtrim($config['server_url'], '/');
            $mountPath = trim($config['mount_path'], '/').'/';

            ParseClient::setServerURL($serverURL, $mountPath);
        endif;

        Auth::provider('parse', function($app, array $config) {
            return new UserProvider($config['model']);
        });
    }


    /**
     * Register any services.
     *
     * @return void
     */
    public function register()
    {
        //
    }


    /**
     * Register any commands.
     */
    protected function registerCommands()
    {
        $this->registerModelMakeCommand();

        $this->commands('command.parse.model.make');
    }


    /**
     * Register the model make command.
     */
    protected function registerModelMakeCommand()
    {
        $this->app->singleton('command.parse.model.make', function ($app) {
            return new ModelMakeCommand($app['files']);
        });
    }


    /**
     * Get the services provided by this provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            //
        ];
    }
}
