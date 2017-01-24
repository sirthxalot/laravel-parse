<?php namespace Sirthxalot\Parse\Auth;

use Illuminate\Auth\Authenticatable;
use Sirthxalot\Parse\UserModel as BaseUserModel;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * Authentication User Model
 * ==================================================================================
 *
 * An entity that determine an authenticated user, in order to use with your Parse
 * driver. This user model class will extend the authentication behaviour and allows
 * to authenticate Parse users within our Laravel application.
 *
 * @package   Sirthxalot\Parse\Auth
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
class UserModel extends BaseUserModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;


    /**
     * Get the object key (id) name.
     *
     * @return string
     */
    public function getKeyName()
    {
        return 'objectId';
    }


    /**
     * Get the object key (id).
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->id();
    }


    /**
     * Get the token name used for `remember me` token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'rememberToken';
    }
}
