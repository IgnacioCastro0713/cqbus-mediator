<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Tests\Unit\Discovery;

use Ignaciocastro0713\CqbusMediator\Discovery\MediatorDiscovery;
use Ignaciocastro0713\CqbusMediator\Exceptions\InvalidRequestClassException;
use PHPUnit\Framework\TestCase;

class MediatorDiscoveryTest extends TestCase
{
    protected function tearDown(): void
    {
        MediatorDiscovery::clearCache();
        parent::tearDown();
    }

    public function test_it_discovers_handlers_events_and_actions()
    {
        $directories = [__DIR__ . '/../../Fixtures'];

        $discovered = MediatorDiscovery::discover($directories);

        $this->assertArrayHasKey('handlers', $discovered);
        $this->assertArrayHasKey('notifications', $discovered);
        $this->assertArrayHasKey('actions', $discovered);

        $this->assertArrayHasKey(\Tests\Fixtures\Handlers\BasicRequest::class, $discovered['handlers']);
        $this->assertEquals(\Tests\Fixtures\Handlers\BasicHandler::class, $discovered['handlers'][\Tests\Fixtures\Handlers\BasicRequest::class]);

        $this->assertArrayHasKey(\Tests\Fixtures\Events\UserRegisteredEvent::class, $discovered['notifications']);
        $this->assertContains(\Tests\Fixtures\EventHandlers\SendWelcomeEmailHandler::class, array_column($discovered['notifications'][\Tests\Fixtures\Events\UserRegisteredEvent::class], 'handler'));

        $this->assertArrayHasKey(\Tests\Fixtures\AttributeAction::class, $discovered['actions']);
    }

    public function test_it_throws_exception_for_non_existent_request_class()
    {
        $this->expectException(InvalidRequestClassException::class);

        $directories = [__DIR__ . '/../../InvalidFixtures/InvalidHandlers/NonExistentRequest'];
        MediatorDiscovery::discover($directories);
    }

    public function test_it_throws_exception_for_non_existent_event_class()
    {
        $this->expectException(InvalidRequestClassException::class);

        $directories = [__DIR__ . '/../../InvalidFixtures/InvalidEventHandlers/NonExistentEvent'];
        MediatorDiscovery::discover($directories);
    }

    public function test_it_skips_classes_that_are_not_autoloaded(): void
    {
        $directories = [__DIR__ . '/../../NonAutoloadedFixtures/ReflectionFailure'];

        $discovered = MediatorDiscovery::discover($directories);

        $this->assertEmpty($discovered['handlers']);
        $this->assertEmpty($discovered['notifications']);
    }

    public function test_it_skips_non_instantiable_classes(): void
    {
        $directories = [__DIR__ . '/../../Fixtures'];

        $discovered = MediatorDiscovery::discover($directories);

        $this->assertArrayNotHasKey(
            \Tests\Fixtures\PrivateConstructorHandler::class,
            $discovered['handlers']
        );
    }
}
