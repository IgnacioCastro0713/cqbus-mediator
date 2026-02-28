<?php

use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Api;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Name;
use Ignaciocastro0713\CqbusMediator\Attributes\Routing\Web;

it('can instantiate Api attribute', function () {
    $attribute = new Api();
    expect($attribute)->toBeInstanceOf(Api::class);
});

it('can instantiate Name attribute', function () {
    $attribute = new Name('api.users.');
    expect($attribute)->toBeInstanceOf(Name::class)
        ->and($attribute->name)->toBe('api.users.');
});

it('can instantiate Web attribute', function () {
    $attribute = new Web();
    expect($attribute)->toBeInstanceOf(Web::class);
});
