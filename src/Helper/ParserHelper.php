<?php

namespace App\Helper;


use App\Dictionary\ParamsDictionary;

class ParserHelper
{
    /**
     * @param string $link
     * @param array  $paramsData
     *
     * @return array
     */
    public static function getProductParams(string $link, array $paramsData): array
    {
        $html = self::request($link);

        $result = [];
        foreach ($paramsData as $paramName => $paramInfo) {
            if (!preg_match_all($paramInfo['pattern'], $html, $matches)) {
                continue;
            }

            $result[$paramName] = self::formatParamValue($matches[1], $paramInfo['type']);
        }
        sleep(1);

        return $result;
    }

    /**
     * @param array  $values
     * @param string $type
     *
     * @return string
     */
    public static function formatParamValue(array $values, string $type): string
    {
        switch ($type) {
            case ParamsDictionary::TYPE_STRING:
                return trim($values[0]);
            case ParamsDictionary::TYPE_INT:
                $value = trim($values[0]);
                $value = (int) $value;

                return (string) $value;
            case ParamsDictionary::TYPE_ARRAY:
                $result = [];
                foreach ($values as $value) {
                    $result[] = trim($value);
                }

                return implode(', ', $result);
        }

        return '';
    }

    /**
     * @param string $url
     * @return string
     */
    public static function request(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_PROXYTYPE      => CURLPROXY_SOCKS5_HOSTNAME,
            CURLOPT_PROXY          => '127.0.0.1:9050',
            CURLOPT_HEADER         => 0,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_ENCODING       => '',
            CURLOPT_COOKIEFILE     => '',
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new \Exception('Request error: ' . $url);
        }

        return $response;
    }
}