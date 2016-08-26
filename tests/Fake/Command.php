<?php

namespace Equip\Queue\Fake;

use Equip\Command\CommandImmutableOptionsTrait;
use Equip\Command\CommandInterface;
use Equip\Command\OptionsInterface;

class Command implements CommandInterface
{
    use CommandImmutableOptionsTrait;

    public function withOptions(OptionsInterface $options)
    {
        return $this->copyWithOptions($options);
    }

    public function execute()
    {
        return true;
    }
}
