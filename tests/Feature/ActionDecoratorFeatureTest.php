<?php

use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidActionException;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Fixtures
 */
class DecoratedRequest extends FormRequest
{
    public function rules(): array
    {
        return ['name' => 'required|string'];
    }
}

class DecoratedHandler
{
    use AsAction;

    public function handle(DecoratedRequest $request): JsonResponse
    {
        return response()->json([
            'received' => $request->input('name'),
        ]);
    }
}

class FallbackHandler
{
    public function __invoke(Request $request)
    {
        return response('fallback-invoked');
    }
}

class NamedArgumentHandler
{
    use AsAction;

    public function handle(Request $request): JsonResponse
    {
        return response()->json([
            'foo' => $request->route('foo'),
            'from_request' => $request->query('bar'),
        ]);
    }
}

class InvalidActionHandler
{
    use AsAction;

    // Missing handle() method - should throw InvalidActionException
}

/**
 * Feature AsController Tests
 */
it('calls the handler and injects custom request', function () {
    Route::post('/decorated', DecoratedHandler::class);

    $response = $this->postJson('/decorated', ['name' => 'foo']);

    $response
        ->assertOk()
        ->assertJson(['received' => 'foo']);
});

it('validates custom request rules', function () {
    Route::post('/decorated', DecoratedHandler::class);

    $response = $this->postJson('/decorated');

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('falls back to __invoke method', function () {
    Route::get('/fallback', FallbackHandler::class);

    $response = $this->get('/fallback');
    $response->assertOk()->assertSee('fallback-invoked');
});

it('injects named argument from route', function () {
    Route::get('/named/{foo}', NamedArgumentHandler::class);

    $response = $this->getJson('/named/testvalue?bar=other');

    $response->assertOk()
        ->assertJson([
            'foo' => 'testvalue',
            'from_request' => 'other',
        ]);
});

it('null named argument from query if not in route', function () {
    Route::get('/named-query', NamedArgumentHandler::class);

    $response = $this->getJson('/named-query?foo=queryfoo&bar=barval');

    $response->assertOk()
        ->assertJson([
            'foo' => null,
            'from_request' => 'barval',
        ]);
});

it('prioritizes route parameters over user input for security', function () {
    // Handler that echoes back the 'id' argument
    Route::post('/secure/{id}', function (Ignaciocastro0713\CqbusMediator\Contracts\Mediator $mediator, $id) {
        // We use an inline closure here to simulate the ActionDecorator logic or use a specific Action.
        // Actually, let's use a routed Action to test the Decorator properly.
    });

    // Let's reuse NamedArgumentHandler which returns 'foo' (route param)
    Route::post('/priority/{foo}', NamedArgumentHandler::class);

    // We send 'foo' in the body as 'hacker', but route has 'safe'
    $response = $this->postJson('/priority/safe', ['foo' => 'hacker']);

    $response->assertOk()
        ->assertJson([
            'foo' => 'safe', // Should match route, not body
        ]);
});

it('throws InvalidActionException when action has no handle method', function () {
    Route::get('/invalid-action', InvalidActionHandler::class);

    $this->withoutExceptionHandling();

    expect(fn () => $this->get('/invalid-action'))
        ->toThrow(InvalidActionException::class);
});
