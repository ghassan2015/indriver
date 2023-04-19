<?php

namespace App\Services\V1;

use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Validator;
use Exception;
use DateTime;
use Auth;
use Lang;
use Setting;
use App\ServiceType;
use App\Promocode;
use App\Provider;
use App\ProviderService;
use App\Helpers\Helper;
use GuzzleHttp\Client;


class ServiceTypes
{

    public function __construct()
    {
    }

    /**
     * Get a validator for a tradepost.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'location' => 'required',
        ];

        $messages = [
            'location.required' => 'Location Required!',
        ];

        return Validator::make($data, $rules, $messages);
    }


    /**
     * get the btc details.
     * get the currency master data.
     * get the payment methods master data.
     * @return response with data,system related errors
     */
    public function show()
    {


    }

    /**
     * get all details.
     * @return response with data,system related errors
     */
    public function getAll()
    {


    }

    /**
     * find tradepost.
     * @param  $id
     * @return response with data,system related errors
     */

    public function find($id)
    {

    }

    /**
     * insert function
     * checking form field validations
     * @param  $postrequest
     * @return response with success,errors,system related errors
     */
    public function create($request)
    {

    }

    /**
     * update function
     * checking form validations
     * @param  $postrequest
     * @return response with success,errors,system related errors
     */
    public function update($request, $id)
    {


    }

    /**
     * delete function.
     * @param  $id
     * @return response with success,errors,system related errors
     */
    public function delete($id)
    {

    }

    public function calculateFare($request, $cflag = 0)
    {

        try {

            $total = $tax_price = '';
            $location = $this->getLocationDistance($request);

            if (!empty($location['errors'])) {
                throw new Exception($location['errors']);
            } else {

                if (Setting::get('distance', 'Kms') == 'Kms')
                    $total_kilometer = round($location['meter'] / 1000, 1); //TKM
                else
                    $total_kilometer = round($location['meter'] / 1609.344, 1); //TMi

                $requestarr['meter'] = $total_kilometer;
                $requestarr['time'] = $location['time'];
                $requestarr['seconds'] = $location['seconds'];
                $requestarr['kilometer'] = 0;
                $requestarr['minutes'] = 0;
                $requestarr['service_type'] = $request['service_type'];

                $tax_percentage = Setting::get('tax_percentage');
                $commission_percentage = Setting::get('commission_percentage');
                $surge_trigger = Setting::get('surge_trigger');

                $price_response = $this->applyPriceLogic($requestarr);

                if ($tax_percentage > 0) {
                    $tax_price = $this->applyPercentage($price_response['price'], $tax_percentage);
                    $total = $price_response['price'] + $tax_price;
                } else {
                    $total = $price_response['price'];
                }
	

                if ($cflag != 0) {

                    if ($commission_percentage > 0) {
                        $commission_price = $this->applyPercentage($price_response['price'], $commission_percentage);
                        $total += $commission_price;

                    }

                    $surge = 0;

                    if ($surge_trigger > 0) {

                        $ActiveProviders = ProviderService::AvailableServiceProvider($request['service_type'])->get()->pluck('provider_id');


                        $distance = Setting::get('provider_search_radius', '10');
                        $latitude = $request['s_latitude'];
                        $longitude = $request['s_longitude'];

                        $Providers = Provider::whereIn('id', $ActiveProviders)
                            ->where('status', 'approved')
                            ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                            ->get();

                        $surge = 0;

                        if ($Providers->count() <= Setting::get('surge_trigger') && $Providers->count() > 0) {
                            $surge_price = $this->applyPercentage($total, Setting::get('surge_percentage'));
                            $total += $surge_price;
                            $surge = 1;
                        }

                    }
                    $surge_percentage = 1 + (Setting::get('surge_percentage') / 100) . "X";
                }
                $return_data['estimated_fare'] = $this->applyNumberFormat(floatval($total +1));


                $return_data['distance'] = $total_kilometer;
                $return_data['time'] = $location['time'];
                $return_data['tax_price'] = $this->applyNumberFormat(floatval($tax_price));
                $return_data['base_price'] = $this->applyNumberFormat(floatval($price_response['base_price']));
                $return_data['service_type'] = (int)$request['service_type'];
                $return_data['admin_fee'] = (int)$request['admin_fee'];

                if (Auth::user()) {
                    $return_data['surge'] = $surge;
                    $return_data['surge_value'] = $surge_percentage;
                    $return_data['wallet_balance'] = $this->applyNumberFormat(floatval(Auth::user()->wallet_balance));
                }

                $service_response["data"] = $return_data;
            }

        } catch (Exception $e) {
            $service_response["errors"] = $e->getMessage();
        }

        return $service_response;
    }

