@extends('corporate.layout.base')

@section('title', 'Recharge ')

@section('content') 
<style type="text/css"> 
 .pagination {
    display: inline-flex;
    border-radius: .25rem;
}
    .pagination li {
    border: 1px solid #f0f1f2;
    width: 25px;
    height: 25px;
    text-align: center;
    list-style: none;
}
</style> 

<!-- Recharge Wallet Model -->
    <div class="modal fade" id="myModalRechargeWallet" role="dialog">
        <div class="modal-dialog modal-sm">
        <form class="form-control" action="{{route('corporate.prepaid.recharge')}}" method="POST">
        {{csrf_field()}}
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Recharge your Wallet</h4>
            </div>  
            <div class="modal-body">
                <input type="number" name="amount" class="form-control" placeholder="Enter Amount">
                <input type="hidden" name="card_id" value="{{$default_card? $default_card->card_id : '' }}">
            </div>
            <div class="modal-footer">
            <button type="submit" style="margin-left: 1em;" class="btn btn-info btn-md">Recharge</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
          </div>
        </form>
        </div>
  </div>
@if(empty($default_card) && (count($cards) !=0))
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>
        Please active any one card.!
    </div>
@elseif(count($cards) ==0) 
    <div class="alert alert-success">
        <button type="button" class="close" data-dismiss="alert">×</button>
        Please Add card.!
    </div>
