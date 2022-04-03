<?php

namespace WS\Mvc;

/**
 * @author Jayson Fong <contact@jaysonfong.org>
 * @copyright Jayson Fong 2022
 */
class Route
{

    protected string $routePath;

    public function __construct(string $routePath = '')
    {
        $this->routePath = $routePath;
    }

    public function getRoutePath(): string
    {
        return $this->routePath;
    }

    public function getCleanRoutePath(): string
    {
        return trim(str_replace('-', '', ucwords($this->routePath, '-')), '/');
    }

}