    public function applyPriceLogic($requestarr, $iflag = 0, $minutes_whole = 0)
    {
	    //   Log::info("ApplyPriceLogic: with params", $requestarr);
        $waiting_charges = 0;
        $fn_response = array();

        $service_type = ServiceType::findOrFail($requestarr['service_type']);
	
	    //   Log::info("ApplyPriceLogic: Service Type: ", $service_type->toArray());
        if (isset($requestarr['minutes_whole'])) {
	        $waiting_charges = $service_type->charges_per_min * $requestarr[ 'minutes_whole' ];
	        // Log::info("ApplyPriceLogic: waqas waiting charges: $waiting_charges");
        }

        // Log::info("ApplyPriceLogic: iFlag: $iflag");
        if ($iflag == 0) {
            //for estimated fare
            $total_kilometer = $requestarr['meter']; //TKM || TMi
            $total_minutes = round($requestarr['seconds'] / 60); //TM
            $total_hours = ($requestarr['seconds'] / 60) / 60; //TH
	        //   Log::info("ApplyPriceLogic iflag 0: total_kilometer: $total_kilometer, total_minutes: $total_minutes, total_hours: $total_hours");
        } else {
            //for invoice fare
            $total_kilometer = $requestarr['kilometer']; //TKM || TMi
            $total_minutes = $requestarr['minutes']; //TM
            $total_hours = $requestarr['minutes'] / 60; //TH
	        //   Log::info("ApplyPriceLogic iflag 1: total_kilometer: $total_kilometer, total_minutes: $total_minutes, total_hours: $total_hours");
        }

        //$rental = ceil($requestarr['rental_hours']);

//        if(isset($requestarr['round']) and $requestarr['round']){
//            $total_kilometer = $total_kilometer * 2;
//        }


        $per_minute = $service_type->minute; //PM
        $per_hour = $service_type->hour; //PH
        $per_kilometer = $service_type->price; //PKM
        $base_distance = $service_type->distance; //BD
        $base_price = $service_type->fixed; //BP
	    $distance_fare = 0;
	
	    //   Log::info("ApplyPriceLogic: per_minute: $per_minute, per_hour: $per_hour, per_kilometer: $per_kilometer, base_distance: $base_distance, base_price: $base_price");

	    //   Log::info("ApplyPriceLogic: Calculator: $service_type->calculator");
	    //   Log::info("ApplyPriceLogic: Calculation Format: $service_type->calculation_format");
	      
        if ($service_type->calculator == 'MIN') {
            //BP+(TM*PM)
            $price = $base_price + ($total_minutes * $per_minute);
        } else if ($service_type->calculator == 'HOUR') {
            //BP+(TH*PH)
            $price = $base_price + ($total_hours * $per_hour);
        } else if ($service_type->calculator == 'DISTANCE') {
            //BP+((TKM-BD)*PKM)

            // if($base_distance>$total_kilometer){
            //     $price = $base_price;
            // }else{
            //     $price = $base_price+(($total_kilometer - $base_distance)*$per_kilometer);
            // }

            ////Type A Calculation
            if ($service_type->calculation_format == 'TYPEA') {
                if ($service_type->between_km > $total_kilometer) {
                    $price = $base_price + ($total_kilometer * $service_type->less_distance_price);
                    $distance_fare = $total_kilometer * $service_type->less_distance_price;

                } else {
                    $price = $base_price + ($total_kilometer * $service_type->greater_distance_price);
                    $distance_fare = $total_kilometer * $service_type->greater_distance_price;
                }


            } else if ($service_type->calculation_format == 'TYPEB') {
                Log::info("ApplyPriceLogic: Calculation format TYPEB: ");

                $price = $base_price + ($total_kilometer * $service_type->price);
                $distance_fare = $total_kilometer * $service_type->price;

            } else {
                $price = $base_price;
                $distance_fare = $base_price;
                $waiting_charges = 0;
            }

        } else if ($service_type->calculator == 'DISTANCEMIN') {
            //BP+((TKM-BD)*PKM)+(TM*PM)
            if ($base_distance > $total_kilometer) {
                $price = $base_price + ($total_minutes * $per_minute);
            } else {
                $price = $base_price + ((($total_kilometer - $base_distance) * $per_kilometer) + ($total_minutes * $per_minute));
            }
        } else if ($service_type->calculator == 'DISTANCEHOUR') {
            //BP+((TKM-BD)*PKM)+(TH*PH)
            if ($base_distance > $total_kilometer) {
                $price = $base_price + ($total_hours * $per_hour);
            } else {
                $price = $base_price + ((($total_kilometer - $base_distance) * $per_kilometer) + ($total_hours * $per_hour));
            }
        } else {
            //by default set Ditance price BP+((TKM-BD)*PKM) 
            // Log::info("ApplyPriceLogic: Calculator Type Fixed Else");
            $travelDistance = ($total_kilometer - $base_distance) * $per_kilometer;
            $price = $base_price + $service_type->admin_fee + $travelDistance;

            // $price = $base_price + (($total_kilometer - $base_distance) * $per_kilometer);
            // $price = 30 + ((2.5 - 0) * 10);
        }

        $fn_response['price'] = $price;
        $fn_response['base_price'] = $base_price;
        // if($base_distance>$total_kilometer){
        //     $fn_response['distance_fare']=0;
        // }
        // else{
        //     $fn_response['distance_fare']=($total_kilometer - $base_distance)*$per_kilometer;
        // }    

        $fn_response['distance_fare'] = $distance_fare;
        $fn_response['minute_fare'] = $total_minutes * $per_minute;
        $fn_response['hour_fare'] = $total_hours * $per_hour;
        $fn_response['calculator'] = $service_type->calculator;
        $fn_response['service_type'] = $requestarr['service_type'];
        $fn_response['waiting_charges'] = $waiting_charges;
        $fn_response['total_kilometer'] = $total_kilometer;
        $fn_response['per_kilometer'] = $per_kilometer;
        $fn_response['base_distance'] = $base_distance;
        $fn_response['admin_fee'] = $service_type->admin_fee;
	      
        // Log::info("ApplyPriceLogic: Calculation Response: ", $fn_response);
	
	      return $fn_response;
    }

