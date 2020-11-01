<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalWorkDaysPerYear extends Model
{
    use HasFactory;
    
    protected $table = 'total_work_days_per_year';

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
        return $this->hasMany('App\Models\TotalWorkDaysPerYearAuditTrail','total_work_days_per_year_id','id');
    }

}
