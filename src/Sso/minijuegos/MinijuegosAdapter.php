<?php

namespace Sso\minijuegos;

use casino\helpers\SsoHelper;
use Sso\SsoUtil;

/**
 * Minijuegos integration
 * Given to us from miniplay instead of SDK.
 * Can be refactored later.
 *
 * http://www.miniplay.com
 *
 */
class MinijuegosAdapter
{
    const API_SERVER = "https://ssl.minijuegos.com/api/lechuck/server";

    protected $api_key_prod;

    protected $api_key_dev;

    protected $game_id;

    protected $game_uid;

    protected $game_devel;

    protected $game_url;

    protected $site_url;

    protected $api_id;

    protected $api_js_url;

    protected $api_js_url_bck;

    protected $api_as3_url;

    protected $api_as3_url_bck;

    protected $api_user_id;

    protected $api_user_token;

    protected $locale;

    protected $timezone;

    protected $query = [];

    /** @var \Closure */
    private $doCurl;

    /**
     * Public constructor
     *
     * @param array $query
     *   [
     *   'mp_api_as3_url' => 'http://api.minijuegos.com/lechuck/static/as3/latest.swf',
     *   'mp_api_as3_url_bck' => 'http://api.minijuegos.com/lechuck/client-as/',
     *   'mp_api_id' => '1934',
     *   'mp_api_js_url' => 'http://api.minijuegos.com/lechuck/static/js/latest.js',
     *   'mp_api_js_url_bck' => 'http://api.minijuegos.com/lechuck/client-js/',
     *   'mp_api_user_id' => '6053645',
     *   'mp_api_user_token' => '878aee5e70fe498be4a6bc23475de575',
     *   'mp_assets' => 'https://s2.minijuegosgratis.com/',
     *   'mp_embed' => '0',
     *   'mp_game_devel' => '1',
     *   'mp_game_id' => '219502',
     *   'mp_game_uid' => 'dev-my-jackpot',
     *   'mp_game_url' => 'http://www.miniplay.com/game/dev-my-jackpot',
     *   'mp_int' => '1',
     *   'mp_locale' => 'en_US',
     *   'mp_player_type' => 'FRAME',
     *   'mp_site_name' => 'miniplay.com',
     *   'mp_site_url' => 'http://www.miniplay.com/',
     *   'mp_timezone' => 'Europe/Madrid',
     *   'mini_signature' => '20a86908017733448c5e5c4664ae36e1', // 31dba6078b93703d78f647f15e2635f6
     *   'xdm_e' => 'http://www.miniplay.com',
     *   'xdm_c' => 'default6257',
     *   'xdm_p' => '1',
     *   ]
     *
     */
    public function __construct($config = [])
    {
        $this->api_key_prod = isset($config['PRODUCTION_API_KEY']) ? $config['PRODUCTION_API_KEY'] : getenv('PRODUCTION_API_KEY');
        $this->api_key_dev = isset($config['DEVEL_API_KEY']) ? $config['DEVEL_API_KEY'] : getenv('DEVEL_API_KEY');
        $this->doCurl = function () {
            return [];
        };
    }

    /**
     * @param \Closure $doCurl
     *
     * @return self
     */
    public function setDoCurl(\Closure $doCurl)
    {
        $this->doCurl = $doCurl;

        return $this;
    }

    /**
     * Checks that the signature is valid
     *
     * @return boolean
     */
    public function isValidSignature()
    {
        return isset($this->query['mini_signature']) && $this->query['mini_signature'] == $this->mpSignParameters($this->query);
    }

    /**
     * Is the user logged in?
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        if ($this->api_user_id != null && $this->api_user_token != null) {
            return true;
        }

        return false;
    }

    /**
     * Is the development version?
     *
     * @return boolean
     */
    public function isDevel()
    {
        return $this->getGame_devel();
    }

    /**
     * Is the preroduiction development version? (development + preproduction)
     *
     * @return boolean
     */
    public function isPreproduction()
    {
        if ($this->isDevel() && isset($this->query['preproduction'])) {
            return true;
        }

        return false;
    }

    /**
     * Get the parameters signature
     *
     * @param array $parameters
     *
     * @return string
     */
    protected function mpSignParameters(array $parameters)
    {
        ksort($parameters); /* Sort array alphabetically by its keys (Although they should be already sorted by key) */
        $signatureString = "";
        foreach ($parameters as $key => $value) {
            if (substr($key, 0, 3) === "mp_") {
                $signatureString .= (string) $value;
            }
        }

        return md5($this->getApiKey() . $signatureString);
    }

    /**
     * Get the corresponding api key, prod or dev by it's status
     *
     * @return string
     */
    protected function getApiKey()
    {
        if ($this->game_devel == true) {
            return $this->api_key_dev;
        } else {
            return $this->api_key_prod;
        }
    }

    /**
     * @return the $game_id
     */
    public function getGame_id()
    {
        return $this->game_id;
    }

    /**
     * @return the $game_uid
     */
    public function getGame_uid()
    {
        return $this->game_uid;
    }

