<?php

namespace Example;

class ExampleHandler
{
    public function handle(ExampleCommand $command)
    {
        var_dump($command->foo());
    }
}
