<?php

namespace WS\Util;

/**
 * @author Jayson Fong <contact@jaysonfong.org>
 * @copyright Jayson Fong 2022
 */
class Php
{

    public static function getElementOrDefault(array $array, string $key, mixed $default = null)
    {
        if (array_key_exists($key, $array))
        {
            return $array[$key];
        }

        return $default;
    }

}