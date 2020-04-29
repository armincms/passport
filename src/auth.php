<?php   
    Route::post('login', 'AuthController@login')->name('user-api.login');
    Route::post('logout', 'AuthController@logout')->name('user-api.logout');
    Route::post('password/reset', 'AuthController@reset')->name('user-api.password.reset');

    // Registration Routes...
    // Route::get('register', 'AuthController@showRegistrationForm')->name('user-api.register');
    Route::post('register', 'AuthController@register')->name('user-api.register');
    Route::post('{user}/verify', 'AuthController@verify')->name('user-api.verify');

    // Password Reset Routes...
    // Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
    // Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    // Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
    // Route::post('password/reset', 'ResetPasswordController@reset');
 