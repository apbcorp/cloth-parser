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
        $html = file_get_contents($link);

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
}