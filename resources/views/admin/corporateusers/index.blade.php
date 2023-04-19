@extends('admin.layout.base')

@section('title', 'Users ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : @lang('admin.demomode')
                </div>
                @endif
            <h5 class="mb-1">
                @lang('admin.corusers.Users')
                @if(Setting::get('demo_mode', 0) == 1)
                <span class="pull-right">(*personal information hidden in demo)</span>
                @endif               
            </h5>
            <a href="{{ route('admin.corporateusers.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New Corporate Company</a>
            <table class="table table-striped table-bordered dataTable" id="table-5">
                <thead>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.company_name')</th>
                        <th>@lang('admin.address')</th>
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th>  
                        <th>@lang('Pay Amount')</th>                        
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @php($page = ($pagination->currentPage-1)*$pagination->perPage)
                    @foreach($corusers as $index => $cuser)
                    @php($page++)
                    <tr>
                        <td>{{ $page }}</td>
                        <td>{{ $cuser->company_name }}</td>
                        <td>{{ $cuser->address }}</td>
                        @if(Setting::get('demo_mode', 0) == 1)
                        <td>{{ substr($cuser->email, 0, 3).'****'.substr($cuser->email, strpos($cuser->email, "@")) }}</td>
                        @else
                        <td>{{ $cuser->email }}</td>
                        @endif
                        @if(Setting::get('demo_mode', 0) == 1)
                        <td>+919876543210</td>
                        @else
                        <td>{{ $cuser->mobile }}</td>
                        @endif
                       <th>{{currency($cuser->payamount) }}</th> 
                       
                        <td>
                            <form action="{{ route('admin.corporateusers.destroy', $cuser->id) }}" method="POST">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="DELETE">
                                
                                @if( Setting::get('demo_mode') == 0)
                                 
                                 @if($cuser->payamount >0)
                                 <a href='{{ url("admin/corporateusers/paynow", ["id" => $cuser->id])}}'class="btn btn-primary"><i class="fa fa-money"></i> Pay Now</a>
                                 @endif

                                 <a href='{{ url("admin/corporateusers/viewUsers", ["id" => $cuser->id])}}' class="btn btn-success"><i class="fa fa-user"></i> View Users</a>


                                <a href="{{ route('admin.corporateusers.edit', $cuser->id) }}" class="btn btn-info"><i class="fa fa-pencil"></i> @lang('admin.edit')</a>

                                <button class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> @lang('admin.delete')</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.company_name')</th>
                        <th>@lang('admin.address')</th>
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th> 
                        <th>@lang('Pay Amount')</th>                         
                        <th>@lang('admin.action')</th>
                    </tr>
                </tfoot>
            </table>
            @include('common.pagination')           
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    jQuery.fn.DataTable.Api.register( 'buttons.exportData()', function ( options ) {
        if ( this.context.length ) {
            var jsonResult = $.ajax({
                url: "{{url('admin/corporateusers')}}?page=all",
                data: {},
                success: function (result) { 
                console.log(result)     ;                 
                    p = new Array();
                    $.each(result.data, function (i, d)
                    {
                        var item = [d.id,d.company_name, d.address, d.email,d.mobile];
                        p.push(item);
                    });
                },
                async: false
            });
            var head=new Array();
            head.push("ID", "Company Name", "Address", "Email", "Mobile");            
            return {body: p, header: head};
        }
    } );

    $('#table-5').DataTable( {
        responsive: true,
        paging:false,
            info:false,
            dom: 'Bfrtip',
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5'
            ]
    } );
</script>
@endsection