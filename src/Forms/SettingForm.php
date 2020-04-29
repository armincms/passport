<?php 
namespace Component\LaravelPassport\Forms;

use Annisa\Form\AnnisaBuilder; 

/**
* 
*/ 
class SettingForm extends AnnisaBuilder
{ 
	public function build()
	{ 
		$setting = armin_setting('_component_passport_setting', collect());

		$verifiers = collect(config('user.verifier.senders'))->mapWithKeys(function($config){
			if($driver = array_get($config, 'driver')) { 
				return [$driver => $driver];
			} 

			return [];
		})->toArray();

	 	$this 
	 		// ->field(
	 		//   	'select', 'login', 'login', ['username', 'email'], 'username' 
	 		// ) 
	 		->field(
	 		  	'select', 'verifier', 'verifier', $verifiers, array_get(
	 		  		$setting, 'verifier', config('user.verifier.default', 'mail')
	 		  	) 
	 		)
	 		->field(
	 		  	'text', 'verify_message', 'verify_message', 
	 		  	array_get($setting, 'verify_message', 'Your Verify Code Is: #'), 
	 		  	['class' => 'input full-width ltr']
	 		)
	 		->field(
	 		  	'text', 'app_key', 'app_key', array_get($setting, 'app_key')
	 		);
	} 
}
