<?php

use Ignaciocastro0713\CqbusMediator\Services\MediatorService;
use Illuminate\Support\Facades\App;

it('handles reflection exception in pipelines resolution when handler class does not exist but is bound', function () {
    $service = new MediatorService(app());

    // Create a dummy request
    $request = new class () {};
    $requestClass = get_class($request);

    // Inject invalid handler map
    $reflection = new ReflectionClass($service);
    $property = $reflection->getProperty('handlers');
    $property->setAccessible(true);
    $property->setValue($service, [
        $requestClass => 'NonExistentHandler',
    ]);

    // Bind the non-existent handler to a valid object
    $handler = new class () {
        public function handle($request)
        {
            return 'handled';
        }
    };

    App::bind('NonExistentHandler', fn () => $handler);

    $result = $service->send($request);

    expect($result)->toBe('handled');
});
