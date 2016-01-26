<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
  
    // allowed fields
    protected $fillable = ['vendor_id','name']; 

    /**
     * Vendor that the code belongs to
     */
    public function vendor()
    {
        return $this->belongsTo('App\Vendor');
    }
}
