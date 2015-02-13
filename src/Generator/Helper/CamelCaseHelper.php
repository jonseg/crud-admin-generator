<?php
/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Crud\Generator\Helper;

/**
 * Class CamelCaseHelper
 */
abstract class CamelCaseHelper
{
    /**
     * @param string  $string
     * @param boolean $first_char_caps
     *
     * @return string
     */
    public static function encode($string, $first_char_caps = false)
    {
        $camelCase = preg_replace_callback('/_([a-z])/', function ($c) { return ucfirst($c[1]); }, $string);

        if ($first_char_caps) {
            $camelCase = ucfirst($camelCase);
        }

        return $camelCase;
    }

    /**
     * @param string $string
     * @param string $splitter
     *
     * @return string
     */
    public static function decode($string, $splitter = "_")
    {
        return strtolower(preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0', preg_replace('/(?!^)[[:upper:]]+/', $splitter.'$0', $string)));
    }
}
