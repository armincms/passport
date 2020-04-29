<?php
Route::get('setting', [
	'uses' 	=> 'SettingController@edit',
	'as'	=> 'passport-setting.edit'
]);         
Route::post('setting', 'SettingController@update');     

// _register::adminMenu('laravel-passport', 'laravel-passport::api_login', '#!'); 
// _register::adminMenu(
// 	'passport-setting', 'laravel-passport::login_setting', route('passport-setting.edit'), 'laravel-passport'
// );   