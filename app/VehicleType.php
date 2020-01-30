<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vehicletype extends Model
{
    protected $fillable = ['name'];
    protected $table = 'vehicle_types';

    public function vehicles(){
        return $this->hasMany(Vehicle::class);
    }

    public function vehicle_models()
    {
        return $this->hasMany(VehicleModel::class);
    }
}
