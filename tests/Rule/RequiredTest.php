<?php
namespace Lezhnev74\Matches\Test\Rule;

use Lezhnev74\Matches\Box;
use PHPUnit\Framework\TestCase;

class RequiredTest extends TestCase
{
    
    public function provider()
    {
        return [
            [
                ["name" => "Jack"],
                ["name" => "required"],
                0,
            ],
            [
                ["name" => " "],
                ["name" => "required"],
                0,
            ],
            [
                ["name" => ""],
                ["last_name" => "required"],
                1,
            ],
            [
                ["name" => ""],
                ["name" => "required"],
                1,
            ],
        ];
    }
    
    /**
     * @dataProvider provider
     */
    function test_box_validates_required_rule($data, $rules, $errors_count)
    {
        $box    = new Box();
        $errors = $box->validate($data, $rules);
        
        
        $this->assertEquals($errors_count, count($errors));
    }
    
}