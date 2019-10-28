<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = ['fullname'];

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
