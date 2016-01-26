<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    // allowed fields
    protected $fillable = ['vendor_id','description','code','net','fees','quantity','custom','refund',
                            'discounts','gross','quantity','commision','total','processed_at']; 

    /**
     * Transactions Vendor
     */
    public function vendor() 
    {
        return $this->belongsTo('App\Vendor');
    }

    /**
     * Transactions Payment
     */
    public function payment() 
    {
        return $this->belongsTo('App\Payment');
    }

    // ---- mutators ---- //

    /**
     * Set transaction code
     *  - updating vendor_id on set
     *
     * @param  string  $value
     * @return string
     */
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = $value;

        // try to set vendor id
        $code = Code::where(array('name' => $value))->first();
        if($code) {
            $this->attributes['vendor_id'] = $code->vendor_id;
        }

    }

}
