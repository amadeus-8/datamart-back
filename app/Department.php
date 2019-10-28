<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'region_id'];

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
