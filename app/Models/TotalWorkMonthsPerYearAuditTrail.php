<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalWorkMonthsPerYearAuditTrail extends Model
{
    use HasFactory;

    public function total_work_months_per_year()
    {
        return $this->belongsTo('App\Models\TotalWorkMonthsPerYear','total_work_months_per_year_id','id');
    }
}
