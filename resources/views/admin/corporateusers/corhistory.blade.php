@extends('admin.layout.base')

@section('title', 'Request History ')

@section('content')



<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : @lang('admin.demomode')
                </div>
                @endif
                <h3>{{$page}}</h3>
                <br><br>
           

                <div class="clearfix" style="margin-top: 15px;">
                    <form class="form-horizontal" action="{{route('admin.ride.corstatement.range')}}" method="GET" enctype="multipart/form-data" role="form">

                        <div class="form-group row col-md-3">
                        <label for="name" class="col-xs-12 col-form-label">Date From</label>
                        <div class="col-xs-12">
                        <input class="form-control" type="date" name="from_date" required placeholder="From Date">
                        </div>
                        </div>

                        <div class="form-group row col-md-3">
                        <label for="email" class="col-xs-12 col-form-label">Date To</label>
                        <div class="col-xs-12">
                        <input class="form-control" type="date" required name="to_date" placeholder="To Date">
                        </div>
                        </div>

                         <div class="form-group row col-md-3">
                        <label for="email" class="col-xs-12 col-form-label">Company</label>
                        <div class="col-xs-12">

                             <select class="form-control" name="company_id"  id="company_id">
                                <option value="0">Select Company</option>
                                @foreach($company as $c)
                                    <option value="{{$c->id}}">{{$c->company_name}}</option>
                                @endforeach
                            </select>

                        </div>
                        </div>

                         <div class="form-group row col-md-3">
                        <label for="email" class="col-xs-12 col-form-label">User</label>
                        <div class="col-xs-12">

                            <select class="form-control" name="user_id"  id="user_id">
                                <option value="0">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
                                @endforeach
                            </select>

                        </div>
                        </div>

                        <div class="form-group row col-md-2">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>


            @if(count($requests) != 0)
            <table class="table table-striped table-bordered dataTable" id="table-4">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Company Name</th>
                        <th>@lang('admin.request.Booking_ID')</th>
                        <th>@lang('admin.request.User_Name')</th>
                        <th>@lang('admin.request.Provider_Name')</th>
                        <th>@lang('admin.request.Date_Time')</th>
                        <th>@lang('admin.status')</th>
                        <th>@lang('admin.amount')</th>                        
                        <th>@lang('admin.request.Payment_Status')</th>                        
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($requests as $index => $request)
                    <tr>
                        <td>{{ $request->id }}</td>
                        <td>{{ $request->user->company_name }}</td>
                        <td>{{ $request->booking_id }}</td>
                        <td>
                            @if($request->provider)
                                {{ $request->user?$request->user->first_name:'' }} {{ $request->user?$request->user->last_name:'' }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($request->provider)
                                {{ $request->provider?$request->provider->first_name:'' }} {{ $request->provider?$request->provider->last_name:'' }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($request->created_at)
                                <span class="text-muted">{{$request->created_at->diffForHumans()}}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $request->status }}</td>
                        <td>
                            @if($request->payment != "")
                                {{ currency($request->payment->total) }}
                            @else
                                N/A
                            @endif
                        </td>
                        
                        <td>
                            @if($request->cor_paid==0)
                                Not Paid
                            @else
                                Paid
                            @endif
                        </td>
                        
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary waves-effect dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    Action
                                </button>
                                <div class="dropdown-menu">
                                    
                                    <a href="{{ route('admin.requests.show', $request->id) }}" class="dropdown-item">
                                        <i class="fa fa-search"></i> More Details
                                    </a>
                                    
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Company Name</th>
                        <th>@lang('admin.request.Booking_ID')</th>
                        <th>@lang('admin.request.User_Name')</th>
                        <th>@lang('admin.request.Provider_Name')</th>
                        <th>@lang('admin.request.Date_Time')</th>
                        <th>@lang('admin.status')</th>
                        <th>@lang('admin.amount')</th>                        
                        <th>@lang('admin.request.Payment_Status')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </tfoot>
            </table>
            @include('common.pagination')
            @else
            <h6 class="no-result">No results found</h6>
            @endif 
        </div>
    </div>
</div>
@endsection