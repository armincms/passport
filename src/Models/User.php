<?php  
namespace Component\LaravelPassport\Models;

use Core\User\Models\User as Model;
use Laravel\Passport\HasApiTokens;
 
class User extends Model
{ 
	use HasApiTokens;
	
	public function findForPassport($username)
	{
		return $this->where('username', $username)->first();
	} 

	public function getMorphClass()
	{
		return parent::class;
	}
}