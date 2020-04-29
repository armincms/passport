<?php 
namespace Component\LaravelPassport\Facades;


use Illuminate\Support\Facades\Facade;

class Verifier extends Facade
{ 
    public static function getFacadeAccessor()
    {
        return 'verifier';
    }
}