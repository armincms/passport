<?php
namespace Component\LaravelPassport;     
use Artisan;


class LaravelPassportInstaller
{       
    /**
     * Your component installation.
     * 
     * @return void
     */
    public function install()
    {            
        \Artisan::call('migrate', [
            '--path'    => __DIR__.'/database/migrations',
            '--realpath'=> true,
        ]);  
         
        Artisan::call('passport:install'); 
        Artisan::call('passport:keys');
    } 

    /**
     * Your uninstaller.
     *
     * @return void
     */
    public function uninstall()
    {     
    } 
}
