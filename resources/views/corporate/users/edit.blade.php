@extends('corporate.layout.base')

@section('title', 'Update User ')

@section('content')

<!-- edit page -->
<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    	    <a href="{{ route('corporate.user.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Back</a>

			<h5 style="margin-bottom: 2em;">Update User</h5>

            <form class="form-horizontal" action="{{route('corporate.user.update', $user->id )}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
            	<input type="hidden" name="_method" value="PATCH">


				<div class="form-group row">
					<label for="employee_id" class="col-xs-2 col-form-label">Employee ID</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $user->employee_id }}" name="employee_id" required id="employee_id" placeholder="Employee ID" readonly="">
					</div>
				</div> 
				<div class="form-group row">
					<label for="first_name" class="col-xs-2 col-form-label">First Name</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $user->first_name }}" name="first_name" required id="first_name" placeholder="First Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="last_name" class="col-xs-2 col-form-label">Last Name</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $user->last_name }}" name="last_name" required id="last_name" placeholder="Last Name">
					</div>
				</div> 

				<div class="form-group row">
					<label for="mobile" class="col-xs-2 col-form-label">Mobile</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $user->mobile }}" name="mobile" required id="mobile" placeholder="Mobile">
					</div>
				</div>

				<div class="form-group row">
					<label for="email" class="col-xs-2 col-form-label">Email</label>
					<div class="col-xs-10">
						<input class="form-control" type="email" value="{{ $user->email }}" name="email" required id="email" placeholder="Email">
					</div>
				</div> 
				<div class="form-group row">
					<label for="pin" class="col-xs-2 col-form-label">Pin</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" required name="pin" value="{{$user->pin}}" id="pin" placeholder="Pin" onkeypress="if ( isNaN(this.value + String.fromCharCode(event.keyCode) )) return false"; minlength="4" maxlength="4">
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Update User</button>
						<a href="{{route('corporate.user.index')}}" class="btn btn-default">Cancel</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection
