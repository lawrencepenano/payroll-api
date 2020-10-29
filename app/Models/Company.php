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

}
