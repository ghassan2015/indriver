@extends('admin.layout.base')

@section('title', 'Request details ')

@section('content')
    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <h4>@lang('admin.request.request_details')</h4>
                <a href="{{ route('admin.requests.index') }}" class="btn btn-default pull-right">
                    <i class="fa fa-angle-left"></i> @lang('admin.back')
                </a>
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">@lang('admin.request.Booking_ID') :</dt>
                            <dd class="col-sm-8">{{ $request->booking_id }}</dd>

                            <dt class="col-sm-4">@lang('admin.request.User_Name') :</dt>
                            <dd class="col-sm-8">{{ $request->user->first_name }}</dd>

                            <dt class="col-sm-4">@lang('admin.request.Provider_Name') :</dt>
                            @if($request->provider)
                                <dd class="col-sm-8">{{ $request->provider->first_name }}</dd>
                            @else
                                <dd class="col-sm-8">@lang('admin.request.provider_not_assigned')</dd>
                            @endif

                            <dt class="col-sm-4">@lang('admin.request.total_distance') :</dt>
                            <dd class="col-sm-8">{{ $request->distance ? $request->distance : 0 }}{{$request->unit}}</dd>

                            @if($request->status == 'SCHEDULED')
                                <dt class="col-sm-4">@lang('admin.request.ride_scheduled_time') :</dt>
                                <dd class="col-sm-8">
                                    @if($request->schedule_at != "")
                                        {{ date('jS \of F Y h:i:s A', strtotime($request->schedule_at)) }}
                                    @else
                                        -
                                    @endif
                                </dd>
                            @else
                                <dt class="col-sm-4">@lang('admin.request.ride_start_time') :</dt>
                                <dd class="col-sm-8">
                                    @if($request->started_at != "")
                                        {{ date('jS \of F Y h:i:s A', strtotime($request->started_at)) }}
                                    @else
                                        -
                                    @endif
                                </dd>

                                <dt class="col-sm-4">@lang('admin.request.ride_end_time') :</dt>
                                <dd class="col-sm-8">
                                    @if($request->finished_at != "")
                                        {{ date('jS \of F Y h:i:s A', strtotime($request->finished_at)) }}
                                    @else
                                        -
                                    @endif
                                </dd>
                            @endif

                            <dt class="col-sm-4">@lang('admin.request.pickup_address') :</dt>
                            <dd class="col-sm-8"><i class="fa fa-map-marker"></i> {{ $request->s_address ? $request->s_address : '-' }}</dd>

                            <dt class="col-sm-4">@lang('admin.request.drop_address') :</dt>
                            @foreach($request->stops as $index => $stop)
                                @if($index > 0)
                                    <dt class="col-sm-4"></dt>
                                @endif
                                 <dd class="col-sm-8"><i class="fa fa-map-marker"></i> {{ $stop->d_address ? $stop->d_address : '-' }}</dd>
                            @endforeach
                            @if($request->payment)
                                <dt class="col-sm-4">@lang('admin.request.base_price') :</dt>
                                <dd class="col-sm-8">{{ currency($request->payment->fixed) }}</dd>

                            <!-- <dd class="col-sm-8">{{ currency($request->payment->fixed - $request->payment->commision) }}</dd> -->
                                @if($request->service_type->calculator=='MIN')
                                    <dt class="col-sm-4">@lang('admin.request.minutes_price') :</dt>
                                    <dd class="col-sm-8">{{ currency($request->payment->minute) }}</dd>
                                @endif
                                @if($request->service_type->calculator=='HOUR')
                                    <dt class="col-sm-4">@lang('admin.request.hours_price') :</dt>
                                    <dd class="col-sm-8">{{ currency($request->payment->hour) }}</dd>
                                @endif
                                @if($request->service_type->calculator=='DISTANCE')
                                    @if($request->service_type->calculation_format=='TYPEA')
                                        <dt class="col-sm-4">@lang('admin.request.distance_price') :</dt>
                                        @if($request->distance > 0)

                                            @if($request->distance >= $request->service_type->between_km)

                                                <dd class="col-sm-8">{{ currency($request->distance * $request->service_type->greater_distance_price) }}</dd>
                                            @else
                                                <dd class="col-sm-8">{{ currency($request->distance * $request->service_type->less_distance_price) }}</dd>

                                            @endif
                                        @else
                                            <dd class="col-sm-8">{{ currency(0) }}</dd>
                                        @endif
                                    @elseif($request->service_type->calculation_format=='TYPEB')

                                        <dt class="col-sm-4">@lang('admin.request.distance_price') :</dt>

                                        <dd class="col-sm-8">{{ currency($request->distance * $request->service_type->price) }}</dd>

                                    @endif
                                @endif
                                @if($request->service_type->calculator=='DISTANCEMIN')
                                    <dt class="col-sm-4">@lang('admin.request.minutes_price') :</dt>
                                    <dd class="col-sm-8">{{ currency($request->payment->minute) }}</dd>
                                    <dt class="col-sm-4">@lang('admin.request.distance_price') :</dt>
                                    <dd class="col-sm-8">{{ currency($request->payment->distance) }}</dd>
                                @endif
                                @if($request->service_type->calculator=='DISTANCEHOUR')
                                    <dt class="col-sm-4">@lang('admin.request.hours_price') :</dt>
                                    <dd class="col-sm-8">{{ currency($request->payment->hour) }}</dd>
                                    <dt class="col-sm-4">@lang('admin.request.distance_price') :</dt>
                                    <dd class="col-sm-8">{{ currency($request->payment->distance) }}</dd>
                                @endif
                                @if($request->service_type->calculator=='FIXED')
                                    <dt class="col-sm-4">@lang('admin.request.distance_price') :</dt>
                                    @if($request->is_round)
                                        <dd class="col-sm-8">{{ currency(($request->payment->per_kilometer * $request->payment->total_kilometer) * 2) }}</dd>
                                    @else
                                        <dd class="col-sm-8">{{ currency(($request->payment->per_kilometer * $request->payment->total_kilometer) ) }}</dd>
                                    @endif
                                @endif
                                <dt class="col-sm-4">@lang('admin.request.commission') :</dt>
                                <dd class="col-sm-8">{{ currency($request->payment->commision ) }}</dd>

                                <dt class="col-sm-4">@lang('admin.request.fleet_commission') :</dt>
                                <dd class="col-sm-8">{{ currency($request->payment->fleet) }}</dd>

                                <dt class="col-sm-4">Waiting Charges :</dt>
{{--                                <dt class="col-sm-4">Admin Fee :</dt>--}}
                                <dd class="col-sm-8">{{ currency($request->payment->waiting_charges) }}</dd>

                                <dt class="col-sm-4">Admin Fee :</dt>
                                <dd class="col-sm-8">{{ currency($request->payment->admin_fee) }}</dd>

                                <dt class="col-sm-4">Cab Number :</dt>
                                <dd class="col-sm-8">@if ($request->provider )
                                    {{$request->provider->service->service_number}}
                                @endif</dd>


                                <dt class="col-sm-4">@lang('admin.request.discount_price') :</dt>
                                <dd class="col-sm-8">{{ currency($request->payment->discount) }}</dd>

                                <dt class="col-sm-4">@lang('admin.request.tax_price') :</dt>
                                <dd class="col-sm-8">{{ currency($request->payment->tax) }}</dd>

                                <dt class="col-sm-4">@lang('admin.request.surge_price') :</dt>
                                <dd class="col-sm-8">{{ currency($request->payment->surge) }}</dd>

                                <dt class="col-sm-4">@lang('admin.request.tips') :</dt>
                                <dd class="col-sm-8">{{ currency($request->payment->tips) }}</dd>
                                <dt class="col-sm-4">@lang('admin.request.total_amount') :</dt>

                                @if($setting->value = 'Flat' && $request->request_type == 'ride')
                                    @php $totalAmount = currency($request->payment->flat_rate+$request->payment->tips) @endphp
                                @elseif($setting->value != 'Flat' && $request->request_type == 'ride')
                                    @php $totalAmount = currency($request->payment->total+$request->payment->tips) @endphp
                                @elseif($request->request_type == 'dispatch')
                                    @php $totalAmount = ($request->dispatcher_payments['eta'] + $request->dispatcher_payments['extraAmount']) - $request->dispatcher_payments['discount'] @endphp
                                @endif
                                <dd class="col-sm-8">{{$totalAmount}}</dd>
                                <dt class="col-sm-4">@lang('admin.request.wallet_deduction') :</dt>
                                <dd class="col-sm-8">{{ currency($request->payment->wallet) }}</dd>

                            <!-- <dt class="col-sm-4">@lang('admin.request.paid_amount') :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->payable) }}</dd> -->

                                <dt class="col-sm-4">@lang('admin.request.Payment_Mode') :</dt>
                                <dd class="col-sm-8">{{ $request->payment->payment_mode }}</dd>
                                <dt class="col-sm-4">@lang('admin.request.cash_amount') :</dt>
                                @if($request->request_type == 'ride')
                                    @if($setting->value = 'Flat')
                                        @php $cashAmount = currency($request->payment->flat_rate) @endphp
                                    @else
                                        @if($request->payment->payment_mode=='CASH')
                                            @php $cashAmount = currency($request->payment->cash) @endphp
                                        @else
                                            @php $cashAmount = currency($request->payment->card) @endphp
                                        @endif
                                    @endif
                                @else
                                    @php $cashAmount = ($request->dispatcher_payments['eta'] + $request->dispatcher_payments['extraAmount']) - 
                                    $request->dispatcher_payments['discount']  @endphp
                                @endif

                                <dd class="col-sm-8">{{ $cashAmount }}</dd>
                                <dt class="col-sm-4">@lang('admin.request.provider_earnings'):</dt>
                                @if($request->request_type == 'ride')
                                    <dd class="col-sm-8">{{ currency($request->payment->provider_pay) }}</dd>
                                @else
                                    <dd class="col-sm-8">{{ $totalAmount - ($request->payment->commision + $request->payment->admin_fee) }}</dd>
                                @endif

                            <!--  <dt class="col-sm-4">Provider Admin Commission :</dt>
                        <dd class="col-sm-8">{{ currency($request->payment->provider_commission) }}</dd> -->
                            @endif

                            <dt class="col-sm-4">@lang('admin.request.ride_status') :</dt>
                            <dd class="col-sm-8">
                                {{ $request->status }}
                            </dd>

                        </dl>
                    </div>
                    <div class="col-md-6">
                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style type="text/css">
        #map {
            height: 450px;
        }
    </style>
