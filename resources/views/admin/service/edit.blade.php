@extends('admin.layout.base')

@section('title', 'Update Service Type ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <a href="{{ route('admin.service.index') }}" class="btn btn-default pull-right"><i
                            class="fa fa-angle-left"></i> @lang('admin.back')</a>

                <h5 style="margin-bottom: 2em;">@lang('admin.service.Update_Service_Type')</h5>

                <form class="form-horizontal" action="{{route('admin.service.update', $service->id )}}" method="POST"
                      enctype="multipart/form-data" role="form">
                    {{csrf_field()}}
                    <input type="hidden" name="_method" value="PATCH">
                    <div class="form-group row">
                        <label for="name" class="col-xs-2 col-form-label">@lang('admin.service.Service_Name')</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="text" value="{{ $service->name }}" name="name" required
                                   id="name" placeholder="Service Name">
                        </div>
                    </div>

                <!--  <div class="form-group row">
                    <label for="provider_name" class="col-xs-2 col-form-label">@lang('admin.service.Provider_Name')</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->provider_name }}" name="provider_name" required id="provider_name" placeholder="Provider Name">
                    </div>
                </div> -->

                    <div class="form-group row">

                        <label for="image" class="col-xs-2 col-form-label">@lang('admin.picture')</label>
                        <div class="col-xs-10">
                            @if(isset($service->image))
                                <img style="height: 90px; margin-bottom: 15px; border-radius:2em;"
                                     src="{{ $service->image }}">
                            @endif
                            <input type="file" accept="image/*" name="image" class="dropify form-control-file"
                                   id="image" aria-describedby="fileHelp">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="calculation_format"
                               class="col-xs-2 col-form-label">Pricing Logic</label>
                        <div class="col-xs-5">
                            <select class="form-control" id="calculator" name="calculator">
                                @foreach(\App\ServiceType::CALCULATORS as $key=>$value)
                                    <option value="{{$key}}" @if($key == $service->calculator) selected @endif>{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="calculation_format"
                               class="col-xs-2 col-form-label">@lang('admin.service.Calculation_format')</label>
                        <div class="col-xs-5">
                            <select class="form-control" id="calculation_format" name="calculation_format">
                                <option value="TYPEA" @if($service->calculation_format =='TYPEA') selected @endif>
                                    TYPEA
                                </option>
                                <option value="TYPEB" @if($service->calculation_format =='TYPEB') selected @endif>
                                    TYPEB
                                </option>
                                <option value="TYPEC" @if($service->calculation_format =='TYPEC') selected @endif>
                                    TYPEC
                                </option>
                            </select>
                        </div>
                        <div class="col-xs-5">
                            <span class="showcal"><i><b>Calculation Format: <span
                                                id="changecalformat"></span></b></i></span>
                        </div>
                    </div>

                    <!-- cancellation charges -->
                    <div class="form-group row">
                        <label for="cancellation_charges"
                               class="col-xs-2 col-form-label">@lang('admin.service.cancellation_charges')</label>
                        <div class="col-xs-5">
                            <input class="form-control" type="number" value="{{ $service->cancellation_charges }}" name="cancellation_charges"  id="cancellation_charges" placeholder="@lang('admin.service.cancellation_charges')" min="0">
                        </div>
                        <div class="col-xs-5">
                        </div>
                    </div>

{{--                    <div class="form-group row" style="display: none;">--}}
{{--                        <label for="calculator"--}}
{{--                               class="col-xs-2 col-form-label">@lang('admin.service.Pricing_Logic')</label>--}}
{{--                        <div class="col-xs-5">--}}
{{--                            <select class="form-control" id="calculator" name="calculator">--}}
{{--                                <option value="MIN" @if($service->calculator =='MIN') selected @endif>--}}
{{--                                    @lang('servicetypes.MIN')--}}
{{--                                </option>--}}
{{--                                <option value="HOUR" @if($service->calculator =='HOUR') selected @endif>--}}
{{--                                    @lang('servicetypes.HOUR')--}}
{{--                                </option>--}}
{{--                                <option value="DISTANCE" @if($service->calculator =='DISTANCE') selected @endif>--}}
{{--                                    @lang('servicetypes.DISTANCE')--}}
{{--                                </option>--}}
{{--                                <option value="DISTANCEMIN" @if($service->calculator =='DISTANCEMIN') selected @endif>--}}
{{--                                    @lang('servicetypes.DISTANCEMIN')--}}
{{--                                </option>--}}
{{--                                <option value="DISTANCEHOUR" @if($service->calculator =='DISTANCEHOUR') selected @endif>--}}
{{--                                    @lang('servicetypes.DISTANCEHOUR')--}}
{{--                                </option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                        <div class="col-xs-5">--}}
{{--                            <span class="showcal"><i><b>Price Calculation: <span id="changecal"></span></b></i></span>--}}
{{--                        </div>--}}
{{--                    </div>--}}

                    {{--<div class="form-group row" >
                        <label for="fixed" class="col-xs-2 col-form-label">@lang('admin.service.hourly_Price') ({{ currency('') }})</label>
                        <div class="col-xs-5">
                            <input class="form-control" type="number" value="{{ $service->hour }}" name="hour" id="hourly_price" placeholder="Set Hour Price (Only for DISTANCEHOUR)" min="0">
                        </div>
                        <div class="col-xs-5">
                            <span class="showcal"><i><b>PH (@lang('admin.service.per_hour')), TH (@lang('admin.service.total_hour'))</b></i></span>
                        </div>
                    </div>--}}

                    <div class="form-group row">
                        <label for="fixed" class="col-xs-2 col-form-label">@lang('admin.service.Base_Price') ({{
                            currency('') }})</label>
                        <div class="col-xs-5">
                            <input class="form-control" type="text" value="{{ $service->fixed }}" name="fixed" required
                                   id="fixed" placeholder="Base Price"
                                   onkeypress="if ( isNaN(this.value + String.fromCharCode(event.keyCode) )) return false"
                                   ;>
                        </div>
                        <div class="col-xs-5">
                            <span class="showcal"><i><b>BP (@lang('admin.service.Base_Price'))</b></i></span>
                        </div>
                    </div>


                    <div class="form-group row">
                        <label for="charges_per_min"
                               class="col-xs-2 col-form-label">@lang('admin.service.charges_per_min')
                            ({{currency('') }})</label>
                        <div class="col-xs-5">
                            <input class="form-control" type="text" value="{{ $service->charges_per_min }}"
                                   name="charges_per_min"
                                   required
                                   id="charges_per_min" placeholder="@lang('admin.service.charges_per_min')"
                                   onkeypress="if ( isNaN(this.value + String.fromCharCode(event.keyCode) )) return false"
                                   ;>
                        </div>

                    </div>


                    <!-- Type A distance price Cal -->
                    <div class="form-group row typea_cal" style="display: none;">
                        <label for="between_km" class="col-xs-12 col-form-label">@lang('admin.service.distance_price')
                            ({{ distance() }})</label>
                        <div class="col-xs-3">
                            <input class="form-control" type="number"
                                   value="{{ $service->between_km ? $service->between_km :'' }}" name="between_km"
                                   required id="between_km" placeholder="Between Kms" min="0">
                        </div>
                        <div class="col-xs-3">
                            <input class="form-control" type="text"
                                   value="{{ $service->less_distance_price ? $service->less_distance_price :'' }}"
                                   name="less_distance_price" required id="less_distance_price"
                                   placeholder="Less Distance Price (1 KM)"
                                   onkeypress="if ( isNaN(this.value + String.fromCharCode(event.keyCode) )) return false"
                                   ;>
                        </div>
                        <div class="col-xs-3">
                            <input class="form-control" type="text"
                                   value="{{ $service->greater_distance_price ? $service->greater_distance_price :'' }}"
                                   name="greater_distance_price" required id="greater_distance_price"
                                   placeholder="Greater Distance Price (1 KM)"
                                   onkeypress="if ( isNaN(this.value + String.fromCharCode(event.keyCode) )) return false"
                                   ;>
                        </div>
                        <div class="col-xs-3">
                            <span class="showcal"><i><b>Between Kms(BK), LDP(Less Distance price), GDP(Greater Distance price)</b></i></span>
                        </div>
                    </div>

                    {{--<div class="form-group row">
                        <label for="distance" class="col-xs-2 col-form-label">@lang('admin.service.Base_Distance') ({{ distance('') }})</label>
                        <div class="col-xs-5">
                            <input class="form-control" type="number" value="{{ $service->distance }}" name="distance" id="distance" placeholder="Base Distance" min="0">
                        </div>
                        <div class="col-xs-5">
                            <span class="showcal"><i><b>BD (@lang('admin.service.Base_Distance')) </b></i></span>
                        </div>
                    </div>--}}

                    {{--<div class="form-group row">
                        <label for="minute" class="col-xs-2 col-form-label">@lang('admin.service.unit_time') ({{ currency() }})</label>
                        <div class="col-xs-5">
                            <input class="form-control" type="number" value="{{ $service->minute }}" name="minute" id="minute" placeholder="Unit Time Pricing" min="0">
                        </div>
                        <div class="col-xs-5">
                            <span class="showcal"><i><b>PM (@lang('admin.service.per_minute')), TM(@lang('admin.service.total_minute'))</b></i></span>
                        </div>
                    </div>--}}

                    <div class="form-group row price">
                        <label for="price" class="col-xs-2 col-form-label">@lang('admin.service.unit') ({{ distance()
                            }})</label>
                        <div class="col-xs-5">
                            <input class="form-control" type="text" value="{{ $service->price }}" name="price"
                                   id="price" placeholder="Unit Distance Price"
                                   onkeypress="if ( isNaN(this.value + String.fromCharCode(event.keyCode) )) return false"
                                   ;>
                        </div>
                        <div class="col-xs-5">
                            <span class="showcal"><i><b>P{{Setting::get('distance')}} (@lang('admin.service.per') {{Setting::get('distance')}}), T{{Setting::get('distance')}} (@lang('admin.service.total') {{Setting::get('distance')}})</b></i></span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="capacity"
                               class="col-xs-2 col-form-label">@lang('admin.service.Seat_Capacity')</label>
                        <div class="col-xs-5">
                            <input class="form-control" type="number" value="{{ $service->capacity }}" name="capacity"
                                   required id="capacity" placeholder="Seat Capacity" min="1">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="capacity"
                               class="col-xs-2 col-form-label">Admin Fee</label>
                        <div class="col-xs-5">
                            <input class="form-control" type="text" value="{{ $service->admin_fee }}" name="adminFee"
                                   required id="capacity" placeholder="Admin Fee" min="1">
                        </div>
                    </div>


                    <div class="form-group row">
                        <div class="col-xs-12 col-sm-6 col-md-3">
                            <a href="{{route('admin.service.index')}}"
                               class="btn btn-danger btn-block">@lang('admin.cancel')</a>
                        </div>
                        <div class="col-xs-12 col-sm-6 offset-md-6 col-md-3">
                            <button type="submit"
                                    class="btn btn-primary btn-block">@lang('admin.service.Update_Service_Type')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        var cal = 'DISTANCE';
        priceInputs('{{$service->calculation_format}}');
        $("#calculation_format").on('change', function () {
            cal = $(this).val();
            priceInputs(cal);
        });

        function priceInputs(cal) {

            if (cal == 'TYPEA') {
                $("#price").prop('required', false);
                $("#between_km").prop('required', true);
                $("#less_distance_price").prop('required', true);
                $("#greater_distance_price").prop('required', true);
                $("#changecalformat").text('BP + (TKms * (LDP || GDP))');
                $(".typea_cal").show();
                $('.price').hide();
            } else if (cal == 'TYPEB') {
                $("#price").prop('required', true);
                $("#between_km").prop('required', false);
                $("#less_distance_price").prop('required', false);
                $("#greater_distance_price").prop('required', false);
                $("#changecalformat").text('BP + (TKms * PKms_price)');
                $(".typea_cal").hide();
                $('.price').show();
            } else if (cal == 'TYPEC') {
                $("#price").prop('required', false);
                $("#between_km").prop('required', false);
                $("#less_distance_price").prop('required', false);
                $("#greater_distance_price").prop('required', false);
                $("#changecalformat").text('BP Only');
                $(".typea_cal").hide();
                $('.price').hide();
            }
            // if(cal=='MIN'){
            //     $("#hourly_price,#distance,#price").attr('value','');
            //     $("#minute").prop('disabled', false);
            //     $("#minute").prop('required', true);
            //     $("#hourly_price,#distance,#price").prop('disabled', true);
            //     $("#hourly_price,#distance,#price").prop('required', false);
            //     $("#changecal").text('BP + (TM*PM)');
            // }
            // else if(cal=='HOUR'){
            //     $("#minute,#distance,#price").attr('value','');
            //     $("#hourly_price").prop('disabled', false);
            //     $("#hourly_price").prop('required', true);
            //     $("#minute,#distance,#price").prop('disabled', true);
            //     $("#minute,#distance,#price").prop('required', false);
            //     $("#changecal").text('BP + (TH*PH)');
            // }
            // else if(cal=='DISTANCE'){
            //     $("#minute,#hourly_price").attr('value','');
            //     $("#price,#distance").prop('disabled', false);
            //     $("#price,#distance").prop('required', true);
            //     $("#minute,#hourly_price").prop('disabled', true);
            //     $("#minute,#hourly_price").prop('required', false);
            //     $("#changecal").text('BP + (T{{Setting::get("distance")}}-BD*P{{Setting::get("distance")}})');
            // }
            // else if(cal=='DISTANCEMIN'){
            //     $("#hourly_price").attr('value','');
            //     $("#price,#distance,#minute").prop('disabled', false);
            //     $("#price,#distance,#minute").prop('required', true);
            //     $("#hourly_price").prop('disabled', true);
            //     $("#hourly_price").prop('required', false);
            //     $("#changecal").text('BP + (T{{Setting::get("distance")}}-BD*P{{Setting::get("distance")}}) + (TM*PM)');
            // }
            // else if(cal=='DISTANCEHOUR'){
            //     $("#minute").attr('value','');
            //     $("#price,#distance,#hourly_price").prop('disabled', false);
            //     $("#price,#distance,#hourly_price").prop('required', true);
            //     $("#minute").prop('disabled', true);
            //     $("#minute").prop('required', false);
            //     $("#changecal").text('BP + ((T{{Setting::get("distance")}}-BD)*P{{Setting::get("distance")}}) + (TH*PH)');
            // }
            // else{
            //     $("#minute,#hourly_price").attr('value','');
            //     $("#price,#distance").prop('disabled', false);
            //     $("#price,#distance").prop('required', true);
            //     $("#minute,#hourly_price").prop('disabled', true);
            //     $("#minute,#hourly_price").prop('required', false);
            //     $("#changecal").text('BP + (T{{Setting::get("distance")}}-BD*P{{Setting::get("distance")}})');
            // }
        }

    </script>
@endsection