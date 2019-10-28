<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['name'];

    public function clients(){
        return $this->hasMany(Client::class);
    }
}
