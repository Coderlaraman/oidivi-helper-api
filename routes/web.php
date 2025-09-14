<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


//Route::get('/test-redis', function () {
//    Redis::set('name', 'Memurai');
//    return Redis::get('name'); // Debe devolver "Memurai"
//});



