<?php

namespace Tests;

use Sso\PartnerNaszaklasa;

class PartnerNaszaklasaTest extends \PHPUnit\Framework\TestCase
{
    /** @var PartnerNaszaklasa */
    private $service;

    public function setUp()
    {
        $config = [
            'id' => getenv('NASZAKLASA_ID'),
            'key' => getenv('NASZAKLASA_KEY'),
            'redirectUri' => '/sso/naszaklasa',
            'redirectHost' => 'https://example.com',
        ];

        $this->service = new PartnerNaszaklasa($config);
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Sso\PartnerNaszaklasa', $this->service);
    }

    public function testGetLoginUrl()
    {
        $expected = 'https://nk.pl/oauth2/login?client_id=jackpot-bd6556de-99a3-4822-a7cf-&response_type=code&redirect_uri=https%3A%2F%2Fexample.com%2Fsso%2Fnaszaklasa&scope=BASIC_PROFILE_ROLE%2CBIRTHDAY_PROFILE_ROLE%2CEMAIL_PROFILE_ROLE';
        $this->assertEquals($expected, $this->service->getLoginUrl());
    }

    /**
     * @expectedException \Sso\naszaklasa\NaszaklasaException
     * @expectedExceptionMessage Missing code in callback
     */
    public function testAuthenticateMissingCode()
    {
        $query = [];
        $expected = [];
        $userData = $this->service->authenticate($query);
        $this->assertEquals($expected, $userData);
    }

    /**
     * @expectedException \Sso\naszaklasa\NaszaklasaException
     * @expectedExceptionMessage invalid_grant
     */
    public function testAuthenticateInvalidGrant()
    {
        $query = [
            'code' => 'a',
        ];
        $expected = [];
        $userData = $this->service->authenticate($query);
        $this->assertEquals($expected, $userData);
    }

    public function testAdapt()
    {
        $data = [
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
                   ['type' => 'email', 'value' => 'skocic@whow.net',],
               ],
               'isViewer' => true,
               'thumbnailUrl' => 'http://avatars.nasza-klasa.pl/img/avatar/avatar_default_male',
               'currentLocation' => ['region' => 'Hamburg',],
               'age' => 47,
           ],
        ];
        $userData = PartnerNaszaklasa::adapt($data);
        $this->assertEquals('person.3bdfcabfa48c9673', $userData['id']);
        $this->assertEquals('Sasa Kocic', $userData['name']);
        $this->assertEquals('Kocic', $userData['lastname']);
        $this->assertEquals('Sasa', $userData['firstname']);
        $this->assertEquals('skocic@whow.net', $userData['email']);
        $this->assertEquals('http://avatars.nasza-klasa.pl/img/avatar/avatar_default_male', $userData['avatar']);
        $this->assertEquals('Hamburg', $userData['location']);


    }

}
