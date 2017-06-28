<?php

namespace Sso;

use Sso\SsoUtil;
use Sso\minijuegos\MinijuegosAdapter;

/**
 * Implementation of partner minijuegos SSO.
 */
class PartnerMinijuegos implements PartnerInterface
{
    /** @var MinijuegosAdapter */
    private $adapter;

    /**
     * PartnerMinijuegos constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->adapter = new MinijuegosAdapter($config);
        $this->adapter->setDoCurl(
            function (...$args) {
                return call_user_func_array(SsoUtil::class . '::doCurl', $args);
            }
        );
    }

    /**
     * @param array $config
     *
     * @static
     * @return PartnerMinijuegos
     */
    public static function create(array $config = [])
    {
        return new self($config);
    }

    /**
     * Get Login url is not used as minijuegos just forwards players to us.
     * If it was used, it would point to this url.
     *
     * @return string $url
     */
    public function getLoginUrl()
    {
        return 'http://www.miniplay.com/game/jackpot';
    }

    /**
     * Authenticate user for given params.
     *
     * @param array $query
     *
     * @return array $userData
     */
    public function authenticate(array $query)
    {
        $this->adapter->parseQuery($query);
        $this->adapter->checkLoggedIn();

        return self::adapt($this->adapter->getUserData());
    }

    /**
     * Adapt data.
     *
     * Example:
     * [
     *  'status' =>
     *  [
     *    'success' => true,
     *    'type' => 'OK',
     *    'code' => 200,
     *    'message' => NULL,
     *  ],
     *  'data' =>
     *  [
     *    'message' => 'User information (private, user_token is valid)',
     *    'user' =>
     *    [
     *      'id' => '6053645',
     *      'uid' => 'skocic',
     *      'gender' => 'M',
     *      'locale' => 'en_US',
     *      'non_profile' => '0',
     *      'profile' => 'http://www.minijuegos.com/perfil/skocic',
     *      'date_birth' => '1969-10-13',
     *      'web_reg' => '10',
     *      'social_total_followers' => 0,
     *      'social_total_subscriptions' => 0,
     *      'social_total_friends' => 0,
     *      'progress_level' => 1,
     *      'progress_points' => 10,
     *      'progress_level_points_min' => 0,
     *      'progress_level_points_max' => 99,
     *      'position_days' => [],
     *      'position_days_known' => [],
     *      'position_status' => 0,
     *      'country' => 'ES',
     *      'avatar' => 'https://www.minijuegosgratis.com/users/avatars/3645/6053645/96x96.jpg',
     *      'avatar_mini' => 'https://www.minijuegosgratis.com/users/avatars/3645/6053645/32x32.jpg',
     *      'avatar_big' => 'https://www.minijuegosgratis.com/users/avatars/3645/6053645/256x256.jpg',
     *      'avatar_alpha' => 'https://www.minijuegosgratis.com/users/avatars/3645/6053645/96x96.png',
     *      'avatar_body' => 'https://www.minijuegosgratis.com/users/avatars/3645/6053645/160x220.png',
     *    ],
     *    'totalTimeMs' => 34,
     *  ],
     * ]
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
            'id' => $wrapper->get('id', $default),
            'username' => $wrapper->get('uid', $default),
            'name' => $wrapper->get('uid', $default),
            'lastname' => $wrapper->get('surname', $default),
            'firstname' => $wrapper->get('name', $default),
            'locale' => $wrapper->get('locale', $default),
            'country' => $wrapper->get('country', $default),
            'gender' => self::formatGender($wrapper->get('gender', $default)),
            'birthday' => self::formatDate($wrapper->get('date_birth', '1970-01-01')),
            'email' => $wrapper->get('email', $default),
            'avatar' => $wrapper->get('avatar_big', $default),
            'data' => $data,
        ];

        return $entity;
    }

    /**
     * Change from 1970-01-02 to 02-01-1970
     *
     * @param $date
     *
     * @static
     * @return string
     */
    public static function formatDate($date)
    {
        return substr($date, 8, 2) . '-' . substr($date, 5, 2) . '-' . substr($date, 0, 4);
    }

    /**
     * Format gender.
     *
     * @param $gender
     *
     * @static
     */
    public static function formatGender($gender)
    {
        switch ($gender) {
            case 'M':
                $result = 'male';
                break;
            case 'F':
                $result = 'female';
                break;
            default:
                $result = 'na';
                break;
        }

        return $result;
    }
}
