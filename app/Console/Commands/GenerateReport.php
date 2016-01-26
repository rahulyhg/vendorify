<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\VendorReport;
use Mail;

class GenerateReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:generate {start} {end} {--include-rent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate reports for a specific time range.';

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
        // generate a transaction report
        $start = $this->argument('start');
        $end = $this->argument('end');
        $includeRent = $this->option('include-rent');

        $report = new VendorReport();
        $report->generate($start, $end, $includeRent);
    }
}
