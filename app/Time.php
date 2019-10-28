<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Time extends Model
{
    protected $fillable = ['date', 'day', 'month', 'year', 'day_of_week'];

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
