<?php

use Ignaciocastro0713\CqbusMediator\Attributes\ApiRoute;
use Ignaciocastro0713\CqbusMediator\Attributes\WebRoute;

it('can instantiate ApiRoute attribute', function () {
    $attribute = new ApiRoute();
    expect($attribute)->toBeInstanceOf(ApiRoute::class);
});

it('can instantiate WebRoute attribute', function () {
    $attribute = new WebRoute();
    expect($attribute)->toBeInstanceOf(WebRoute::class);
});
