<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nature_of_business',
        'address1',  
        'email',
        'phone',
    ];

    public function access_status()
    {
        return $this->hasOne('App\Models\CompanyStatusAssignment','company_id','id')
                    ->join('access_statuses','company_status_assignments.status_id','access_statuses.id')
                    ->select('access_statuses.id as value', 'access_statuses.status as label');
    }

    public function cost_centers()
    {
        return $this->hasMany('App\Models\CostCenter','company_id','id');
    }

    public function deparments()
    {
        return $this->hasMany('App\Models\Department','company_id','id');
    }

    public function total_work_days_per_year()
    {
        return $this->hasMany('App\Models\TotalWorkDaysPerYear','company_id','id');
    }

    public function total_work_months_per_year()
    {
        return $this->hasMany('App\Models\TotalWorkMonthsPerYear','company_id','id');
    }

    public function whs_standard()
    {
        return $this->hasMany('App\Models\WHSStandard','company_id','id');
    }

    public function audit()
    {
        return $this->hasMany('App\Models\CompanyAuditTrail','company_id','id');
    }

}
