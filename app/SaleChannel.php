<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleChannel extends Model
{
    protected $fillable = ['name'];

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
