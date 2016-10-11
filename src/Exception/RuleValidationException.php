<?php

namespace Lezhnev74\Matches\Exception;

class RuleValidationException extends \Exception
{
    /**
     * Values to replace within the message
     * For example ":field" replaced with "name"
     *
     * @var array
     */
    protected $message_values = [];
    
    /**
     * The name of the message template to use as a validation response
     *
     * @var
     */
    protected $message_name;
    
    
    public function __construct(array $message_values = [], $message_name = "default")
    {
        parent::__construct();
        
        $this->message_values = $message_values;
        $this->message_name   = $message_name;
    }
    
    /**
     * Return the given message name
     *
     * @return string
     */
    public function getMessageName()
    {
        return $this->message_name;
    }
    
    /**
     * Return message parameters to use as replacement
     *
     * @return array
     */
    public function getMessageParameters()
    {
        return $this->message_values;
    }
    
}