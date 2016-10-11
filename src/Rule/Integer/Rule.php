<?php

namespace Lezhnev74\Matches\Rule\Integer;

use Lezhnev74\Matches\Exception\RuleValidationException;
use Lezhnev74\Matches\Rule\BaseRule;

class Rule extends BaseRule
{
    public function validate($value, $parameters = [])
    {
        // do not validate absent values
        if(is_null($value)) {
            return;
        }
        
        if(!preg_match("#^[+-]?([1-9]\d*|0)$#", $value)) {
            throw new RuleValidationException();
        }
        
        if("yes" === $unsigned = $parameters['allow_negative'] ?? false) {
            if(!preg_match("#^[+]?([1-9]\d*|0)$#", $value)) {
                throw new RuleValidationException([], "negative_found");
            }
        }
        
    }
    
}