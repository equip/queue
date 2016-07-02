<?php

class ExampleJob
{
    public function __invoke(array $data = [])
    {
        var_dump('invoke', $data);
    }
}
