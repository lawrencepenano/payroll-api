<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WHSStandard extends Model
{
    use HasFactory;

    protected $table = 'whs_standards';

    protected $fillable = [
        'type',
        'wd_per_year',  
        'wh_per_day',
        'wm_per_year',
        'wh_start',  
        'wh_end',
        'break_hours',
        'rd_monday',  
        'rd_tuesday',
        'rd_wednesday',  
        'rd_thursday',
        'rd_friday',  
        'rd_saturday',
        'rd_sunday',  
    ];

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id')
                    ->select('id');
    }

    public function total_working_days_per_year()
    {
        return $this->hasOne('App\Models\TotalWorkDaysPerYear','code','wd_per_year');
    }

    public function total_working_mondts_per_year()
    {
        return $this->hasOne('App\Models\TotalWorkDaysPerYear','code','wm_per_year');
    }


    public function audit()
    {
        return $this->hasMany('App\Models\WHSStandardAuditTrail','whs_standard_id','id');
    }

}
