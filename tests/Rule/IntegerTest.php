<?php
namespace Lezhnev74\Matches\Test\Rule;

use Lezhnev74\Matches\Box;
use PHPUnit\Framework\TestCase;

class IntegerTest extends TestCase
{
    
    public function provider()
    {
        return [
            [
                ["age" => "20"],
                ["age" => "integer"],
                0,
            ],
            [
                ["age" => "twenty"],
                ["age" => "integer"],
                1,
            ],
            [
                ["age" => "-0"],
                ["age" => "integer"],
                0,
            ],
            [
                ["age" => "+0"],
                ["age" => "integer"],
                0,
            ],
            [
                ["age" => ""],
                ["age" => "integer"],
                1,
            ],
            [
                [],
                ["age" => "integer"],
                0,
            ],
        ];
    }
    
    /**
     * @dataProvider provider
     */
    function test_box_validates_integer_rule($data, $rules, $errors_count)
    {
        $box    = new Box();
        $errors = $box->validate($data, $rules);
        
        
        $this->assertEquals($errors_count, count($errors));
    }
    
}