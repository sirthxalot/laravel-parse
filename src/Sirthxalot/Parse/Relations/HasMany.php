<?php namespace Sirthxalot\Parse\Relations;

use Sirthxalot\Parse\Query;
use Sirthxalot\Parse\ObjectModel;

/**
 * Has Many Relationship
 * ==================================================================================
 *
 * An entity for `has-many` relationships between your Laravel applications and your
 * Parse driver. It allows to use all Parse relationship features on your Laravel
 * application and reversed.
 *
 * @package   Sirthxalot\Parse\Relations
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
class HasMany extends RelationWithQuery
{
    /**
     * Foreign Key
     *
     * @var string $foreignKey
     * A string that determine the foreign key for the relation.
     */
    protected $foreignKey;


    /**
     * Handles new instances.
     *
     * @param Query       $query
     * An instance of the `QueryObject` that will be used for assignment.
     *
     * @param ObjectModel $parentObject
     * An instance of the `ObjectModel` that determine the parent of the query.
     *
     * @param string      $foreignKey
     * A string that determine the foreign key used for the relationship.
     */
    public function __construct(Query $query, ObjectModel $parentObject, $foreignKey)
    {
        $this->foreignKey = $foreignKey;

        parent::__construct($query, $parentObject);
    }


    /**
     * Add constraints for the has many relationship.
     */
    public function addConstraints()
    {
        $this->query->where($this->foreignKey, $this->parentObject);
    }


    /**
     * Get the results for the has many relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->query->get();
    }


    /**
     * Create a new child object, and relate it to this.
     *
     * @param  array $data
     * An array that determine the data used for creation.
     *
     * @return ObjectModel
     */
    public function create(array $data)
    {
        $class = $this->query->getFullClassName();

        $model = new $class($data);

        return $this->save($model);
    }


    /**
     * Relate other object to this object.
     *
     * @param  ObjectModel $model
     * An `ObjectModel` instance that determine the child class.
     *
     * @return ObjectModel
     */
    public function save(ObjectModel $model)
    {
        $model->{$this->foreignKey} = $this->parentObject;

        $model->save();

        return $model;
    }
}
