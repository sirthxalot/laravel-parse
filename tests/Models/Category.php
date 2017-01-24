<?php namespace Sirthxalot\Parse\Test\Models;

use Sirthxalot\Parse\ObjectModel;

class Category extends ObjectModel
{
    public function posts()
    {
        return $this->hasManyArray(Post::class);
    }
}
