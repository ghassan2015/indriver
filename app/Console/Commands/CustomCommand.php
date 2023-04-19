<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Http\Controllers\SendPushNotification;
use App\Http\Controllers\AdminController;
use Carbon\Carbon;
use Setting;
use App\Provider;
use App\RequestFilter;
class CustomCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob:rides';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updating the Scheduled Rides Timing';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $UserRequest = DB::table('user_requests')->where('status','SCHEDULED')
                        ->where('schedule_at','<=',\Carbon\Carbon::now()->addMinutes(5))
                        ->get();

        $hour =  \Carbon\Carbon::now()->subHour();
        $futurehours = \Carbon\Carbon::now()->addMinutes(5);
        $date =  \Carbon\Carbon::now();           

        // \Log::info("Schedule Service Request Started.".$date."==".$hour."==".$futurehours);

        if(!empty($UserRequest)){
            foreach($UserRequest as $ride){
                DB::table('user_requests')
                        ->where('id',$ride->id)
                        ->update(['status' => 'STARTED', 'assigned_at' =>Carbon::now() , 'schedule_at' => null ]);

                 //scehule start request push to user
                (new SendPushNotification)->user_schedule($ride->user_id);
                 //scehule start request push to provider
                (new SendPushNotification)->provider_schedule($ride->provider_id);

                DB::table('provider_services')->where('provider_id',$ride->provider_id)->update(['status' =>'riding']);
            }
        }



         /// New schedule ride function 

        $UserRequest_new = DB::table('user_requests')->where('status','SCHEDULES')
                        ->where('schedule_at','<=',\Carbon\Carbon::now()->addMinutes(30))
                        ->get();

        if(!empty($UserRequest_new)){
            foreach($UserRequest_new as $ride){
 
                $distance = Setting::get('provider_search_radius', '10');
                $latitude = $ride->s_latitude;
                $longitude = $ride->s_longitude;
                $service_type = $ride->service_type_id;

                $Providers = Provider::with('service')
                    ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"),'id')
                    ->where('status', 'approved')
                    ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                    ->whereHas('service', function($query) use ($service_type){
                                $query->where('status','active');
                                $query->where('service_type_id',$service_type);
                            })
                    ->orderBy('distance','asc')
                    ->get();


                    if(Setting::get('manual_request',0) == 0){
                        foreach ($Providers as $key => $Provider) {

                            if(Setting::get('broadcast_request',0) == 1){
                               (new SendPushNotification)->IncomingRequest($Provider->id); 
                            }

                            $Filter = new RequestFilter;
                            // Send push notifications to the first provider
                            // incoming request push to provider
                            
                            $Filter->request_id = $ride->id;
                            $Filter->provider_id = $Provider->id; 
                            $Filter->save();
                        }
                    }


                  
                        DB::table('user_requests')
                            ->where('id',$ride->id)
                            ->update(['status' => 'SEARCHING','assigned_at' => \Carbon\Carbon::now(),'schedule_at' => null]);  
                    

                    // DB::table('user_requests')
                    //     ->where('id',$ride->id)
                    //     ->update(['status' => 'STARTED', 'assigned_at' =>Carbon::now() , 'schedule_at' => null ]);


                 //scehule start request push to user
                (new SendPushNotification)->user_schedule($ride->user_id);
                 //scehule start request push to provider
                (new SendPushNotification)->provider_schedule($ride->provider_id);

                //DB::table('provider_services')->where('provider_id',$ride->provider_id)->update(['status' =>'riding']);
            }
        }    
        /// End New schedule ride function 


        $CustomPush = DB::table('custom_pushes')
                        ->where('schedule_at','<=',\Carbon\Carbon::now()->addMinutes(5))
                        ->get();

        if(!empty($CustomPush)){
            foreach($CustomPush as $Push){
                DB::table('custom_pushes')
                        ->where('id',$Push->id)
                        ->update(['schedule_at' => null ]);

                // sending push
                (new AdminController)->SendCustomPush($Push->id);
            }
        }


    }
}
