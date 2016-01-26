<?php

namespace App\Libraries;

use App\Report;
use App\Transaction;
use App\Vendor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\SendVendorReport;

use Excel;
use File;

class VendorReport
{
    use DispatchesJobs;

    /**
     * generate
     *  - generate a vendor report
     *
     * @param string $start
     * @param string $end
     * @param bool $includeRent
     *
     * @return array $report
     */
    public function generate($start, $end, $includeRent = false) {

        $unixStart = strtotime($start);
        $unixEnd = strtotime($end);
        $start = date('Y-m-d H:i:s', strtotime('midnight', $unixStart));
        $end = date('Y-m-d H:i:s', strtotime('tomorrow', $unixEnd)-1);

        // TODO paginate/break requests into multiple
        // recursive calls and make this a queable job
        // to overcome the 10k trans limit? This is
        // dependent on transaction volumes per pay period

        $transactions = Transaction::with('payment','vendor')
                        ->whereBetween('processed_at', [$start, $end])
                        ->orderBy('processed_at','desc')
                        ->limit(10000)
                        ->get();

        $report = array(
            'gross' => 0,
            'discounts' => 0,
            'net' => 0,
            'total' => 0,
            'rent' => 0,
            'fees' => 0,
            'payout' => 0,
            'commision' => 0,
            'vendors' => array(),
            'fees' => array(),
            'misc' => array()
        );

        // format
        foreach($transactions->all() as $transaction) {
            if(!$transaction->vendor) {
                $trans = $transaction->toArray();
                unset($trans['vendor']);
                $report['misc'][] = $trans;
                continue;
            }

            if(!isset($report['vendors'][$transaction->vendor->id])) {
                $report['vendors'][$transaction->vendor->id] = array(
                    'gross' => 0,
                    'discounts' => 0,
                    'net' => 0,
                    'total' => 0,
                    'rent' => $transaction->vendor->rent,
                    'commision' => 0,
                    'vendor' => $transaction->vendor->toArray(),
                    'transactions' => array()
                );
            }
            $trans = $transaction->toArray();
            unset($trans['vendor']);
            $report['vendors'][$transaction->vendor->id]['transactions'][] = $trans;
        }

        // calculate totals
        foreach($report['vendors'] as $vendor_id => $vendor) {
            foreach($vendor['transactions'] as $index => $transaction) {
                // calculate commision and total
                $rate = $transaction['custom'] ? 0 : $vendor['vendor']['rate']/100;
                $commision = ($transaction['net']*$rate);
                $total = $transaction['net']-$commision;

                // global
                $report['gross'] += $transaction['gross'];
                $report['discounts'] += $transaction['discounts'];
                $report['net'] += $transaction['net'];
                $report['total'] += $total;
                $report['commision'] += $commision;

                // vendor
                $report['vendors'][$vendor_id]['gross'] += $transaction['gross'];
                $report['vendors'][$vendor_id]['discounts'] += $transaction['discounts'];
                $report['vendors'][$vendor_id]['net'] += $transaction['net'];
                $report['vendors'][$vendor_id]['total'] += $total;
                $report['vendors'][$vendor_id]['commision'] += $commision;

                // add commision/total to transaction
                $report['vendors'][$vendor_id]['transactions'][$index]['total'] = $total;
                $report['vendors'][$vendor_id]['transactions'][$index]['commision'] = $commision;

                // fees
                if(!isset($report['fees'][$transaction['payment']['id']])) {
                    $report['fees'][$transaction['payment']['square_id']] = $transaction['payment']['processing_fee'];
                }
            }
        }

        // add vendors to the report that
        // didn't have any tx this pay period
        $vendors = Vendor::where('status', 'active')->orWhere('status', 'overdue')->orderBy('business', 'asc')->get();
        foreach($vendors->all() as $vendor) {
            if(!isset($report['vendors'][$vendor->id])) {
                $report['vendors'][$vendor->id] = array(
                    'gross' => 0,
                    'discounts' => 0,
                    'net' => 0,
                    'total' => 0,
                    'rent' => $vendor->rent,
                    'commision' => 0,
                    'payout' => 0,
                    'vendor' => $vendor->toArray(),
                    'transactions' => array()
                );
            }
        }

        // calculate payout / global rent values
        foreach($report['vendors'] as $vendor_id => $vendor) {
            $payout = $vendor['total'];
            $report['rent'] += $vendor['rent'];
            if($includeRent) {
                $payout = $vendor['total']-$vendor['rent']; 
            }
            $report['vendors'][$vendor_id]['payout'] = $payout;
            $report['payout'] += $payout;
        }

        // re-order vendors - map as sorted array
        $vendorCollection = new Collection($report['vendors']);
        $collection = $vendorCollection->sortBy(function($item){
            return $item['vendor']['business'];
        });
        $report['vendors'] = $collection->values()->all();

        // calculate vendor fees
        $fees = 0;
        foreach($report['fees'] as $fee) {
            $fees += $fee;
        }
        $report['fees'] = $fees;

        // store report
        $report = new Report(array(
            'start_date' => $start,
            'end_date' => $end,
            'data' => json_encode($report),
            'include_rent' => $includeRent
        ));
        $report->touch();

        // decode before sending client side
        $report->data = json_decode($report->data);

        return $report;
    }

