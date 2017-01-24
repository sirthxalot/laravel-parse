<?php namespace Sirthxalot\Parse;

use Closure;
use Traversable;
use Parse\ParseQuery;
use Parse\ParseObject;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Query Class
 * ==================================================================================
 *
 * A class that translates Parse queries into Laravel friendly (Eloquent) queries and
 * reversed. It contain many helpful methods to translate queries and couple your Laravel
 * application together with your Parse driver.
 *
 * @package   Sirthxalot\Parse
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
class Query
{
    /**
     * Query Operators
     *
     * @var array OPERATORS
     * An array that determine the relation for operator
     * translation.
     */
    const OPERATORS = [
        '='  => 'equalTo',
        '!=' => 'notEqualTo',
        '>'  => 'greaterThan',
        '>=' => 'greaterThanOrEqualTo',
        '<'  => 'lessThan',
        '<=' => 'lessThanOrEqualTo',
        'in' => 'containedIn',
    ];


    /**
     * Included Keys
     *
     * @var array $includeKeys
     * An array that determine the included keys.
     */
    protected $includeKeys = [];


    /**
     * Parse Query
     *
     * @var ParseQuery $parseQuery
     * A parse query that determine the current query.
     */
    protected $parseQuery;


    /**
     * Full Class Name
     *
     * @var string $fullClassName
     * A string that determine the full class name of the current query.
     */
    protected $fullClassName;


    /**
     * Parse Class Name
     *
     * @var string $parseClassName
     * A string that determine the class name used in parse.
     */
    protected $parseClassName;


    /**
     * Custom Master Key Usage
     *
     * @param bool|null $useMasterKey
     * A boolean that determine, whether if a custom master
     * key should be used (`true`) or not (`false`).
     */
    protected $useMasterKey;


