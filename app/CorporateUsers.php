<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CorporateUsers extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "corporate_users";
    protected $fillable = [
        'corporate_id', 'first_name', 'last_name', 'email', 'mobile', 'password', 'employee_id', 'pin',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['remember_token'];


    public function company()
    {
        return $this->belongsTo('App\Corporate','corporate_id','id');
    }

}
