<?php namespace Sirthxalot\Parse\Relations;

use Sirthxalot\Parse\ObjectModel;
use Illuminate\Support\Collection;

/**
 * Belongs To Many Relationship
 * ==================================================================================
 *
 * An entity for `belongs-to-many` relationships between your Laravel applications and
 * your Parse driver. It allows to use all Parse relationship features on your Laravel
 * application and reversed.
 *
 * @package   Sirthxalot\Parse\Relations
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
class BelongsToMany extends Relation
{
    /**
     * Embedded Class
     *
     * @var mixed $embeddedClass
     * A mixed value that determine the embedded class.
     */
    protected $embeddedClass;


    /**
     * Parent Object
     *
     * @var ObjectModel $parentObject
     * An instance of the parse object model.
     */
    protected $parentObject;


    /**
     * Key Name
     *
     * @var string $keyName
     * A string that determine the key name used for the relation.
     */
    protected $keyName;


    /**
     * Collection
     *
     * @var Collection $collection
     * An instance of a collection.
     */
    protected $collection;


    /**
     * Children in Queue
     *
     * @var array $childrenQueue
     * An array that consist of the child classes in queue.
     */
    protected $childrenQueue = [];


    /**
     * Handles new instances.
     *
     * @param mixed $embeddedClass
     * A mixed value that determine an object used for assignment.
     *
     * @param string   $keyName
     * A string that determine the key used for the object.
     *
     * @param ObjectModel $parentObject
     * An `ObjectModel` instance that will be used for assignment.
     */
    public function __construct($embeddedClass, $keyName, ObjectModel $parentObject)
    {
        $this->embeddedClass = $embeddedClass;
        $this->parentObject = $parentObject;
        $this->keyName = $keyName;
        $this->collection = new Collection();

        $this->createItems();
    }


    /**
     * Create the items from embedded classes.
     */
    protected function createItems()
    {
        $items = $this->parentObject->getParseObject()->get($this->keyName);

        if ($items):
            $class = $this->embeddedClass;

            foreach ($items as $item):
                $this->collection[] = new $class($item);
            endforeach;
        endif;
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
        $return = call_user_func_array([ $this->collection, $method ], $params);

        if ($return === $this->collection):
            return $this;
        endif;

        return $return;
    }


    /**
     * Get the relationship results.
     *
     * @return Collection
     */
    public function getResults()
    {
        return $this->collection;
    }


    /**
     * Save one or more parents to this relation.
     *
     * ```
     * $post->comments()->save($comment);
     * ```
     *
     * This object is saved automatically.
     *
     * The children will be added with `addUnique` or `add`
     * depending on the `$unique` parameter.
     *
     * @param ObjectModel|ObjectModel[] $others
     * An instance or collection of the `ObjectModel`.
     *
     * @param bool                      $unique
     * A boolean that determine whether if it must be unique
     * (`true`) or not (`false`).
     */
    public function save($others, $unique = true)
    {
        if ( ! is_array($others)):
            $this->addOne($others, $unique);
        else:
            foreach ($others as $other):
                $this->addOne($other, $unique);
            endforeach;
        endif;

        $this->parentObject->save();
    }


    /**
     * Add an item to the relation.
     *
     * @param ObjectModel|ObjectModel[] $other
     * An instance or collection of the `ObjectModel`.
     *
     * @param bool                      $unique
     * A boolean that determine whether if it must be unique
     * (`true`) or not (`false`).
     */
    protected function addOne(ObjectModel $other, $unique = true)
    {
        $parentParse = $this->parentObject->getParseObject();

        $count = count($parentParse->{$this->keyName});

        if ($unique):
            $this->parentObject->addUnique($this->keyName,
                [ $other->getParseObject() ]);
        else:
            $this->parentObject->add($this->keyName, [ $other->getParseObject() ]);
        endif;

        if ($count < count($parentParse->{$this->keyName})):
            $this->childrenQueue[] = $other;

            $this->collection[] = $other;
        endif;
    }
}