    /**
     * Handles new instances.
     *
     * @param string $parseClassName
     * A string that determine the class name used in parse.
     *
     * @param string $fullClassName
     * A string that determine the full class name (including
     * namespace).
     *
     * @param bool $useMasterKey
     * A boolean that determine, whether if a custom master
     * key should be used (`true`) or not (`false`).
     */
    public function __construct($parseClassName, $fullClassName, $useMasterKey = false)
    {
        $this->parseClassName = $parseClassName;
        $this->parseQuery = new ParseQuery($parseClassName);
        $this->fullClassName = $fullClassName;
        $this->useMasterKey = $useMasterKey;
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
     * @return $this
     */
    public function __call($method, array $params)
    {
        $return = call_user_func_array([ $this->parseQuery, $method ], $params);

        if ($return === $this->parseQuery):
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
        $this->parseQuery = clone $this->parseQuery;
    }


    /**
     * Assign custom master key.
     *
     * @param string $value
     * A string that determine the custom master key.
     *
     * @return $this
     */
    public function useMasterKey($value)
    {
        $this->useMasterKey = $value;

        return $this;
    }


    /**
     * Get the full class name of current query.
     *
     * @return string
     */
    public function getFullClassName()
    {
        return $this->fullClassName;
    }


    /**
     * Conditional query (or query).
     *
     * Pass `Query`, `ParseQuery` or `Closure`, as params or
     * in an array. If `Closure` is passed, a new `Query` will
     * be passed as parameter.
     *
     * First element must be an instance of `Query`.
     *
     * @return static
     */
    public function orQuery()
    {
        $queries = func_get_args();

        if (is_array($queries[0])):
            $queries = $queries[0];
        endif;

        array_unshift($queries, $this);

        return static::orQueries($queries);
    }


    /**
     * Conditional queries (or queries).
     *
     * Pass `Query`, `ParseQuery` or `Closure`, as params or
     * in an array. If `Closure` is passed, a new `Query` will
     * be passed as parameter.
     *
     * First element must be an instance of `Query`.
     *
     * ```php
     * Query::orQueries($query, $parseQuery);
     * Query::orQueries([$query, $parseQuery]);
     * Query::orQueries($query, function(Query $query) { $query->where(...); });
     * ```
     *
     * @return static
     */
    public static function orQueries()
    {
        $queries = func_get_args();

        if (is_array($queries[0])):
            $queries = $queries[0];
        endif;

        $q = $queries[0];
        $parseQueries = [];

        foreach ($queries as $query):
            if ($query instanceof Closure):
                $closure = $query;

                $query = new static($q->parseClassName, $q->fullClassName,
                    $q->useMasterKey);

                $closure($query);

                $parseQueries[] = $query;
            else:
                $parseQueries[] = $q->parseQueryFromQuery($query);
            endif;
        endforeach;

        $orQuery = new static($queries[0]->parseClassName,
            $queries[0]->fullClassName, $queries[0]->useMasterKey);

        $orQuery->parseQuery = ParseQuery::orQueries($parseQueries);

        return $orQuery;
    }


    /**
     * A basic `whereIn` clause for the query.
     *
     * @param  string $key
     * A string that determine the key used for the instance.
     *
     * @param  mixed  $values
     * A mixed value that determines the values being searched for.
     *
     * @return $this
     */
    public function whereIn($key, $values)
    {
        return $this->containedIn($key, $values);
    }


    /**
     * A basic `containedIn` clause for the query.
     *
     * `ObjectModel`s are replaced for their `ParseObject`s.
     * It also accepts any kind of traversable variable.
     *
     * @param  string $key
     * A string that determine the key used for the instance.
     *
     * @param  mixed  $values
     * A mixed value that determines the values being searched for.
     *
     * @return $this
     */
    public function containedIn($key, $values)
    {
        if ( ! is_array($values) && ! $values instanceof Traversable):
            $values = [ $values ];
        endif;

        foreach ($values as $k => $value):
            if ($value instanceof ObjectModel):
                $values[$k] = $value->getParseObject();
            endif;
        endforeach;

        $this->parseQuery->containedIn($key, $values);

        return $this;
    }


    /**
     * A basic `whereNotFound` clause to the query.
     *
     * @param  string $key
     * A string that determine the key used for the instance.
     *
     * @return $this
     */
    public function whereNotExists($key)
    {
        $this->parseQuery->doesNotExist($key);

        return $this;
    }


    /**
     * Find a record by its object id or throw an exception.
     *
     * @param string      $objectId
     * A string that determine the object id being searched for.
     *
     * @param mixed|null  $selectKeys
     * A mixed values that determine the keys to select.
     *
     * @throws ModelNotFoundException
     * @return ObjectModel
     */
    public function findOrFail($objectId, $selectKeys = null)
    {
        $this->parseQuery->equalTo('objectId', $objectId);

        return $this->firstOrFail($selectKeys);
    }


    /**
     * Get the first record that matches the query or throw
     * an exception.
     *
     * @param mixed|null $selectKeys
     * A mixed values that determine the keys to select.
     *
     * @throws ModelNotFoundException
     * @return ObjectModel
     */
    public function firstOrFail($selectKeys = null)
    {
        $first = $this->first($selectKeys);

        if ( ! $first) {
            $e = new ModelNotFoundException();

            $e->setModel($this->fullClassName);

            throw $e;
        }

        return $first;
    }


    /**
     * Get the first record that matches the query.
     *
     * @param mixed|null $selectKeys
     * A mixed values that determine the keys to select.
     *
     * @return ObjectModel|bool
     */
    public function first($selectKeys = null)
    {
        if ($selectKeys):
            $this->parseQuery->select($selectKeys);
        endif;

        $data = $this->parseQuery->first($this->useMasterKey);

        if ($data):
            return $this->createModel($data);
        else:
            return false;
        endif;
    }


    /**
     * Create model from parse object.
     *
     * @param ParseObject $data
     * A parse object that will be used for modeling.
     *
     * @return mixed
     */
    protected function createModel(ParseObject $data)
    {
        $className = $this->fullClassName;

        return new $className($data, $this->useMasterKey);
    }


    /**
     * Find a record by its object id or return a new instance.
     *
     * @param string      $objectId
     * A string that determine the object id being searched for.
     *
     * @param mixed|null  $selectKeys
     * A mixed values that determine the keys to select.
     *
     * @return ObjectModel
     */
    public function findOrNew($objectId, $selectKeys = null)
    {
        $record = $this->find($objectId, $selectKeys);

        if ( ! $record):
            $class = $this->fullClassName;

            $record = new $class(null, $this->useMasterKey);
        endif;

        return $record;
    }


    /**
     * Find a record by its object id.
     *
     * @param string      $objectId
     * A string that determine the object id being searched for.
     *
     * @param mixed|null  $selectKeys
     * A mixed values that determine the keys to select.
     *
     * @return ObjectModel|null
     */
    public function find($objectId, $selectKeys = null)
    {
        $this->parseQuery->equalTo('objectId', $objectId);

        return $this->first($selectKeys);
    }


    /**
     * Get the first record that matches the query or create
     * it otherwise.
     *
     * @param array $data
     * An array that determine the data to find or create.
     *
     * @return ObjectModel
     */
    public function firstOrCreate(array $data)
    {
        $record = $this->firstOrNew($data);

        if ( ! $record->id):
            $record->save();
        endif;

        return $record;
    }


    /**
     * Get the first record that matches the query
     * or return a new instance otherwise.
     *
     * @param array $data
     * An array that determine the data to find or instantiate.
     *
     * @return ObjectModel
     */
    public function firstOrNew(array $data)
    {
        $record = $this->where($data)->first();

        if ($record):
            return $record;
        endif;

        $class = $this->fullClassName;

        return new $class($data, $this->useMasterKey);
    }


    /**
     * A basic `where` clause to the query.
     *
     * ```php
     * $query->where($key, '=', $value);
     * $query->where([$key => $value]);
     * $query->where($key, $value);
     * ```
     *
     * @param string      $key
     * A string that determine the value being searched for.
     *
     * @param string|null $operator
     * A string that determine the operator used for the query.
     *
     * @param mixed|null $value
     * A mixed value that determine the value being searched on.
     *
     * @throws Exception
     * @return $this
     */
    public function where($key, $operator = null, $value = null)
    {
        if (is_array($key)):
            $where = $key;
            foreach ($where as $key => $value):
                if ($value instanceof ObjectModel):
                    $value = $value->getParseObject();
                endif;

                $this->parseQuery->equalTo($key, $value);
            endforeach;
        elseif (func_num_args() == 2):
            if ($operator instanceof ObjectModel):
                $operator = $operator->getParseObject();
            endif;

            $this->parseQuery->equalTo($key, $operator);
        else:
            if ( ! array_key_exists($operator, self::OPERATORS)):
                throw new Exception("Invalid operator: ".$operator);
            endif;

            call_user_func([ $this, self::OPERATORS[$operator] ], $key, $value);
        endif;

        return $this;
    }


    /**
     * Executes the query and returns its results.
     *
     * @param mixed|null $selectKeys
     * A mixed values that determine the keys to select.
     *
     * @return Collection
     */
    public function get($selectKeys = null)
    {
        if ($selectKeys):
            $this->select($selectKeys);
        endif;

        return $this->createModels($this->parseQuery->find($this->useMasterKey));
    }


    /**
     * Create multiple models.
     *
     * @param array|ParseObject $objects
     * An array consist of `ParseObject` for modeling.
     *
     * @return Collection
     */
    protected function createModels(array $objects)
    {
        $models = [];

        foreach ($objects as $object):
            $models[] = $this->createModel($object);
        endforeach;

        return new Collection($models);
    }


    /**
     * A basic `matchesQuery` clause for the query.
     *
     * @param string           $key
     * A string that determine the key used for the instance.
     *
     * @param Query|ParseQuery $query
     * An allowed query object that will be checked for.
     *
     * @return $this
     */
    public function matchesQuery($key, $query)
    {
        $this->parseQuery->matchesQuery($key, $this->parseQueryFromQuery($query));

        return $this;
    }


    /**
     * Parse query from `Query`.
     *
     * @param  Query|ParseQuery $query
     * A mixed object that determine the query to parse.
     *
     * @return ParseQuery
     */
    protected function parseQueryFromQuery($query)
    {
        return $query instanceof self ? $query->parseQuery : $query;
    }


    /**
     * A basic `doesNotMatchQuery` clause for the query.
     *
     * @param string           $key
     * A string that determine the key used for the instance.
     *
     * @param Query|ParseQuery $query
     * An allowed query object that will be checked for.
     *
     * @return $this
     */
    public function doesNotMatchQuery($key, $query)
    {
        $this->parseQuery->doesNotMatchQuery(
            $key, $this->parseQueryFromQuery($query)
        );

        return $this;
    }


    /**
     * A basic `matchesKeyInQuery` clause for the query.
     *
     * @param string           $key
     * A string that determine the key used for the instance.
     *
     * @param string           $queryKey
     * A string that determine the key used for the query.
     *
     * @param Query|ParseQuery $query
     * An allowed query object that will be checked for.
     *
     * @return $this
     */
    public function matchesKeyInQuery($key, $queryKey, $query)
    {
        $this->parseQuery->matchesKeyInQuery(
            $key, $queryKey, $this->parseQueryFromQuery($query)
        );

        return $this;
    }


    /**
     * A basic `doesNotMatchKeyInQuery` clause for the query.
     *
     * @param string           $key
     * A string that determine the key used for the instance.
     *
     * @param string           $queryKey
     * A string that determine the key used for the query.
     *
     * @param Query|ParseQuery $query
     * An allowed query object that will be checked for.
     *
     * @return $this
     */
    public function doesNotMatchKeyInQuery($key, $queryKey, $query)
    {
        $this->parseQuery->doesNotMatchKeyInQuery(
            $key, $queryKey, $this->parseQueryFromQuery($query)
        );

        return $this;
    }


    /**
     * A basic `orderBy` clause for the query.
     *
     * @param string $key
     * A string that determine the key used for the instance.
     *
     * @param bool $order
     * An boolean that determine whether if the ordering is
     * ascending (true) or descending (false).
     *
     * @return $this
     */
    public function orderBy($key, $order = true)
    {
        if ($order == true):
            $this->ascending($key);
        else:
            $this->descending($key);
        endif;

        return $this;
    }


    /**
     * Add a basic `count` clause for the query.
     *
     * @return int
     */
    public function count()
    {
        return $this->parseQuery->count($this->useMasterKey);
    }


    /**
     * Alias for `ParseQuery`'s includeKey.
     *
     * @param string|array $keys
     * A mixed value consist of the key/s.
     *
     * @return $this
     */
    public function with($keys)
    {
        if (is_string($keys)):
            $keys = func_get_args();
        endif;

        $this->includeKeys = array_merge($this->includeKeys, $keys);

        $this->parseQuery->includeKey($keys);

        return $this;
    }


    /**
     * Get the parse query.
     *
     * @return ParseQuery
     */
    public function getParseQuery()
    {
        return $this->parseQuery;
    }
}
