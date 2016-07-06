<?php

namespace Equip\Queue;

use PHPUnit_Framework_TestCase as BaseTestCase;
use ReflectionClass;
use ReflectionMethod;

abstract class TestCase extends BaseTestCase
{
    /**
     * Make protected/private methods accessible
     *
     * @param object $class
     * @param string $name
     *
     * @return ReflectionMethod
     */
    public static function getProtectedMethod($class, $name)
    {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
