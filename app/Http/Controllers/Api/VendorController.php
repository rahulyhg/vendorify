<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Config;
use App\Vendor;
use App\Code;
use App\Transaction;
//use DB;

use App\Http\Requests;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

class VendorController extends ApiGuardController
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
        // get all query parameters
        // TODO sort / paginate / search
        $filters = $request->all();
        
        return Vendor::with('codes')
                ->orderBy('business', 'asc')
                ->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        // create a new listing
        $vendor = new Vendor();
        $vendor->name = $request->name;
        $vendor->email = $request->email;
        $vendor->business = $request->business;
        $vendor->phone = $request->phone;
        $vendor->rent = $request->rent;
        $vendor->rate = $request->rate;
        $vendor->status = $request->status;
        $vendor->notes = $request->notes;
        $vendor->email_notification = $request->email_notification;
        $vendor->touch();


        // sync codes
        if($request->codes) {
            $newCodes = array();
            $codes = array();
            foreach($request->codes as $code) {
                array_push($newCodes, $code['name']);
                $code['vendor_id'] = $vendor->id;
                $code = Code::firstOrCreate($code);
                array_push($codes, $code);
            }
            foreach($codes as $code) {
                if(!in_array($code['name'], $newCodes)){
                    $code->delete();
                }
            }
        }

        return response(Vendor::with(['codes'])->find($vendor->id), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        // get a specific listing
        $vendor = Vendor::with(['transactions' => function ($query) {
            $query->orderBy('processed_at', 'desc');
            $query->limit(100);
        }, 'codes'])->find($id);

        return $vendor;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        // update a specific vendor
        $vendor = Vendor::with('codes')->find($id);
        $vendor->name = $request->name;
        $vendor->email = $request->email;
        $vendor->business = $request->business;
        $vendor->phone = $request->phone;
        $vendor->rent = $request->rent;
        $vendor->rate = $request->rate;
        $vendor->status = $request->status;
        $vendor->notes = $request->notes;
        $vendor->email_notification = $request->email_notification;
        $vendor->touch();

        // sync codes
        $codes = array();
        $newCodes = array();

        // current codes
        foreach($vendor->codes as $code) {
            array_push($codes, $code->name);
        }

        // new codes
        if(isset($request->codes)) {
            foreach($request->codes as $code) {
                array_push($newCodes, $code['name']);
            }
        }

        $deleteCodes = array_diff($codes, $newCodes);

        // sync codes
        if(count($deleteCodes)) {
            Code::where('name', $deleteCodes)->delete();
        }
        
        foreach($newCodes as $code) {
            Code::firstOrCreate(array(
                'vendor_id' => $vendor->id,
                'name' => $code
            ));
        }

        // check for transactions
        if(count($newCodes)) {
            $transactions = Transaction::where('code', $newCodes)->get();

            if(!$transactions->isEmpty()) {
                foreach($transactions as $transaction) {
                    $transaction->vendor_id = $vendor->id;
                    $transaction->save();
                }
            }
        }

        return response(Vendor::with(['transactions' => function ($query) {
            $query->orderBy('processed_at', 'desc');
            $query->limit(100);
        }, 'codes'])->find($id), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        // delete a specific vendor
        $vendor = Vendor::with('codes','transactions')->find($id);

        if(!$vendor->transactions->isEmpty()) {
            return response(array('status' => 'error', 'msg' => 'Cannot delete a vendor with transactions!'), 400);
        }

        if(!$vendor->codes->isEmpty()) {
            $vendor->codes()->delete();
        }

        $vendor->delete();

        return response(array('status' => 'success'), 200);
    }
}
