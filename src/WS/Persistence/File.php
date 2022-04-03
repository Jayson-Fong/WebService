<?php

namespace WS\Persistence;

use WS\App;

/**
 * @author Jayson Fong <contact@jaysonfong.org>
 * @copyright Jayson Fong 2022
 */
class File extends Persistence
{

    protected static array $values = [];
    private string $filePath;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->filePath = $this->app->getConfigurationOption('persistence', 'file', 'path');
        $fileContents = file_get_contents($this->filePath);
        if (strlen($fileContents) !== 0)
        {
            self::$values = unserialize($fileContents);
        }
    }

    public function put(string $key, mixed $value, bool $save = true): void
    {
        self::$values[$key] = $value;
        if ($save)
        {
            $this->save();
        }
    }

    public function delete(string $key): void
    {
        if ($this->has($key))
        {
            unset(self::$values[$key]);
            $this->save();
        }
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, self::$values);
    }

    public function clear(): void
    {
        self::$values = [];
        $this->save();
    }

    /**
     * @return array
     */
    public function keys(): array
    {
        return array_keys(self::$values);
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->has($key))
        {
            return self::$values[$key];
        }

        return $default;
    }

    public function save(): void
    {
        file_put_contents($this->filePath, serialize(self::$values));
    }

}