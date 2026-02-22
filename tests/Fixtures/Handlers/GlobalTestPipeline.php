<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Closure;

class GlobalTestPipeline
{
    public function handle(object $request, Closure $next): mixed
    {
        $request->pipelineOrder[] = 'global';
        $request->value .= '-global';

        return $next($request);
    }
}
