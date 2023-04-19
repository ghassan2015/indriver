@extends('admin.layout.base')

@section('title', 'Providers ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <div class="clearfix" style="margin-top: 15px;">
                <form class="form-horizontal" action="{{url('admin/providers/search')}}" method="POST" role="form">
                    {{ csrf_field() }}
                    <div class="form-group row col-md-5">
                        <label for="email" class="col-xs-4 col-form-label">First Name</label>
                        <div class="col-xs-8">
                            <input class="form-control" type="text" name="first-name" placeholder="First Name" value="{{old('first-name')}}">
                        </div>
                    </div>
                    <div class="form-group row col-md-5">
                        <label for="email" class="col-xs-4 col-form-label">Last Name</label>
                        <div class="col-xs-8">
                            <input class="form-control" type="text" name="last-name" placeholder="Last Name" value="{{old('last-name')}}">
                        </div>
                    </div>
                    <div class="form-group row col-md-5">
                        <label for="email" class="col-xs-4 col-form-label">Cab No</label>
                        <div class="col-xs-8">
                            <input class="form-control" type="text" name="cab-no" placeholder="Cab No" value="{{old('cab-no')}}">
                        </div>
                    </div>
                    <div class="form-group row col-md-5">
                        <label for="email" class="col-xs-4 col-form-label">Mobile No</label>
                        <div class="col-xs-8">
                            <input class="form-control" type="text" name="mobile-no" placeholder="Mobile No" value="{{old('mobile-no')}}">
                        </div>
                    </div>
                    <div class="form-group row col-md-2">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>

        @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : @lang('admin.demomode')
                </div>
                @endif
            <h5 class="mb-1">
                @lang('admin.provides.providers')
                @if(Setting::get('demo_mode', 0) == 1)
                <span class="pull-right">(*personal information hidden in demo)</span>
                @endif
            </h5>
            <a href="{{ route('admin.provider.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i>@lang('admin.provides.add_new_provider')</a>
            <table class="table table-striped table-bordered dataTable" id="table-5"> 
                <thead>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.provides.full_name')</th>
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th>
                        <th>@lang('admin.provides.total_requests')</th>
                        <th>@lang('admin.provides.accepted_requests')</th>
                        <th>@lang('admin.provides.cancelled_requests')</th>
                        <th>@lang('admin.provides.service_type')</th>
                        <th>@lang('admin.provides.online')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody>
                @php($page = ($pagination->currentPage-1)*$pagination->perPage)
                @foreach($providers as $index => $provider)
                @php($page++)
                    <tr>
                        <td>{{ $page }}</td>
                        <td>{{ $provider->first_name }} {{ $provider->last_name }}</td>
                        @if(Setting::get('demo_mode', 0) == 1)
                        <td>{{ substr($provider->email, 0, 3).'****'.substr($provider->email, strpos($provider->email, "@")) }}</td>
                        @else
                        <td>{{ $provider->email }}</td>
                        @endif
                        @if(Setting::get('demo_mode', 0) == 1)
                        <td>+919876543210</td>
                        @else
                        <td>{{ $provider->mobile }}</td>
                        @endif
                        <td>{{ $provider->total_requests() }}</td>
                        <td>{{ $provider->accepted_requests() }}</td>
                        <td>{{ $provider->total_requests() - $provider->accepted_requests() }}</td>
                        <td>
                            @if($provider->active_documents() >= $total_documents && $provider->service != null)
                                 <a class="btn btn-success btn-block" href="{{route('admin.provider.document.index', $provider->id )}}">All Set!</a>
                            @else                               
                                <a class="btn btn-danger btn-block label-right" href="{{route('admin.provider.document.index', $provider->id )}}">Attention! <span class="btn-label">{{ $provider->pending_documents() }}</span></a>
                            @endif
                        </td>
                        <td>
                            @if($provider->service)
                                @if($provider->service->status == 'active')
                                    <label class="btn btn-block btn-primary">Yes</label>
                                @else
                                    <label class="btn btn-block btn-warning">No</label>
                                @endif
                            @else
                                <label class="btn btn-block btn-danger">N/A</label>
                            @endif
                        </td>
                        <td>
                            <div class="input-group-btn">
                                @if($provider->status == 'approved')
                                <a class="btn btn-danger btn-block" href="{{ route('admin.provider.disapprove', $provider->id ) }}">@lang('Disable')</a>
                                @else
                                <a class="btn btn-success btn-block" href="{{ route('admin.provider.approve', $provider->id ) }}">@lang('Enable')</a>
                                @endif
                                <button type="button" 
                                    class="btn btn-info btn-block dropdown-toggle"
                                    data-toggle="dropdown">@lang('admin.action')
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="{{ route('admin.provider.request', $provider->id) }}" class="btn btn-default"><i class="fa fa-search"></i> @lang('admin.History')</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.provider.statement', $provider->id) }}" class="btn btn-default"><i class="fa fa-account"></i> @lang('admin.Statements')</a>
                                    </li>
                                    @if( Setting::get('demo_mode') == 0)
                                    <li>
                                        <a href="{{ route('admin.provider.edit', $provider->id) }}" class="btn btn-default"><i class="fa fa-pencil"></i> @lang('admin.edit')</a>
                                    </li>
                                    @endif
                                    <li>
                                        <form action="{{ route('admin.provider.destroy', $provider->id) }}" method="POST">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="_method" value="DELETE">
                                            @if( Setting::get('demo_mode') == 0)
                                            <button class="btn btn-default look-a-like" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i>@lang('admin.delete')</button>
                                            @endif
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.provides.full_name')</th>
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th>
                        <th>@lang('admin.provides.total_requests')</th>
                        <th>@lang('admin.provides.accepted_requests')</th>
                        <th>@lang('admin.provides.cancelled_requests')</th>
                        <th>@lang('admin.provides.service_type')</th>
                        <th>@lang('admin.provides.online')</th>
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
                url: "{{url('admin/provider')}}?page=all",
                data: {},
                success: function (result) {
                    p = new Array();
                    $.each(result.data, function (i, d)
                    {
                        var item = [d.id,d.first_name, d.last_name, d.email,d.mobile,d.rating, d.wallet_balance];
                        p.push(item);
                    });
                },
                async: false
            });
            var head=new Array();
            head.push("ID", "First Name", "Last Name", "Email", "Mobile", "Rating", "Wallet");
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