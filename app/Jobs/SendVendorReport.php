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
use Excel;
use File;

class SendVendorReport extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $report;
    protected $start_date;
    protected $end_date;
    protected $include_rent;
    protected $cc;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($report, $start_date, $end_date, $include_rent, $cc)
    {
        $this->report = $report;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->include_rent = $include_rent;
        $this->cc = $cc;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // set vars
        $report = $this->report;
        $start_date = $this->start_date;
        $end_date = $this->end_date;
        $include_rent = $this->include_rent;
        $cc = $this->cc;

        // create temp report csv
        $business = preg_replace("/[^a-zA-Z0-9_]| /","_", strtolower($this->report['vendor']['business']));
        $business = preg_replace("/^_|_$/","",$business);
        $filename = $business.'-'.$start_date.'-'.$end_date;
        $filename = preg_replace("/_{2,}/","_",$filename);

        // create temp report csv
        $excel = Excel::create($filename, 
            function($excel) use ($report, $start_date, $end_date, $include_rent)  {
                $excel->sheet($start_date.' - '.$end_date, function($sheet) use ($report, $start_date, $end_date, $include_rent) {
                    $sheet->loadView('excel.vendorreport',[
                        'report' => $report,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'include_rent' => $include_rent
                        ]);
                });
        })->store('csv', false, true);

        // send email with csv attachement
        Mail::send('emails.vendorreport', [
                'report' => $report,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'include_rent' => $include_rent
            ],
            function ($message) use ($report, $start_date, $end_date, $excel, $cc) {
                $message->from(env('APP_EMAIL'), env('APP_BUSINESS'));
                $message->to($report['vendor']['email']);
                if($cc) {
                    $message->cc($cc);
                }
                $message->subject(env('APP_BUSINESS').' Sales Summary ( '.$start_date.' - '.$end_date.' )');
                $message->attach($excel['full']);
        });

        // delete temp report csv
        File::delete($excel['full']);
    }

}
