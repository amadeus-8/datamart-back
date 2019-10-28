<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    protected $fillable = ['name'];

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
