@extends('admin.layout.base')

@section('title', 'Corporates')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
                </div>
                @endif
            <h5 class="mb-1">
                @lang('admin.include.corporates')
                @if(Setting::get('demo_mode', 1) == 1)
                <span class="pull-right">(*personal information hidden in demo)</span>
                @endif
            </h5>
            <a href="{{ route('admin.corporate.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> @lang('admin.corporate.add_new_Corporate')</a>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.account-manager.full_name')</th>
                        <th>@lang('admin.corporate.company_name')</th>
                        <!-- <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th> -->
                        <th>@lang('admin.limit_deposit_amount')</th>

                        <th>@lang('admin.recharge_option')</th>
                        <th>@lang('admin.Wallet_balance')</th>
                        <th>@lang('admin.total_riding_amount')</th> 
                        <!-- <th>@lang('admin.pending_amount')</th> -->
                       <!--  <th>@lang('admin.payment_status')</th> -->
                        <th>@lang('admin.picture')</th> 
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($corporates as $index => $corporate)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $corporate->name }}</td>
                        <td>{{ $corporate->company }}</td> 
                   <!-- <td>{{ $corporate->email }}</td>  
                        <td>{{ $corporate->mobile }}</td>  -->
                        <td>{{ ($corporate->recharge_option == 'POSTPAID') ? $corporate->limit_amount : $corporate->deposit_amount }}</td>
                        <td>{{ $corporate->recharge_option }}</td>

                        <td>{{ currency($corporate->wallet_balance) }}</td>
                        <td>
                        @php 
                        if($corporate->recharge_option == 'POSTPAID'){
                            $user_ids = \App\User::wherecorporate_id($corporate->id)->pluck('id');
                            $user_request_ids = \App\UserRequests::whereIn('user_id',$user_ids)->wherepostpaid_payment_status('NOTPAID')->pluck('id');
                            $limit_deposit_amount = $corporate->limit_amount;
                            $ride_amount = \App\UserRequestPayment::whereIn('request_id',$user_request_ids)->sum('total');
                        }
                        else
                        {  
                            $user_ids = \App\User::wherecorporate_id($corporate->id)->pluck('id');
                            $user_request_ids = \App\UserRequests::whereIn('user_id',$user_ids)->wherepostpaid_payment_status('PAID')->pluck('id');
                            $limit_deposit_amount = $corporate->deposit_amount;
                            $ride_amount = abs($corporate->wallet_balance);
                        }
                        @endphp
                        {{ currency($ride_amount) }}</td>
                        <!-- <td>  
                        {{ currency($limit_deposit_amount - $ride_amount)}} 
                        </td> -->
                      <!--   <td>
                        @if($ride_amount != 0)
                        <span>Unpaid</span> <button type="button" data-toggle="modal" data-target="#myModal{{$corporate->id}}" class="btn btn-primary">Clear</button>
                        @else
                        Paid
                        @endif
                        </td> -->
                        <td><img src="{{img($corporate->logo)}}" style="height: 100px;"></td> 
                        <td>
                            <form action="{{ route('admin.corporate.destroy', $corporate->id) }}" method="POST">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="DELETE">
                                <a href="{{ route('admin.requests.index') }}?corporate={{$corporate->id}}" class="btn btn-primary"> @lang('admin.corporate.history')</a>
                                <a href="{{ route('admin.corporate.transaction.history') }}?corporate={{$corporate->id}}" class="btn btn-primary"> @lang('admin.corporate.transaction_history')</a>
                                <a href="{{ route('admin.user.index') }}?corporate={{$corporate->id}}" class="btn btn-info"> @lang('admin.corporate.show_users')</a>


                                @if( Setting::get('demo_mode') == 0)
                               <!--   <a href="{{ route('admin.corporate.edit', $corporate->id) }}" class="btn btn-info"><i class="fa fa-pencil"></i> @lang('admin.edit')</a>
                                <button class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> @lang('admin.delete')</button> -->
                                @endif

                            </form>
                            
                        </td>
                    </tr>

                    <!-- Clear Model -->
                    <div class="modal fade" id="myModal{{$corporate->id}}" role="dialog">
                        <div class="modal-dialog modal-sm">
                        <form action="{{route('admin.corporate.clear.payment')}}" method="GET"> 
                        <input type="hidden" name="user_request_ids" value="{{json_encode($user_request_ids)}}">
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                              <h4 class="modal-title">Are you sure?</h4>
                            </div> 
                            <div class="modal-footer">
                            <button type="submit" style="margin-left: 1em;" class="btn btn-info btn-md">Clear</button>
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            </div>
                          </div>
                          </form>
                        </div>
                      </div>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.account-manager.full_name')</th>
                        <th>@lang('admin.corporate.company_name')</th>
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th>
                        <!-- <th>@lang('admin.limit_deposit_amount')</th> -->
                        <th>@lang('admin.recharge_option')</th>
                        <th>@lang('admin.Wallet_balance')</th>
                        <th>@lang('admin.total_riding_amount')</th> 
                        <!-- <th>@lang('admin.pending_amount')</th> -->
                    <!--     <th>@lang('admin.payment_status')</th> -->
                        <th>@lang('admin.picture')</th> 
                        <th>@lang('admin.action')</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
@section('scripts')

<script type="text/javascript">
    $(document).ready(function() {
       
       $(document).on('click', '.switchery-default', function(){

            // alert('dhilip')
            var id = $(this).parent('td').find('input[type="checkbox"]').val();

            if($(this).parent('td').find('input[type="checkbox"]').prop('checked') == true) {

               
               $.ajax({url: "{{ url('admin/status') }}/"+id,dataType: "json",success: function(data){ 
               console.log(data);
               }});                
            } else {
               
                 $.ajax({url: "{{ url('admin/update') }}/"+id,dataType: "json",success: function(data){ 
               console.log(data);
              }});               
            }
           
        });
    });
</script>
@endsection