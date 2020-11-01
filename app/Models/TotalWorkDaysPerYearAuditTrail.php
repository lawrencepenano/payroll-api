<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalWorkDaysPerYearAuditTrail extends Model
{
    use HasFactory;

    public function total_work_day_per_year()
    {
        return $this->belongsTo('App\Models\TotalWorkDaysPerYear','total_work_days_per_year_id','id');
    }
}
