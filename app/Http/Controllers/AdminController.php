<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Excel;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Libraries\Google;
use App\Libraries\VendorReport;
use App\Vendor;
use Chrisbjr\ApiGuard\Models\ApiKey;

class AdminController extends Controller
{   

    /**
     * Google API Lib
     */
    protected $google = null;

   /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct(Request $request)
    {
        $this->middleware('adminauth');

        if($request->user()) {
            $this->google = new Google($request->user()->google_token);
        }
    }

    /**
     * Display main controller
     *
     * @return Response
     */
    public function index()
    {
        return view('admin');
    }

    /**
     * Display api key
     *
     * @return Response
     */
    public function apiKey(Request $request) {
        return ApiKey::where('user_id', $request->user()->id)->first();
    }

    /**
     * Connect Google Account
     *
     * @return Response
     */
    public function connectGoogle(Request $request) {

        // Attempt OAuth connect
        return $this->google->connect($request->user()->id, $request->get('code'));

    }

    /**
     * Disconnect Google Account
     *
     * @return Response
     */
    public function disconnectGoogle(Request $request) {

        // Attempt OAuth connect
        $this->google->disconnect($request->user()->id);

        return response(array('status' => 'success'), 200);
    }

    /**
     * Sync Google Contacts
     *
     * @return Response
     */
    public function syncGoogleContacts(Request $request) {

        // attempt google sync
        return $this->google->syncContacts();

    }

    /**
     * Download Report
     */
    public function downloadReport($reportId, $vendorId = null) {

        $report = new VendorReport();
        return $report->download($reportId, $vendorId);
        
    }


    /**
     * Download Vendors
     */
    public function downloadVendors() {

        $vendors = Vendor::with('codes')->orderBy('business','asc')->get();
        
        $filename = 'vendors-'.date('Y-m-d');
        $excel = Excel::create($filename, 

            function($excel) use ($vendors)  {
                $excel->sheet('veendors', function($sheet) use ($vendors) {
                    $sheet->loadView('excel.vendors',[
                        'vendors' => $vendors
                        ]);
                });

        })->export('csv');
        
    }
}