@endif
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1"> Card Details </h5> 
            <div class="col-md-12">
                <div class="col-md-6"> </div>
                <div class="col-md-6">  
                   @if(Setting::get('CARD') == 1) 
                    <a href="#" class="sub-right pull-right btn btn-info" data-toggle="modal" data-target="#add-card-modal">@lang('user.card.add_card')</a>&nbsp;&nbsp;&nbsp;&nbsp; 
                    @endif
                    @if(Auth::user()->recharge_option == 'PREPAID' && $default_card) 
                        <a href="#" class="sub-right pull-right btn btn-primary" data-toggle="modal" data-target="#myModalRechargeWallet">@lang('user.card.Recharge')</a> 
                    @else
                        @if(Auth::user()->recharge_option == 'PREPAID')
                            <button disabled class="sub-right pull-right btn btn-primary">@lang('user.card.Recharge')</button>             
                        @endif
                    @endif
                </div>   
           
            </div>
           <br><br>
            <table class="table table-striped table-bordered dataTable" id="cus-table-3">
                <thead>
                    <tr> 
                        <th>@lang('admin.id')</th>  
                        <th>Last Four Digit</th>
                        <th>Brand</th>  
                        <th>Status</th>  
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody> 
                    @foreach($cards as $index => $card)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>****{{$card->last_four}}</td>
                        <td>{{ $card->brand }}</td> 
                        <td><span @if($card->is_default==0) style="background-color: greenyellow;" @else style="background-color: #c4e88b;" @endif>{{ ($card->is_default==0) ? 'NoDefault' : 'Default' }}</span></td> 
                        <td>
                            <form action="{{ route('corporate.card.delete', $card->id) }}" method="POST"> 
                                {{csrf_field()}} 
                                @if($card->is_default ==0 )
                                <a href="{{ route('corporate.card.default', $card->id) }}?default=1" class="btn btn-default">Active</a>
                                @else
                                <a href="{{ route('corporate.card.default', $card->id) }}?default=0" class="btn btn-success">Deactive</a>
                                @endif
                                <button class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> @lang('admin.delete')</button> 
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody> 
                <tfoot>
                    <tr>
                        <th>@lang('admin.id')</th>  
                        <th>Last Four Digit</th>
                        <th>Brand</th>  
                        <th>Status</th>  
                        <th>@lang('admin.action')</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
                </div>
                @endif
            <h5 class="mb-1"> Payment Transaction
                @if(Setting::get('demo_mode', 0) == 1)
                <span class="pull-right">(*personal information hidden in demo)</span>
                @endif
            </h5>   
            <table class="table table-striped table-bordered dataTable" id="cus-table-3">
                <thead>
                    <tr> 
                        <th>@lang('admin.corporate.Ride_count')</th> 
                        <!-- <th>@if($corporate->recharge_option == 'POSTPAID') @lang('admin.limit_amount') @else @lang('admin.deposit_amount') @endif</th> -->
                        <th>@lang('admin.recharge_option')</th>
                        <!-- <th>@lang('admin.Wallet_balance')</th> -->
                        <!-- <th>@if($corporate->recharge_option == 'POSTPAID') @lang('admin.remaining_amount') @else @lang('admin.pending_amount') @endif</th> -->
                        <th>@lang('admin.total_riding_amount')</th>  
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody> 
                    @php
                    $user_ids = \App\User::wherecorporate_id($corporate->id)->pluck('id');
                    @endphp
                    <tr>  
                        <td>{{ count(\App\UserRequests::whereIn('user_id',$user_ids)->wherepostpaid_payment_status('NOTPAID')->wherestatus('COMPLETED')->pluck('id')) }}</td> 
                        <!-- <td>{{ ($corporate->recharge_option == 'POSTPAID') ? $corporate->limit_amount : $corporate->deposit_amount }}</td> -->

                        <td>{{ $corporate->recharge_option }}</td>

                        <!-- <td>{{ currency($corporate->wallet_balance) }}</td>  -->
                        <!-- <td>
                        @php  
                        if($corporate->recharge_option == 'PREPAID'){
                            $user_request_ids = \App\UserRequests::whereIn('user_id',$user_ids)->wherepostpaid_payment_status('PAID')->wherestatus('COMPLETED')->pluck('id');
                            $limit_deposit_amount = $corporate->deposit_amount; 
                        }
                        else
                        {
                            $user_request_ids = \App\UserRequests::whereIn('user_id',$user_ids)->wherepostpaid_payment_status('NOTPAID')->wherestatus('COMPLETED')->pluck('id');
                            $limit_deposit_amount = $corporate->limit_amount; 
                        }
                        @endphp 
                        
                        {{ currency($limit_deposit_amount - (\App\UserRequestPayment::whereIn('request_id',$user_request_ids)->sum('total'))) }} 
                        </td> -->
                        <td> 
                        {{ currency(\App\UserRequestPayment::whereIn('request_id',$user_request_ids)->sum('total')) }}</td>
                        <td>
                        @if(\App\UserRequestPayment::whereIn('request_id',$user_request_ids)->sum('total') != 0)
                            @if($default_card && (Auth::user()->recharge_option == 'POSTPAID'))
                            <button type="button" data-toggle="modal" data-target="#myModal" class="btn btn-primary">Pay Now {{ currency(\App\UserRequestPayment::whereIn('request_id',$user_request_ids)->sum('total')) }}</button>
                            @else
                                @if(Auth::user()->recharge_option == 'POSTPAID')
                                    <button disabled class="btn btn-warning">Pay Now {{ currency(\App\UserRequestPayment::whereIn('request_id',$user_request_ids)->sum('total')) }}</button>
                                @else
                                PAID
                                @endif
                            @endif
                        @else
                        Paid
                        @endif
                        </td>  
                    </tr>
                    <!-- Delete Model -->
                    <div class="modal fade" id="myModal" role="dialog">
                        <div class="modal-dialog modal-sm">
                        <form action="{{route('corporate.pay.now')}}" method="POST">
                        {{csrf_field()}}
                        <input type="hidden" name="amount" value="{{\App\UserRequestPayment::whereIn('request_id',$user_request_ids)->sum('total')}}">
                        <input type="hidden" name="card_id" value="{{$default_card? $default_card->card_id : '' }}">
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                              <h4 class="modal-title">Are you sure?</h4>
                            </div> 
                            <div class="modal-footer">
                            <button type="submit" style="margin-left: 1em;" class="btn btn-info btn-md"> Pay Now</button>
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            </div>
                          </div>
                          </form>
                        </div>
                      </div> 
                </tbody>
                <tfoot>
                    <tr> 
                        <th>@lang('admin.corporate.Ride_count')</th> 
                        <!-- </th>@if($corporate->recharge_option == 'POSTPAID') @lang('admin.limit_amount') @else @lang('admin.deposit_amount')  @endif</th> -->
                        <th>@lang('admin.recharge_option')</th>
                        <!-- <th>@lang('admin.Wallet_balance')</th> -->
                        <!-- <th>@if($corporate->recharge_option == 'POSTPAID') @lang('admin.remaining_amount') @else @lang('admin.pending_amount') @endif</th> -->
                        <th>@lang('admin.total_riding_amount')</th>  
                        <th>@lang('admin.action')</th>
                    </tr>
                </tfoot>
            </table> 
        </div> 
    </div>
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
                </div>
                @endif
            <h5 class="mb-1"> Transaction History
                @if(Setting::get('demo_mode', 0) == 1)
                <span class="pull-right">(*personal information hidden in demo)</span>
                @endif
            </h5>  
            <table class="table table-striped table-bordered dataTable" id="cus-table-3">
                <thead>
                    <tr> 
                        <th>@lang('admin.id')</th>  
                        <th>Recharge Option</th>
                        <th>Amount</th> 
                        <th>Payment Status</th> 
                        <th>Paid Date</th> 
                    </tr>
                </thead>
                <tbody>
                 <!-- @php($page_plus=Request::get('page') ? Request::get('page') : 10 - 10) -->
                    @foreach($tranasction_history as $index => $history)
                    <tr>
                        <td>{{ $index + 1 + $page_plus }}</td>
                        <td>{{ $history->recharge_option }}</td>
                        <td>{{ $history->amount }}</td>
                        <td>{{ $history->payment_status }}</td>
                        <td>{{ date('d M Y', strtotime($history->created_at) )}}</td> 
                    </tr>
                    @endforeach
                </tbody> 
                <tfoot>
                    <tr>
                        <th>@lang('admin.id')</th>  
                        <th>Recharge Option</th>
                        <th>Amount</th> 
                        <th>Payment Status</th> 
                        <th>Paid Date</th> 
                    </tr>
                </tfoot>
            </table>
            {{--$recharges->appends(['search' => @Request::get('search')])->links()--}} 
        </div> 
    </div>
