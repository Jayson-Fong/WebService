<?php

namespace WS\Mvc;

use ArrayObject;

/**
 * @author Jayson Fong <contact@jaysonfong.org>
 * @copyright Jayson Fong 2022
 */
class Request
{

    /** Request Types */
    const SERVER = 'server';
    const COOKIE = 'cookie';
    const ENV = 'env';
    const FILES = 'files';
    const GET = 'get';
    const POST = 'post';
    const USER = 'user';

    /** Defaults */
    const DEFAULT_STRING = '';
    const DEFAULT_BOOL = false;
    const DEFAULT_INT = 0;
    const DEFAULT_NULL = null;

    protected ArrayObject $requestData;

    protected function __construct(ArrayObject $requestData)
    {
        $this->requestData = $requestData;
    }

    /**
     * @return Request
     */
    public static function initialize(): Request
    {
        $requestData = [
            'server' => $_SERVER,
            'cookie' => $_COOKIE,
            'env' => $_ENV,
            'files' => $_FILES,
            'get' => $_REQUEST,
            'post' => $_POST,
            'user' => array_merge($_GET, $_SERVER)
        ];

        return new self(new ArrayObject($requestData));
    }

    /**
     * @param string $type
     * @param int|string $key
     * @return false|mixed
     */
    public function get(string $type, int|string $key, mixed $default = self::DEFAULT_NULL)
    {
        $typeData = $this->requestData->offsetGet($type);
        if ($typeData && array_key_exists($key, $typeData))
        {
            return $typeData[$key];
        }

        return $default;
    }

    /**
     * @param int|string|array $key
     * @param int|array $filter
     * @param bool $options If passing an array for $key, will be used as $addEmpty
     * @param bool $addEmpty
     * @return bool|array|null
     */
    public function getFiltered(int|string|array $key, int|array $filter, bool|int|array $options = true, bool $addEmpty = true): bool|array|null
    {
        if (is_array($key))
        {
            $typeData = array_intersect_key(
                array_flip($key),
                $this->requestData->offsetGet(self::USER)
            );
            return filter_var_array($typeData, $filter, $options);
        }

        $value = $this->get(self::USER, $key) ?: false;
        return filter_var($value, $filter, $options);
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        $queryString = $this->get(self::USER, 'QUERY_STRING', self::DEFAULT_STRING);

        $routePieces = array();
        $success = preg_match('/^([A-Za-z0-9-\/]+)(&.*|)$/', $queryString, $routePieces);

        if ($success)
        {
            return new Route($routePieces[0b1]);
        }

        return new Route();
    }

}