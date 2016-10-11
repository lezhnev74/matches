<?php

namespace Lezhnev74\Matches\Rule;

use Lezhnev74\Matches\Exception\RuleValidationException;

class BaseRule
{
    
    /**
     * Validate the value against the rule
     *
     * @throws RuleValidationException
     *
     * @param $value      data to be validated
     * @param $parameters some parameters passed from developer
     */
    public function validate($value, $parameters)
    {
        //
        // reload this function to make actual validation
        // if everything is okay - return nothing
        // in case of failing validation - throw an exception
        //
    }
    
}