<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function index()
    {
        return view('home', ['body_class' => 'home']);
    }

     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function privacy()
    {
        return view('privacy', ['body_class' => 'privacy']);
    }

     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function contact()
    {
        return view('contact', ['body_class' => 'contact']);
    }
}
