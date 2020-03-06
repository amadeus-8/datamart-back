<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleCenterDepartment extends Model
{
    protected $fillable = ['name'];
    protected $table = 'sale_centers_department';

    public function department(){
        return $this->hasMany(Department::class);
    }

    public function sale_center(){
        return $this->hasMany(SaleCenter::class);
    }
}
