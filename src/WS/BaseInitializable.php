<?php

namespace WS;

/**
 * @author Jayson Fong <contact@jaysonfong.org>
 * @copyright Jayson Fong 2022
 */
abstract class BaseInitializable
{

    protected App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

}