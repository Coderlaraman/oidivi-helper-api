<?php

use App\Events\MyEvent;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Redis;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/proof', function () {
    event(new MyEvent);
    return 'Event has been sent!';
});

Route::get('/test-redis', function () {
    Redis::set('name', 'Memurai');
    return Redis::get('name'); // Debe devolver "Memurai"
});
