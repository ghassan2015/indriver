@extends('dispatcher.layout.base')

@section('title', 'Provider\'s Status ')
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
                @lang('admin.provides.providers')
            </h5>
            @foreach($statuses as $status)
                <a href="{{url('/dispatcher/provider-status/' . $status)}}" style="margin-left: 1em;" class="btn btn-{{\App\ProviderService::$SERVICE_STATUS_COLORS[$status]}} pull-left">
                    {{ strtoupper($status)}}  
                    [{{@$providerCounts->where('status', $status)->first()->total}}]
                </a>
            @endforeach
            <table class="table table-striped table-bordered dataTable" id="table-5"> 
                <thead>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.provides.full_name')</th>
                        <th>Cab No</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @php($page = ($pagination->currentPage-1)*$pagination->perPage)
                @foreach($providers as $index => $provider)
                @php($page++)
                    <tr>
                        <td>{{ $page }}</td>
                        <td>{{ $provider->first_name }} {{ $provider->last_name }}</td>
                        <td>{{ @$provider->service->service_number }}</td>
                        <td>
                            <label class="btn btn-block btn-{{\App\ProviderService::$SERVICE_STATUS_COLORS[@$provider->service->status]}}">
                                {{ strtoupper(@$provider->service->status) }}
                            </label>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.provides.full_name')</th>
                        <th>Cab No</th>
                        <th>Status</th>
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
    } );
</script>
@endsection