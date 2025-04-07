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

Route::get('my-proof-event', function(){
    $message = "Hola desde laravel";
    broadcast(new MyProofEvent($message));
    return response()->json([
        'status' => 'success',
        'message' => 'MyProofEvent Send',
        'data' => [
            'message' => $message,
            'channel' => 'my-proof',
            'event' => 'MyProofEvent'
        ]
    ]);
});

Route::get('/emitir-evento-privado', function () {
    $user = auth()->user(); // O define el usuario directamente para pruebas
    $message = "Hola {$user->name}, este es un mensaje privado";
    broadcast(new PrivateMessageEvent($user->id, $message));
    
    return response()->json(['status' => 'enviado',
'message' => 'MyProofEvent Send',
        'data' => [
            'message' => $message,
            'channel' => 'private-channel',
            'event' => 'PrivateMessageEvent'
        ]
    ]);
})->middleware('auth:sanctum');


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


