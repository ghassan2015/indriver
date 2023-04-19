@extends('corporate.layout.base')

@section('title', 'Users ')

@section('content')
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

<script type="text/javascript">
    
    $(document).ready(function () {
        $('#seleted_delete').hide();
        $('input[type="checkbox"]').click(function () {
            var checked_count = $('input:checkbox:checked').length;
            if(checked_count >= 1) {
            $('#seleted_delete').show();
            }
            else
            {
            $('#seleted_delete').hide();
            }
        });
        $('body').on('click', '#selectAll', function () { 
          if ($(this).hasClass('allChecked')) { 
             $('input[type="checkbox"]').prop('checked', false);
             $('#seleted_delete').hide();
          } else { 
           $('input[type="checkbox"]').prop('checked', true);
           $('#seleted_delete').show();
           }
           $(this).toggleClass('allChecked');
         })
           

        $('body').on('click', '#seleted_delete', function () {
             var deleted = [];
             var deleted_id = [];
             <?php 
                foreach($users as $key => $data)
                { ?>
                    
                    if($('.delete{{$data->id}}').prop('checked'))
                    { 
                        deleted_id[{{$key}}]= '{{$data->id}}'; 
                    }
                    


                <?php }
                ?>
                
                $.post( "user/seleted_delete", { deleted_id: deleted_id }) 
                 .done(function( data ) {
                    //alert( "Data Loaded: " + data );
                    window.location.replace("{{url('corporate/user')}}");
                  });

        })
    });
</script>
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

<!-- Delete Model -->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog modal-sm">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Are you sure?</h4>
            </div> 
            <div class="modal-footer">
            <button type="button" id="seleted_delete" style="margin-left: 1em;" class="btn btn-info btn-md"> Delete</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
          </div>
        </div>
      </div>
<!-- Recharge Wallet Model -->
    <div class="modal fade" id="myModalRechargeWallet" role="dialog">
        <div class="modal-dialog modal-sm">
        <form class="form-control" action="{{route('corporate.user.wallet.recharge')}}" method="POST">
        {{csrf_field()}}
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Recharge Wallet for All Users</h4>
            </div>  
            <div class="modal-body">
               <input type="number" name="wallet_amount" class="form-control" placeholder="Enter Amount">
            </div>
            <div class="modal-footer">
            <button type="submit" style="margin-left: 1em;" class="btn btn-info btn-md">Recharge</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
          </div>
        </form>
        </div>
  </div>

<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
                </div>
                @endif
            <h5 class="mb-1">
                @lang('admin.users.Users')
                @if(Setting::get('demo_mode', 0) == 1)
                <span class="pull-right">(*personal information hidden in demo)</span>
                @endif
            </h5> 
            <div class="col-md-12"> 
            <div class="col-md-3">
            <button type="button" class="btn btn-info btn-md pull-left" data-toggle="modal" data-target="#myModal" style="margin-left: -30PX;"><span class="glyphicon glyphicon-trash"></span> Seleted Delete</button>
            </div>
            <div class="col-md-6">
            <form action="{{route('corporate.user.index')}}" method="GET"> 
            <div class="col-md-6">
            <input type="text" class="form-control col-md-6" name="search" value="{{@Request::get('search')}}">
            </div>
            <div class="col-md-6"> 
            <button type="submit" class="btn btn-success col-md-6" ><span class="glyphicon glyphicon-search" style="font-size: 15px;"> Search</span></button>
            </div> 
            </form>
            </div>
            <!-- <div class="col-md-2">
            <a href="{{url('corporate/user/export/excel')}}" class="btn btn-primary form-control col-md-6" >Download Excel</a> 
            </div> -->
            <!-- <div class="col-md-2"> 
            <button type="button" class="btn btn-info btn-md pull-left" data-toggle="modal" data-target="#myModalRechargeWallet"><i class="fa fa-money" aria-hidden="true"></i> Recharge Wallet</button> 
            </div> --> 
            <div class="col-md-3" style="text-align: right;">
            <a href="{{ route('corporate.user.create') }}" style="margin-left: 1em;" class="btn btn-primary"><i class="fa fa-plus"></i> Add New User</a> 
            </div> 
            </div> <br> <br>
            <table class="table table-striped table-bordered dataTable" id="cus-table-2">
                <thead>
                    <tr>
                        <th><button type="button" id="selectAll" class="main">
                        <span class="sub"></span> All </button></th>
                        <th>@lang('admin.id')</th> 
                        <th>Employee ID</th> 
                        <th>Joined At</th>
                        <th>@lang('admin.first_name')</th>
                        <th>@lang('admin.last_name')</th> 
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th>
                        <th>Password</th> 
                        <th>Pin</th> 
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody> @php($page_plus=Request::get('page') ? Request::get('page') : 10 - 10)
                    @foreach($users as $index => $user)

                    <?php

                        $user_data = \App\User::where('email',$user->email)->first();

                    ?>
                    <tr>
                        <td><input type="checkbox" class="delete{{$user->id}}" data-id="{{$user->id}}"></td>
                        <td>{{ $index + 1 + $page_plus }}</td>
                        <td>{{ $user->employee_id }}</td> 
                        <td>{{ date('d M Y', strtotime($user->created_at) )}}</td>
                        <td>{{ $user->first_name }}</td>
                        <td>{{ $user->last_name }}</td>  
                        <td>{{ $user->email }}</td>  
                        <td>{{ $user->mobile }}</td> 
                        <td>{{ $user->password }}</td> 
                        <td>{{ $user->pin }}</td> 
                        <td>
                            <form action="{{ route('corporate.user.destroy', $user->id) }}" method="POST">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="DELETE">

                                @if(@$user_data->id)
                                
                                      <a href="{{ route('corporate.user.request', @$user_data->id) }}" class="btn btn-info"><i class="fa fa-search"></i> @lang('admin.History')</a>
                                @endif
                              
                                @if( Setting::get('demo_mode') == 0)
                                <a href="{{ route('corporate.user.edit', $user->id) }}" class="btn btn-info"><i class="fa fa-pencil"></i> @lang('admin.edit')</a>
                                <button class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> @lang('admin.delete')</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody> 
                <tfoot>
                    <tr>
                        <th><button type="button" id="selectAll" class="main">
                        <span class="sub"></span> All </button></th>
                        <th>@lang('admin.id')</th> 
                        <th>Employee ID</th> 
                        <th>Joined At</th>
                        <th>@lang('admin.first_name')</th>
                        <th>@lang('admin.last_name')</th> 
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th>
                        <th>Password</th> 
                        <th>Pin</th> 
                        <th>@lang('admin.action')</th>
                    </tr>
                </tfoot>
            </table>
            {{$users->appends(['search' => @Request::get('search')])->links()}} 
        </div> 
    </div>
</div>
@endsection