<?php namespace Sirthxalot\Parse\Relations;

/**
 * Has Many Array Relationship
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
class HasManyArray extends HasMany
{
    /**
     * Add constraints has many optimized for array.
     */
    public function addConstraints()
    {
        $this->query->containedIn($this->foreignKey, $this->parentObject);
    }
}
