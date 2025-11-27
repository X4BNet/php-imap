<?php
/*
* File:     Str.php
* Category: Support
* Author:   M. Goldenbaum
* Created:  16.03.18 03:13
* Updated:  -
*
* Description:
*  String helper class replacing Illuminate\Support\Str
*/

namespace Webklex\PHPIMAP\Support;

/**
 * Class Str
 *
 * @package Webklex\PHPIMAP\Support
 */
class Str {

    /**
     * Convert a string to snake_case.
     *
     * @param string $value
     * @param string $delimiter
     * @return string
     */
    public static function snake(string $value, string $delimiter = '_'): string {
        if (ctype_lower($value)) {
            return $value;
        }

        $value = preg_replace('/\s+/u', '', ucwords($value));
        $value = preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value);

        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Convert a string to camelCase.
     *
     * @param string $value
     * @return string
     */
    public static function camel(string $value): string {
        return lcfirst(static::studly($value));
    }

    /**
     * Convert a string to StudlyCase.
     *
     * @param string $value
     * @return string
     */
    public static function studly(string $value): string {
        $words = explode(' ', str_replace(['-', '_'], ' ', $value));

        $studlyWords = array_map(function ($word) {
            return ucfirst($word);
        }, $words);

        return implode('', $studlyWords);
    }
}
