@extends('admin.layout.base')

@section('title', 'Transaction History ')

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

 
<div class="content-area py-1"> 
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
            <a style="margin-left: 1em;" class="btn btn-primary pull-right" href="{{route('admin.corporate.index')}}">Back</a>
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
        </div> 
    </div>
</div>    
@endsection