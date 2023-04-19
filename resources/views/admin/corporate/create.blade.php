@extends('admin.layout.base')

@section('title', 'Add Corporate ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
            <a href="{{ route('admin.corporate.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">@lang('admin.corporate.add_Corporate')</h5>

            <form class="form-horizontal" action="{{route('admin.corporate.store')}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
				<div class="form-group row">
					<label for="name" class="col-xs-12 col-form-label">@lang('admin.account-manager.full_name')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="name" placeholder="Full Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="company" class="col-xs-12 col-form-label">@lang('admin.corporate.company_name')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('company') }}" name="company" required id="company" placeholder="Company Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="email" class="col-xs-12 col-form-label">@lang('admin.email')</label>
					<div class="col-xs-10">
						<input class="form-control" type="email" required name="email" value="{{old('email')}}" id="email" placeholder="Email">
					</div>
				</div>

				<div class="form-group row">
					<label for="password" class="col-xs-12 col-form-label">@lang('admin.password')</label>
					<div class="col-xs-10">
						<input class="form-control" type="password" name="password" id="password" placeholder="Password">
					</div>
				</div>

				<div class="form-group row">
					<label for="password_confirmation" class="col-xs-12 col-form-label">@lang('admin.account-manager.password_confirmation')</label>
					<div class="col-xs-10">
						<input class="form-control" type="password" name="password_confirmation" id="password_confirmation" placeholder="Re-type Password">
					</div>
				</div>

				<div class="form-group row">
					<label for="logo" class="col-xs-12 col-form-label">@lang('admin.corporate.company_logo')</label>
					<div class="col-xs-10">
						<input type="file" accept="image/*" name="logo" class="dropify form-control-file" id="logo" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-12 col-form-label">@lang('admin.mobile')</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ old('mobile') }}" name="mobile" required id="mobile" placeholder="Mobile">
					</div>
				</div>
				{{--<div class="form-group row">
					<label for="pin" class="col-xs-12 col-form-label">@lang('admin.pin')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('pin') }}" name="pin" required id="pin" placeholder="Set Pin" onkeypress="if ( isNaN(this.value + String.fromCharCode(event.keyCode) )) return false";>
					</div>
				</div>--}}

				<div class="form-group row">
					<label for="recharge_option" class="col-xs-12 col-form-label">@lang('admin.recharge_option')</label>
					<!-- <div class="col-xs-3">
						<span><input type="radio" checked name="recharge_option" id="recharge_option" value="PREPAID" onchange="rechargeOption('PREPAID')"></span><span> PREPAID</span>
					</div> -->
					<div class="col-xs-3">
						<span><input type="radio" name="recharge_option" id="recharge_option" value="POSTPAID" onchange="rechargeOption('POSTPAID')" checked></span><span> POSTPAID</span>
					</div>
				</div>
				<!-- <div class="form-group row limit_amount" style="display: none;">
					<label for="limit_amount" class="col-xs-12 col-form-label">@lang('admin.Limit_amount')</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" name="limit_amount" id="limit_amount" placeholder="Limit Amount" value="0">
					</div>
				</div> --> 

				<!--<div class="form-group row deposit_amount">
					<label for="deposit_amount" class="col-xs-12 col-form-label">@lang('admin.deposit_amount')</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" name="deposit_amount" id="deposit_amount" placeholder="Deposit Amount" value="0">
					</div>
				</div> -->

				<div class="form-group row">
					<label for="zipcode" class="col-xs-12 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.corporate.add_Corporate')</button>
						<a href="{{route('admin.corporate.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>
<script type="text/javascript">
	// function rechargeOption(option)
	// {
	// 	if(option == 'POSTPAID'){
	// 		$('.limit_amount').show();
	// 		// $('.deposit_amount').hide();
	// 		// $('#deposit_amount').val(0); 
	// 	}
	// 	else{
	// 		$('.limit_amount').hide();
	// 		$('#limit_amount').val(0);
	// 		// $('.deposit_amount').show();
	// 	}

	// }
</script>

@endsection
