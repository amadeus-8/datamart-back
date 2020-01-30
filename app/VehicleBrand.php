<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleBrand extends Model
{
    protected $fillable = ['name','vehicle_type_id'];
    public function vehicles(){
        return $this->hasMany(Vehicle::class);
    }

    public function vehicle_models()
    {
        return $this->hasMany(VehicleModel::class);
    }
}
