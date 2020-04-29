<?php 
namespace Component\LaravelPassport\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface VerifyCodeSender
{
	/**
	 * Send verify code to requested user.
	 * 
	 * @param  string $code 
	 * @param  \Illuminate\Foundation\Auth\User $user 
	 * 
	 * @return boolean
	 */
	public function send(string $code, Authenticatable $user);
}