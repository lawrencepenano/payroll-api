<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_id',
        'comment',
        'commentor'
    ];


    public function blog()
    {
        return $this->belongsTo('App\Models\Comment', 'blog_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'commentor');
    }
}
