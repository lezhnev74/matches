<?php
namespace Lezhnev74\Matches;

use Lezhnev74\Matches\Exception\RuleValidationException;
use Lezhnev74\Matches\MessageResolverInterface;
use Lezhnev74\Matches\Rule\BaseRule;

/**
 * Class Box
 *
 * Designed to be used as Singleton and contains a map of all available rules
 *
 * @package lezhnev74\Matches
 */
class Box
{
    /**
     * @var array $rules A map of rule names and rule Fully Qualified Names (FQNs)
     */
    protected $rules = [];
    
    protected $locale = "en";
    
    /**
     * Resolver to call to get new message
     *
     * @var callable|null
     */
    protected $messageResolver = null;
    
    /**
     * @var Hub|null $instance
     */
    static private $instance = null;
    
    
    /**
     * Singleton resolver
     *
     * @return Hub
     */
    static public function getInstance()
    {
        if(is_null(self::$instance)) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    
    public function __construct()
    {
        
        // Register built in rules
        $this->addRule('required', \Lezhnev74\Matches\Rule\Required\Rule::class);
        $this->addRule('integer', \Lezhnev74\Matches\Rule\Integer\Rule::class);
        
    }
    
    
    /**
     * Set locale to find appropriate messages
     *
     * @param string $locale
     */
    public function setLocale($locale = "en")
    {
        $this->locale = $locale;
    }
    
    /**
     * Set message resolver which will be used to find message for failed Rule
     * This resolver will be called after built-in resolver
     * Use this resolver to integrate this library into your environment
     *
     * @param callable $resolver
     */
    public function setMessageResolver(callable $resolver)
    {
        $this->messageResolver = $resolver;
    }
    
    /**
     * Add\Replace a rule with given FQN
     *
     * @param $name
     * @param $fqn
     */
    public function addRule($name, $fqn)
    {
        $this->rules[ $name ] = $fqn;
    }
    
    /**
     * Parse the string of rules like
     * "file:min_size=512B;max_size=1GB|required"
     * and return array like
     * ["file"=>["min_size"=>"512B", "max_size"=>"1GB"], "required"=>[]]
     *
     * @throws \Exception;
     *
     * @param string $rules_string
     *
     * @return array
     */
    protected function parseRuleString(string $rules_string)
    {
        $rules = [];
        
        // one string can have multiple rules divided by "|"
        $single_rule_strings = explode("|", $rules_string);
        
        foreach($single_rule_strings as $single_rule_string) {
            
            // find the rule name
            preg_match("#([^:]+):?([^$]*)#u", $single_rule_string, $matches);
            if(!isset($matches[1])) {
                throw new \Exception("Rule " . $rules_string . " contains no field name");
            }
            // validate rule name
            if(!preg_match("#^[a-z0-9_]+$#ui", $matches[1])) {
                throw new \Exception("Rule " . $rules_string . " contains wrong field name");
            }
            $rule_name           = $matches[1];
            $rules[ $rule_name ] = [];
            
            // find rule parameters
            $rule_parameters = explode(";", $matches[2]);
            // remove empty
            $rule_parameters = array_filter($rule_parameters, function($element) {
                return mb_strlen($element);
            });
            foreach($rule_parameters as $rule_parameter) {
                if(!preg_match("#^(([^=]+)=([^$]*))$#u", $rule_parameter, $matches)) {
                    throw new \Exception("Rule parameter " . $rule_parameter . " is malformatted");
                }
                
                $rules[ $rule_name ][ $matches[2] ] = $matches[3];
                
            }
            
        }
        
        return $rules;
    }
    
    /**
     * Validates given data against rules
     *
     * @throws \Exception;
     *
     * @param array|mixed  $data
     * @param array|string $rules
     *
     * @return array $failed_rules
     */
    public function validate($data, $rules)
    {
        //
        // This method will execute validation rules one-by-one
        // If rule throws an exception, this method will find appropriate message and will
        // put it into the $failed_rules array
        //
        
        $failed_rules = [];
        
        foreach($rules as $data_name => $rule_string) {
            if(!is_string($rule_string)) {
                throw new \Exception("Rules are expected as a string, something else was found for data with name " . $data_name);
            }
            
            $data_item_rules = $this->parseRuleString($rule_string);
            
            foreach($data_item_rules as $rule_name => $rule_parameters) {
                try {
                    $this->invokeRule($data[ $data_name ] ?? null, $rule_name, $rule_parameters);
                } catch(RuleValidationException $e) {
                    $message = $this->resolveMessage($rule_name, $e->getMessageName());
                    $message = $this->resolvePlaceholders(
                        $message,
                        array_merge(
                            [
                                "field" => $data_name,
                                "rule" => $rule_name,
                                "value" => $data[ $data_name ] ?? null,
                            ],
                            $e->getMessageParameters()
                        )
                    );
                    
                    if(!isset($failed_rules[ $data_name ])) {
                        $failed_rules[ $data_name ] = [];
                    }
                    
                    $failed_rules[ $data_name ][ $rule_name ] = $message;
                    
                }
            }
        }
        
        // in case of "all green" this array will be empty
        return $failed_rules;
    }
    
    
    /**
     * Find the rule and invoke it's code
     *
     * @throws \Exception if rule not found
     *
     * @param $value data to be validated
     * @param $rule_name
     * @param $rule_parameters
     */
    private function invokeRule($value, $rule_name, $rule_parameters)
    {
        if(!isset($this->rules[ $rule_name ])) {
            throw new \Exception("Rule " . $rule_name . " was not found");
        }
        
        $rule_resolved = $this->rules[ $rule_name ];
        
        // is it is a callable thing - just call it right away
        if(is_callable($rule_resolved)) {
            $rule_resolved($value, $rule_parameters);
            
            return;
        }
        
        // if not - test if this is a FQN for class which is derived from BaseRule
        if(class_exists($rule_resolved)) {
            $rule_instance = new $rule_resolved;
            
            if($rule_instance instanceof BaseRule) {
                $rule_instance->validate($value, $rule_parameters);
                
                return;
            }
        }
        
        
        throw new \Exception("Rule " . $rule_name . " cannot be resolved");
    }
    
    
    /**
     * Find and return message template (with possible placeholders)
     *
     * @throws \Exception
     *
     * @param                         $rule_name
     * @param string                  $message_name
     *
     * @return string
     */
    private function resolveMessage($rule_name, string $message_name)
    {
        $message = "Rule " . $rule_name . " failed validation";
        
        //
        // if this is build-in validator - then find it's messages
        //
        $built_in_message_file_path = __DIR__ . DIRECTORY_SEPARATOR
                                      . "Rule" . DIRECTORY_SEPARATOR
                                      . ucfirst($rule_name) . DIRECTORY_SEPARATOR
                                      . "Messages.php";
        
        if(file_exists($built_in_message_file_path)) {
            $messages = include $built_in_message_file_path;
            
            if(isset($messages[ $message_name ][ $this->locale ])) {
                $message = $messages[ $message_name ][ $this->locale ];
            }
        }
        
        //
        // Also use message resolver if presented
        //
        // is it is a callable thing - just call it right away
        if(is_callable($this->messageResolver)) {
            $message = $this->messageResolver($rule_name, $message_name, $this->locale);
        } else {
            // if not - test if this is a FQN for class which is derived from BaseRule
            if(!is_null($this->messageResolver) && class_exists($this->messageResolver)) {
                $message_resolver_instance = new $this->messageResolver;
                
                if($message_resolver_instance instanceof MessageResolverInterface) {
                    $message = $message_resolver_instance->getMessage($rule_name, $message_name, $this->locale);
                } else {
                    throw new \Exception("MessageResolver " . get_class($message_resolver_instance) . " does not implement interface MessageResolverInterface");
                }
            }
        }
        
        return $message;
    }
    
    
    /**
     * Replace placeholders with given data
     *
     * @param $message
     * @param $parameters
     */
    private function resolvePlaceholders($message, $parameters)
    {
        foreach($parameters as $name => $value) {
            $message = str_replace(":" . $name, $value, $message);
        }
        
        return $message;
    }
    
}

