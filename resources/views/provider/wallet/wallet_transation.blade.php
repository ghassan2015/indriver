@extends('provider.layout.app')

@section('content')
<div class="pro-dashboard-head">
    <div class="container">
        <a href="{{ route('provider.profile.index') }}" class="pro-head-link">@lang('provider.profile.profile')</a>
        <a href="{{ route('provider.documents.index') }}" class="pro-head-link">@lang('provider.profile.manage_documents')</a>
        <a href="{{ route('provider.location.index') }}" class="pro-head-link">@lang('provider.profile.update_location')</a>
        <a href="#" class="pro-head-link active">@lang('provider.profile.wallet_transaction')</a>
        @if(Setting::get('CARD')==0)
            <a href="{{ route('provider.cards') }}" class="pro-head-link">@lang('provider.card.list')</a>
        @endif
        <a href="{{ route('provider.transfer') }}" class="pro-head-link">@lang('provider.profile.transfer')</a>
    </div>
</div>

<div class="pro-dashboard-content gray-bg">
    <div class="container">
        <div class="manage-docs pad30">
            <div class="manage-doc-content">
                <div class="manage-doc-section pad50">
                    <div class="manage-doc-section-head row no-margin">
                        <h3 class="manage-doc-tit">
                            @lang('provider.profile.wallet_transaction')
                            (@lang('provider.current_balance') : {{currency($wallet_balance)}})
                        </h3>
                    </div>

                   
                     <div class="manage-doc-section-content">
                     <div class="tab-content list-content">
                      <div class="list-view pad30 ">

                            <table class="earning-table table table-responsive">
                                <thead>
                                    <tr>
                                        <th>@lang('provider.sno')</th>
                                        <th>@lang('provider.transaction_ref')</th>
                                        <th>@lang('provider.datetime')</th>
                                        <th>@lang('provider.transaction_desc')</th>
                                        <th>@lang('provider.status')</th>
                                        <th>@lang('provider.amount')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php($page = ($pagination->currentPage-1)*$pagination->perPage)
                               @foreach($wallet_transation as $index=>$wallet)
                               @php($page++)
                                    <tr>
                                        <td>{{$page}}</td>
                                        <td>{{$wallet->transaction_alias}}</td>
                                        <td>{{$wallet->created_at->diffForHumans()}}</td>
                                        <td>{{$wallet->transaction_desc}}</td>
                                        <td>@if($wallet->type == 'C') @lang('provider.credit') @else @lang('provider.debit') @endif</td>
                                        <td>{{currency($wallet->amount)}}
                                        </td>
                                       
                                    </tr>
                                @endforeach

                                </tbody>

                            </table>
                            {{ $wallet_transation->links() }}
                        </div>
                     </div>
                     </div>
               
                </div>
            </div>
        </div>
    </div>

</div>
@endsection


