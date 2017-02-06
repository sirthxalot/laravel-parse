<?php namespace Sirthxalot\Parse\Relations;

use Sirthxalot\Parse\Query;
use Sirthxalot\Parse\ObjectModel;

/**
 * Relationship with Query
 * ==================================================================================
 *
 * An entity to create custom relationships, by adding additional query, for your Parse
 * driver. It allows to define custom relationships and add some extra sauce - yummi.
 *
 * @package   Sirthxalot\Parse\Relations
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
abstract class RelationWithQuery extends Relation
{
    abstract protected function addConstraints();

    /**
     * Query
     *
     * @var Query $query
     * A query object used in the relationship.
     */
    protected $query;

    /**
     * Parent Object
     *
     * @param ObjectModel $parentObject
     * A object model instance.
     */
    protected $parentObject;

    /**
     * Handles new instances.
     *
     * @param Query       $query
     * An `Query` instance that determine the query used for
     * assignment.
     *
     * @param ObjectModel $parentObject
     * An `ObjectModel` instance that determine the parent
     * object of the relation.
     */
    public function __construct(Query $query, ObjectModel $parentObject)
    {
        $this->query = $query;
        $this->parentObject = $parentObject;

        $this->addConstraints();
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
    public function __call($method, $params)
    {
        $result = call_user_func_array([ $this->query, $method ], $params);

        if ($result === $this->query):
            return $this;
        endif;

        return $result;
    }
}
