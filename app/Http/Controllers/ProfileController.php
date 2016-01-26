<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;


class ProfileController extends Controller
{   

   /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display main controller
     *
     * @return Response
     */
    public function index()
    {
        return view('profile');
    }

}
