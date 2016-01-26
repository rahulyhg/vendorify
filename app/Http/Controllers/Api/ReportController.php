<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Config;
use App\Report;
use App\Libraries\VendorReport;

use App\Http\Requests;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

class ReportController extends ApiGuardController
{

   /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct(Request $request)
    {   
        // check for an access token in header - if
        // it doesn't exist, use adminauth middleware
        $key = $request->header(Config::get('apiguard.keyName', 'X-Authorization'));

        if($key) {
            // construct parent (ApiGuard Controller)
            parent::__construct();
        } else {
            $this->middleware('adminauth');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {       
        $reports = Report::orderBy('id','desc')->get();

        // remove data when viewing list of all reports

        foreach($reports as $key => $report) {
            unset($reports[$key]->data);
        }
        return $reports;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        // TODO - handled exclusively by library?

        return response(['status' => 'Not Implemented'], 501);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        // get a specific transaction
        $report = Report::find($id);
        $report->data = json_decode($report->data);
        return $report;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {

        $report = Report::find($id);
        $report->message = $request->message;
        $report->touch();

        $report->data = json_decode($report->data);

        return response(array('status' => 'success','report' => $report), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        // delete a specific listingData
        Report::find($id)->delete();

        return response(array('status' => 'success'), 200);
    }

    /**
     * Generate a Vendor Report
     *
     * @return Response
     */
    public function generate(Request $request) {

        $vendor_report = new VendorReport();
        $report = $vendor_report->generate($request->start, $request->end, $request->rent);

        return response(array('status' => 'success','report' => $report), 200);
    }

    /**
     * Send a Vendor Report
     *
     * @return Response
     */
    public function send(Request $request) {

        $reportId = $request->reportId;
        $vendorId = $request->vendorId;
        $message = $request->message;
        $cc = $request->cc ? $request->user()->email : false;

        $vendorReport = new VendorReport();
        $vendorReport->send($reportId, $vendorId, $message, $cc);

        return response(array('status' => 'success'), 200);
    }

}
