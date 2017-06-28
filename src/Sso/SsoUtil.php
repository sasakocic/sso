<?php

namespace Sso;

/**
 * Util for sso logins
 */
class SsoUtil
{
    /**
     * Curl wrapper.
     *
     * @param string $url
     * @param array $post
     * @param array $headers
     *
     * @return array
     * @throws \RuntimeException
     * @static
     */
    public static function doCurl($url, $post = [], $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($post)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $string = curl_exec($ch);
        curl_close($ch);
        if (!$string) {
            throw new \RuntimeException('Curl failed for ' . $url);
        }
        $data = json_decode($string, true);
        if (!is_array($data)) {
            throw new \RuntimeException('json decoding failed for ' . $string);
        }

        return $data;
    }
}
