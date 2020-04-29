<?php
namespace Component\LaravelPassport;
 
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;  
use Illuminate\Contracts\Auth\Authenticatable; 
use Illuminate\Foundation\Auth\User;
use Laravel\Passport\Passport;
use Illuminate\Foundation\AliasLoader; 
use Config;

class LaravelPassportServiceProvider extends ServiceProvider
{     
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [];

    /**
     * Define your route model bindings, pattern filters, etc.
     * 
     * @return void
     */
    public function boot()
    {        
        $this->loadViewsFrom(__DIR__.'/resources/views', 'passport');
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'passport');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->registerPolicies();   
        $this->map();  

    } 

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {     
        \Config::set('user.verifier', require __DIR__.'/verifier.php');
        
        $this->app->register('\\Laravel\\Passport\\PassportServiceProvider'); 

        $this->app->singleton('verifier', function($app) {
            return new SenderManager($app);
        });  
        $this->app->singleton('verifier.sender', function($app) {
            return $app['verifier']->driver();
        });  

        $loader = AliasLoader::getInstance();

        $loader->alias('Verifier', Facades\Verifier::class); 

        $this->commands([
            \Laravel\Passport\Console\InstallCommand::class,
            \Laravel\Passport\Console\ClientCommand::class,
            \Laravel\Passport\Console\KeysCommand::class,
        ]);


        Config::set('auth.guards.api.driver', 'passport');  
        Config::set('auth.guards.api.provider', 'api_users');  
        Config::set('auth.providers.api_users', [
            'driver'=> 'eloquent', 
            'table' => 'users',
            'model' => Models\User::class,
        ]);    
    } 

    public function map()
    {    
        $this->mapAuthRoutes(); 
        $this->auth();
        $this->oAuth(); 
    }

    public function mapAuthRoutes()
    { 
        
        $this->app['router']->prefix(
            config('admin.panel.path_prefix', 'panel'). '/laravel-passport'
        )
                            ->middleware(config('admin.panel.middleware', ['auth:admin'])) 
                            ->namespace(__NAMESPACE__.'\Http\Controllers')
                            ->group(__DIR__.DS.'routes.php');

        $this->app['router']/*->middleware(config('admin.api.middleware', ['auth:api']))*/
                     ->prefix(config('admin.api.path_prefix', 'api'))
                     ->namespace(__NAMESPACE__.'\Http\Controllers\Api') 
                     ->group(__DIR__.DS.'api.php') ;

    } 
    
    public function auth()
    {
       $this->app['router']
                     ->prefix('api/user')
                     ->namespace(__NAMESPACE__.'\Http\Controllers\Api\Auth') 
                     ->group(__DIR__.DS.'auth.php');  
    }

    public function oAuth()
    {
       $this->app['router']
                     ->prefix('api/user') 
                     ->group(function() { 
                        Passport::routes();
                     });  
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'verifier', 'verifier.sender'
        ];
    }
}
