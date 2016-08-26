<?php

namespace Example;

use Equip\Command\OptionsInterface;
use Equip\Command\OptionsSerializerTrait;

class ExampleOptions implements OptionsInterface
{
    use OptionsSerializerTrait;

    public function test()
    {
        return 'example';
    }
}
