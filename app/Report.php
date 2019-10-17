<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    public function productsReports(){
        return $this->hasMany(ProductsReport::class);
    }

    public function referrersReports(){
        return $this->hasMany(ReferrersReport::class);
    }

    public function giftsReports(){
        return $this->hasMany(GiftsReport::class);
    }

    public function agesReports(){
        return $this->hasMany(AgesReport::class);
    }

    public function territoriesReports(){
        return $this->hasMany(TerritoriesReport::class);
    }

    public function KBMReports(){
        return $this->hasMany(KBMReport::class);
    }

    public function saleCentersReports(){
        return $this->hasMany(SaleCentersReport::class);
    }
}
