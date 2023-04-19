@extends('admin.layout.base')

@section('title', 'Update User ')

@section('content')

<!-- edit page -->
<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    	    <a href="{{ route('admin.corporateusers.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Back</a>

			<h5 style="margin-bottom: 2em;">Update Corporate Company</h5>

            <form class="form-horizontal" action="{{route('admin.corporateusers.update', $coruser->id )}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
            	<input type="hidden" name="_method" value="PATCH">
				<div class="form-group row">
					<label for="first_name" class="col-xs-2 col-form-label">Company Name</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $coruser->company_name }}" name="company_name" required id="company_name" placeholder="Company Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="address" class="col-xs-2 col-form-label">Address</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $coruser->address }}" name="address" required id="address" placeholder="Address">
					</div>
				</div>


				<div class="form-group row">
					
					<label for="picture" class="col-xs-2 col-form-label">Picture</label>
					<div class="col-xs-10">
					@if(isset($coruser->picture))
                    	<img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{img($coruser->picture)}}">
                    @endif
						<input type="file" accept="image/*" name="picture" class="dropify form-control-file" id="picture" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-2 col-form-label">Mobile</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ $coruser->mobile }}" name="mobile" required id="mobile" placeholder="Mobile">
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Update Corporate User</button>
						<a href="{{route('admin.corporateusers.index')}}" class="btn btn-default">Cancel</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection
