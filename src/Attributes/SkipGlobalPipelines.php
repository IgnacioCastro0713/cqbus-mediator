<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Attributes;

use Attribute;

/**
 * When applied to a handler, global pipelines will be skipped.
 * Only handler-level pipelines (via #[Pipeline]) will be executed.
 *
 * Useful for:
 * - Health check endpoints
 * - Internal/system handlers
 * - High-frequency handlers that don't need logging/transactions
 */
#[Attribute(Attribute::TARGET_CLASS)]
class SkipGlobalPipelines
{
}
