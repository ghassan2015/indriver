@extends('user.layout.app')

@section('content')
<div class="row gray-section no-margin">
    <div class="container">
        <div class="content-block">
            <h2>{{ $title }}</h2>
            <div class="title-divider"></div>
            <h3>COMMUNITY GUIDELINES (SHOULD FALL UNDER A SAFETY AS A SUBPAGE)</h3>
            <p>We want to make sure every ride is safe, respectful, and comfortable. This applies to everyone across our apps, including both drivers, and riders alike. Everyone who signs up for a 6ixTaxi account is required to follow our safety guidelines, and procedures.</p>
            <p><h3>Riders:</h3>Failure to abide to our Terms of Service or other policies can result in temporary or permanent deactivation of account. </p>
            <p>To ensure a safe and respectful ride, do not:</p>
            <ul>
                <li>Bring a weapon along on a ride. </li>
                <li>Transport a child without a safety seat appropriate for the child’s weight</li>
                <li>Request rides for groups that cannot fit in the specified ride type</li>
                <li>Violate road safety laws</li>
                <li>Discriminate against another member of the community on the basis of race, color, religion, national origin, disability, sexual orientation, sex, marital status, gender identity, age, military status, or any other characteristic protected under law</li>
                <li>Touch drivers or other riders without their explicit consent</li>
                <li>Use abusive, discriminatory, sexual, or inappropriate language, behavior, or gestures</li>
                <li>Smoke, vape, or consume alcohol during your ride</li>
                <li>Damage drivers’ or other riders’ property</li>
                <li>Engage in fraudulent behavior, including but not limited to: using a stolen phone or credit card to request a ride, coupon phishing, or manipulating reviews for ride credit</li>
            </ul>
            <p><h3>Drivers rules:</h3>Failure to abide to our Terms of Service or other policies can result in temporary or permanent deactivation of account. To ensure a safe and respectful ride, do not </p>
            
            <ul>
                <li>Drive under the influence of drugs or alcohol </li>
                <li>Bringing a weapon along while driving under 6ixTaxi </li>
                <li>Physically touch riders or other drivers without their explicit consent</li>
                <li>Use abusive, discriminatory, sexual, or inappropriate language, behavior, or gestures</li>
                <li>Engage in unsafe driving behaviors including but not limited to: speeding, running red lights and stop signs, tailgating, and making lane changes without proper signaling</li>
                <li>Discriminate against another member of the community on the basis of race, color, religion, national origin, disability, sexual orientation, sex, marital status, gender identity, age, military status, or any other characteristic protected under law</li>
                <li>Refuse to provide a ride to a rider with a service animal, or with a wheelchair that can safely and securely fit in your car’s trunk or backseat without obstructing the driver’s view</li>
                <li>Damage riders’ property, or retain and refuse to return a rider’s items</li>
            </ul>
        </div>
    </div>
</div>
@endsection