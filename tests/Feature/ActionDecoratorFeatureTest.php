<?php

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
