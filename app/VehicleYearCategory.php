<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleYearCategory extends Model
{
    protected $fillable = ['category'];

    public function vehicles(){
        return $this->hasMany(Vehicle::class);
    }
}
