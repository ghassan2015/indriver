@extends('user.layout.base')

@section('title', 'Wallet ')

@section('content')

<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">@lang('user.my_wallet')</h4>
            </div>
        </div>
        @include('common.notify')

        <div class="row no-margin">
            <form action="{{url('add/money')}}" method="POST">
            {{ csrf_field() }}
                <div class="col-md-6">
                     
                    <div class="wallet">
                        <h4 class="amount">
                        	<span class="price">{{currency(Auth::user()->wallet_balance)}}</span>
                        	<span class="txt">@lang('user.in_your_wallet')</span>
                        </h4>
                    </div>                                                               

                </div>
               @if(Setting::get('CARD') == 1)
                <div class="col-md-6">
                    
                    <h6><strong>@lang('user.add_money')</strong></h6>

                    <div class="input-group full-input">
                        <input type="number" class="form-control" name="amount" placeholder="@lang('user.enter_amount')" >
                    </div>
                    <br>
                    @if($cards->count() > 0)
                        <select class="form-control" name="card_id">
                          @foreach($cards as $card)
                            <option @if($card->is_default == 1) selected @endif value="{{$card->card_id}}">{{$card->brand}} **** **** **** {{$card->last_four}}</option>
                          @endforeach
                        </select>
                    @else
                        <p>@lang('user.please') <a href="{{url('payment')}}" class="add-card-btn">@lang('user.add_card')</a> @lang('user.to_continue')</p>
                    @endif
                    
                    <button type="submit" class="full-primary-btn fare-btn">@lang('user.add_money')</button> 

                </div>
                @endif
            </form>

        </div>

        <div class="manage-doc-section-content border-top">
             <div class="tab-content list-content">
                <div class="list-view pad30 ">
                    <table class="earning-table table table-responsive">
                        <thead>
                            <tr>
                                <th>@lang('provider.sno')</th>
                                <th>@lang('provider.transaction_ref')</th>
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
                                        <td>{{$wallet->transaction_desc}}</td>
                                        <td>@if($wallet->type == 'C')  @lang('user.credit') @else @lang('user.debit') @endif</td>
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

@endsection