</div>  
@if(Setting::get('CARD') == 1)
 <div id="add-card-modal" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content" >
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">@lang('user.card.add_card')</h4>
          </div>
            <form id="payment-form" action="{{ route('corporate.stripe.add') }}" method="POST" >
                {{ csrf_field() }}
          <div class="modal-body">
            <div class="row no-margin" id="card-payment">
                <div class="form-group col-md-12 col-sm-12">
                    <label>@lang('user.card.fullname')</label>
                    <input data-stripe="name" autocomplete="off" required type="text" class="form-control" placeholder="@lang('user.card.fullname')">
                </div>
                <div class="form-group col-md-12 col-sm-12">
                    <label>@lang('user.card.card_no')</label>
                    <input data-stripe="number" type="text" onkeypress="return isNumberKey(event);" required autocomplete="off" maxlength="16" class="form-control" placeholder="@lang('user.card.card_no')">
                </div>
                <div class="form-group col-md-4 col-sm-12">
                    <label>@lang('user.card.month')</label>
                    <input type="text" onkeypress="return isNumberKey(event);" maxlength="2" required autocomplete="off" class="form-control" data-stripe="exp-month" placeholder="MM">
                </div>
                <div class="form-group col-md-4 col-sm-12">
                    <label>@lang('user.card.year')</label>
                    <input type="text" onkeypress="return isNumberKey(event);" maxlength="2" required autocomplete="off" data-stripe="exp-year" class="form-control" placeholder="YY">
                </div>
                <div class="form-group col-md-4 col-sm-12">
                    <label>@lang('user.card.cvv')</label>
                    <input type="text" data-stripe="cvc" onkeypress="return isNumberKey(event);" required autocomplete="off" maxlength="4" class="form-control" placeholder="@lang('user.card.cvv')">
                </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-default">@lang('user.card.add_card')</button>
          </div>
        </form>

        </div>

      </div>
    </div>
@endif
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> 
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>

    <script type="text/javascript">
        Stripe.setPublishableKey("{{ Setting::get('stripe_publishable_key')}}");

         var stripeResponseHandler = function (status, response) {
            var $form = $('#payment-form');

            console.log(response);

            if (response.error) {
                // Show the errors on the form
                $form.find('.payment-errors').text(response.error.message);
                $form.find('button').prop('disabled', false);
                alert('error');

            } else {
                // token contains id, last4, and card type
                var token = response.id;
                // Insert the token into the form so it gets submitted to the server
                $form.append($('<input type="hidden" id="stripeToken" name="stripe_token" />').val(token));
                jQuery($form.get(0)).submit();
            }
        };
                
        $('#payment-form').submit(function (e) {
            
            if ($('#stripeToken').length == 0)
            {
                console.log('ok');
                var $form = $(this);
                $form.find('button').prop('disabled', true);
                console.log($form);
                Stripe.card.createToken($form, stripeResponseHandler);
                return false;
            }
        });

    </script>
<script type="text/javascript">
    function isNumberKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode;
        if (charCode != 46 && charCode > 31 
        && (charCode < 48 || charCode > 57))
            return false;

        return true;
    }
</script>
@endsection