<?php

namespace App\Aspects;

use AhmadVoid\SimpleAOP\Aspect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Logging implements Aspect
{
    public function __construct(public string $message = 'Logging...')
    {
    }

    public function executeBefore($request, $controller, $method)
    {
        $user = Auth::user();

        Log::info($this->message);
        Log::info('Request: ' . $request->fullUrl());
        Log::info('Controller: ' . get_class($controller));
        Log::info('Method: ' . $method);

        if ($request->has('file')) {
            $fileName = $request->input('file_id');
            Log::info('File: ' . $fileName);
        }
    }

    public function executeAfter($request, $controller, $method, $response)
    {
        $user = Auth::user();

        Log::info('Response: ' . $response->getContent());

        if ($user) {
            Log::info('User ID: ' . $user->id);
            Log::info('User Name: ' . $user->name);
        } else {
            Log::info('User: Guest');
        }
    }

    public function executeException($request, $controller, $method, $exception)
    {
        $user = Auth::user();

        Log::error($exception->getMessage());

        if ($user) {
            Log::info('User ID: ' . $user->id);
            Log::info('User Name: ' . $user->name);
        } else {
            Log::info('User: Guest');
        }
    }
}
