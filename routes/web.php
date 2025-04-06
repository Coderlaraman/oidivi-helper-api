<?php

use App\Events\MyEvent;
//use App\Events\SomethingHappened;
//use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/emitir-evento', function () {
    $message = 'Mensaje desde Laravel a canal público';
    broadcast(new MyEvent($message));
    return response()->json([
        'status' => 'success',
        'message' => 'Evento enviado',
        'data' => [
            'message' => $message,
            'channel' => 'public-channel',
            'event' => 'MyEvent'
        ]
    ]);
});

//Route::get('/test-redis', function () {
//    Redis::set('name', 'Memurai');
//    return Redis::get('name'); // Debe devolver "Memurai"
//});

//Route::get('/websocket-test', function () {
//    event(new App\Events\TestEvent('Mensaje de prueba ' . now()));
//    return view('websocket-test');
//});
//
//
//Route::get('/emitir-evento', function () {
//    broadcast(new SomethingHappened());
//    return 'Evento emitido.';
//});
