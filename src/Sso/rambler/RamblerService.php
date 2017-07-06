<?php

namespace Sso\rambler;

use Sso\ConfigEntity;

class RamblerService
{
    /** @var ConfigEntity */
    private $config;
    /** @var \Closure */
    private $doCurl;

    public function __construct(ConfigEntity $config)
    {
        $this->config = $config;
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

    public function getLoginUrl()
    {
        return 'https://sandbox.games.rambler.ru/igra-myjackpot';
    }

    /**
     * @param array $query
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getUserData(array $query)
    {
        if (isset($query['error'])) {
            return $query;
        }
        $this->checkSignature($query);
        if (isset($result['error'])) {
            throw new \RuntimeException($result['error']);
        }
        $result = ['id' => $query['user_id']];

        return $result;
    }

    public function checkSignature($query)
    {
        if (!isset($query['sig'])) {
            throw new \RuntimeException('Signature `sig` missing in query ' . var_export($query, true));
        }
        $sig = $query['sig'];
        unset($query['sig']);
        foreach (['user_id', 'game_id', 'slug', 'timestamp'] as $key) {
            if (!isset($query[$key])) {
                throw new \RuntimeException('Query is missing ' . $key);
            }
        }
        ksort($query);
        $string = http_build_query($query) . '&' . $this->config->secret;
        $signature = md5($string);
        if ($signature !== $sig) {
            throw new \RuntimeException('Signature ' . $sig . ' does not correspond to ' . $signature);
        }
    }

    private function sessionWrite($string, $result)
    {
        $_SESSION[$string] = $result;
    }
}