    /**
     * @return the $game_devel
     */
    public function getGame_devel()
    {
        return $this->game_devel;
    }

    /**
     * @return the $game_url
     */
    public function getGame_url()
    {
        return $this->game_url;
    }

    /**
     * @return the $site_url
     */
    public function getSite_url()
    {
        return $this->site_url;
    }

    /**
     * @return the $api_id
     */
    public function getApi_id()
    {
        return $this->api_id;
    }

    /**
     * @return the $api_js_url
     */
    public function getApi_js_url()
    {
        return $this->api_js_url;
    }

    /**
     * @return the $api_js_url_bck
     */
    public function getApi_js_url_bck()
    {
        return $this->api_js_url_bck;
    }

    /**
     * @return the $api_as3_url
     */
    public function getApi_as3_url()
    {
        return $this->api_as3_url;
    }

    /**
     * @return the $api_as3_url_bck
     */
    public function getApi_as3_url_bck()
    {
        return $this->api_as3_url_bck;
    }

    /**
     * @return the $api_user_id
     */
    public function getApi_user_id()
    {
        return $this->api_user_id;
    }

    /**
     * @return the $api_user_token
     */
    public function getApi_user_token()
    {
        return $this->api_user_token;
    }

    /**
     * @return the $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return the locale language (ie. en, es)
     */
    public function getLocaleLang()
    {
        $locale = $this->getLocale();
        if (empty($locale)) {
            $locale = "en";
        }

        return substr($locale, 0, 2);
    }

    /**
     * @return the $timezone
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Get user data
     *
     * by calling API
     * https://ssl.minijuegos.com/api/lechuck/server/user/6053645/?api_key=a3506b552dd320088186aa375ab1cc52&user_token=5f36b30969f38a4adcf22747903e5501
     *
     * @param string $userId
     *
     * @return array $data
     */
    public function getUserData($userId = '')
    {
        if ($userId === '') {
            $userId = $this->getApi_user_id();
        }
        $apiKey = $this->isDevel() ? $this->api_key_dev : $this->api_key_prod;
        $url = self::API_SERVER . "/user/" . $userId .
            "/?api_key=" . $apiKey . "&user_token=" . $this->getApi_user_token();
        $curl = $this->doCurl;
        $userData = $curl($url);
        if (!isset($userData['status']['success']) ||
            !$userData['status']['success'] ||
            !isset($userData['data']['user'])
        ) {
            throw new \RuntimeException('No user data for ' . $url . var_export($userData, true));
        }

        return $userData['data']['user'];
    }

    /**
     * Check if user is logged in, as minijuegos defines it
     */
    public function checkLoggedIn()
    {
        if (!$this->isLoggedIn()) {
            throw new \RuntimeException('User not logged in');
        }
    }

    /**
     * Parse Query.
     *
     * @param $query
     *
     * @return array
     */
    public function parseQuery($query = [])
    {
        if ($query === []) {
            $query_string = str_replace('&amp;', '&', $_SERVER['QUERY_STRING']);
            parse_str($query_string, $query);
        }
        if (!isset($query['mp_api_user_token'])) {
            throw new \RuntimeException('Missing mp_api_user_token in query');
        }
        $this->game_id = !empty($query['mp_game_id']) ? $query['mp_game_id'] : null;
        $this->game_uid = !empty($query['mp_game_uid']) ? $query['mp_game_uid'] : null;
        $this->game_devel = !empty($query['mp_game_devel']) ? !!$query['mp_game_devel'] : false;
        $this->game_url = !empty($query['mp_game_url']) ? $query['mp_game_url'] : null;
        $this->site_url = !empty($query['mp_site_url']) ? ($query['mp_site_url']) : "http://www.miniplay.com/";
        $this->api_id = !empty($query['mp_api_id']) ? $query['mp_api_id'] : null;
        $this->api_js_url = !empty($query['mp_api_js_url']) ? ($query['mp_api_js_url']) : null; // Url of the javascript api to use
        $this->api_js_url_bck = !empty($query['mp_api_js_url_bck']) ? ($query['mp_api_js_url_bck']) : null; // Url for js api connections
        $this->api_as3_url = !empty($query['mp_api_as3_url']) ? ($query['mp_api_as3_url']) : null; // Url of the as3 api to use
        $this->api_as3_url_bck = !empty($query['mp_api_as3_url_bck']) ? ($query['mp_api_as3_url_bck']) : null; // Url for as3 api connections
        $this->api_user_id = !empty($query['mp_api_user_id']) ? $query['mp_api_user_id'] : null;
        $this->api_user_token = !empty($query['mp_api_user_token']) ? $query['mp_api_user_token'] : null;
        $this->locale = !empty($query['mp_locale']) ? $query['mp_locale'] : "en_US";
        $this->timezone = !empty($query['mp_timezone']) ? $query['mp_timezone'] : "GMT";
        $this->query = $query;
        if (!$this->isValidSignature()) {
            // throw new \RuntimeException('Invalid signature for ' . print_r($query, true) . $query_string);
        }
    }
}
