<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Referrer extends Model
{
    protected $fillable = ['name'];

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
