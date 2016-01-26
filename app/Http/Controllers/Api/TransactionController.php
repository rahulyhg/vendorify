<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Config;
//use App\Vendor;
use App\Transaction;

use App\Http\Requests;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;

class TransactionController extends ApiGuardController
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
    public function index(Request $request, $vendorId)
    {
        $query = Transaction::where('vendor_id', $vendorId);

        if($request->get('date')) {
            $day = date('Y-m-d', $request->get('date'));
            $query->whereBetween('processed_at', [$day.' 00:00:00', $day.' 23:59:59']);
        }
        $query->orderBy('processed_at','desc');

        return $query->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request, $vendorId)
    {
        // create a new listingData
        $data = $request->all();
        $transaction = new Transaction($data);
        $transaction->vendor()->associate($vendorId);
        $transaction->touch();

        return response(['status' => 'success', 'transaction' => $transaction], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(Request $request, $vendorId, $id)
    {
        // get a specific transaction
        return Transaction::where('vendor_id', $vendorId)->find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $vendorId, $id)
    {

        // update a specific listingData
        $transaction = Transaction::where('vendor_id', $vendorId)->find($id);
        $transaction->update($request->all());

        return response(array('status' => 'success', 'transaction' => $transaction), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(Request $request, $vendorId, $id)
    {
        // delete a specific listingData
        Transaction::where('vendor_id', $vendorId)->find($id)->delete();

        return response(array('status' => 'success'), 200);
    }
}
