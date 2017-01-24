<?php namespace Sirthxalot\Parse\Test\Models;

use Sirthxalot\Parse\ObjectModel;

class User extends ObjectModel
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
