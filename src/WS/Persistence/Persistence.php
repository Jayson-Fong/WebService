<?php

namespace WS\Persistence;

use WS\BaseInitializable;

/**
 * @author Jayson Fong <contact@jaysonfong.org>
 * @copyright Jayson Fong 2022
 */
abstract class Persistence extends BaseInitializable
{

    public abstract function get(string $key, mixed $default = null): mixed;
    public abstract function put(string $key, mixed $value, bool $save = true): void;
    public abstract function delete(string $key): void;
    public abstract function has(string $key): bool;
    public abstract function keys(): array;
    public abstract function clear(): void;
    public abstract function save(): void;

}