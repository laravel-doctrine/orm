<?php

use LaravelDoctrine\ORM\Utilities\ArrayUtil;

class ArrayUtilTest extends PHPUnit_Framework_TestCase
{
    public function test_returns_value_when_exists()
    {
        $values = [
            'key' => 'value'
        ];

        $this->assertEquals('value', ArrayUtil::get($values['key']));
    }

    public function test_returns_default_value_when_not_exists()
    {
        $values = [
            'key' => 'value'
        ];

        $this->assertNull(ArrayUtil::get($values['key2']));
    }

    public function test_can_pass_custom_default_value()
    {
        $values = [
            'key' => 'value'
        ];

        $this->assertEquals('default', ArrayUtil::get($values['key2'], 'default'));
    }

    /**
     * @dataProvider arrayProvider
     *
     * @param array $array1
     * @param array $array2
     * @param bool  $expectedEquals
     */
    public function test_hashArray_returns_unique_hash(array $array1, array $array2, $expectedEquals)
    {
        $hash1 = ArrayUtil::hashArray($array1);
        $hash2 = ArrayUtil::hashArray($array2);

        if ($expectedEquals) {
            $this->assertEquals($hash1, $hash2);
        } else {
            $this->assertNotEquals($hash1, $hash2);
        }
    }

    /**
     * @return array
     */
    public function arrayProvider()
    {
        return [
            [[], [], true],
            [['key1' => 'value1'], ['key1' => 'value1'], true],
            [['key1' => 'value1'], ['key1' => 'value1-NOT-EQUALS'], false],
        ];
    }
}
