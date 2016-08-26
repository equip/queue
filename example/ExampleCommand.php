<?php

namespace Example;

use Equip\Command\CommandImmutableOptionsTrait;
use Equip\Command\CommandInterface;

class ExampleCommand implements CommandInterface
{
    use CommandImmutableOptionsTrait;

    public function withOptions(ExampleOptions $options)
    {
        return $this->copyWithOptions($options);
    }

    public function execute()
    {
        var_dump($this->options);
    }
}
