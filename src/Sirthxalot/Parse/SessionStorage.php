<?php namespace Sirthxalot\Parse;

use Illuminate\Support\Facades\Session;
use Parse\ParseStorageInterface;

/**
 * Session Storage
 * ==================================================================================
 *
 * A session storage class for Parse. This class allows to communicate between your
 * application session storage and that one from Parse. You will have the same interface
 * as you are using any other driver on Laravel.
 *
 * @package   Sirthxalot\Parse
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
class SessionStorage implements ParseStorageInterface
{
    /**
     * Sets a key-value pair in session storage.
     *
     * @param string $key
     * A string that determine the key used for the storage.
     *
     * @param mixed $value
     * A mixed value that determine the value used to store.
     *
     * @return null
     */
    public function set($key, $value)
    {
        Session::set($key, $value);
    }


    /**
     * Remove a key from session storage.
     *
     * @param string $key
     * A string that determine the key used for the storage.
     *
     * @return null
     */
    public function remove($key)
    {
        Session::remove($key);
    }


    /**
     * Gets the value for a key from session storage.
     *
     * @param string $key
     * A string that determine the key used for the storage.
     *
     * @return mixed
     */
    public function get($key)
    {
        return Session::get($key);
    }


    /**
     * Clear all values in session storage.
     *
     * @return null
     */
    public function clear()
    {
        Sessions::clear();
    }


    /**
     * Save the data, if necessary.
     *
     * This would be a no-op when using the `$_SESSION`
     * implementation, but could be used for saving to
     * file or database as an action instead of on every
     * set.
     *
     * @return null
     */
    public function save()
    {
        Session::save();
    }


    /**
     * Get all keys from session storage.
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->getAll());
    }


    /**
     * Get all key-value pairs from session storage.
     *
     * @return array
     */
    public function getAll()
    {
        return Session::all();
    }
}
