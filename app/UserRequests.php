<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRequests extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'provider_id',
        'user_id',
        'current_provider_id',
        'service_type_id',
        'promocode_id',
        'rental_hours',
        'status',
        'cancelled_by',
        'is_track',
        'otp',
        'travel_time',
        'distance',
        's_latitude',
        'd_latitude',
        's_longitude',
        'd_longitude',
        'track_distance',
        'track_latitude',
        'track_longitude',
        'paid',
        's_address',
        'd_address',
        'assigned_at',
        'schedule_at',
        'is_scheduled',
        'started_at',
        'finished_at',
        'use_wallet',
        'user_rated',
        'provider_rated',
        'surge',   
        'postpaid_payment_status',  
        'description',
        'is_booster_cable',
        'instructions',
        'dispatcher_payments',
        'request_type'
    ];


    protected $hidden = [
        // 'created_at', 'updated_at'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'assigned_at',
        'schedule_at',
        'started_at',
        'finished_at',
    ];

    public $REQEST_TYPE_RIDE = 'ride';
    public $REQEST_TYPE_DISPATCH = 'dispatch';

    protected $casts = ['dispatcher_payments' => 'array'];
    
    public function service_type()
    {
        return $this->belongsTo('App\ServiceType');
    }

    public function payment()
    {
        return $this->hasOne('App\UserRequestPayment', 'request_id');
    }

    public function rating()
    {
        return $this->hasOne('App\UserRequestRating', 'request_id');
    }

    public function filter()
    {
        return $this->hasMany('App\RequestFilter', 'request_id');
    }


    public function stops()
    {
        return $this->hasMany('App\UserRequestStop', 'user_request_id');
    }


    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function provider()
    {
        return $this->belongsTo('App\Provider');
    }

    public function provider_service()
    {
        return $this->belongsTo('App\ProviderService', 'provider_id', 'provider_id');
    }

    public function scopePendingRequest($query, $user_id)
    {
        return $query->where('user_id', $user_id)
                ->whereNotIn('status' , ['CANCELLED', 'COMPLETED', 'SCHEDULED', 'SCHEDULES']);
    }

    public function scopeRequestHistory($query)
    {
        return $query->orderBy('user_requests.created_at', 'desc')
                        ->with('user','payment','provider','stops');
    }

    public function scopeUserTrips($query, $user_id)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.status','COMPLETED')
                    ->orderBy('user_requests.created_at','desc')
                    ->select('user_requests.*')
                    ->with('payment','service_type','stops');
    }

    public function scopeUserUpcomingTrips($query, $user_id)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.status', 'SCHEDULED')
                    ->orwhere('user_requests.status', 'SCHEDULES')
                    ->orderBy('user_requests.created_at','desc')
                    ->select('user_requests.*')
                    ->with('service_type','provider','stops');
    }

    public function scopeProviderUpcomingRequest($query, $user_id)
    {
        return $query->where('user_requests.provider_id', $user_id)
                    ->where('user_requests.status', 'SCHEDULED')
                    ->orwhere('user_requests.status', 'SCHEDULES')
                    ->select('user_requests.*')
                    ->with('service_type','user','provider','stops');
    }

    public function scopeUserTripDetails($query, $user_id, $request_id)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.id', $request_id)
                    ->where('user_requests.status', 'COMPLETED')
                    ->select('user_requests.*')
                    ->with('payment','service_type','user','provider','rating','stops');
    }

    public function scopeUserUpcomingTripDetails($query, $user_id, $request_id)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.id', $request_id)
                    ->where('user_requests.status', 'SCHEDULED')
                    ->orwhere('user_requests.status', 'SCHEDULES')
                    ->select('user_requests.*')
                    ->with('service_type','user','provider'.'stops');
    }

    public function scopeUserRequestStatusCheck($query, $user_id, $check_status)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.user_rated',0)
                    ->whereNotIn('user_requests.status', $check_status)
                    ->select('user_requests.*')
                    ->with('user','provider','service_type','provider_service','rating','payment','stops');
    }

    public function scopeUserRequestAssignProvider($query, $user_id, $check_status)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.user_rated',0)
                    ->where('user_requests.provider_id',0)
                    ->whereIn('user_requests.status', $check_status)
                    ->select('user_requests.*')
                    ->with('filter');
    }
}
