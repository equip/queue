<?php

namespace Example;

class ExampleJob
{
    public function __invoke(Message $message)
    {
        var_dump($message);
    }
}
