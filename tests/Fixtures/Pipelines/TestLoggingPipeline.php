<?php

declare(strict_types=1);

namespace Tests\Fixtures\Pipelines;

use Closure;

class TestLoggingPipeline
{
    public function handle(object $request, Closure $next): mixed
    {
        if (property_exists($request, 'pipelineOrder')) {
            $request->pipelineOrder[] = 'logging';
        }

        return $next($request);
    }
}
