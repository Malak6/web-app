<?php
namespace App\Aspects;

use AhmadVoid\SimpleAOP\Aspect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class UserActivityMonitoring implements Aspect
{


    public function __construct(public string $message = 'User monitoring...')
    {
    }

    public function executeBefore($request, $controller, $method)
    {


        Log::info($this->message);
        if ($method === 'register') {
            Log::info('New user account created');
        } elseif ($method === 'log') {
            Log::info('User logged in');
        } elseif ($method === 'logout') {
            Log::info('User logged out');
        }
    }

    public function executeAfter($request, $controller, $method, $response)
    {

        $user = Auth::user();

        Log::info('Response: ' . $response->getContent());

    }

    public function executeException($request, $controller, $method, $exception)
    {

        Log::error("Exception occurred in {$method}: " . $exception->getMessage());
    }
}
