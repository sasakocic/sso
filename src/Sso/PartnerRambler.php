<?php

namespace Sso;

use Sso\rambler\RamblerService;
use Sso\ArrayWrapper;

/**
 * Implementation of Rambler SSO.
 */
class PartnerRambler implements PartnerInterface
{
    const REDIRECT_HOST = 'https://staging-6-www.jackpot.de';
    const REDIRECT_URI = '/sso/rambler';
    /** @var RamblerService */
    private $service;

    /**
     * PartnerRambler constructor.
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
     * @return RamblerService
     */
    private function createService(array $config = [])
    {
        $configEntity = new ConfigEntity($config);
        $configEntity->key = $configEntity->key
            ? $configEntity->key
            : getenv('RAMBLER_KEY');
        $configEntity->secret = $configEntity->secret
            ? $configEntity->secret
            : getenv('RAMBLER_SECRET');
        $configEntity->redirectHost = $configEntity->redirectHost
            ? $configEntity->redirectHost
            : self::REDIRECT_HOST;
        $configEntity->redirectUri = $configEntity->redirectUri
            ? $configEntity->redirectUri
            : self::REDIRECT_URI;
        $service = new RamblerService($configEntity);
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
     * Adapt the data.
     *
     * @param $data
     *
     * @static
     * @return array
     */
    private static function adapt($data)
    {
        $wrapper = new ArrayWrapper($data);
        $default = '';
        $entity = [
            'id' => $wrapper->get('user_id', $default),
            'data' => $data,
        ];

        return $entity;
    }
}
