<?php

declare(strict_types=1);

namespace Tests\Fixtures\Handlers;

use Closure;

class BasicPipeline
{
    public function handle(object $request, Closure $next): mixed
    {
        if (property_exists($request, 'name')) {
            $request->name = 'processed';
        }

        return $next($request);
    }
}
