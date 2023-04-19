@extends('corporate.layout.base')

@section('title', 'Update Profile ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">

			<h5 style="margin-bottom: 2em;">Update Profile</h5>

            <form class="form-horizontal" action="{{route('corporate.profile.update')}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}

				<div class="form-group row">
					<label for="name" class="col-xs-2 col-form-label">Name</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Auth::guard('corporate')->user()->name }}" name="name" required id="name" placeholder=" Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="email" class="col-xs-2 col-form-label">Email</label>
					<div class="col-xs-10">
						<input class="form-control" type="email" required name="email" value="{{ isset(Auth::guard('corporate')->user()->email) ? Auth::guard('corporate')->user()->email : '' }}" id="email" placeholder="Email" readonly>
					</div>
				</div>

				<div class="form-group row">
					<label for="company" class="col-xs-2 col-form-label">Company</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" required name="company" value="{{ isset(Auth::guard('corporate')->user()->company) ? Auth::guard('corporate')->user()->company : '' }}" id="company" placeholder="Company">
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-2 col-form-label">Mobile</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" required name="mobile" value="{{ isset(Auth::guard('corporate')->user()->mobile) ? Auth::guard('corporate')->user()->mobile : '' }}" id="mobile" placeholder="Mobile">
					</div>
				</div>
				{{--<div class="form-group row">
					<label for="pin" class="col-xs-2 col-form-label">Pin</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" required name="pin" value="{{Auth::guard('corporate')->user()->pin}}" id="pin" placeholder="Pin">
					</div>
				</div>--}}

				<div class="form-group row">
					<label for="logo" class="col-xs-2 col-form-label">Logo</label>
					<div class="col-xs-10">
						@if(isset(Auth::guard('corporate')->user()->logo))
	                    	<img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{img(Auth::guard('corporate')->user()->logo)}}">
	                    @endif
						<input type="file" accept="image/*" name="logo" class=" dropify form-control-file" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Update Profile</button>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection
