<?php

return array (
  'handlers' => 
  array (
    'Tests\\Fixtures\\Handlers\\BasicRequest' => 'Tests\\Fixtures\\Handlers\\BasicHandler',
    'Tests\\Fixtures\\Handlers\\GlobalAndHandlerRequest' => 'Tests\\Fixtures\\Handlers\\GlobalAndHandlerPipelineHandler',
    'Tests\\Fixtures\\Handlers\\RequestForInvalidHandler' => 'Tests\\Fixtures\\Handlers\\HandlerWithoutHandleMethod',
    'Tests\\Fixtures\\Handlers\\MultiplePipelineRequest' => 'Tests\\Fixtures\\Handlers\\MultiplePipelineHandler',
    'Tests\\Fixtures\\Handlers\\NoPipelineRequest' => 'Tests\\Fixtures\\Handlers\\NoPipelineHandler',
    'Tests\\Fixtures\\Handlers\\PipelineTestRequest' => 'Tests\\Fixtures\\Handlers\\SinglePipelineHandler',
    'Tests\\Fixtures\\Handlers\\SkipGlobalRequest' => 'Tests\\Fixtures\\Handlers\\SkipGlobalHandler',
    'Tests\\Fixtures\\Handlers\\SkipGlobalWithHandlerPipelineRequest' => 'Tests\\Fixtures\\Handlers\\SkipGlobalWithHandlerPipelineHandler',
  ),
  'event_handlers' => 
  array (
    'Tests\\Fixtures\\Events\\UserRegisteredEvent' => 
    array (
      0 => 
      array (
        'handler' => 'Tests\\Fixtures\\EventHandlers\\SendWelcomeEmailHandler',
        'priority' => 10,
      ),
      1 => 
      array (
        'handler' => 'Tests\\Fixtures\\EventHandlers\\CreateDefaultSettingsHandler',
        'priority' => 5,
      ),
      2 => 
      array (
        'handler' => 'Tests\\Fixtures\\EventHandlers\\LogUserRegistrationHandler',
        'priority' => 1,
      ),
    ),
    'Tests\\Fixtures\\Events\\EventForInvalidHandler' => 
    array (
      0 => 
      array (
        'handler' => 'Tests\\Fixtures\\EventHandlers\\EventHandlerWithoutHandleMethod',
        'priority' => 0,
      ),
    ),
    'Tests\\Fixtures\\Events\\EventWithPipeline' => 
    array (
      0 => 
      array (
        'handler' => 'Tests\\Fixtures\\EventHandlers\\EventHandlerWithPipeline',
        'priority' => 0,
      ),
    ),
  ),
  'actions' => 
  array (
    0 => 'Tests\\Fixtures\\AttributeAction',
    1 => 'Tests\\Fixtures\\AuthAction',
    2 => 'Tests\\Fixtures\\NoAttributesAction',
    3 => 'Tests\\Fixtures\\NoPrefixAction',
  ),
);
