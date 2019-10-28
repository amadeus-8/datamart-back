<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleCenter extends Model
{
    protected $fillable = ['name'];

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
