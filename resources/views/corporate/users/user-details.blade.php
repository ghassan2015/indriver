@extends('corporate.layout.base')

@section('title', 'Profile ')

@section('content')

<!-- edit page -->
<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    	    <a href="{{ route('corporate.user.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Back</a>

			<h5 style="margin-bottom: 2em;">User Details</h5>

  
            	<input type="hidden" name="_method" value="PATCH">
				<div class="form-group row">
					<label for="first_name" class="col-xs-2 col-form-label">First Name</label>
					<div class="col-xs-10">
						{{ $user->first_name }}
					</div>
				</div>

				<div class="form-group row">
					<label for="last_name" class="col-xs-2 col-form-label">Last Name</label>
					<div class="col-xs-10">
						{{ $user->last_name }}
					</div>
				</div>


				<div class="form-group row">
					<label for="last_name" class="col-xs-2 col-form-label">Email</label>
					<div class="col-xs-10">
						{{ $user->email }}
					</div>
				</div>


				<div class="form-group row">
					
					<label for="picture" class="col-xs-2 col-form-label">Picture</label>
					<div class="col-xs-10">
					@if(isset($user->picture))
                    	<img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{img($user->picture)}}">
        			@else
        				N/A
                    @endif
		
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-2 col-form-label">Mobile</label>
					<div class="col-xs-10">
						{{ $user->mobile }}
					</div>
				</div>
			
		</div>
    </div>
</div>

@endsection
