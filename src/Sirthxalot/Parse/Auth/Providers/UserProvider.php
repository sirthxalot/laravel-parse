<?php namespace Sirthxalot\Parse\Auth\Providers;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Parse User Authentication Provider
 * ==================================================================================
 *
 * @package   Sirthxalot\Parse\Auth\Providers
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @license   MIT-License
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
class UserProvider extends BaseProvider
{
    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        return $this->retrieveByUsername($credentials);
    }


    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array                                      $credentials
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->validateWithPassword($user, $credentials);
    }
}
