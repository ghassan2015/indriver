<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $fillable = [
        'name',
        'provider_name',
        'image',
        'price',
        'fixed',
        'description',
        'status',
        'minute',
        'hour',
        'distance',
        'calculator',
        'capacity',
        'calculation_format',
        'between_km',
        'less_distance_price',
        'greater_distance_price',
        'charges_per_min'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
    
    const SEDAN_SERVICE_ID = 1;
    const SUV_SERVICE_ID = 3;
    const MINI_VAN_SERVICE_ID = 4;
    const TOW_TRUCK_ID = 6;
    const BOOSTER_CABLE_SERVICE_ID = 7;

    const MIN = 'MIN';
    const HOUR = 'HOUR';
    const DISTANCE = 'DISTANCE';
    const DISTANCE_MIN = 'DISTANCEMIN';
    const DISTANCE_HOUR = 'DISTANCEHOUR';
    const FIXED = 'FIXED';

    const CALCULATORS = [
        self::MIN => 'Min',
        self::HOUR => 'Hour',
        self::DISTANCE => 'Distance',
        self::DISTANCE_MIN => 'Distance Min',
        self::DISTANCE_HOUR => 'Distance Hour',
        self::FIXED => 'Fixed',
    ];
}
