<?php

namespace Lezhnev74\Matches\Test;

class TestHelper
{
    
    /**
     * Call method of an object isolated
     *
     * @param       $obj
     * @param       $name
     * @param array $args
     *
     * @return mixed
     */
    public static function callMethod($obj, $name, array $args)
    {
        $class  = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        
        return $method->invokeArgs($obj, $args);
    }
}