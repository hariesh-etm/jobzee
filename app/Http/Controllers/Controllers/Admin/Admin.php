<?php

namespace App\Http\Controllers\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Redirect;
use Route;
use Helper;
use Session;
use App;
use PDF;
use App\Models\Admin\Mcommon;

class Admin extends Controller
{
    private $data;
    private $method;
    private $helper;
    private $_user;
    private $mcommon;

    public function __construct(){
      $this->data=array();
      $this->_user = array();
      $this->mcommon = new Mcommon;
      $this->helper = new Helper();
      $url = Route::getCurrentRoute()->getActionName();
      $res = explode("@",$url);
      $this->method = $this->urlTokenizer($res[1]);
      $this->middleware(function ($request, $next) {
        if((!session()->has('auth_token')) && $this->method != "login" && $this->method != "page_not_found" && $this->method != "signup"){
          Redirect::to('/')->send();
          }
          if(session()->has('auth_token'))
          {
           //echo session('auth_token');
           $this->_user = $this->helper->getAuthentication();
          }
        return $next($request);
      });
      }
    function urlTokenizer($method){
      return $uri_method=str_replace(" ", '', lcfirst(ucwords(str_replace("-"," ",$method))));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function login(){
        if((session()->has('auth_token'))){
            Redirect::to('/')->send();
            }
        return view('admin.login');
      }

      function signup() {
        return view('admin.signup');
      }

      function dashboard(){
        // echo $this->_user['role'];
        return view('admin.dashboard', $this->data);
      }


     public function logout(Request $request)
    {
        // session_destroy();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
        //Redirect::to('/')->send();
    }

    public function page_not_found(){
        return view('admin.404');
    }




}
