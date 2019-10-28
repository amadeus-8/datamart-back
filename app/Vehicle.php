<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function vehicle_brand(){
        return $this->belongsTo(VehicleBrand::class);
    }

    public function vehicle_model(){
        return $this->belongsTo(VehicleModel::class);
    }

    public function year_category(){
        return $this->belongsTo(VehicleYearCategory::class);
    }
}
