<?php
namespace Lezhnev74\Matches\Test;

use Lezhnev74\Matches\Box;
use PHPUnit\Framework\TestCase;

class BoxRuleStringParsingTest extends TestCase
{
    
    public function wrong_rules_provider()
    {
        return [
            ["file=2|required"],
            ["fileЙ|required"],
            ["|required"],
            ["|"],
            ["||"],
            ["some:x,y"],
            ["some:a=x,y;q"],
            ["type:image"],
            ["type:;q;"],
        ];
    }
    
    public function good_rules_provider()
    {
        return [
            [
                "file",
                [
                    'file' => [],
                ],
            ],
            [
                "required|between:from=1;to=10",
                [
                    'required' => [],
                    'between' => [
                        'from' => "1",
                        "to" => "10",
                    ],
                ],
            ],
            [
                "file:min_size=512B;max_size=1GB|required|type:allowed=video,image",
                [
                    'file' => [
                        'min_size' => '512B',
                        'max_size' => '1GB',
                    ],
                    'required' => [],
                    'type' => [
                        'allowed' => 'video,image',
                    ],
                ],
            ],
        ];
    }
    
    /**
     * @dataProvider good_rules_provider
     */
    function test_rule_string_parsed_correctly($string, $expected_array)
    {
        $box = new Box();
        
        $returnVal = TestHelper::callMethod($box, 'parseRuleString', [$string]);
        
        $this->assertEquals($returnVal, $expected_array);
    }
    
    /**
     * @dataProvider wrong_rules_provider
     */
    function test_incorrect_rule_string_gives_exception($string)
    {
        $box = new Box();
        $this->expectException('Exception');
        
        $returnVal = TestHelper::callMethod($box, 'parseRuleString', [$string]);
        
    }
    
}