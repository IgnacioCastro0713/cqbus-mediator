<?php

declare(strict_types=1);

namespace Ignaciocastro0713\CqbusMediator\Exceptions;

use Exception;

class InvalidPipelineException extends Exception
{
    public function __construct(
        public readonly string $pipelineClass,
        public readonly string $context = ''
    ) {
        $message = "Pipeline class '$pipelineClass' is invalid or does not exist";

        if ($context !== '') {
            $message .= " (configured in '$context')";
        }

        $message .= ".\n\n";
        $message .= "Suggested solutions:\n";
        $message .= "1. Verify the class name and namespace are correct.\n";
        $message .= "2. Ensure the class has a public 'handle(\$payload, Closure \$next): mixed' method.\n";
        $message .= "3. Run 'composer dump-autoload' to refresh autoloading.\n";
        $message .= "4. Check 'config/mediator.php' for typos in global_pipelines, request_pipelines, or notification_pipelines.";

        parent::__construct($message);
    }
}
