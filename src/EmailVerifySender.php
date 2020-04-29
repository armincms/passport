<?php  
namespace Component\LaravelPassport;

use Component\LaravelPassport\Contracts\VerifyCodeSender; 
use Illuminate\Contracts\Auth\Authenticatable;
use Component\LaravelPassport\Mail\SendVerifyCode;
use Mail;

class EmailVerifySender implements VerifyCodeSender
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
	 * @param  \Illuminate\Foundation\Auth\User $user 
	 * 
	 * @return boolean
	 */
	public function send(string $code, Authenticatable $user)
	{
		if(! isset($user->email)) { 
			return false;
		} 

		try {
			Mail::to($user)->send(new SendVerifyCode($code));
		} catch (\Exception $e) {
			return false;
		} 

		return true;
	}
}