@extends('admin.layout.base')

@section('title', 'Payment Settings ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <form action="{{route('admin.settings.payment.store')}}" method="POST">
                {{csrf_field()}}
                <h5>@lang('admin.payment.payment_modes')</h5>
                <div class="card card-block card-inverse card-primary">
                    <blockquote class="card-blockquote">
                        <i class="fa fa-3x fa-cc-stripe pull-right"></i>
                        <div class="form-group row">
                            <div class="col-xs-4 arabic_right">
                                <label for="stripe_secret_key" class="col-form-label">
                                    @lang('admin.payment.card_payments')
                                </label>
                            </div>
                            <div class="col-xs-6">
                                <input @if(Setting::get('CARD') == 1) checked  @endif  name="CARD" id="stripe_check" onchange="cardselect()" type="checkbox" class="js-switch" data-color="#43b968">
                            </div>
                        </div>
                        <div id="card_field" @if(Setting::get('CARD') == 0) style="display: none;" @endif>
                            <div class="form-group row">
                                <label for="stripe_secret_key" class="col-xs-4 col-form-label">@lang('admin.payment.stripe_secret_key')</label>
                                <div class="col-xs-8">
                                    <input class="form-control" type="text" value="{{Setting::get('stripe_secret_key', '') }}" name="stripe_secret_key" id="stripe_secret_key"  placeholder="Stripe Secret key">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="stripe_publishable_key" class="col-xs-4 col-form-label">@lang('admin.payment.stripe_publishable_key')</label>
                                <div class="col-xs-8">
                                    <input class="form-control" type="text" value="{{Setting::get('stripe_publishable_key', '') }}" name="stripe_publishable_key" id="stripe_publishable_key"  placeholder="Stripe Publishable key">
                                </div>
                            </div>
                            <!-- <div class="form-group row">
                                <label for="stripe_oauth_url" class="col-xs-4 col-form-label">Stripe Oauth Url</label>
                                <div class="col-xs-8">
                                    <input class="form-control" type="text" value="{{Setting::get('stripe_oauth_url', '') }}" name="stripe_oauth_url" id="stripe_oauth_url"  placeholder="Stripe Oauth Url">
                                </div>
                            </div> -->
                        </div>
                    </blockquote>
                </div>

                <div class="card card-block card-inverse card-primary">
                    <blockquote class="card-blockquote">
                        <i class="fa fa-3x fa-money pull-right"></i>
                        <div class="form-group row">
                            <div class="col-xs-4 arabic_right">
                                <label for="cash-payments" class="col-form-label">
                                   @lang('admin.payment.cash_payments') 
                                </label>
                            </div>
                            <div class="col-xs-6">
                                <input @if(Setting::get('CASH') == 1) checked  @endif name="CASH" id="cash-payments" onchange="cardselect()" type="checkbox" class="js-switch" data-color="#43b968">
                            </div>
                        </div>
                    </blockquote>
                </div>

               <!--   <div class="card card-block card-inverse card-primary">
                    <blockquote class="payu-blockquote">
                        
                        <div class="form-group row">
                            <div class="col-xs-4 arabic_right">
                                <label for="payu_merchant_id" class="col-form-label" style="color: white">
                                    @lang('admin.payment.converge_elavon')
                                </label>
                            </div>
                            <div class="col-xs-6">
                                <input @if(Setting::get('ELAVON') == 1) checked  @endif  name="ELAVON" id="elavon_check" onchange="elavonselect()" type="checkbox" class="js-switch" data-color="#43b968">
                            </div>
                            <div class="col-xs-2 braintree_icon pull-right">
                                <img src="{{asset('asset/img/elavon.jpg')}}" style="width:100px;height:70px;">
                            </div>
                        </div>

                        <div id="elavon_field" @if(Setting::get('ELAVON') == 0) style="display: none;" @endif>
                            <div class="form-group row">
                                <label for="elavon_merchant_id" class="col-xs-4 col-form-label" style="color: white">Merchant ID</label>
                                <div class="col-xs-8">
                                    <input class="form-control" type="text" value="{{Setting::get('elavon_merchant_id', '') }}" name="elavon_merchant_id" id="elavon_merchant_id"  placeholder="Merchant ID">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="elavon_user_id" class="col-xs-4 col-form-label" style="color: white">Merchant User ID</label>
                                <div class="col-xs-8">
                                    <input class="form-control" type="text" value="{{Setting::get('elavon_user_id', '') }}" name="elavon_user_id" id="elavon_user_id"  placeholder="User ID">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="elavon_pin" class="col-xs-4 col-form-label" style="color: white">Merchant PIN</label>
                                <div class="col-xs-8">
                                    <input class="form-control" type="text" value="{{Setting::get('elavon_pin', '') }}" name="elavon_pin" id="elavon_pin"  placeholder="PIN">
                                </div>
                            </div>                           
                            <div class="form-group row">
                                <label for="elavon_mode" class="col-xs-4 col-form-label" style="color: white">ELAVON Mode</label>
                                <div class="col-xs-8">
                                    
                                     <select name="elavon_mode" class="form-control" required>
                                          <option @if(Setting::get('elavon_mode') == "demo") selected @endif value="demo">Demo</option>
                                          <option @if(Setting::get('elavon_mode') == "live") selected @endif value="live">Production</option>
                                    </select>

                                </div>
                            </div>
                        </div>
                    </blockquote>
                </div> -->

                <h5>@lang('admin.payment.payment_settings')</h5>

                <div class="card card-block card-inverse card-info">
                    <blockquote class="card-blockquote">
                        <div class="form-group row">
                            <label for="daily_target" class="col-xs-4 col-form-label">@lang('admin.payment.daily_target')</label>
                            <div class="col-xs-8">
                                <input class="form-control" 
                                    type="number"
                                    value="{{ Setting::get('daily_target', '0')  }}"
                                    id="daily_target"
                                    name="daily_target"
                                    min="0"
                                    required
                                    placeholder="Daily Target">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="tax_percentage" class="col-xs-4 col-form-label">@lang('admin.payment.tax_percentage')</label>
                            <div class="col-xs-8">
                                <input class="form-control"
                                    type="number"
                                    value="{{ Setting::get('tax_percentage', '0')  }}"
                                    id="tax_percentage"
                                    name="tax_percentage"
                                    min="0"
                                    max="100"
                                    placeholder="Tax Percentage">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="surge_trigger" class="col-xs-4 col-form-label">@lang('admin.payment.surge_trigger_point')</label>
                            <div class="col-xs-8">
                                <input class="form-control"
                                    type="number"
                                    value="{{ Setting::get('surge_trigger', '')  }}"
                                    id="surge_trigger"
                                    name="surge_trigger"
                                    min="0"
                                    required
                                    placeholder="Surge Trigger Point">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="surge_percentage" class="col-xs-4 col-form-label">@lang('admin.payment.surge_percentage')</label>
                            <div class="col-xs-8">
                                <input class="form-control"
                                    type="number"
                                    value="{{ Setting::get('surge_percentage', '0')  }}"
                                    id="surge_percentage"
                                    name="surge_percentage"
                                    min="0"
                                    max="100"
                                    placeholder="Surge percentage">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="commission_percentage" class="col-xs-4 col-form-label">@lang('admin.payment.commission_percentage')</label>
                            <div class="col-xs-8">
                                <input class="form-control"
                                    type="number"
                                    value="{{ Setting::get('commission_percentage', '0') }}"
                                    id="commission_percentage"
                                    name="commission_percentage"
                                    min="0"
                                    max="100"
                                    placeholder="Commission percentage">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="fleet_commission_percentage" class="col-xs-4 col-form-label">@lang('admin.payment.fleet_commission_percentage') <span style="color:red">(It will work if admin commission 0%) </span> </label>
                            <div class="col-xs-8">
                                <input class="form-control"
                                    type="number"
                                    value="{{ Setting::get('fleet_commission_percentage','0') }}"
                                    id="fleet_commission_percentage"
                                    name="fleet_commission_percentage"
                                    min="0"
                                    max="100"
                                    placeholder="Fleet Commission Percentage">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="booking_prefix" class="col-xs-4 col-form-label">@lang('admin.payment.booking_id_prefix')</label>
                            <div class="col-xs-8">
                                <input class="form-control"
                                    type="text"
                                    value="{{ Setting::get('booking_prefix', '0') }}"
                                    id="booking_prefix"
                                    name="booking_prefix"
                                    min="0"
                                    max="4"
                                    placeholder="Booking ID Prefix">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="base_price" class="col-xs-4 col-form-label">@lang('admin.payment.currency')
                                 ( <strong>{{ Setting::get('currency', '$')  }} </strong>)
                            </label>
                            <div class="col-xs-8">
                                <select name="currency" class="form-control" required>
                                    

                                    <option @if(Setting::get('currency') == "C$") selected @endif value="C$">Canadian Dollar (CAD)</option>
                                    <option @if(Setting::get('currency') == "$") selected @endif value="$">US Dollar (USD)</option>
                                    <option @if(Setting::get('currency') == "Rs") selected @endif value="Rs">Pakistani Rupee (Rs)</option>
                                    <option @if(Setting::get('currency') == "₹") selected @endif value="₹"> Indian Rupee (INR)</option>
                                    <option @if(Setting::get('currency') == "د.ك") selected @endif value="د.ك">Kuwaiti Dinar (KWD)</option>
                                    <option @if(Setting::get('currency') == "د.ب") selected @endif value="د.ب">Bahraini Dinar (BHD)</option>
                                    <option @if(Setting::get('currency') == "﷼") selected @endif value="﷼">Omani Rial (OMR)</option>
                                    <option @if(Setting::get('currency') == "£") selected @endif value="£">British Pound (GBP)</option>
                                    <option @if(Setting::get('currency') == "€") selected @endif value="€">Euro (EUR)</option>
                                    <option @if(Setting::get('currency') == "CHF") selected @endif value="CHF">Swiss Franc (CHF)</option>
                                    <option @if(Setting::get('currency') == "ل.د") selected @endif value="ل.د">Libyan Dinar (LYD)</option>
                                    <option @if(Setting::get('currency') == "B$") selected @endif value="B$">Bruneian Dollar (BND)</option>
                                    <option @if(Setting::get('currency') == "S$") selected @endif value="S$">Singapore Dollar (SGD)</option>
                                    <option @if(Setting::get('currency') == "AU$") selected @endif value="AU$"> Australian Dollar (AUD)</option>
                                    
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="user_negative_wallet_limit" class="col-xs-4 col-form-label">@lang('admin.payment.user_negative_wallet_limit')</label>
                            <div class="col-xs-8">
                                <input class="form-control"
                                    type="text"
                                    value="{{ Setting::get('user_negative_wallet_limit', '0') }}"
                                    id="user_negative_wallet_limit"
                                    name="user_negative_wallet_limit"
                                    min="0"
                                    max="4"
                                    placeholder="Booking ID Prefix">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="ride_cancellation_minutes" class="col-xs-4 col-form-label">@lang('admin.payment.ride_cancellation_minutes')</label>
                            <div class="col-xs-8">
                                <input class="form-control"
                                    type="text"
                                    value="{{ Setting::get('ride_cancellation_minutes', '0') }}"
                                    id="ride_cancellation_minutes"
                                    name="ride_cancellation_minutes"
                                    min="0"
                                    max="4"
                                    placeholder="Booking ID Prefix">
                            </div>
                        </div>
                    </blockquote>
                </div>

                <div class="form-group row">
                    <div class="col-xs-4">
                        <a href="{{ route('admin.index') }}" class="btn btn-warning btn-block">@lang('admin.back')</a>
                    </div>
                    <div class="offset-xs-4 col-xs-4">
                        <button type="submit" class="btn btn-primary btn-block">@lang('admin.payment.update_site_settings')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
function cardselect()
{
    if($('#stripe_check').is(":checked")) {
        $("#card_field").fadeIn(700);
    } else {
        $("#card_field").fadeOut(700);
    }
}

function elavonselect()
{
    if($('#elavon_check').is(":checked")) {
        $("#elavon_field").fadeIn(700);
    } else {
        $("#elavon_field").fadeOut(700);
    }
}

$(function() {
    var ad_com="{{ Setting::get('commission_percentage') }}";   
    if(ad_com>0){        
        $("#fleet_commission_percentage").val(0);
        $("#fleet_commission_percentage").prop('disabled', true);
        $("#fleet_commission_percentage").prop('required', false);       
    }
    else{
        $("#fleet_commission_percentage").prop('required', true);
    }
    $("#commission_percentage").on('keyup', function(){
        var ad_ins=parseFloat($(this).val());
        console.log(ad_ins);
        if(ad_ins>0){
            $("#fleet_commission_percentage").val(0);
            $("#fleet_commission_percentage").prop('disabled', true);
            $("#fleet_commission_percentage").prop('required', false);
        }
        else{
            $("#fleet_commission_percentage").val('');
            $("#fleet_commission_percentage").prop('disabled', false);
            $("#fleet_commission_percentage").prop('required', true);
        }
        
    });
});    
</script>
@endsection