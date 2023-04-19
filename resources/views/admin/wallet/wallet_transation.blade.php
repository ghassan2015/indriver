@extends('admin.layout.base')

@section('title', 'Admin Transactions')

@section('content')

<div class="content-area py-1">
        <div class="container-fluid">
            
            <div class="box box-block bg-white">
                <h5 class="mb-1">Total Transactions (@lang('provider.current_balance') : {{currency($wallet_balance)}})</h5>
                <div class="clearfix" style="margin-top: 15px;">
                <form class="form-horizontal" action="{{route('admin.transactions')}}" method="GET" enctype="multipart/form-data" role="form">
                    <div class="form-group row col-md-3">
                        <label for="name" class="col-xs-4 col-form-label">Date From</label>
                        <div class="col-xs-8">
                            <input class="form-control" type="date" name="from_date" required placeholder="From Date" value="{{isset($params['from_date']) ? $params['from_date'] : old('from_date')}}">
                        </div>
                    </div>
                    <div class="form-group row col-md-3">
                        <label for="email" class="col-xs-4 col-form-label">Date To</label>
                        <div class="col-xs-8">
                            <input class="form-control" type="date" required name="to_date" placeholder="To Date" value="{{isset($params['to_date']) ? $params['to_date'] :old('to_date')}}">
                        </div>
                    </div>
                    <div class="form-group row col-md-3">
                        <label for="email" class="col-xs-4 col-form-label">Taxi #</label>
                        <div class="col-xs-8">
                            <input class="form-control" type="text" name="taxiNo" placeholder="Enter Taxi #" value="{{isset($params['taxiNo']) ? $params['taxiNo'] :old('taxiNo')}}">
                        </div>
                    </div>
                    <div class="form-group row col-md-2">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
                </div>
                <table class="table table-striped table-bordered dataTable" id="table-4">
                    <thead>
                        <tr>
                            <th>@lang('admin.sno')</th>
                            <th>@lang('admin.transaction_ref')</th>
                            <th>Taxi #</th>
                            <th>@lang('admin.datetime')</th>
                            <th>@lang('admin.transaction_desc')</th>
                            <th>@lang('admin.status')</th>
                            <th>@lang('admin.amount')</th>
                        </tr>
                    </thead>
                    <tbody>
                       @php($page = ($pagination->currentPage-1)*$pagination->perPage)
                       @foreach($wallet_transation as $index=>$wallet)
                       @php($page++)
                            <tr>
                                <td>{{$page}}</td>
                                <td>{{$wallet->transaction_alias}}</td>
                                <td>{{@$wallet->walletRequest->provider->service->service_number}}</td>
                                <td>{{$wallet->created_at->diffForHumans()}}</td>
                                <td>{{$wallet->transaction_desc}}</td>
                                <td>{{$wallet->type == 'C' ? 'Credit' : 'Debit'}}</td>
                                <td>{{currency($wallet->amount)}}
                                </td>
                               
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @include('common.pagination')
                <p style="color:#ff0000;">{{Setting::get('booking_prefix', '') }} - Ride Transactions, PSET - Provider Settlements, FSET - Fleet Settlements, URC - User Recharges</p>
            </div>
            
        </div>
    </div>
@endsection



