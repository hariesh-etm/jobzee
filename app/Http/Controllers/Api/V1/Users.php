<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\Musers;
use Route;
use Session;
use Response;
use Illuminate\Support\Facades\DB;
use Helper;


class Users extends Controller
{

  private $_response_data;
  private $_user;
  private $musers;
  private $data;
  private $request;
  private $helper;

  public function __construct(Request $request){

  $this->request = $request;
  $this->musers = new Musers;
  $this->_response_data = array();
  $this->data = array();
  $this->_user = array();
  $this->helper = new Helper();
    $url = Route::getCurrentRoute()->getActionName();
    $res = explode("@",$url);
    $method = $res[1];
    if(!in_array($method, array('register','login'))){
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
      $this->_response_data['message']=$validator->errors()->all();
    }
  }
  function listUsersgt(){
      $limit=$this->request->input('limit');
      $offset=$this->request->input('offset');
      $role=$this->request->input('search_role');
      $status=$this->request->input('search_status');
      $list=$this->musers->getUsersListGt($role, $status, $limit, $offset);
      $total_size=0;
      $record=array();
      if(!empty($list) && isset($list[0]) && isset($list[0]->total_size)){
        $record=$this->helper->setEncryptValue($list, array('id'), true);
        $total_size=$list[0]->total_size;
      }
      $this->_response_data['status']="SUCCESS";
      $this->_response_data['list']=$record;
      $this->_response_data['total_size']=$total_size;
      $this->_response_data['code']="206";


    return $this->buildResponse();
  }

  function getallUsers(){
    $list=$this->musers->getallUsers();
    if(!empty($list)){
      $this->_response_data['status']="SUCCESS";
      $this->_response_data['list']=$list;
      $this->_response_data['code']="206";
    }else{
      $this->_response_data['status']="FAILED";
      $this->_response_data['code']="109";
    }
  return $this->buildResponse();
}

  function listUsersdt(){
    $postData = $this->request->input();
    $data = $this->musers->getUsersListDt($postData);
    // print_r($this->_user['user_id']);
    // exit;
    return $data;
  }

  function listUsersById (){
    $id=$this->request->input('id');
    if($id){
        $list=$this->musers->getUsersListById($id);
        $record=array();
        if(!empty($list) ){
            $record=$list;
            $record->username=$record->username?$this->helper->decrypt($record->username):null;
            $record->password=$record->password?$this->helper->decrypt($record->password):null;
        }
        $this->_response_data['status']="SUCCESS";
        $this->_response_data['list']=$record;
        $this->_response_data['code']="206";
    }else{
        $this->_response_data['status']="FAILED";
        $this->_response_data['code']="207";
    }
    return $this->buildResponse();
 }

  function createUsers(){
      $rules=$this->helper->makeValidateRules($this->request->all());
      $this->checkValidator($rules);


            $username=$this->request->input('username');
			$password=$this->request->input('password');
			$fname=$this->request->input('firstName');
			$lname=$this->request->input('lastName');
			$email=$this->request->input('email');
			$dob=$this->request->input('dob');
			$role=$this->request->input('role');
			$address=$this->request->input('address');
			$created_by=$this->_user['user_id'];
      if($fname != "" && $email != "" && $dob != ""){
        $i_status=$this->musers->createUsers($username,$password,$fname,$lname,$email,$dob,$role,$address,$created_by);
        if($i_status=='A'){
          $this->_response_data['status']="FAILED";
          $this->_response_data['code']="208";
        }else if($i_status=='E'){
          $this->_response_data['status']="FAILED";
          $this->_response_data['code']="105";
        }else{
            $id = $i_status;
            $image_url = '';

            if (isset($_FILES['media']) && !empty($_FILES['media']) ) {
            $media_name = $_FILES['media']['name'];
            $media_type = $_FILES['media']['type'];
            $media_tmp_name = $_FILES['media']['tmp_name'];
            $media_error = $_FILES['media']['error'];
            $media_size = $_FILES['media']['size'];
            $tmp = explode(".", $media_name);
            $media_ext = array_pop($tmp);
            if ($media_ext == 'gif' || $media_ext == 'jpg' || $media_ext == 'jpeg' || $media_ext == 'png' || $media_ext == 'PNG') {
            $file_name = md5("profile|media|" . date('Y-m-d H:i:s'));
            $target_dir = "cdn/profile/o/".$id;
            $return = $this->helper->saveCdn($media_ext, $media_tmp_name, $media_error, $file_name, $target_dir);
            if ($return['status'] == 'S') {
            $m_target_dir = "cdn/profile/m/".$id;
            $this->helper->imageCrop($return['url'], $m_target_dir, $file_name, $media_ext, 620, 400); //resize the image
            $image_url = $m_target_dir . '/' . $file_name . '.' . $media_ext;
            } else {
            $invalid_image_format = true;
            }
            } else {
            $invalid_image_format = true;
            }
            if (!empty($image_url)  ) {
              $this->musers->profileupload($image_url, $id);
              }
          }
          $this->_response_data['status']="SUCCESS";
          $this->_response_data['code']="201";

        }
      }else{
        $this->_response_data['status']="FAILED";
        $this->_response_data['code']="109";
      }

    return $this->buildResponse();
  }
  function updateUsers(){
      $rules=$this->helper->makeValidateRules($this->request->all());
      $this->checkValidator($rules);
      $id=$this->request->input('u_id');
      $username=$this->request->input('u_username');
      $password=$this->request->input('u_password');
      $fname=$this->request->input('u_firstName');
      $lname=$this->request->input('u_lastName');
      $email=$this->request->input('u_email');
      $dob=$this->request->input('u_dob');
      $role=$this->request->input('u_role');
      $address=$this->request->input('u_address');
      $updated_by=$this->_user['user_id'];

      if($fname != "" && $email != "" && $dob != ""){
        $i_status=$this->musers->updateUsers($id,$username,$password,$fname,$lname,$email,$dob,$role,$address,$updated_by);
        if($i_status == 'S'){
          $this->_response_data['status']="SUCCESS";
          $this->_response_data['code']="202";
        }else{
          $this->_response_data['status']="FAILED";
          $this->_response_data['code']="209";
        }
      }else{
        $this->_response_data['status']="FAILED";
        $this->_response_data['code']="109";
      }

    return $this->buildResponse();
  }
  function deleteUsers(){
        $id=$this->request->input('id');
        if($id){
        $status=$this->musers->deleteUsers($id);
        if($status){
          $this->_response_data['status']="SUCCESS";
          $this->_response_data['code']="205";
        }else{
          $this->_response_data['status']="FAILED";
          $this->_response_data['code']="302";
        }
      }else{
        $this->_response_data['status']="FAILED";
        $this->_response_data['code']="109";
      }
    return $this->buildResponse();
  }
}