    public function applyPercentage($total, $percentage)
    {
        return ($percentage / 100) * $total;
    }

    public function applyNumbeFrFormat($total)
    {
        return round($total, Setting::get('round_decimal'));
    }

    public function getLocationDistance($locationarr)
    {

        $fn_response = array('data' => null, 'errors' => null);
        $destinations = json_decode($locationarr['positions']);

        try {
            $totalMeter = 0;
            $totalSeconds = 0;
            $currentSourceLat = $locationarr['s_latitude'];
            $currentSourceLng = $locationarr['s_longitude'];
            foreach ($destinations as $key => $destination){
                $apiurl = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $currentSourceLat . "," . $currentSourceLng . "&destinations=" . $destination->d_latitude . "," . $destination->d_longitude . "&mode=driving&sensor=false&units=imperial&key=" . Setting::get('map_key');
                $client = new Client;
                $location = $client->get($apiurl);
                $location = json_decode($location->getBody(), true);

                if (!empty($location['rows'][0]['elements'][0]['status']) && $location['rows'][0]['elements'][0]['status'] == 'ZERO_RESULTS') {
                    throw new Exception("Out of service area", 1);
                }

                $totalMeter = $totalMeter + $location['rows'][0]['elements'][0]['distance']['value'];
                $totalSeconds = $totalSeconds + $location['rows'][0]['elements'][0]['duration']['value'];

                $currentSourceLat = $destination->d_latitude;
                $currentSourceLng = $destination->d_longitude;
            }

            $fn_response["meter"] = $totalMeter;
            $fn_response["time"] = CarbonInterval::seconds($totalSeconds)->cascade()->forHumans();
            $fn_response["seconds"] = $totalSeconds;

        } catch (Exception $e) {
            $fn_response["errors"] = trans('user.maperror');
        }

        return $fn_response;
    }

    public function applyNumberFormat($total)
    {
//        return round($total, Setting::get('round_decimal'));
        return round($total, 2);
    }

