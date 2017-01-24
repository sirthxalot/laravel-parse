<?php namespace Sirthxalot\Parse;

use DateTime;
use LogicException;
use ReflectionClass;
use Parse\ParseFile;
use JsonSerializable;
use Parse\ParseObject;
use Illuminate\Support\Arr;
use Illuminate\Support\Pluralizer;
use Sirthxalot\Parse\Relations\HasMany;
use Sirthxalot\Parse\Relations\Relation;
use Sirthxalot\Parse\Relations\BelongsTo;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Sirthxalot\Parse\Relations\HasManyArray;
use Sirthxalot\Parse\Relations\BelongsToMany;

/**
 * Parse Object Model
 * ==================================================================================
 *
 * An entity that represents a `ParseObject` class. This class allows to create
 * new `ParseObject` entities from your Laravel application. You will have the
 * same features as you will have in Parse.
 *
 * @package   Sirthxalot\Parse
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
abstract class ObjectModel implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * Parse Class Name
     *
     * @var string $parseClassName
     * A string that determine the parse class name.
     */
    protected static $parseClassName;


    /**
     * Has Been Fetched Property
     *
     * @var \ReflectionProperty
     */
    protected static $hasBeenFetchedProp;


    /**
     * Default Master Key
     *
     * Defines the default value of `$useMasterKey` throughout all class methods,
     * such as `query`, `create`, `all`, `__construct`, and `__callStatic`.
     *
     * @var mixed $defaultUseMasterKey
     * A mixed that determine the default master key.
     */
    protected static $defaultUseMasterKey = false;


    /**
     * Parse Object Instance
     *
     * @var ParseObject $parseObject
     * An instance of the parse object.
     */
    protected $parseObject;


    /**
     * Object Model Relations
     *
     * @var array $relations
     * An array that determines the relations set for the object model.
     */
    protected $relations = [];


    /**
     * Use master key?
     *
     * @var bool|null $useMasterKey
     * A boolean that determine whether the default master key is in use (false) or
     * not (true).
     */
    protected $useMasterKey;


    /**
     * Handles new instances.
     *
     * @param ParseObject|array $data
     * A mixed value that will be assigned to the object model's data property.
     *
     * @param bool $useMasterKey
     * A boolean that determine if the default master key will be used (false) or
     * not (true).
     */
    public function __construct($data = null, $useMasterKey = null)
    {
        if ($data instanceof ParseObject):
            $this->parseObject = $data;
        else:
            $this->parseObject = new ParseObject(static::parseClassName());

            if (is_array($data)):
                $this->fill($data);
            endif;
        endif;

        $this->useMasterKey = $useMasterKey !== null ? $useMasterKey : static::$defaultUseMasterKey;
    }


    /**
     * Get the parse class name.
     *
     * @return string
     */
    public static function parseClassName()
    {
        return static::$parseClassName ?: static::shortName();
    }


    /**
     * Shorten namespace down to class name.
     *
     * @return string
     */
    public static function shortName()
    {
        return substr(static::class, strrpos(static::class, '\\') + 1);
    }


    /**
     * Fill the object model with data.
     *
     * @param array $data
     * An array that determine the data to fill into the model object.
     *
     * @return $this
     */
    public function fill(array $data)
    {
        foreach ($data as $key => $value):
            $this->set($key, $value);
        endforeach;

        return $this;
    }


    /**
     * Set Parse-ACL.
     *
     * `ParseACL` can be set by passing `acl` as key. This is useful specially in
     * mass assignments, e.g. ACL can be set alongside attributes with `create()`.
     *
     * @param string $key
     * A string that determine the key used for set parse ACL.
     *
     * @param mixed  $value
     * A mixed value that determine the value to set in parse ACL.
     *
     * @return $this
     */
    public function set($key, $value)
    {
        if (is_array($value)):
            if (Arr::isAssoc($value)):
                $this->parseObject->setAssociativeArray($key, $value);
            else:
                $this->parseObject->setArray($key, $value);
            endif;
        elseif ($value instanceof ObjectModel):
            $this->parseObject->set($key, $value->parseObject);
        elseif ($key == 'acl'):
            $this->parseObject->setACL($value);
        else:
            $this->parseObject->set($key, $value);
        endif;

        return $this;
    }


    /**
     * Create a new `ObjectModel`.
     *
     * @param ParseObject|array $data
     * A mixed value that contain the data used for creating
     * a new `ObjectModel`.
     *
     * @param bool|null         $useMasterKey
     * A boolean that determine, whether if a custom master
     * key should be used (`true`) or not (`false`).
     *
     * @return static
     */
    public static function create($data, $useMasterKey = null)
    {
        if ($useMasterKey === null):
            $useMasterKey = static::$defaultUseMasterKey;
        endif;

        $model = new static($data, $useMasterKey);

        $model->save();

        return $model;
    }


    /**
     * Save the `ObjectModel`.
     */
    public function save()
    {
        $this->parseObject->save($this->useMasterKey);
    }


    /**
     * Set the current pointer for `ObjectModel`.
     *
     * @param string $id
     * A string that determine the id to point.
     *
     * @return static
     */
    public static function pointer($id)
    {
        $pointer = new ParseObject(static::parseClassName(), $id, true);

        return new static($pointer);
    }


    /**
     * Fetch all data from `ObjectModel`.
     *
     * @param bool|null $useMasterKey
     * A boolean that determine, whether if a custom master
     * key should be used (`true`) or not (`false`).
     *
     * @return mixed
     */
    public static function all($useMasterKey = null)
    {
        if ($useMasterKey === null):
            $useMasterKey = static::$defaultUseMasterKey;
        endif;

        return static::query($useMasterKey)->get();
    }


    /**
     * Query the `ObjectModel`.
     *
     * @param bool|null $useMasterKey
     * A boolean that determine, whether if a custom master
     * key should be used (`true`) or not (`false`).
     *
     * @return Query
     */
    public static function query($useMasterKey = null)
    {
        if ($useMasterKey === null):
            $useMasterKey = static::$defaultUseMasterKey;
        endif;

        return new Query(static::parseClassName(), static::class, $useMasterKey);
    }


    /**
     * Set default usage for master key.
     *
     * Set the default value for defaultUseMasterKey. This is intended to be used
     * as a global configuration, hence the value is set to "self" and not to
     * "static".
     *
     * @param bool $value
     * A boolean that determine, whether if a custom master
     * key should be used (`true`) or not (`false`).
     */
    public static function setDefaultUseMasterKey($value)
    {
        self::$defaultUseMasterKey = (bool) $value;
    }


    /**
     * Handles static method calls.
     *
     * @param string $method
     * A string that determine the static method name.
     *
     * @param array $params
     * An array that determine any arguments from the static
     * method.
     *
     * @return mixed
     */
    public static function __callStatic($method, array $params)
    {
        $query = static::query(static::$defaultUseMasterKey);

        return call_user_func_array([ $query, $method ], $params);
    }


    /**
     * Get the `ObjectModel` from key.
     *
     * @param string $key
     * A string that determine the key used as reference.
     *
     * @return mixed|null|string
     */
    public function __get($key)
    {
        return $this->get($key);
    }


    /**
     * Set a value to `ObjectModel`.
     *
     * @param string $key
     * A string that determine the key used as reference.
     *
     * @param mixed  $value
     * A mixed value that will be used in order to store in object model.
     *
     * @return ObjectModel
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }


    /**
     * Get the `ObjectModel` ACL.
     *
     * @param string $key
     * A string that determine the key used for the object model ACL.
     *
     * @return mixed|null|string
     */
    public function get($key)
    {
        if ($key == 'id'):
            return $this->id();
        endif;

        if ($this->isRelation($key)):
            return $this->getRelationValue($key);
        endif;

        $value = $this->parseObject->get($key);

        return $value;
    }


    /**
     * Get the object model id.
     *
     * @return null|string
     */
    public function id()
    {
        return $this->parseObject->getObjectId();
    }


    /**
     * Check if object is a relation.
     *
     * @param string $name
     * A string that determine the relation name to check against.
     *
     * @return bool
     */
    public function isRelation($name)
    {
        return method_exists($this, $name);
    }


    /**
     * Get the values from the object model relation.
     *
     * @param string $key
     * A string that determine the key being searched for.
     *
     * @return mixed
     */
    public function getRelationValue($key)
    {
        if ($this->relationLoaded($key)):
            return $this->relations[$key];
        endif;

        if ($this->isRelation($key)):
            return $this->getRelationshipFromMethod($key);
        endif;
    }


    /**
     * Check if relation has been loaded.
     *
     * @param string $key
     * A string that determine the key being searched for.
     *
     * @return bool
     */
    public function relationLoaded($key)
    {
        return array_key_exists($key, $this->relations);
    }


    /**
     * Get the relationship from the method.
     *
     * @param mixed $method
     * A mixed value which determine the method being
     * searched for in the relationship.
     *
     * @return mixed
     */
    protected function getRelationshipFromMethod($method)
    {
        $relations = $this->$method();

        if ( ! $relations instanceof Relation):
            throw new LogicException('Relationship method must return an object of type '.'Sirthxalot\Parse\Relations\Relation');
        endif;

        $results = $relations->getResults();

        $this->setRelation($method, $results);

        return $results;
    }


    /**
     * Set a relationship for `ObjectModel`.
     *
     * @param string $relation
     * A string that determine the name used for the relationship.
     *
     * @param mixed  $value
     * A mixed value that determine the value used to store in relationship.
     *
     * @return $this
     */
    public function setRelation($relation, $value)
    {
        $this->relations[$relation] = $value;

        return $this;
    }


    /**
     * Check if object exists.
     *
     * @param string $key
     * A string that determine the key used for the model object.
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->parseObject->has($key);
    }


    /**
     * Handles static method calls.
     *
     * @param string $method
     * A string that determine the static method name.
     *
     * @param array $params
     * An array that determine any arguments from the static
     * method.
     *
     * @return mixed
     */
    public function __call($method, array $params)
    {
        $return = call_user_func_array([ $this->parseObject, $method ], $params);

        if ($return === $this->parseObject):
            return $this;
        endif;

        return $return;
    }


    /**
     * Clones an instance.
     *
     * When an object is cloned, PHP will perform a shallow
     * copy of all of the object's properties. Any properties
     * that are references to other variables will remain
     * references.
     */
    public function __clone()
    {
        $this->parseObject = clone $this->parseObject;
    }


    /**
     * Use the parse master key.
     *
     * @param string $value
     * A string that determine the parse master key.
     *
     * @return $this
     */
    public function useMasterKey($value)
    {
        $this->useMasterKey = (bool) $value;

        return $this;
    }


    /**
     * This will delete the object from the database. To delete a key,
     * use `removeKey()`.
     *
     * @return void
     */
    public function delete()
    {
        $this->parseObject->destroy($this->useMasterKey);
    }


    /**
     * Remove an object model key.
     *
     * @param string $key
     * A string that determine the key used for deleting.
     *
     * @return $this
     */
    public function removeKey($key)
    {
        $this->parseObject->delete($key);

        return $this;
    }


    /**
     * Update the model object.
     *
     * @param array $data
     * An array that determines the data used for updating.
     */
    public function update(array $data)
    {
        $this->fill($data)->save();
    }


    /**
     * Decrement the object key.
     *
     * @param  string  $key
     * A string that determine the key of the object for decrement.
     *
     * @param  integer $amount
     * An integer that determine the amount to decrease.
     *
     * @return $this
     */
    public function decrement($key, $amount = 1)
    {
        return $this->increment($key, $amount * -1);
    }


    /**
     * Increment the object key
     *
     * @param  string  $key
     * A string that determine the key to increment.
     *
     * @param  integer $amount
     * An integer that determine the amount to increment.
     *
     * @return $this
     */
    public function increment($key, $amount = 1)
    {
        $this->parseObject->increment($key, $amount);

        return $this;
    }


    /**
     * Add a value to `ObjectModel`.
     *
     * `ParseObject::add()`'s second parameter must be an array.
     * This allows to pass non-array values.
     *
     * @param string $key
     * A string that determine the key used for the object.
     *
     * @param mixed  $value
     * A mixed value to add to the object.
     *
     * @return $this
     */
    public function add($key, $value)
    {
        if ( ! is_array($value)):
            $value = [ $value ];
        endif;

        $this->parseObject->add($key, $value);

        return $this;
    }


    /**
     * Add a unique value to `ObjectModel`.
     *
     * `ParseObject::addUnique()`'s second parameter must be
     * an array. This allows to pass non-array values.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function addUnique($key, $value)
    {
        if ( ! is_array($value)):
            $value = [ $value ];
        endif;

        $this->parseObject->addUnique($key, $value);

        return $this;
    }


    /**
     * Fetch a `ModelObject`.
     *
     * @param bool $force
     * A boolean that determine to force fetching.
     *
     * @return $this
     */
    public function fetch($force = false)
    {
        if ( ! $this->hasBeenFetched() || $force):
            $this->parseObject->fetch();
        endif;

        return $this;
    }


    /**
     * Check if an object has been fetched.
     *
     * @return mixed
     */
    public function hasBeenFetched()
    {
        if ( ! self::$hasBeenFetchedProp):
            self::$hasBeenFetchedProp = (new ReflectionClass(ParseObject::class))->getProperty('hasBeenFetched');
            self::$hasBeenFetchedProp->setAccessible(true);
        endif;

        return self::$hasBeenFetchedProp->getValue($this->parseObject);
    }


    /**
     * Translate values into json.
     *
     * @param int $options
     * An integer that determine the options.
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }


    /**
     * Serialize the json.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }


    /**
     * Translate values into an array.
     *
     * @return array
     */
    public function toArray()
    {
        $array = $this->parseObjectToArray($this->parseObject);

        $relations = array_diff_key($this->relations, $array);

        if ($relations):
            foreach ($this->relations as $name => $relation):
                if ($relation instanceof Collection):
                    $coll = [];

                    foreach ($relation as $object):
                        $coll[] = $object->toArray();
                    endforeach;

                    $array[$name] = $coll;
                else:
                    $array[$name] = $relation->toArray();
                endif;
            endforeach;
        endif;

        return $array;
    }


    /**
     * Translate any `ParseObject` to an array.
     *
     * @param ParseObject $object
     * A `ParseObject` that will be used to convert into an array.
     *
     * @return array
     */
    public function parseObjectToArray(ParseObject $object)
    {
        $array = $object->getAllKeys();
        $array['objectId'] = $object->getObjectId();

        $createdAt = $object->getCreatedAt();
        if ($createdAt):
            $array['createdAt'] = $this->dateToString($createdAt);
        endif;

        $updatedAt = $object->getUpdatedAt();
        if ($updatedAt):
            $array['updatedAt'] = $this->dateToString($updatedAt);
        endif;

        if ($object->getACL()):
            $array['ACL'] = $object->getACL()->_encode();
        endif;

        foreach ($array as $key => $value):
            if ($value instanceof ParseObject):
                if ($value->getClassName() == $this->parseObject->getClassName() &&
                    $value->getObjectId() == $this->parseObject->getObjectId()):
                    // If a key points to this parent object, we will skip it to avoid
                    // infinite recursion.
                elseif ($value->isDataAvailable()):
                    $array[$key] = $this->parseObjectToArray($value);
                endif;
            elseif ($value instanceof ParseFile):
                $array[$key] = $value->_encode();
            endif;
        endforeach;

        return $array;
    }


    /**
     * Formats a `DateTime` object the way it is returned from Parse Server.
     *
     * @param DateTime $date
     * An `DateTime` object that determine the date to
     * being formatted.
     *
     * @return string
     */
    protected function dateToString(DateTime $date)
    {
        return $date->format('Y-m-d\TH:i:s.'.substr($date->format('u'), 0, 3).'\Z');
    }


    /**
     * Get the `ParseObject` for the `ObjectModel`.
     *
     * @return ParseObject
     */
    public function getParseObject()
    {
        return $this->parseObject;
    }


    /**
     * Get the `ObjectModel` relationship from key.
     *
     * @param string $key
     * A string that determine the key used for the relationship.
     *
     * @return static
     */
    protected function getRelation($key)
    {
        return $this->relations[$key];
    }


    /**
     * Check if object has any relationships.
     *
     * @param string $key
     * A string that determine the key used for searching the relation.
     *
     * @return bool
     */
    protected function hasRelation($key)
    {
        return isset($this->relations[$key]);
    }


    /**
     * Set belongs to many relationship.
     *
     * This object will have an array with references to many other objects.
     *
     * @param  string $otherClass The other object's class
     * @param  string $key        The key under which the array will be stored
     *
     * @return BelongsToMany
     */
    protected function belongsToMany($otherClass, $key = null)
    {
        if ( ! $key):
            $key = $this->getCallerFunctionName();
        endif;

        return new BelongsToMany($otherClass, $key, $this);
    }


    /**
     * Get the function name of the caller.
     *
     * @return mixed
     */
    protected function getCallerFunctionName()
    {
        return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];
    }


    /**
     * Set belongs to relationship.
     *
     * @param mixed       $otherClass
     * A mixed value that the model belongs to.
     *
     * @param string|null $key
     * A string that determine the key for the relation.
     *
     * @return BelongsTo
     */
    protected function belongsTo($otherClass, $key = null)
    {
        if ( ! $key):
            $key = $this->getCallerFunctionName();
        endif;

        return new BelongsTo($otherClass, $key, $this);
    }


    /**
     * Set the has many relationship.
     *
     * @param mixed       $otherClass
     * A mixed value that the model relates to.
     *
     * @param string|null $key
     * A string key that determine the key for the model.
     *
     * @return HasMany
     */
    protected function hasMany($otherClass, $key = null)
    {
        if ( ! $key):
            $key = lcfirst(static::parseClassName());
        endif;

        return new HasMany($otherClass::query(), $this, $key);
    }


    /**
     * Set the has many array relationship.
     *
     * This is the reverse relation of belongsToMany. Children are expected to
     * store the parents' keys in an array. By default, the $foreignKey is
     * expected to be the plural of the parent object's name.
     *
     * @param  string $otherClass
     * @param  string $foreignKey
     *
     * @return HasManyArray
     */
    protected function hasManyArray($otherClass, $foreignKey = null)
    {
        if ( ! $foreignKey):
            $foreignKey = Pluralizer::plural(lcfirst(static::parseClassName()));
        endif;

        return new HasManyArray($otherClass::query(), $this, $foreignKey);
    }
}
