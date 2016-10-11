<?php

namespace Lezhnev74\Matches\Rule\Required;

use Lezhnev74\Matches\Rule\BaseRule;
use Lezhnev74\Matches\Exception\RuleValidationException;

/**
 * Rule validates that data is presented (NOT NULL) and not an empty string
 *
 * @package Lezhnev74\Matches\Rule\Required
 */
class Rule extends BaseRule
{
    public function validate($value, $parameters)
    {
        if(is_null($value)) {
            throw new RuleValidationException();
        }
        
        if(is_string($value) && !mb_strlen($value)) {
            throw new RuleValidationException([], "empty");
        }
    }
    
}