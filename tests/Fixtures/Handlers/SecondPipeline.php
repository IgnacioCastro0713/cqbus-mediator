<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Closure;

class SecondPipeline
{
    public function handle(object $request, Closure $next): mixed
    {
        $request->pipelineOrder[] = 'second';
        $request->value .= '-second';

        return $next($request);
    }
}
