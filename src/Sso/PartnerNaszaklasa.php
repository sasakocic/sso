<?php

namespace Sso;

use Sso\naszaklasa\NaszaklasaService;

/**
 * Implementation of Naszaklasa SSO.
 */
class PartnerNaszaklasa implements PartnerInterface
{
    const REDIRECT_HOST = 'https://staging-6-www.jackpot.de';
    const REDIRECT_URI = '/sso/naszaklasa';

    /** @var NaszaklasaService */
    private $service;

    /**
     * PartnerNaszaklasa constructor.
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
     * @return NaszaklasaService
     */
    private function createService(array $config = [])
    {
        $configEntity = new ConfigEntity($config);
        $configEntity->key = $configEntity->key
            ? $configEntity->key
            : getenv('NASZAKLASA_KEY');
        $configEntity->secret = $configEntity->secret
            ? $configEntity->secret
            : getenv('NASZAKLASA_SECRET');
        $configEntity->redirectHost = $configEntity->redirectHost
            ? $configEntity->redirectHost
            : self::REDIRECT_HOST;
        $configEntity->redirectUri = $configEntity->redirectUri
            ? $configEntity->redirectUri
            : self::REDIRECT_URI;
        $service = new NaszaklasaService($configEntity);
        $service->setDoCurl(
            function (...$args) {
                return call_user_func_array(SsoUtil::class . '::doCurl', $args);
            }
        );

        return $service;
    }

    /**
     * Get Login url
     * example:
     * https://nk.pl/oauth2/login?
     * client_id=demo&
     * response_type=code&
     * redirect_uri=<?php echo urlencode(">&scope=BASIC_PROFILE_ROLE,EMAIL_PROFILE_ROLE,CREATE_SHOUTS_ROLE">
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
    public static function adapt($data)
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
