@extends('user.layout.app')

@section('content')
    <!--<div class="banner row no-margin" style="background-image: url('{{ asset('asset/img/banner-bg.jpg') }}');">
        <div class="banner-overlay"></div>
        <div class="container pad-60">
            <div class="col-md-8">
                <h2 class="banner-head"><span class="strong">Always the ride you want</span><br>The best way to get wherever you’re going</h2>
            </div>
            <div class="col-md-4">
                <div class="banner-form">
                    <div class="row no-margin fields">
                        <div class="left">
                           <img src="{{asset('asset/img/taxi-app.png')}}">
                        </div>
                        <div class="right">
                            <a href="{{url('login')}}">
                                <h3>Ride with {{Setting::get('site_title','6ixTaxi')}}</h3>
                                <h5>SIGN IN <i class="fa fa-chevron-right"></i></h5>
                            </a>
                        </div>
                    </div>
                    <div class="row no-margin fields">
                        <div class="left">
                        <img src="{{asset('asset/img/taxi-app.png')}}">
                        </div>
                        <div class="right">
                            <a href="{{url('provider/login')}}">
                                <h3>Sign in to drive</h3>
                                <h5>SIGN UP <i class="fa fa-chevron-right"></i></h5>
                            </a>
                        </div>
                    </div>

                   <!--  <p class="note-or">Or <a href="{{url('provider/login')}}">sign in</a> with your driver account.</p> 
                    
                </div>
            </div>
        </div>
    </div>-->

    <div class="row gray-section pad-60 full-section">
    <div class="container">
        <div class="col-md-6 content-block">
              <div class="icon"><img src="{{ asset('asset/img/seat-belt.png') }}"></div>
              <h2>Your safety matter to us</h2>
            <!--<h2>Safety Putting people first</h2>-->
            <div class="title-divider"></div>
            <p>We want to make sure every ride is safe, respectful, and comfortable. This applies to everyone across our apps, including both drivers, and riders alike. Everyone who signs up for a 6ixTaxi account is required to follow our safety guidelines, and procedures. </p>
            <!-- <p>Whether riding in the backseat or driving up front, every part of the {{ Setting::get('site_title', '6ixTaxi') }}  experience is designed around your safety and security. </p> -->
           <!-- <p>Whether riding in the backseat or driving up front, every part of the {{ Setting::get('site_title', '6ixTaxi') }} experience has been designed around your safety and security.</p>-->
            <!-- <a class="content-more more-btn" href="{{url('login')}}">HOW WE KEEP YOU SAFE <i class="fa fa-chevron-right"></i></a> -->
        </div>
        <!-- <div class="col-md-6 img-box text-center"> 
            <img src="{{ asset('asset/img/seat-belt.jpg') }}">
        </div> -->
        <div class="col-md-6 full-img text-center" style="background-image: url({{ asset('asset/img/safty-bg.jpg') }});"> 
            <!-- <img src="img/anywhere.png"> -->
        </div>
    </div>
    
</div>
<div class="row white-section pad-60 no-margin">
        <div class="container ">
            
            <div class="col-md-6 content-block small">
                <div class="box-shadow">
                <!-- <div class="icon"><img src="{{asset('asset/img/taxi-app.png')}}"></div> -->
                <h2>Every driver is screened prior to becoming a 6ixtaxi partner</h2>
                <div class="title-divider"></div>
                <p>Every driver must undergo a multi-step background check prior to becoming a driver, and must pass an annual background check to continue. This includes criminal background check, and driving records. </p>
                <!-- <p>{{ Setting::get('site_title', '6ixTaxi')  }} is the smartest way to get around. One tap and a car comes directly to you. Your driver knows exactly where to go. And you can pay with either cash or card.</p> -->
            </div>
        </div>

           

            <div class="col-md-6 content-block small">
                 <div class="box-shadow">
                 <!-- <div class="icon"><img src="{{asset('asset/img/budget.png')}}"></div> -->
                <h2>Community guidelines </h2>
                <div class="title-divider"></div>
                <p>At 6ixTaxi we want every driver, and rider to be safe, and comfortable, therefore we require everyone to follow our community guidelines. Failure to meet the expectations of our Community Guidelines can lead to permanent deactivation of account.</p>
                 <a class="content-more more-btn" href="{{url('Communityguidelines')}}">LEARN MORE <i class="fa fa-chevron-right"></i></a> 

                <!-- <p>Rate your driver and provide anonymous feedback about your trip. Your input helps us make every ride a 5-star experience.</p> -->
            </div>
        </div>


        </div>
    </div>


    </div>
</div>
<div class="row white-section no-margin">
        <div class="container ">
            
            <div class="col-md-6 content-block small">
                <div class="box-shadow">
                <!-- <div class="icon"><img src="{{asset('asset/img/taxi-app.png')}}"></div> -->
                <h2>Our support system </h2>
                <div class="title-divider"></div>
                <p>Our support system is available to you 24/7. However, if you’re in immediate danger, you should always contact authorities first. </p>
                <!-- <p>Every driver must undergo a multi-step background check prior to becoming a driver, and must pass an annual background check to continue. This includes criminal background check, and driving records. </p> -->
                <!-- <p>{{ Setting::get('site_title', '6ixTaxi')  }} is the smartest way to get around. One tap and a car comes directly to you. Your driver knows exactly where to go. And you can pay with either cash or card.</p> -->
                <a class="content-more more-btn" href="{{url('help')}}">How to reach us<i class="fa fa-chevron-right"></i></a> 
            </div>
        </div>

           

            <div class="col-md-6 content-block small">
                 <div class="box-shadow">
                 <!-- <div class="icon"><img src="{{asset('asset/img/budget.png')}}"></div> -->
                <h2>Real-time tracking </h2>
                <div class="title-divider"></div>
                <p>All trips are tracked from start to finish, so there’s a record of your trip if something happens.Using sensors and GPS data we can help detect if a trip has an unexpected long stop. If so, we'll check on you and offer resources to get help.</p>
                <!-- <p>At 6ixTaxi we want every driver, and rider to be safe, and comfortable, therefore we require everyone to follow our community guidelines. Failure to meet the expectations of our Community Guidelines can lead to permanent deactivation of account.</p> -->
                 <!-- <a class="content-more more-btn" href="{{url('Communityguidelines')}}">LEARN MORE <i class="fa fa-chevron-right"></i></a>  -->

                <!-- <p>Rate your driver and provide anonymous feedback about your trip. Your input helps us make every ride a 5-star experience.</p> -->
            </div>
        </div>


        </div>
    </div>


    </div>
</div>
    
    <?php $footer = asset('asset/img/footer-city.png'); ?>
    <!-- <div class="footer-city row no-margin" style="background-image: url({{$footer}});"></div> -->
@endsection


@section('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript">

$(document).ready(function () {

    $("#btnSubmit").click(function (event) {


    event.preventDefault();

    $.ajax({
       type: "POST",
       url: "{{url('/fare')}}",
       data: $("#idForm").serialize(),

       success: function(data)
       { 
           $("#div1").show();
           $("#div2").show();
           $("#btnSubmit").hide();
           $("#div1").html("Estimated Fare - "+data.estimated_fare+"$");
           $("#div2").html("Distance - "+data.distance+"mile(s)");


       }
     });


 

   });

});

</script>


@endsection



