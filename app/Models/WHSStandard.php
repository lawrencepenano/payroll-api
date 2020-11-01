<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WHSStandard extends Model
{
    use HasFactory;

    protected $table = 'whs_standrds';

    // protected $fillable = [
    //     'code',
    //     'description',  
    //     'remarks',
    // ];

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id')
                    ->select('id');
    }

    public function audit()
    {
        return $this->hasMany('App\Models\WHSStandardAuditTrail','whs_standard_id','id');
    }

}
