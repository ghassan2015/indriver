@extends('user.layout.app')

@section('content')
<div class="row gray-section no-margin">
    <div class="container">
        <div class="content-block">
            <h2>{{ $title }}</h2>
            <div class="title-divider"></div>
            <p>{!! Setting::get($page) !!}</p>
            {{-- <p>We are committed to protecting your privacy and being transparent about how We handle your personal data.</p>
            <p>We are committed to protecting your privacy and being transparent about how We handle your personal data.
            </p>
            <p>This Privacy Policy forms an integral part of the Terms and all capitalized terms used herein shall have the     meanings set forth in the Terms. Please note that additional privacy policy of the distribution platforms may apply depending on the means of access to the Services you use.</p>
            <p>This Privacy Policy forms an integral part of the Terms and all capitalized terms used herein shall have the meanings set forth in the Terms. Please note that additional privacy policy of the distribution platforms may apply depending on the means of access to the Services you use.</p>
            <ul>
                <li>Identification and contact details (such as name and e-mail)</li>
                <li>Account details (such as password, authentication token, profile picture, referral), settings and preferences</li>
                <li>Game data (including chat conversations)</li>
                <li>Service use data (such as cookies data, device, IP, error logs)</li>
                <li>Support inquiries</li>
               
            </ul>
            <h2>Purchase and payment history</h2>
            <p>We do not process your payment method details such as card numbers (this data is processed solely by the provider of the payment solution and therefore third-party privacy policy applies).</p>
            <p>The provision of identification data necessary for contract conclusion and data necessary for your authentication is obligatory. The scope of the data we need may vary depending on the means of access to the Services you use (e.g. Facebook Login). The provision of other data is entirely voluntary. Some of the processed data is collected by automated means, including third party trackers and analytic tools (e.g. Rollbar and Google Analytics).</p>
            <h2>How do We use your data (purpose and legal basis)?</h2>
            <p>We may process your personal data specified above for the following purposes</p>
            <ul>
                <li>Provision of the Services including support (legal basis of a contract performance)</li>
                <li>Sale of merchandise (legal basis of a contract performance)</li>
                <li>Organization of contests, sweepstakes and promotions (legal basis of a contract performance)</li>
                <li>Development and improvement of the Services (legal basis of our legitimate interests in providing better Services)</li>
                <li>Compliance (legal basis of compliance with our legal obligations)</li>
                <li>Protection of our rights and interests (legal basis of our legitimate interests in keeping ourselves harmless)</li>
                <li>Protection of our rights and interests (legal basis of our legitimate interests in keeping ourselves harmless)</li>
                <li>You will never be a subject to a decision based solely on automated processing, including profiling, that would produce legal effects concerning you or would similarly significantly affect you</li>
            </ul>
            <p>Where the processing is based on consent, you have the right to withdraw your consent at any time.</p>
            <h2>How long do We keep your data?</h2>
            <p>We process the personal data of our users for the following time periods:</p>
            <ul>
                <li>Duration of our contractual relationship (where the legal basis is the performance of a contract)</li>
                <li>Period specified by law (where the legal basis is compliance with our legal obligations)</li>
                <li>Period for which you have granted the consent or until the consent withdrawal (where the legal basis is your consent)</li>
                <li>Period necessary to fulfil the purpose of the processing or until a justified objection is made (where the legal basis of legitimate interests applies)</li>
                <li>We will cease to process your personal data and We will destroy all its copies once We will not have a valid legal basis for the processing</li>
            </ul>
            <p>You can delete your Sim Companies account at any time. After the account deletion, We can still process your personal data for the compliance purposes and for the purposes of protecting our rights and interests.</p>
            <p>Data transfers and disclosures</p>
            <p>Some of your game and account data is publicly available within the Services. Otherwise, We try to limit the personal data transfers and disclosures to what is strictly necessary. When We transfer or disclose your personal data to a third-party recipient it is always only on a need-to-know basis.</p>
            <p>Your personal data may be transferred or disclosed to the following recipients:</p>
            <ul>
                <li>Other users of the Services</li>
                <li>Our employees</li>
                <li>Data processors (e.g. cloud storage, software tools, distribution platforms and payment solution providers)</li>
                <li>State authorities (in cases required by applicable laws)</li>
            </ul>
            <p>Our Services rely on some service providers (data processors – e.g. Heroku) established in the USA. There is no EU Commission decision on adequate protection for transfers of personal data to the USA and therefore the transfers are carried out on the basis of appropriate safeguards in the form of standard contractual clauses or "Privacy Shield" certification. A copy of your personal data processed by these data processors can be obtained through Us.</p>
            <p>Notifications and newsletter</p>
            <p>We are sending our users transactional e-mails, including game notifications, as well as newsletters that can have a nature of commercial communication. You can unsubscribe from our newsletter at any time by clicking the link in the footer of any newsletter e-mail. You can also always change your email preferences in your Sim Companies account.</p>
            <p>Cookies<br>
Our Services use cookies and similar technologies as further described in our Cookie Policy.</p>
            <p>Your rights<br>
You have the following rights with regards to your personal data being processed (if the statutory conditions are met):</p>
            <ul>
                <li>Right to withdraw given consent to the processing at any time</li>
                <li>Right to be informed of your personal data We process and to request access to it</li>
                <li>Right to have your personal data rectified where it is inaccurate or incomplete</li>
                <li>Right to request temporary restriction of the processing</li>
                <li>Right to have your personal data erased</li>
                <li>Right to object to processing based on our legitimate interests or to processing for direct marketing purposes</li>
                
            </ul>
            <p>Right to receive a copy of your personal data in a machine-readable format and to transmit it freely to another service.</p>
            <p>In order to exercise any of the above-mentioned rights, please contact Us using our contact details below. You can also contact Us with any question you may have regarding this Privacy Policy.</p>
            <p>If you have a complaint about the way We handle your personal data, you have also the right to address this directly with the data protection authority (Czech Office for Personal Data Protection).</p>
            <p>
                Contact details<br>
Sim Companies s.r.o. (data controller)<br>
Id. No.: 07198248<br>
Registered seat: Slévacská 1108/1b, 198 00 Prague<br>
Email: support@simcompanies.com
            </p> --}}
        </div>
    </div>
</div>
@endsection