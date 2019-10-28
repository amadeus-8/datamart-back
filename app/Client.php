<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['isn', 'gender', 'age', 'age_category', 'insurance_class', 'region_id'];
    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function region(){
        return $this->belongsTo(Region::class);
    }
}
