<?php
namespace Lezhnev74\Matches\Test;

use Lezhnev74\Matches\Box;
use PHPUnit\Framework\TestCase;

class BoxTest extends TestCase
{
    
    public function success_data_rules_provider()
    {
        return [
            [
                ["name" => "Jack"],
                ["name" => "required"],
            ],
            [
                ["name" => ""],
                ["last_name" => "integer"],
            ],
        ];
    }
    
    public function fail_data_rules_provider()
    {
        return [
            [
                [],
                ["name" => "required"],
            ],
            [
                ["name" => "", "age" => "twenty years old"],
                ["age" => "integer"],
            ],
        ];
    }
    
    /**
     * @dataProvider success_data_rules_provider
     */
    function test_box_validates_rules_with_success($data, $rules)
    {
        $box    = new Box();
        $errors = $box->validate($data, $rules);
        
        $this->assertEquals(0, count($errors));
        
    }
    
    /**
     * @dataProvider fail_data_rules_provider
     */
    function test_box_validates_rules_and_fails($data, $rules)
    {
        $box    = new Box();
        $errors = $box->validate($data, $rules);
        
        $this->assertEquals(1, count($errors));
    }
    
}