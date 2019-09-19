<?php

use LaravelDoctrine\ORM\Utilities\ArrayUtil;
use PHPUnit\Framework\TestCase;

class ArrayUtilTest extends TestCase
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
}
