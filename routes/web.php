<?php

use App\Events\MyEvent;
use App\Events\MyProofEvent;
use App\Events\PrivateMessageEvent;
//use App\Events\SomethingHappened;
//use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


//Route::get('/test-redis', function () {
//    Redis::set('name', 'Memurai');
//    return Redis::get('name'); // Debe devolver "Memurai"
//});



