<?php namespace Sirthxalot\Parse\Auth\Providers;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Parse\ParseException;

/**
 * Base Authentication Provider
 * ==================================================================================
 *
 * This class consist of all the additional behaviour used to make a basic
 * authentication for your Parse driver. It manages the workflow of Laravel's
 * authentication service.
 *
 * @package   Sirthxalot\Parse\Auth\Providers
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
abstract class BaseProvider implements UserProvider
{
    /**
     * User Class
     *
     * @var object $userClass
     * An instance of the current user class.
     */
    protected $userClass;


    /**
     * Handles new instances.
     *
     * @param string $userClass
     * A string that determine the name of the user class.
     */
    public function __construct($userClass)
    {
        $this->userClass = $userClass;
    }


    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $class = $this->userClass;

        return $class::query(true)->find($identifier) ?: null;
    }


    /**
     * Retrieve a user by their unique identifier and `remember me` token.
     *
     * @param  string $identifier
     * A string that determines the identifier.
     *
     * @param  string $token
     * A string that determine the token.
     *
     * @return Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $class = $this->userClass;

        return $class::query(true)->where([
            'objectId'      => $identifier,
            'rememberToken' => $token
        ])->first();
    }


    /**
     * Update the `remember me` token for the given user in storage.
     *
     * @param  Authenticatable $user
     * An object that represents the authenticated user.
     *
     * @param  string          $token
     * A string that determine the token.
     *
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->update([ 'rememberToken' => $token ], true);
    }


    /**
     * Retrieve a user from the given credentials.
     *
     * @param array $credentials
     * An array that determine the credentials for the username
     * being searched for.
     *
     * @return mixed
     */
    protected function retrieveByUsername(array $credentials)
    {
        $class = $this->userClass;

        $username = $this->getUsernameFromCredentials($credentials);

        return $class::query(true)->where([ 'username' => $username ])->first();
    }


    /**
     * Get the username from the given credentials.
     *
     * @param array $credentials
     * An array that determine the credentials from where the
     * username will be fetched.
     *
     * @return mixed|null
     */
    protected function getUsernameFromCredentials(array $credentials)
    {
        $username = null;

        if (empty($credentials['username'])):
            if ( ! empty($credentials['email'])):
                $username = $credentials['email'];
            endif;
        else:
            $username = $credentials['username'];
        endif;

        return $username;
    }


    /**
     * Validate the user with the given credentials.
     *
     * @param  Authenticatable $user
     * An object that represents the authenticated user.
     *
     * @param array            $credentials
     * An array that determine the credentials being validated for.
     *
     * @return bool
     */
    protected function validateWithPassword(Authenticatable $user, array $credentials)
    {
        return $this->validatePassword($user, $credentials);
    }


    /**
     * Validate a password against the given credentials.
     *
     * @param  Authenticatable $user
     * An object that represents the authenticated user.
     *
     * @param array            $credentials
     * An array that determine the credentials being validated for.
     *
     * @return bool
     */
    protected function validatePassword(Authenticatable $user, array $credentials)
    {
        $username = $this->getUsernameFromCredentials($credentials);

        try {
            $user->logIn($username, $credentials['password']);
        } catch (ParseException $e) {
            return false;
        }

        return true;
    }
}
