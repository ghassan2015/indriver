<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Setting;
use Exception;
use App\Helpers\Helper;

use App\ServiceType;
use App\ProviderService;

class ServiceResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $services = ServiceType::all();
        if ($request->ajax()) {
            return $services;
        } else {
            return view('admin.service.index', compact('services'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.service.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'capacity' => 'required|numeric',
            'fixed' => 'required|numeric',
            'price' => 'sometimes|nullable|numeric',
            'minute' => 'sometimes|nullable|numeric',
            'hour' => 'sometimes|nullable|numeric',
            'distance' => 'sometimes|nullable|numeric',
            'calculator' => 'required|in:MIN,HOUR,DISTANCE,DISTANCEMIN,DISTANCEHOUR,FIXED',
            'image' => 'mimes:ico,png'
        ]);

        try {
            $service = new ServiceType;

            $service->name = $request->name;
            $service->fixed = $request->fixed;
            $service->description = $request->description;
            $service->calculation_format = $request->calculation_format;
            $service->cancellation_charges = $request->cancellation_charges;
            $service->calculator = $request->calculator;


            if (!empty($request->charges_per_min))
                $service->charges_per_min = $request->charges_per_min;

            if ($request->hasFile('image')) {
                $service->image = Helper::upload_picture($request->image);
            }

            if (!empty($request->price))
                $service->price = $request->price;
            else
                $service->price = 0;

            if (!empty($request->minute))
                $service->minute = $request->minute;
            else
                $service->minute = 0;

            if (!empty($request->hour))
                $service->hour = $request->hour;
            else
                $service->hour = 0;

            if (!empty($request->distance))
                $service->distance = $request->distance;
            else
                $service->distance = 0;

            if (!empty($request->between_km))
                $service->between_km = $request->between_km;
            else
                $service->between_km = 0;

            if (!empty($request->less_distance_price))
                $service->less_distance_price = $request->less_distance_price;
            else
                $service->less_distance_price = 0;

            if (!empty($request->greater_distance_price))
                $service->greater_distance_price = $request->greater_distance_price;
            else
                $service->greater_distance_price = 0;

            $service->save();

            return back()->with('flash_success', trans('admin.service_type_msgs.service_type_saved'));
        } catch (Exception $e) {

            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\ServiceType $serviceType
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            return ServiceType::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\ServiceType $serviceType
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        try {
            $service = ServiceType::findOrFail($id);
            return view('admin.service.edit', compact('service'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\ServiceType $serviceType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {


        $this->validate($request, [
            'name' => 'required|max:255',
            'fixed' => 'required|numeric',
            'price' => 'sometimes|nullable|numeric',
            'charges_per_min' => 'sometimes|nullable|numeric',
            'minute' => 'sometimes|nullable|numeric',
            'hour' => 'sometimes|nullable|numeric',
            'distance' => 'sometimes|nullable|numeric',
            'image' => 'mimes:ico,png'
        ]);

        try {

            $imgservice = ServiceType::find($id);

            if ($request->hasFile('image')) {
                if ($imgservice->image) {
                    Helper::delete_picture($imgservice->image);
                }
                $service['image'] = Helper::upload_picture($request->image);
            }

            $service['name'] = $request->name;
            $service['fixed'] = $request->fixed;

            $service['calculation_format'] = $request->calculation_format;
            $service['cancellation_charges'] = $request->cancellation_charges;


//            if (!empty($request->charges_per_min))
                $service['charges_per_min'] = $request->charges_per_min;


            if (!empty($request->price))
                $service['price'] = $request->price;
            else
                $service['price'] = 0;

            if (!empty($request->minute))
                $service['minute'] = $request->minute;
            else
                $service['minute'] = 0;

            if (!empty($request->hour))
                $service['hour'] = $request->hour;
            else
                $service['hour'] = 0;

            if (!empty($request->distance))
                $service['distance'] = $request->distance;
            else
                $service['distance'] = 0;

            if (!empty($request->between_km))
                $service['between_km'] = $request->between_km;
            else
                $service['between_km'] = 0;

            if (!empty($request->less_distance_price))
                $service['less_distance_price'] = $request->less_distance_price;
            else
                $service['less_distance_price'] = 0;

            if (!empty($request->greater_distance_price))
                $service['greater_distance_price'] = $request->greater_distance_price;
            else
                $service['greater_distance_price'] = 0;

            $service['calculator'] = $request->calculator;
            $service['capacity'] = $request->capacity;
            $service['admin_fee'] = $request->adminFee;

            ServiceType::where('id', $id)->update($service);

            return redirect()->route('admin.service.index')->with('flash_success', trans('admin.service_type_msgs.service_type_update'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\ServiceType $serviceType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        try {
            $provider_service = ProviderService::where('service_type_id', $id)->count();
            if ($provider_service > 0) {
                return back()->with('flash_error', trans('admin.service_type_msgs.service_type_using'));
            }

            ServiceType::find($id)->delete();
            return back()->with('flash_success', trans('admin.service_type_msgs.service_type_delete'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        }
    }
}