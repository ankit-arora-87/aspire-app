<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Input;
use Validator;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
        Validator::extend('max_if', function ($attribute, $value, $parameters, $validator) {
            $type = Input::get($parameters[0]);
            $responseValue = false;
            $typeAmountLimit = 0;
            $validator->addReplacer('max_if', function($message, $attribute, $rule, $parameters){
                return str_replace([':validator'], '100000 or 500000', $message);
            });
            if($type == config('constants.loanTypes')[0]['type']){
                $typeAmountLimit = $parameters[1];
                $responseValue =  ($value <= $typeAmountLimit);
                $validator->addReplacer('max_if', function($message, $attribute, $rule, $parameters){
                    return str_replace([':validator'], $parameters[1], $message);
                });
    
            } else if($type == config('constants.loanTypes')[1]['type']){
                $typeAmountLimit = $parameters[2];
                $responseValue =  ($value <= $typeAmountLimit);
                $validator->addReplacer('max_if', function($message, $attribute, $rule, $parameters){
                    return str_replace([':validator'], $parameters[2], $message);
                });
    
            } 
            return $responseValue;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}