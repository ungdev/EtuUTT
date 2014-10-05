<?php

namespace Etu\Core\CoreBundle\Framework\Cache;

class Apc
{
    /**
     * Checks if APC key exists.
     *
     * @param mixed $key - A string, or an array of strings, that contain keys.
     *
     * @return mixed - Returns true if the key exists, otherwise false or if an
     *                 array was passed to keys, then an array is returned that
     *                 contains all existing keys, or an empty array if none exist.
     */
    public static function has($key = '')
    {
        return apc_exists($key);
    }

    /**
     * Cache a variable in the data store.
     *
     * @param string $key - Store the variable using this name.
     * @param string $data - The variable to store.
     * @param int $ttl - Time To Live; store var in the cache for ttl seconds.
     * @param bool $overwrite
     *
     * @return boolean - Returns true on success or false on failure.
     */
    public static function store($key, $data, $ttl = 0, $overwrite = false)
    {
        if ($overwrite) {
            return apc_store($key, $data, $ttl);
        } else {
            return apc_add($key, $data, $ttl);
        }
    }

    /**
     * Fetch stored value in APC from key.
     *
     * @param string $key - The key used to store the value.
     *
     * @return boolean - The stored variable or array of variables on success; false on failure.
     */
    public static function fetch($key = '')
    {
        if (self::has($key)) {
            return apc_fetch($key);
        } else {
            return false;
        }
    }

    /**
     * Removes a stored variable from the cache.
     *
     * @param string $key - The key used to store the value (with apc_store()).
     *
     * @return boolean - Returns true on success or false on failure.
     */
    public static function delete($key = '')
    {
        return apc_delete($key);
    }

    /**
     * Clears the APC cache.
     *
     * @param string $type - If $type is "user", the user cache will be cleared; otherwise,
     *                       the system cache (cached files) will be cleared.
     *
     * @return boolean - Returns true on success or false on failure.
     */
    public static function clear($type = '')
    {
        return apc_clear_cache($type);
    }

    /**
     * @return bool
     */
    public static function enabled()
    {
        return function_exists('apc_fetch');
    }
}