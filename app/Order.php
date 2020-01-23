<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function vehicle(){
        return $this->belongsTo(Vehicle::class);
    }

    public function time(){
        return $this->belongsTo(Time::class);
    }

    public function client(){
        return $this->belongsTo(Client::class);
    }

    public function region(){
        return $this->belongsTo(Region::class);
    }

    public function gift(){
        return $this->belongsTo(Gift::class);
    }

    public function department(){
        return $this->belongsTo(Department::class);
    }

    public function sale_channel(){
        return $this->belongsTo(SaleChannel::class);
    }

    public function sale_center(){
        return $this->belongsTo(SaleCenter::class);
    }

    public function referrer(){
        return $this->belongsTo(Referrer::class);
    }

    public function agent(){
        return $this->belongsTo(Agent::class);
    }
}
