<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WHSStandardAuditTrail extends Model
{
    use HasFactory;
    
    protected $table = 'whs_standrds';

    public function whs_standard()
    {
        return $this->belongsTo('App\Models\WHSStandard','whs_standard_id','id');
    }
}
