@extends('admin.layout.base')

@section('title', $page)

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
            	<h3>{{$page}}</h3>
	            <div class = "float-xs-right" >
			            <form class = "form-horizontal" action = "{{route('admin.global-search')}}" method = "POST" role = "form" >
				            {{ csrf_field() }}
				            <div class = "form-group row" >
						            {{--<label for="name" class="col-xs-4 col-form-label">Search by Taxi No: </label>--}}
					            <div class = "col-xs-12" >
						            <input class = "form-control" type = "text" value = "" name = "search" id = "global-search-taxi-no"
						                   placeholder = "Search by Taxi No." onkeydown="this.submit();" required>
					            </div >
				            </div >
			            </form >
			            </form >
	            </div >
            	<div class="row">

						<div class="row row-md mb-2" style="padding: 15px;">
							<div class="col-md-12">
									<div class="box bg-white">
										<div class="box-block clearfix">
											<h5 class="float-xs-left">@lang('admin.include.provider_earnings')</h5>
											<div class="float-xs-right">
											</div>
										</div>

										@if(count($Providers) != 0)
								            <table class="table table-striped table-bordered dataTable" id="table-4">
								                <thead>
								                   <tr>
														<td>@lang('admin.provides.provider_name')</td>
														<td>@lang('admin.mobile')</td>
														<td>@lang('admin.status')</td>
														<td>@lang('admin.provides.Total_Rides')</td>
														<td>@lang('admin.provides.Total_Earning')</td>
														<td>@lang('admin.provides.Commission')</td>
														<td>@lang('admin.provides.Joined_at')</td>
														<td>@lang('admin.provides.Details')</td>
													</tr>
								                </thead>
								                <tbody>
								                <?php $diff = ['-success','-info','-warning','-danger']; ?>
														@foreach($Providers as $index => $provider)
															<tr>
																<td>
																	{{$provider->first_name}} 
																	{{$provider->last_name}}
																</td>
																<td>
																	{{$provider->mobile}}
																</td>
																<td>
																	@if($provider->status == "approved")
																		<span class="tag tag-success">{{$provider->status}}</span>
																	@elseif($provider->status == "banned")
																		<span class="tag tag-danger">{{$provider->status}}</span>
																	@else
																		<span class="tag tag-info">{{$provider->status}}</span>
																	@endif
																</td>
																<td>
																	@if($provider->rides_count)
																		{{$provider->rides_count}}
																	@else
																	 	-
																	@endif
																</td>
																<td>
																	@if($provider->payment)
																		{{currency($provider->payment[0]->overall)}}
																	@else
																	 	-
																	@endif
																</td>
																<td>
																	@if($provider->payment)
																		{{currency($provider->payment[0]->commission)}}
																	@else
																	 	-
																	@endif
																</td>
																<td>
																	@if($provider->created_at)
																		<span class="text-muted">{{$provider->created_at->diffForHumans()}}</span>
																	@else
																	 	-
																	@endif
																</td>
																<td>
																	<a href="{{route('admin.provider.statement', $provider->id)}}">View by Ride</a>
																</td>
															</tr>
														@endforeach
															
								                <tfoot>
								                    <tr>
														<td>@lang('admin.provides.provider_name')</td>
														<td>@lang('admin.mobile')</td>
														<td>@lang('admin.status')</td>
														<td>@lang('admin.provides.Total_Rides')</td>
														<td>@lang('admin.provides.Total_Earning')</td>
														<td>@lang('admin.provides.Commission')</td>
														<td>@lang('admin.provides.Joined_at')</td>
														<td>@lang('admin.provides.Details')</td>
													</tr>
								                </tfoot>
								            </table>
								            @include('common.pagination')
								            @else
								            <h6 class="no-result">No results found</h6>
								            @endif 

									</div>
								</div>

							</div>

            	</div>

            </div>
        </div>
    </div>

@endsection
