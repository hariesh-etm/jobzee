<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\Mcommon;
use Route;
use Session;
use Response;
use Illuminate\Support\Facades\DB;
use Helper;
use PDF;

class Admin extends Controller
{

  private $_response_data;
  private $_user;
  private $mcommon;
  private $data;
  private $request;
  private $helper;

  public function __construct(Request $request){

  $this->request = $request;
  $this->mcommon = new Mcommon;
  $this->_response_data = array();
  $this->data = array();
  $this->_user = array();
  $this->helper = new Helper();

    //session_start();
    $url = Route::getCurrentRoute()->getActionName();
    $res = explode("@",$url);
    $method = $res[1];
    if(!in_array($method, array('register','login','generateuser'))){

      $token = $request->bearerToken();
      $this->_user = $this->helper->getAuthentication($token);
    }

  }

  public function buildResponse()
  {
    if($this->_response_data['code'] && $this->_response_data['code']!='' && !isset($this->_response_data['message']))
      $this->_response_data['message']=__('api.'.$this->_response_data['code']);
    if($this->_user == 401){
      $response_data = array('status' => 'FAILED', 'code' => '401', 'message' => __('api.501'));
      return Response::json($response_data, 200);
    }else {
      return Response::json($this->_response_data, 200);
    }
  }

  function checkValidator($rules){
    $validator = Validator::make($this->request->all(), $rules);
    if($validator->fails()){
      $this->_response_data['status']="FAILED";
      $this->_response_data['code']="110";
      $this->_response_data['message']=$validator->errors();
      return true;
    }else{
      return false;
    }
  }


  function login(){
    $rules=$this->helper->makeValidateRules($this->request->all());
    $this->checkValidator($rules);
    $username=$this->request->input('username');
    $password=$this->request->input('password');
    // echo $this->helper->encrypt($username);
    if($username && $password){
      $user=$this->mcommon->verifyUser($username, $password);
      if(!empty($user)){
        if($user->status=='1'){
          $auth_token=$this->helper->encrypt($this->helper->encodeData($user->id)."|".($user->role)."|".time());
          $session_data = [
            'auth_token'  => $auth_token,
            'display_name' => $username,
            'logged_in' => true,
          ];
          session()->put('auth_token', $auth_token);
          session()->put('role', $user->role);
          session()->put('display_name', $username);
          session()->put('logged_in', true);
          session()->put('user_id', $user->id);
          $this->_response_data['status']="SUCCESS";
          $this->_response_data['auth_token']=$auth_token;
          $this->_response_data['role']=$user->role;
          $this->_response_data['code']="203";


        }else{
          $this->_response_data['status']="FAILED";
          $this->_response_data['code']="107";
        }
      }else{
        $this->_response_data['status']="FAILED";
        $this->_response_data['code']="103";
      }
    }else{
      $this->_response_data['status']="FAILED";
      $this->_response_data['code']="102";
    }
    return $this->buildResponse();

  }

  function register(){
    $rules=$this->helper->makeValidateRules($this->request->all());
    $this->checkValidator($rules);
    $firstname=$this->request->input('firstname');
    $lastname=$this->request->input('lastname');
    $email=$this->request->input('email');
    $phone=$this->request->input('phone');
    $address=$this->request->input('address');
    $country=$this->request->input('country');
    $state=$this->request->input('state');
    $city=$this->request->input('city');
    $pincode=$this->request->input('pincode');
    if($firstname != "" && $lastname != "" && $email != "" && $phone != "" && $lastname != ""){
    $item=$this->mcommon->register($firstname,$lastname,$email,$phone,$address,$country,$state,$city,$pincode);
    if($item == 'A'){
        $this->_response_data['status']="FAILED";
        $this->_response_data['code']="103";
    }else{
        $this->_response_data['status']="SUCCESS";
        $this->_response_data['code']="203";

    }
    }else{
        $this->_response_data['status']="FAILED";
        $this->_response_data['code']="102";
    }
    return $this->buildResponse();
  }

  }
