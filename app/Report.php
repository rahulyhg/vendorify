<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    
    // allowed fields
    protected $fillable = ['start_date','end_date','data','message','include_rent'];

}
