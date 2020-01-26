<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Age extends Model
{
    protected $fillable = ['name'];

    public function clients(){
        return $this->hasMany(Client::class);
    }
}
