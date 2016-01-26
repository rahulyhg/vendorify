<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    
    protected $fillable = ['square_id','total','processing_fee','refunded','square_url','tender'];

    /**
     * Transactions
     */
    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }

}
