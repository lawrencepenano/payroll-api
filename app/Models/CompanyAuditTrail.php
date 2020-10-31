<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAuditTrail extends Model
{
    use HasFactory;

    public function company()
    {
        return $this->belongsTo('App\Models\Company','id','company_id');
    }

}
