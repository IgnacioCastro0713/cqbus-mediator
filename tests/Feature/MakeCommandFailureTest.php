<?php

use Ignaciocastro0713\CqbusMediator\Console\MakeEventHandlerCommand;
use Ignaciocastro0713\CqbusMediator\Console\MakeHandlerCommand;
use Illuminate\Filesystem\Filesystem;
use Mockery\MockInterface;

it('shows error when event handler stub file read fails', function () {
    /** @var Filesystem|MockInterface $files */
    $files = Mockery::mock(Filesystem::class);

    // ensureDirectoryExists checks if directory exists
    $files->shouldReceive('isDirectory')->andReturn(true);

    // shouldOverwriteFiles checks if file exists
    $files->shouldReceive('exists')->andReturn(false);

    // generateFile reads stub
    $files->shouldReceive('get')->andThrow(new Exception('Read error'));

    // put should NOT be called
    $files->shouldReceive('put')->never();

    $command = new MakeEventHandlerCommand($files);
    $command->setLaravel(app());

    $input = new \Symfony\Component\Console\Input\ArrayInput(['name' => 'TestHandler']);
    $output = new \Symfony\Component\Console\Output\BufferedOutput();

    $command->run($input, $output);

    expect($output->fetch())->toContain('Could not read stub file:');
});

it('shows error when handler stub file read fails', function () {
    /** @var Filesystem|MockInterface $files */
    $files = Mockery::mock(Filesystem::class);

    // ensureDirectoryExists checks if directory exists
    $files->shouldReceive('isDirectory')->andReturn(true);

    // shouldOverwriteFiles checks if file exists
    $files->shouldReceive('exists')->andReturn(false);

    // generateFile reads stub
    $files->shouldReceive('get')->andThrow(new Exception('Read error'));

    // put should NOT be called
    $files->shouldReceive('put')->never();

    $command = new MakeHandlerCommand($files);
    $command->setLaravel(app());

    $input = new \Symfony\Component\Console\Input\ArrayInput(['name' => 'TestHandler']);
    $output = new \Symfony\Component\Console\Output\BufferedOutput();

    $command->run($input, $output);

    expect($output->fetch())->toContain('Could not read stub file:');
});
