<?php

namespace Fixtures\Handlers;

use Ignaciocastro0713\CqbusMediator\Attributes\RequestHandler;

class TestRequest
{
    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}

#[RequestHandler(TestRequest::class)]
class TestRequestHandler
{
    public function handle(TestRequest $request): string
    {
        return 'Handled: ' . $request->message;
    }
}


