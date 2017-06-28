<?php

namespace Tests;

use Sso\ConfigEntity;

class ConfigEntityTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $config = [
            'id' => 'id',
            'key' => 'key',
            'secret' => 'secret',
            'url' => 'url',
        ];
        $configEntity = new ConfigEntity($config);
        $this->assertInstanceOf('Sso\ConfigEntity', $configEntity);
        $this->assertEquals('id', $configEntity->id);
        $this->assertEquals('key', $configEntity->key);
        $this->assertEquals('secret', $configEntity->secret);
        $this->assertEquals('url', $configEntity->url);
    }
}
