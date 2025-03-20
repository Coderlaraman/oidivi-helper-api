<?php

use App\Events\MyEvent;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/proof', function () {
    event(new MyEvent);
    return 'Event has been sent!';
});
