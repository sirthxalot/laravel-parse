<?php namespace Sirthxalot\Parse;

use Parse\ParseUser;

/**
 * User Model
 * ==================================================================================
 *
 * An entity that determine the user model for your Parse driver. It manages all the
 * dynamic user data from your Parse driver and couple them with your applications
 * user entity.
 *
 * @package   Sirthxalot\Parse
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
class UserModel extends ObjectModel
{
    /**
     * Parse Class Name
     *
     * @var string $parseClassName
     * A string that determine the class name used in Parse.
     */
    protected static $parseClassName = '_User';

    /**
     * Static Parse User Methods
     *
     * @var array $parseUserStaticMethods
     * An array that determine all static methods, which will
     * be allowed to return a new `ParseUser` instance.
     */
    protected static $parseUserStaticMethods = [
        'logIn',
        'loginWithAnonymous',
        'become'
    ];

    /**
     * Handles new instances.
     *
     * @param ParseUser|array $data
     * A mixed value of data to proceed.
     *
     * @param boolean|null    $useMasterKey
     * A boolean that determine whether to use the master key
     * (`true`) or not (`false`).
     *
     * @throws Exception
     * An exception will be thrown if wrong arguments has
     * been passed in.
     */
    public function __construct($data = null, $useMasterKey = null)
    {
        if ($data != null && ! $data instanceof ParseUser && ! is_array($data)):
            $type = is_object($data) ? get_class($data) : gettype($data);

            $exceptionMessage = "Whether a `ParseUser` or an array must be passed to instantiate a `UserModel`, %s passed";
            throw new Exception(sprintf($exceptionMessage, $type));
        endif;

        if ($data instanceof ParseUser):
            $this->parseObject = $data;
        else:
            $this->parseObject = new ParseUser(static::parseClassName());

            if ($data):
                $this->fill($data);
            endif;
        endif;

        $this->useMasterKey = $useMasterKey !== null ? $useMasterKey : static::$defaultUseMasterKey;
    }

    /**
     * Handles static method calls.
     *
     * @param string $method
     * A string that determine the static method name.
     *
     * @param array $params
     * An array that determines any arguments passed in
     * to the static method.
     *
     * @return static
     */
    public static function __callStatic($method, array $params)
    {
        if (in_array($method, self::$parseUserStaticMethods)):
            return new static(
                call_user_func_array(ParseUser::class.'::'.$method, $params)
            );
        endif;

        return parent::__callStatic($method, $params);
    }

    /**
     * Create (sign up) a new user.
     *
     * @param \Parse\ParseObject|array $data
     * A mixed value that contain the data used for signing up.
     *
     * @param boolean|null             $useMasterKey
     * A boolean that determine whether to use the master key
     * (`true`) or not (`false`).
     *
     * @return static
     */
    public static function create($data, $useMasterKey = null)
    {
        if ($useMasterKey === null):
            $useMasterKey = static::$defaultUseMasterKey;
        endif;

        $model = new static($data, $useMasterKey);
        $model->signUp();

        return $model;
    }
}
