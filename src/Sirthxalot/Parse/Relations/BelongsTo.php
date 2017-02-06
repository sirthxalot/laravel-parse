<?php namespace Sirthxalot\Parse\Relations;

use Sirthxalot\Parse\ObjectModel;

/**
 * Belongs To Relationship
 * ==================================================================================
 *
 * An entity for `belongs-to` relationships between your Laravel applications and
 * your Parse driver. It allows to use all Parse relationship features on your Laravel
 * application and reversed.
 *
 * @package   Sirthxalot\Parse\Relations
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
class BelongsTo extends Relation
{
    /**
     * Embedded Class
     *
     * @var mixed $embeddedClass
     * An instance of the embedded class.
     */
    protected $embeddedClass;

    /**
     * Key Name
     *
     * @var string $keyName
     * A string that determine the key name used for the relationship.
     */
    protected $keyName;

    /**
     * Child Object
     *
     * @var ObjectModel $childObject
     * An `ObjectModel` instance.
     */
    protected $childObject;

    /**
     * Handles new instances.
     *
     * @param mixed       $embeddedClass
     * A mixed value that determine the class to embed.
     *
     * @param string      $keyName
     * A string key that determine the key used for the object.
     *
     * @param ObjectModel $childObject
     * An instance of the `ObjectModel` that will be used for the `$childObject`.
     */
    public function __construct($embeddedClass, $keyName, ObjectModel $childObject)
    {
        $this->embeddedClass = $embeddedClass;
        $this->childObject = $childObject;
        $this->keyName = $keyName;
    }

    /**
     * Get the relationship results.
     *
     * @return mixed
     */
    public function getResults()
    {
        $class = $this->embeddedClass;

        return (new $class($this->childObject->getParseObject()->get($this->keyName)))->fetch();
    }
}
