<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',  
        'remarks',
    ];

    public function company()
    {
     return $this->belongsTo('App\Models\Company','company_id','id')
                 ->select('id');
    }

    public function audit()
    {
        return $this->hasMany('App\Models\PayGroupAuditTrail','pay_group_id','id');
    }
}
