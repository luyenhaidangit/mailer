<?php


namespace App\Helpers\Traits;


trait HasMemoization
{
    protected static $memoized = [];

    /**
     * Memoize Operation Result
     * @param $key
     * @param \Closure $callback
     * @param bool $refresh
     * @return mixed
     */
    public function memoize($key, \Closure $callback, $refresh = false)
    {
        if (!isset(static::$memoized[$key]) || $refresh) {
            return static::$memoized[$key] = $callback();
        }
        return static::$memoized[$key];
    }
}
