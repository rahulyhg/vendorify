<?php

namespace App\Libraries;

use App\Payment;
use App\Code;
use App\Transaction;

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\SendSaleNotifications;

class Square
{
    use DispatchesJobs;

    /**
     * @var string $token
     */
    private $token;

    /**
     * @var string url
     */
    private $url = 'https://connect.squareup.com/v1/me/';

    /**
     * @var array $response
     */
    protected $response = array();

    /**
     * default constructor
     */
    public function __construct() {
        $this->token = env('SQUARE_TOKEN','');
    }

    /** 
     * processPayments
     *  - do a square API request and process payments
     * 
     * @return void
     */
    public function processPayments($start = null, $end = null) {
        $notifications = array();
        
        $start = $start ? $start : '-1 day';
        $end = $end ? $end : 'now';
        $start = gmdate("Y-m-d\TH:i:s\Z", strtotime($start));
        $end = gmdate("Y-m-d\TH:i:s\Z", strtotime($end));

        // do request
        $square_payments = $this->request('payments?begin_time='.$start.'&end_time='.$end);

        foreach($square_payments as $square_payment) {
            foreach($square_payment as $sp) {

                // get payment tender types
                if(isset($sp['tender'])) {
                    $tender = '';
                    foreach($sp['tender'] as $key => $sp_item) {
                        if($key > 0) { 
                            $tender = ','; 
                        }
                        $tender .= $sp_item['type'];
                    }
                }

                // create payment
                $payment = Payment::firstOrCreate(array(
                    'square_id' => $sp['id'],
                    'total' => $sp['total_collected_money']['amount']/100,
                    'processing_fee' => $sp['processing_fee_money']['amount']/100,
                    'refunded' => $sp['refunded_money']['amount']/100,
                    'square_url' => $sp['payment_url'],
                    'tender' => $tender
                ));

                // if just created, process
                if($payment->wasRecentlyCreated) {

                    // loop through and store itemizations (transactions)
                    foreach($sp['itemizations'] as $item) {

                        // attempt to determine refunds
                        $itemRefunded = false;
                        if(count($sp['refunds'])) {
                            foreach($sp['refunds'] as $refund) {
                                $refundAmt = ($refund['refunded_money']['amount'] * -1);
                                if($item['total_money']['amount'] == $refundAmt) {
                                    $itemRefunded = true;
                                }
                            }
                        }

                        // create item
                        $net = $item['net_sales_money']['amount']/100;
                        $gross = $item['gross_sales_money']['amount']/100;
                        $transaction = new Transaction(array(
                            'code' => $item['name'],
                            'description' => isset($item['notes']) ? $item['notes'] : 'undefined',
                            'net' => $itemRefunded ? ($net * -1) : $net,
                            'discounts' => $item['discount_money']['amount']/100,
                            'gross' => $itemRefunded ? ($gross * -1) : $gross,
                            'quantity' => intval($item['quantity']),
                            'processed_at' => date('Y-m-d H:i:s', strtotime($sp['created_at'])),
                            'refund' => $itemRefunded
                        ));

                        // set payment
                        $transaction->payment()->associate($payment->id);
                        $transaction->touch();

                        // try to associate vendor
                        // and calculate commision rate
                        $code = Code::with('vendor')->where('name', $item['name'])->first();
                        
                        if($code) {
                            // associate
                            $transaction->vendor()->associate($code->vendor_id);
                            $transaction->touch();
                            
                            // Calculate commision
                            // Note: Not persisted in db
                            $rate = $code->vendor->rate/100;
                            $transaction->commision = $transaction->net*$rate;
                            $transaction->total = $transaction->net-$transaction->commision;

                            // vendor notifications
                            if(!isset($notifications[$code->vendor_id])) {
                                $notifications[$code->vendor_id] = array();
                            }
                            $notifications[$code->vendor_id]['vendor'] = $code->vendor;
                            $notifications[$code->vendor_id]['transactions'][] = $transaction;
                        }
                        
                    }
                }

            }

        }

        // queue notifications
        $delay = 0;
        foreach($notifications as $key => $data) {
            if(!$data['vendor']->email_notification) continue;

            $job = new SendSaleNotifications($data);
            $job->onQueue('notifications');
            $job->delay($delay);
            $this->dispatch($job);
            $delay+=5;
        }
    }

    /**
     * request
     *  - perform api curl request
     *
     * @param string $endpoint
     * @return array $response
     */
    protected function request($endpoint, $pagination = null) {

        $url = $pagination ? $pagination : $this->url.$endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '.$this->token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);

        // parse header/body from response
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close ($ch);
        $headers = substr($response, 0, $header_size);
        $this->response[] = json_decode(substr($response, $header_size), true);

        // check for pagination
        preg_match("/Link: <(.*?)>;rel='next'/", $headers, $matches);
        if(count($matches)) {
            $next = $matches[1];
            return $this->request(null, $next);
        }

        // return json response
        return $this->response;
    }

}