@endsection

@section('scripts')
    <script type="text/javascript">
        var map;
        var zoomLevel = 11;

        function initMap() {

            map = new google.maps.Map(document.getElementById('map'));

            var marker = new google.maps.Marker({
                map: map,
                icon: '/asset/img/marker-start.png',
                anchorPoint: new google.maps.Point(0, -29)
            });

            var markerSecond = new google.maps.Marker({
                map: map,
                icon: '/asset/img/marker-end.png',
                anchorPoint: new google.maps.Point(0, -29)
            });

            var bounds = new google.maps.LatLngBounds();

            source = new google.maps.LatLng({{ $request->s_latitude }}, {{ $request->s_longitude }});
            marker.setPosition(source);
            @foreach($request->stops as $stop)
                destination = new google.maps.LatLng({{ $stop->d_latitude }}, {{ $stop->d_longitude }});
                markerSecond.setPosition(destination);
            @endforeach

            var directionsService = new google.maps.DirectionsService;
            var directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true, preserveViewport: true});
            directionsDisplay.setMap(map);

            directionsService.route({
                origin: source,
                destination: destination,
                travelMode: google.maps.TravelMode.DRIVING
            }, function (result, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    console.log(result);
                    directionsDisplay.setDirections(result);

                    marker.setPosition(result.routes[0].legs[0].start_location);
                    @foreach($request->stops as $stop)
                        markerSecond.setPosition(result.routes[0].legs[0].end_location);
                    @endforeach
                }
            });

                    @if($request->provider && $request->status != 'COMPLETED')
            var markerProvider = new google.maps.Marker({
                    map: map,
                    icon: "/asset/img/marker-car.png",
                    anchorPoint: new google.maps.Point(0, -29)
                });

            provider = new google.maps.LatLng({{ $request->provider->latitude }}, {{ $request->provider->longitude }});
            markerProvider.setVisible(true);
            markerProvider.setPosition(provider);
            console.log('Provider Bounds', markerProvider.getPosition());
            bounds.extend(markerProvider.getPosition());
            @endif

            bounds.extend(marker.getPosition());
            bounds.extend(markerSecond.getPosition());
            map.fitBounds(bounds);
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ Setting::get('map_key') }}&libraries=places&callback=initMap"
            async defer></script>
@endsection