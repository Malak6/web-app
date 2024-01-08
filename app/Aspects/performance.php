<?php

namespace App\Aspects;

use AhmadVoid\SimpleAOP\Aspect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class performance implements Aspect
{
  
        private $startTime;
    
        public function __construct()
        {
            $this->startTime = microtime(true);
        }
    
        public function executeBefore($request, $controller, $method)
        {
            DB::enableQueryLog();
            $this->startTime = microtime(true);
        }
    
        public function executeAfter($request, $controller, $method, $response)
        {
            DB::enableQueryLog();
            $endTime = microtime(true);
            $executionTime = $endTime - $this->startTime;
    
            Log::info("Execution time of {$method} is: {$executionTime} seconds.");

            // log the number of database queries executed:
            $queryCount = count(DB::getQueryLog());
            Log::info("Number of database queries executed by {$method}: {$queryCount}.");
    
            // log the response time:
            $responseTime = $endTime - $_SERVER["REQUEST_TIME_FLOAT"];
            Log::info("Response time of {$method} is: {$responseTime} seconds.");
    
            // To log the throughput:
            $throughput = memory_get_peak_usage(true) / 1024 / 1024; // in MB
            Log::info("Peak memory usage of {$method} is: {$throughput} MB.");
    
        }
    
        public function executeException($request, $controller, $method, $exception)
        {
            Log::Error("The method {$method} failed. Cannot calculate the response time , the throughput and the query count.");
        }
}
    