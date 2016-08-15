<?php

class ExampleJob
{
    public function __invoke($message)
    {
        var_dump($message);
    }
}
