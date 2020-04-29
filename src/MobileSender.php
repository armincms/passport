<?php  
namespace Component\LaravelPassport;

use Component\LaravelPassport\Contracts\VerifyCodeSender; 
use Illuminate\Contracts\Auth\Authenticatable;

class MobileSender implements VerifyCodeSender
{
	protected $config = [];

    function __construct(array $config)
    {
    	$this->config = array_merge($this->config, $config);
    }

	/**
	 * Send verify code to requested user.
	 * 
	 * @param  string $code 
	 * @param  Illuminate\Contracts\Auth\Authenticatable $user 
	 * 
	 * @return boolean
	 */
	public function send(string $code, Authenticatable $user)
	{ 
		send_sms($user->username, $code); 

		return true;
	}
}