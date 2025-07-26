<?php

namespace Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

class AnotherTestRequest
{
    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}


#[RequestHandler(AnotherTestRequest::class)]
class AnotherTestRequestHandler
{
    public function handle(AnotherTestRequest $request): string
    {
        return 'Another Handled: ' . $request->value;
    }
}
