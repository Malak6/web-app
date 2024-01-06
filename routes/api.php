<?php

use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Message\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\FileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;
use Illuminate\Validation\ValidationException;


use AhmadVoid\SimpleAOP\AspectMiddleware;

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


Route::post('/getFileStatus' , [FileController::class , 'getFileStatus']);
Route::post('/login',    [UserController::class , 'log']);
Route::post('/register', [UserController::class, 'register']);



//Route::post('/addUserToGroup', [GroupController::class, 'addUserToGroup']);


Route::middleware('auth:sanctum')->group(function () {

Route::post('/logout', [UserController::class, 'logout']);

Route::post('/upload' , [FileController::class , 'upload']);

Route::get('/getUserFiles' , [FileController::class , 'getUserFiles']);

Route::post('/createGroup' , [GroupController::class , 'createGroup']);

Route::post('/addUserToGroup', [GroupController::class, 'addUserToGroup']);

Route::post('/download' , [FileController::class , 'download'])
->withoutMiddleware(AspectMiddleware::class);


Route::post('/checkIn' , [FileController::class , 'checkIn']);

Route::post('/checkOut' , [FileController::class , 'checkOut']);

Route::get('/getUserGroups', [GroupController::class, 'getUserGroups']);

Route::post('/read' , [FileController::class , 'readFile']);

Route::post('/downloadFiles' , [FileController::class , 'downlaodManyFiles'])
->withoutMiddleware(AspectMiddleware::class);

Route::get('/getGroupFiles/{id}' ,[FileController::class , 'getGroupFiles'] );

});




