@extends('user.layout.base')

@section('title', 'Profile ')

@section('content')

<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">@lang('user.profile.corp_information')</h4>
            </div>
        </div>
            @include('common.notify')
        <div class="row no-margin">
            <form action="{{url('edit/corprofile')}}" method="POST">
                {{ csrf_field() }}
                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>@lang('user.profile.first_name')</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->first_name}}</p>                       
                </div>
                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>@lang('user.profile.last_name')</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->last_name}}</p>                       
                </div>
                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>@lang('user.profile.email')</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->email}}</p>
                </div>

                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>@lang('user.profile.mobile')</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->mobile}}</p>
                </div>

                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>Company</strong></h5>
                    <p class="col-md-6 no-padding">
                    
                        @if($exist==0)
                        <select class="form-control" name="company_id">
                            <option value="0">Select Company</option>
                            @foreach($companyList as $cl)                        
                            <option value="{{$cl->id}}" >{{$cl->company}}</option>
                            @endforeach
                        </select>
                        @else
                         <input type="text" name="empid" class="form-control" readonly="true" value="{{$user->company_name}}">
                        @endif
                    </p>
                </div>

                   <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>EmpID</strong></h5>
                    <p class="col-md-6 no-padding">
                        @if($exist==0) 
                       <input type="text" name="empid" class="form-control">
                       @else
                       <input type="text" name="empid" class="form-control" readonly="true" value="{{$user->emp_id}}">
                       @endif
                    </p>
                </div>

                @if($exist==0) 
                  <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>Company Mobile No</strong></h5>
                    <p class="col-md-6 no-padding">
                       <input type="text" name="cmobile" class="form-control">
                    </p>
                </div> 
                  <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>Company Password</strong></h5>
                    <p class="col-md-6 no-padding">
                       <input type="password" name="cpassword" class="form-control">
                    </p>
                </div>           

               

                <div class="col-md-6 pro-form pull-right">                   
             

                  <button type="submit" class="full-primary-btn fare-btn">@lang('user.profile.save')</button> 

                     </div>
                    @endif

            </form>
        </div>

    </div>
</div>

@endsection