<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ServerTimingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.server_timing', false)) {
            return $next($request);
        }

        $startedAt = microtime(true);
        $queryCount = 0;
        $queryDuration = 0.0;

        DB::listen(function (QueryExecuted $query) use (&$queryCount, &$queryDuration): void {
            $queryCount++;
            $queryDuration += $query->time;
        });

        $response = $next($request);

        $totalDuration = round((microtime(true) - $startedAt) * 1000, 2);
        $databaseDuration = round($queryDuration, 2);
        $applicationDuration = round(max($totalDuration - $databaseDuration, 0), 2);

        $metrics = [
            "total;dur={$totalDuration}",
            "db;dur={$databaseDuration}",
            "app;dur={$applicationDuration}",
            "queries;desc=\"{$queryCount}\"",
        ];

        $existingMetrics = $response->headers->get('Server-Timing');

        $response->headers->set(
            'Server-Timing',
            $existingMetrics !== null && $existingMetrics !== ''
                ? $existingMetrics.', '.implode(', ', $metrics)
                : implode(', ', $metrics),
        );

        return $response;
    }
}
