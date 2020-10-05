<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'subject',
        'body',
        'user',
    ];


    public function comments()
    {
        return $this->hasMany('App\Models\Comment','blog_id', 'id')
        ->join('users','comments.commentor','users.id');
    }
    
}


