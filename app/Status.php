<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = ['name'];
    protected $table = 'statuses';

    public function clients(){
        return $this->hasMany(Client::class);
    }
}
