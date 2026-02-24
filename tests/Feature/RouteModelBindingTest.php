<?php

namespace Tests\Feature;

use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Traits\AsAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class MockUser extends Model
{
    protected $guarded = [];
}

class UserUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }
}

#[\Ignaciocastro0713\CqbusMediator\Attributes\ApiRoute]
class UserUpdateAction
{
    use AsAction;

    public function __construct(private readonly Mediator $mediator)
    {
    }

    public static function route(Router $router): void
    {
        // En Laravel 11, las rutas registradas manualmente (sin estar en un grupo 'api' o 'web')
        // no tienen asignado automáticamente el middleware de binding
        $router->get('api/user/{user}/update', self::class)->middleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    }

    public function handle(UserUpdateRequest $request, MockUser $user): array
    {
        return [
            'message' => 'User updated successfully',
            'user' => $user->toArray(),
        ];
    }
}

beforeEach(function () {
    Schema::create('mock_users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
});

test('it resolves implicit route model binding with middleware', function () {
    $user = MockUser::create(['name' => 'John Doe']);

    // Manually register route and force action manager to process it
    UserUpdateAction::route(app('router'));

    $response = $this->getJson("/api/user/{$user->id}/update");

    $response->assertStatus(200)
             ->assertJson([
                 'message' => 'User updated successfully',
                 'user' => [
                     'id' => $user->id,
                     'name' => 'John Doe',
                 ],
             ]);
});

test('it fails to resolve implicit route model binding without middleware', function () {
    $user = MockUser::create(['name' => 'John Doe']);

    // Route WITHOUT SubstituteBindings middleware
    Route::get('api/user-no-middleware/{user}/update', [UserUpdateAction::class, 'handle']);

    $response = $this->getJson("/api/user-no-middleware/{$user->id}/update");

    // It returns an empty model instance (not loaded from DB)
    $response->assertStatus(200)
             ->assertJson([
                 'message' => 'User updated successfully',
                 'user' => [], // This is the issue you're seeing!
             ]);
});
