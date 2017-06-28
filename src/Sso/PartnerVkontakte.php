<?php

namespace Sso;

use Sso\vkontakte\VkontakteService;

/**
 * Implementation of Vkontakte SSO.
 */
class PartnerVkontakte implements PartnerInterface
{
    const REDIRECT_HOST = 'https://example.com';
    const REDIRECT_URI = '/sso/vkontakte';

    /** @var VkontakteService */
    private $service;

    /**
     * PartnerVkontakte constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->service = self::createService($config);
    }

    /**
     * Factory method.
     *
     * @param array $config
     *
     * @static
     * @return self
     */
    public static function create(array $config = [])
    {
        return new self($config);
    }

    /**
     * Create service.
     *
     * @param array $config
     *
     * @return VkontakteService
     */
    private function createService(array $config = [])
    {
        $params = [
       		'client_id' => isset($config['vkontakte_id']) ? $config['vkontakte_id'] : getenv('VKONTAKTE_ID'),
       		'client_secret' => isset($config['vkontakte_secret']) ? $config['vkontakte_secret'] : getenv('VKONTAKTE_SECRET'),
       		'redirect_uri' => isset($config['redirectHost'], $config['redirectUri'])
                ? $config['redirectHost'] . $config['redirectUri']
                : self::REDIRECT_HOST . self::REDIRECT_URI,
       		'scope' => ['email', 'offline'],
       	];
        $service = new VkontakteService($params);
        $service->setDoCurl(
            function (...$args) {
                return call_user_func_array(SsoUtil::class . '::doCurl', $args);
            }
        );

        return $service;
    }

    /**
     * Get Login url.
     *
     * @return string $url
     * @static
     */
    public function getLoginUrl()
    {
        return $this->service->getLoginUrl();
    }

    /**
     * Authenticate user for given params.
     *
     * @param array $query
     *
     * @return array $entity
     */
    public function authenticate(array $query)
    {
        $doCurl = function (...$args) {
            return call_user_func_array(SsoUtil::class . '::doCurl', $args);
        };
        $this->service->setDoCurl($doCurl);
        $data = $this->service->getUserData($query);

        return self::adapt($data);
    }

    /**
     * @param $data
     *
     * @static
     * @return array
     */
    private static function adapt($data)
    {
        if (isset($data['error_description'])) {
            throw new \RuntimeException($data['error_description']);
        }
        if (!isset($data['entry'])) {
            throw new \InvalidArgumentException('Invalid user data ' . var_export($data, true));
        }
        $wrapper = new ArrayWrapper($data['entry']);
        $default = '';
        $entity = [
            'id' => $wrapper->get('id', $default),
            'name' => $wrapper->get('name.formatted', $default),
            'lastname' => $wrapper->get('name.familyName', $default),
            'firstname' => $wrapper->get('name.givenName', $default),
            'location' => $wrapper->get('currentLocation.region', $default),
            'age' => $wrapper->get('age', $default),
            'gender' => $wrapper->get('gender', 'na'),
            'birthday' => $wrapper->get('birthday', '1970-01-01T23:00:00.000Z'),
            'email' => $wrapper->get('emails.0.value', $default),
            'avatar' => $wrapper->get('thumbnailUrl', $default),
            'data' => $data,
        ];
        // add missing data
        $names = explode(' ', $entity['name']);
        $entity['firstname'] = array_shift($names);
        $entity['lastname'] = implode(' ', $names);
        // default avatar url //avatars.nasza-klasa.pl/img/avatar/avatar_default_male does not work with https
        $entity['birthday'] = date('d-m-Y', strtotime($entity['birthday']));

        return $entity;
    }
}
