<?php

use Sso\naszaklasa\NaszaklasaService;
use Sso\naszaklasa\NaszaklasaException;
use Sso\ConfigEntity;

class NaszaklasaServiceTest extends \PHPUnit\Framework\TestCase
{
    const CODE = '2135|1495061|7c89fbbab12b1';
    /** @var NaszaklasaService */
    const TOKEN = [
        'access_token' => 'xxxxxxxx-xxxxxxxxxxxxxxxxxxxxxxxx-xxxxxxxx',
        'token_type' => 'Bearer',
        'expires_in' => 900,
        'refresh_token' => 'xxxxxxx-xxxxxxxxxxxxxxxxxxxxxxxxx_xxx',
    ];
    const DATA = [
       'entry' => [
           'isOwner' => true,
           'id' => 'person.3bdfcabfa48c9673',
           'name' => [
               'formatted' => 'Sasa Kocic',
               'familyName' => 'Kocic',
               'givenName' => 'Sasa',
           ],
           'displayName' => 'Sasa Kocic',
           'emails' => [
               ['type' => 'email', 'value' => 'skocic@example.com',],
           ],
           'isViewer' => true,
           'thumbnailUrl' => 'http://avatars.nasza-klasa.pl/img/avatar/avatar_default_male',
           'currentLocation' => ['region' => 'Athens',],
           'age' => 40,
       ],
    ];
    const ERROR = [
        'error' => "invalid_grant",
        'error_description' => "Authorization code is no longer valid",
        'error_uri' => "http://developers.nk.pl/wiki/NKConnect_Errors",
    ];

    /** @var NaszaklasaService */
    private $service;

    public function setUp()
    {
        $config = [
            'id' => 'NASZAKLASA_ID',
            'key' => 'NASZAKLASA_KEY',
            'secret' => 'NASZAKLASA_SECRET',
            'redirectHost' => 'https://integrations.yahuah.net',
            'redirectUri' => '/naszaklasa/callback',
        ];
        $configEntity = new ConfigEntity($config);
        $this->service = new NaszaklasaService($configEntity);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('Sso\naszaklasa\NaszaklasaService', $this->service);
    }

    public function testGetLoginUrl()
    {
        $url = $this->service->getLoginUrl();
        $expected = 'https://nk.pl/oauth2/login?client_id=NASZAKLASA_KEY&response_type=code&redirect_uri=https%3A%2F%2Fintegrations.yahuah.net%2Fnaszaklasa%2Fcallback&scope=BASIC_PROFILE_ROLE%2CBIRTHDAY_PROFILE_ROLE%2CEMAIL_PROFILE_ROLE';
        $this->assertEquals($expected, $url);
    }

    public function testGetUser()
    {
        $doCurl = function ($url) {
            if ($url === 'https://nk.pl/oauth2/token') {
                return self::TOKEN;
            } else {
                return self::DATA;
            }
        };
        $this->service->setDoCurl($doCurl);
        $query = [
            'code' => self::CODE,
        ];
        $return = $this->service->getUserData($query);
        $this->assertEquals('person.3bdfcabfa48c9673', $return['entry']['id']);
    }

    /**
     * @expectedException \Sso\naszaklasa\NaszaklasaException
     * @expectedExceptionMessage invalid_grant
     */
    public function testGetUserError()
    {
        $doCurl = function () {
            return self::ERROR;
        };
        $this->service->setDoCurl($doCurl);
        $query = [
            'code' => self::CODE,
        ];
        $this->service->getUserData($query);
        $this->fail('Exception was not thrown');
    }

    public function testProcessCode()
    {
        $doCurl = function () {
            return self::TOKEN;
        };
        $this->service->setDoCurl($doCurl);
        $code = self::CODE;
        $result = $this->service->getTokenForCode($code);
        $this->assertEquals(self::TOKEN, $result);
    }
}
