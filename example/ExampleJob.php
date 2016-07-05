<?php

class ExampleJob
{
    public function __invoke($name, array $data = [], array $meta = [])
    {
        var_dump($name, $data, $meta);
    }
}
