<?php

namespace Example;

class ExampleJob
{
    public function __invoke(Options $message)
    {
        var_dump($message);
    }
}
