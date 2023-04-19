<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRequestPayment extends Model
{

    const FLAT = 'Flat';
    const DISTANCE_CALCULATION = 'Distance Calculation';
    protected $fillable = [
        'request_id', 'user_id', 'provider_id', 'fleet_id', 'promocode_id', 'payment_id',
        'payment_mode',
        'fixed',
        'distance',
        'minute',
        'hour',
        'commision', 'commision_per', 'fleet', 'fleet_per',
        'discount', 'discount_per',
        'tax', 'tax_per',
        'total',
        'wallet', 'is_partial', 'cash', 'online', 'tips',
        'payable',
        'provider_commission',
        'provider_pay',
        'surge',
        'waiting_charges'
    ];

    protected $casts = ['waiting_charges' => 'float'];


    public function getTotalAttribute($value)
    {
        return round($value, 2);
    }


    public function getPayableAttribute($value)
    {
        return round($value, 2);
    }

//    protected $casts = [
//        'payable' => 'float',
//        'total' => 'float',
//        'tax' => 'float',
//        'provider_pay' => 'float'
//    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'status', 'password', 'remember_token', 'created_at', 'updated_at'
    ];

    /**
     * The services that belong to the user.
     */
    public function request()
    {
        return $this->belongsTo('App\UserRequests');
    }

    /**
     * The services that belong to the user.
     */
    public function provider()
    {
        return $this->belongsTo('App\Provider');
    }
	
		/**
	 * The user who created the request.
	 */
		public function user()
		{
			return $this->belongsTo('App\User');
		}
}
