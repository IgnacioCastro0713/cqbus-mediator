<?php

namespace Unit;

use Fixtures\Handlers\TestRequestHandler;
use Ignaciocastro0713\CqbusMediator\Contracts\Mediator;
use Ignaciocastro0713\CqbusMediator\Services\MediatorService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MediatorServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('config:clear');
        $this->app->boot();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Artisan::call('config:clear');
    }

    /** @test */
    public function it_binds_mediator_interface_to_service_implementation()
    {
        $this->assertTrue($this->app->bound(Mediator::class));
        $this->assertInstanceOf(MediatorService::class, $this->app->make(Mediator::class));
    }

    /** @test */
    public function it_registers_handlers_with_request_handler_attribute()
    {
        $toBind = "mediator.handler." . TestRequestHandler::class;
        $this->app->bind($toBind);
        $this->assertTrue($this->app->bound($toBind), "Mediator handler for TestRequest not bound.");
    }

    /** @test */
    public function it_does_not_register_handlers_without_request_handler_attribute()
    {
        $dummyFilePath = realpath(__DIR__ . '/../Fixtures/Handlers') . DIRECTORY_SEPARATOR . 'NoAttributeHandler.php';

        $this->app->make('files')->put(
            $dummyFilePath,
            '<?php namespace Fixtures\Handlers; class NoAttributeRequest {} class NoAttributeHandler { public function handle(NoAttributeRequest $request) { return "Handled no attribute"; } }'
        );

        Artisan::call('config:clear');
        $this->app->boot();

        $this->assertFalse($this->app->bound("mediator.handler.Fixtures\\Handlers\\NoAttributeRequest"));

        $this->app->make('files')->delete($dummyFilePath);
    }

    /** @test */
    public function it_publishes_config_file()
    {
        $this->artisan('vendor:publish', ['--tag' => 'mediator-config'])->assertExitCode(0);
        $this->assertFileExists(config_path('mediator.php'));
    }

    /** @test */
    public function it_merges_config_from_package()
    {
        $this->assertIsArray($this->app['config']->get('mediator.handler_paths'));

        $expectedPath = realpath(__DIR__ . '/../Fixtures/Handlers');
        $this->assertEquals([$expectedPath], $this->app['config']->get('mediator.handler_paths'));

        $this->app['config']->set('mediator.handler_paths', ['/custom/path']);
        $this->assertEquals(['/custom/path'], $this->app['config']->get('mediator.handler_paths'));
    }
}
