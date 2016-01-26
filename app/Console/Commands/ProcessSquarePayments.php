<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\Square;

class ProcessSquarePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'square:process-payments {start?} {end?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Square payments, sending user notifications and storing payment transactions.';

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

        // Do square API request and process payments
        $square = new Square();
        $square->processPayments($start, $end);
    }
}
