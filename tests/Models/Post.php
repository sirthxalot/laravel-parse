<?php namespace Sirthxalot\Parse\Test\Models;

use Sirthxalot\Parse\ObjectModel;

class Post extends ObjectModel
{
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
