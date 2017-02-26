<?php
/**
 * Created by PhpStorm.
 * User: tingle
 * Date: 25.02.17
 * Time: 22:03
 */

namespace Core\Helpers;

/**
 * Class CurlHelper
 * @package Helpers
 */
class CurlHelper
{
    /**
     * SSL
     *
     * @var bool
     */
    protected $enabledSSL = true;

    /**
     * Errors array
     *
     * @var array
     */
    protected $errors = [
        301 => 'Moved permanently',
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        429 => 'Too many queries',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable'
    ];

    /**
     * Enable SSL
     */
    public function disableSSL() {
        $this->enabledSSL = false;
    }

    /**
     * Disable SSL
     */
    public function enableSSL() {
        $this->enabledSSL = true;
    }

    /**
     * @param $url
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public function request($url, $options = []) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/auth/cookie.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/auth/cookie.txt');

        if ($this->enabledSSL) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $result = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($code !== 200 && $code !== 204) {
            throw new \Exception(isset($this->errors[$code]) ? $this->errors[$code] : 'Unknown error', $code);
        }

        return $result;
    }
}