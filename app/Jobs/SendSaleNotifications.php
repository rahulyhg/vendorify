<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

// includes
use Log;
use Mail;

class SendSaleNotifications extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // set vars
        $data = $this->data;

        // send notification
        Mail::send('emails.salenotification', $data, function ($message) use ($data) {
            $message->from(env('APP_EMAIL'), env('APP_BUSINESS'));
            $message->to($data['vendor']->email);
            $message->subject(env('APP_BUSINESS').' Item Sold!');
        });

    }

}
