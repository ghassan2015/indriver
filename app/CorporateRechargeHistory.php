<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CorporateRechargeHistory extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'corporate_recharge_histories';
    protected $fillable = [
        'corporate_id',
        'recharge_option',
        'amount',
        'payment_status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
