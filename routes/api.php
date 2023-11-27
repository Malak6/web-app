<?php

use Illuminate\Http\Request;
use App\Http\Message\Message;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\FileController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use App\Http\Controllers\UserController;


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
Route::post('/upload' , [FileController::class , 'upload']);

Route::post('/download' , [FileController::class , 'download']);


Route::post('/register', [UserController::class, 'register']);

Route::post('/login', [UserController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
});

