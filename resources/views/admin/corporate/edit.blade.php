@extends('admin.layout.base')

@section('title', 'Update Corporate ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    	    <a href="{{ route('admin.corporate.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">@lang('admin.corporate.update_Corporate')</h5>

            <form class="form-horizontal" action="{{route('admin.corporate.update', $corporate->id )}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
            	<input type="hidden" name="_method" value="PATCH">
				<div class="form-group row">
					<label for="name" class="col-xs-2 col-form-label">@lang('admin.account-manager.full_name')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $corporate->name }}" name="name" required id="name" placeholder="Full Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="company" class="col-xs-2 col-form-label">@lang('admin.corporate.company_name')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $corporate->company }}" name="company" required id="company" placeholder="Company Name">
					</div>
				</div>


				<div class="form-group row">
					
					<label for="logo" class="col-xs-2 col-form-label">@lang('admin.corporate.company_logo')</label>
					<div class="col-xs-10">
					@if(isset($corporate->logo))
                    	<img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{img($corporate->logo)}}">
                    @endif
						<input type="file" accept="image/*" name="logo" class="dropify form-control-file" id="logo" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-2 col-form-label">@lang('admin.mobile')</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ $corporate->mobile }}" name="mobile" required id="mobile" placeholder="Mobile">
					</div>
				</div>
				{{--<div class="form-group row">
					<label for="pin" class="col-xs-2 col-form-label">@lang('admin.pin')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $corporate->pin }}" name="pin" required id="pin" placeholder="Set Pin" onkeypress="if ( isNaN(this.value + String.fromCharCode(event.keyCode) )) return false";>
					</div>
				</div>--}}
				<div class="form-group row">
					<label for="recharge_option" class="col-xs-2 col-form-label">@lang('admin.recharge_option')</label>
					<!-- <div class="col-xs-3">
						<span><input type="radio" name="recharge_option" id="recharge_option" value="PREPAID" onchange="rechargeOption('PREPAID')" @if($corporate->recharge_option == 'PREPAID') checked @endif></span><span> PREPAID</span>
					</div> -->
					<div class="col-xs-3">
						<span><input type="radio" name="recharge_option" id="recharge_option" value="POSTPAID" onchange="rechargeOption('POSTPAID')" @if($corporate->recharge_option == 'POSTPAID') checked @endif></span><span> POSTPAID</span>
					</div>
				</div>
				<!-- <div class="form-group row limit_amount" @if($corporate->recharge_option == 'PREPAID') style="display: none;" @else style="display: black;" @endif>
					<label for="limit_amount" class="col-xs-2 col-form-label">@lang('admin.Limit_amount')</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" name="limit_amount" id="limit_amount" value="{{ $corporate->limit_amount ? : 0 }}" placeholder="Limit Amount">
					</div>
				</div>  --> 

				<!--<div class="form-group row deposit_amount" @if($corporate->recharge_option == 'POSTPAID') style="display: none;" @else style="display: black;" @endif>
					<label for="deposit_amount" class="col-xs-2 col-form-label">@lang('admin.deposit_amount')</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" name="deposit_amount" id="deposit_amount" placeholder="Deposit Amount" value="{{ $corporate->deposit_amount ? : 0 }}">
					</div>
				</div> -->

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.corporate.update_Corporate')</button>
						<a href="{{route('admin.corporate.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>
<script type="text/javascript">
	function rechargeOption(option)
	{
		if(option == 'POSTPAID'){
			$('.limit_amount').show();
			// $('.deposit_amount').hide();
			// $('#deposit_amount').val(0); 
			$('#limit_amount').val('{{$corporate->limit_amount}}'); 
		}
		else{
			$('.limit_amount').hide();
			$('#limit_amount').val(0);
			// $('#deposit_amount').val('{{$corporate->deposit_amount}}');
			// $('.deposit_amount').show();
		}

	}
</script>

@endsection
