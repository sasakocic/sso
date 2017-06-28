<?php

namespace Tests;

use Sso\ArrayHelper;

class ArrayHelperTest extends \PHPUnit\Framework\TestCase
{
    private $array = [];

    public function setUp()
    {
        $this->array = [
            'key' => [
                'subkey' => 2
            ],
            'key2' => 'ok',
            'key3' => null,
        ];
    }

    public function testGetRecursiveWithBlankKey()
    {
        $result = ArrayHelper::get($this->array, '', false);
        $this->assertEquals(false, $result);
    }

    public function testGetRecursiveWithNotArray()
    {
        $result = ArrayHelper::get('Not an array', '', false);
        $this->assertEquals(false, $result);
    }

    public function testGetRecursiveFirstLevel()
    {
        $result = ArrayHelper::get($this->array, 'key2', false);
        $this->assertEquals('ok', $result);
    }

    public function testGetRecursiveWithSublevel()
    {
        $result = ArrayHelper::get($this->array, 'key.subkey', false);
        $this->assertEquals(2, $result);
    }

    public function testGetRecursiveWithNullElement()
    {
        $result = ArrayHelper::get($this->array, 'key3', false);
        $this->assertEquals(null, $result);
    }
}
