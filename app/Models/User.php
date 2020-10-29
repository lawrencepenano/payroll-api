<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function company()
    {
        return $this->hasOne('App\Models\UserCompanyAssignment','user_id','id')
                    ->join('companies','user_company_assignments.company_id','companies.id')
                    ->select('companies.id','companies.company_name as name');
    }

    public function assigned_user_name()
    {
        return $this->hasOne('App\Models\UserNameAssignment','user_id','id')
                    ->join('user_names','user_name_assignments.user_name_id','user_names.id')
                    ->select('user_names.user_name');
    }

    public function assigned_role()
    {
        return $this->hasOne('App\Models\UserRoleAssignment','user_id','id')
                    ->join('roles','user_role_assignments.role_id','roles.id')
                    ->select('roles.id as value', 'roles.name as label');
    }

    public function assigned_modules()
    {
        return $this->hasMany('App\Models\UserModuleAssignment','user_id','id')
                    ->join('modules','user_module_assignments.module_id','modules.id')
                    ->select('modules.id as id', 'modules.name as label');
    }

    public function access_status()
    {
        return $this->hasOne('App\Models\UserAccessStatusAssignment','user_id','id')
                    ->join('modules','user_module_assignments.module_id','modules.id')
                    ->select('modules.name');
    }
}
