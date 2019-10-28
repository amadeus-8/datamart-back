<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    protected $fillable = ['name', 'vehicle_brand_id'];

//    protected $attributes = [
//        'vehicle_brand_id' => 0
//    ];

    public function vehicle_brand(){
        return $this->belongsTo(VehicleBrand::class);
    }
}
