<?php

use Illuminate\Http\Request;
use App\Http\Message\Message;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/get' ,function(Request $request){
    $obj = new Message();
    $msg = $obj->getMessage();
    $msg =$obj->setMessage("ke" , "vvv4vv");
    return $msg ;
});