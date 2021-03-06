<?php

namespace Robot\Models;


use Robot\Core\BaseClasses\BaseModel;

class Keyboard extends BaseModel
{
    protected static $table = 'keyboards';
    protected static $primary_key = 'id';

    public function posts()
    {
        return $this->hasMany('keyboardposts','keyboards.id','keyboardposts.keyboard_id','INNER')->hasMany('posts','post_id','posts.id','INNER');
    }
}