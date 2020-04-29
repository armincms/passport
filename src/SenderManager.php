<?php 
namespace Component\LaravelPassport;

use Closure;
use InvalidArgumentException;

class SenderManager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved verify sender.
     *
     * @var array
     */
    protected $senders = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];


	function __construct($app)
	{
		$this->app = $app;
	} 

    public function driver($name = null)
    {
    	$name = $name ?: $this->getDefaultDriver();

        return $this->senders[$name] = $this->get($name); 
    }

    /**
     * Attempt to get the sender from the local cache.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Cache\Repository
     */
    protected function get($name)
    {
        return $this->stores[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given sender.
     *
     * @param  string  $name
     * @return \Component\LaravelPassport\Contracts\VerifyCodeSender
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name); 

        if (is_null($config)) {
            throw new InvalidArgumentException("Verify sender [{$name}] is not defined.");
        }

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        } else {
            $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

            if (method_exists($this, $driverMethod)) {
                return $this->{$driverMethod}($config);
            } else {
                throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
            }
        }
    }

    /**
     * Call a custom driver creator.
     *
     * @param  array  $config
     * @return mixed
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }

    /**
     * Call mail driver creator.
     *
     * @param  array  $config
     * @return mixed
     */
    protected function createMailDriver(array $config)
    {
        return new EmailVerifySender($config);
    }

    /**
     * Call mail driver creator.
     *
     * @param  array  $config
     * @return mixed
     */
    protected function createMobileDriver(array $config)
    {
        return new MobileSender($config);
    }
 

    /**
     * Get the user verifier sender configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["user.verifier.senders.{$name}"];
    }

    /**
     * Get the default user verifier driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['user.verifier.default'];
    }

    /**
     * Set the default user verifier driver name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['config']['user.verifier.default'] = $name;
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string    $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback->bindTo($this, $this);

        return $this;
    }
}