    /**
     * send
     *  - send the report to all or a specific vendor
     *
     * @param int $reportId
     * @param int $vendorId
     * @param boolean $cc send copy to logged in email
     *
     * @return void
     */
    public function send($reportId, $vendorId = null, $message = '', $cc = false) {

        $reports = Report::find($reportId);
        $reports->data = json_decode($reports->data, true);

        $reportFound = false;
        $delay = 0;
        foreach($reports->data['vendors'] as $key => $report) {

            // set vendor report
            if($vendorId && $report['vendor']['id'] == $vendorId) {
                $reportFound = true;
            }

            // vendor not found yet
            if($vendorId && !$reportFound) {
                continue;
            }

            // create csv / send notification
            $report['message'] = $reportFound ? $message : $reports->message;
            $start_date = date('m.d.Y', strtotime($reports->start_date));
            $end_date = date('m.d.Y', strtotime($reports->end_date));
            $include_rent = $reports->include_rent;

            // queue SendVendorReport job
            $job = new SendVendorReport($report, $start_date, $end_date, $include_rent, $cc);
            $job->delay($delay);
            $job->onQueue('reports');
            $this->dispatch($job);

            // reset
            $reportFound = false;
            $delay+=5;
        }

    }

    /**
     * download
     *  - download a report in csv (or quickbooks ?) format
     *
     * @param int $reportId
     * @param int $vendorId
     * @param string $format
     *
     * @return void
     */
    public function download($reportId, $vendorId = null, $format = 'csv') {

        $reports = Report::find($reportId);
        $start_date = date('m.d.Y', strtotime($reports->start_date));
        $end_date = date('m.d.Y', strtotime($reports->end_date));

        if($vendorId) {

            $reports->data = json_decode($reports->data, true);

            foreach($reports->data['vendors'] as $report) {

                // set vendor report
                if($report['vendor']['id'] != $vendorId) {
                    continue;
                }

                // create csv / send notification
                $include_rent = $reports->include_rent;

                // create temp report csv
                $business = preg_replace("/[^a-zA-Z0-9_]| /","_",strtolower($report['vendor']['business']));
                $business = preg_replace("/^_|_$/","",$business);
                $filename = $business.'-'.$start_date.'-'.$end_date;
                $filename = preg_replace("/_{2,}/","_",$filename);

                $excel = Excel::create($filename, 
                    function($excel) use ($report, $start_date, $end_date, $include_rent)  {

                        $excel->sheet($start_date.'-'.$end_date, function($sheet) use ($report, $start_date, $end_date, $include_rent) {

                            $sheet->loadView('excel.vendorreport',[
                                'report' => $report,
                                'start_date' => $start_date,
                                'end_date' => $end_date,
                                'include_rent' => $include_rent
                                ]);
                        });

                })->export('csv');
            }

        } else {

            // create xls download
            $excel = Excel::create('summary_'.$start_date.'-'.$end_date, 
                function($excel) use ($reports)  {

                    // report data
                    $data = json_decode($reports->data, true);

                    // summary sheet
                    $excel->sheet('summary', function($sheet) use ($reports, $data) {
                        $sheet->loadView('excel.reportsummary',[
                            'reports' => $reports,
                            'data' => $data
                        ]);
                    });

                    // misc sheet
                    $misc = $data['misc'];
                    $excel->sheet('misc', function($sheet) use ($reports, $misc) {
                        $sheet->loadView('excel.reportmisc',[
                            'reports' => $reports,
                            'misc' => $misc
                        ]);
                    });

            })->export('xlsx');

        }

    }

}