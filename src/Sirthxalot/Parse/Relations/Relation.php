<?php namespace Sirthxalot\Parse\Relations;

/**
 * Relation
 * ==================================================================================
 *
 * An abstract class that defines the interfaces used for the relations.
 *
 * @package   Sirthxalot\Parse\Relations
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
abstract class Relation
{
    abstract public function getResults();
}
