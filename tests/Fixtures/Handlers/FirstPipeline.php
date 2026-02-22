<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Closure;

class FirstPipeline
{
    public function handle(object $request, Closure $next): mixed
    {
        $request->pipelineOrder[] = 'first';
        $request->value .= '-first';

        return $next($request);
    }
}