    public function calculateFareV1($request, $cflag = 0 , $round=0 , $waitingMinutes=0)
    {
    	// Log::info("CalculateFareV1: cFlag: $cflag");

        try {

            $total = $tax_price = '';
            $location = $this->getLocationDistance($request);
	        //   Log::info("CalculateFareV1: locations distance", $location);
            if (!empty($location['errors'])) {
                throw new Exception($location['errors']);
            } else {

                if (Setting::get('distance', 'Kms') == 'Kms') {
	                // Log::info("CalculateFareV1: distance in Kms");
	                $total_kilometer = round( $location[ 'meter' ] / 1000, 5 ); //TKM
//	                $total_kilometer = round( $location[ 'meter' ] / 1000, 1 ); //TKM
                }else {
	                // Log::info("CalculateFareV1: distance not in Kms");
	                $total_kilometer = round( $location[ 'meter' ] / 1609.344, 1 ); //TMi
                }

                $requestarr['meter'] = $total_kilometer;
                $requestarr['time'] = $location['time'];
                $requestarr['seconds'] = $location['seconds'];
                $requestarr['kilometer'] = 0;
                $requestarr['minutes'] = 0;
                $requestarr['service_type'] = $request['service_type'];
                $requestarr['round'] = $round;

                $tax_percentage = Setting::get('tax_percentage');
                $commission_percentage = Setting::get('commission_percentage');
                // Log::info("calculateFareV1: Commission: $commission_percentage Round: $round fullUrl: ".  request()->fullUrl());
                $surge_trigger = Setting::get('surge_trigger');

                $price_response = $this->applyPriceLogic($requestarr);

                if ($tax_percentage > 0  && $request['service_type'] !== ServiceType::BOOSTER_CABLE_SERVICE_ID) {
	                //   Log::info("CalculateFareV1 first if: Tax percentage: $tax_percentage and Service Type" . $request['service_type']);
                    $tax_price = $this->applyPercentage($price_response['price'], $tax_percentage);
	                //   Log::info("CalculateFareV1  first if: Tax Price $tax_price");
                    $total = $price_response['price'] + $tax_price;
                } else {
                    //   Log::info("CalculateFareV1 else-------------------------------------------------- " . $price_response['price']);
                    $total = $price_response['price'];
                }
	            //   Log::info("CalculateFareV1: Total:  $total");

                if ($cflag != 0) {
	                // Log::info("CalculateFareV1: cFlag Not equal to Zero: 0");

                    if ($commission_percentage > 0 && $request['service_type'] !== ServiceType::BOOSTER_CABLE_SERVICE_ID) {
	                    //   Log::info("CalculateFareV1 cflat 1 if : Commission Percentage: $commission_percentage and Service Type" . $request['service_type']);
                        $distanceTravel = ($price_response['total_kilometer'] - $price_response['base_distance']) * $price_response['per_kilometer'] ;
                        $distanceCommission = $round ? $distanceTravel * 2 : $distanceTravel;
                        $commission_price = $this->applyPercentage($price_response['base_price'] + $distanceCommission , $commission_percentage);
	                    //   Log::info("CalculateFareV1 cflat 1 if: Commission Price: $commission_price");
//                        $total += $commission_price;
                        $return_data['commission'] = $commission_price;
	                    //   Log::info("CalculateFareV1: Total with Commission: $total");
                    }

                    $surge = 0;

                    if ($surge_trigger > 0) {
	                    //   Log::info("CalculateFareV1: Surge Trigger: $surge_trigger");
                        $ActiveProviders = ProviderService::AvailableServiceProvider($request['service_type'])->get()->pluck('provider_id');
                        $distance = Setting::get('provider_search_radius', '10');
	                    //   Log::info("CalculateFareV1: distance: $distance");
	                      $latitude = $request['s_latitude'];
                        $longitude = $request['s_longitude'];
	                    //   Log::info("CalculateFareV1: latitude: $latitude, longitude: $longitude");

                        $Providers = Provider::whereIn('id', $ActiveProviders)
                            ->where('status', 'approved')
                            ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                            ->get();

                        $surge = 0;

                        if ($Providers->count() <= Setting::get('surge_trigger') && $Providers->count() > 0) {
	                        //   Log::info("CalculateFareV1: if condition true against provider count vs surge trigger");
		                        $surge_price = $this->applyPercentage($total, Setting::get('surge_percentage'));
//                            $total += $surge_price;
                            $surge = 1;
	                        //   Log::info("CalculateFareV1: surge price: $surge_price, Total with surge price: $total ");
                        }
                    }
                    $surge_percentage = 1 + (Setting::get('surge_percentage') / 100) . "X";
	                //   Log::info("CalculateFareV1: Surge Percentage: $surge_percentage");
                }
               $service = ServiceType::where('id',$request['service_type'])->first();
//                $total = $total + ($waitingMinutes * $service->charges_per_min);
                if($round){ // in case of round trip
                    $total = (($total  * 2) - ($price_response['base_price'] + $price_response['admin_fee'])) + 
                                                ($waitingMinutes * $service->charges_per_min) ;
//                    $service = ServiceType::where('id',$request['service_type'])->first();
//                    $total = $total + ($waitingMinutes * $service->charges_per_min);
                }
                $return_data['estimated_fare'] = $this->applyNumberFormat(floatval($total));

                $return_data['distance'] = $total_kilometer;
                $return_data['time'] = $location['time'];
                $return_data['tax_price'] = $this->applyNumberFormat(floatval($tax_price));
                $return_data['base_price'] = $this->applyNumberFormat(floatval($price_response['base_price']));
                $return_data['service_type'] = (int)$request['service_type'];

                if (Auth::user()) {
                    $return_data['surge'] = $surge;
                    $return_data['surge_value'] = $surge_percentage;
                    $return_data['wallet_balance'] = $this->applyNumberFormat(floatval(Auth::user()->wallet_balance));
                }

	            //   Log::info("CalculateFareV1: Return Data", $return_data );
                $service_response["data"] = $return_data;
            }

        } catch (Exception $e) {
            $service_response["errors"] = $e->getMessage();
        }

        return $service_response;
    }

}