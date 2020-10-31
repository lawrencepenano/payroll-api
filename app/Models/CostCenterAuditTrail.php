<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCenterAuditTrail extends Model
{
    use HasFactory;

    public function cost_center()
    {
        return $this->belongsTo('App\Models\CostCenter','cost_center_id','id');
    }
}
