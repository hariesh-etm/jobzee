<?php

namespace App\Helpers;
use Session;
use Illuminate\Support\Facades\Validator;

class Helper {
  function isAdminLoggedin($pages){
    $user=$this->getAuthentication();
    $url=null;
    if(isset($user['role'])){
      if(session()->get('logged_in') && $pages['parent']=='login'){
        $url=url('dashboard');
      }else if(!session()->get('logged_in') && $pages['parent']!='login'){
        $url=url('admin');
      }
    }else if(isset($user['role'])){
      $url=url('admin');
    }else if($user==401){
      $url=url('admin');
    }
    return $url;
  }
  public function getAuthentication($access_token=null)
  {
    $authorization=null;
    if ($access_token) {
      $authorization=$access_token;
    }else{
      if(session()->get('auth_token')){
        $authorization=session()->get('auth_token');
      }
    }
    $authorized = false;
    $token_expired = false;
    if ($authorization) {
      $authorization_details = explode('|', $this->decrypt($authorization));
      if(!empty($authorization_details) && sizeof($authorization_details) >= 3) {
        $authorized = true;
      } else {
        $authorized = false;
      }
    }
    if ($authorized) {
      return $this->setUserInfo($authorization_details);
    } else {
      return $this->sendUnauthorized();
    }
  }
  function setUserInfo($item){
    if (count($item) >= 3) {
      $data=array(
        'user_id'=>isset($item[0])?$this->decodeData($item[0]):NULL,
        'role'=>isset($item[1])?$item[1]:NULL,
        'logged_in_time'=>isset($item[4])?$item[4]:NULL
      );
    } else {
      $data = array('user_id' => null, 'role' => null, 'logged_in_time' => null);
    }
    return $data;
  }
  function sendUnauthorized(){
    return "401";
  }
  function base64clean($base64string){
		$base64string = str_replace(array('=','+','/'),'',$base64string);
		return $base64string;
	}
	function encodeData($string) {
		if (isset($string) || !empty($string)) {
			$string = trim($string);
			$string = base64_encode(@serialize($string));
			return $this->base64clean($string);
		}
	}
	function decodeData($string) {
		if (isset($string) || !empty($string)) {
			$string = trim($string);
			return @unserialize(base64_decode($string));
		}
	}
  function encrypt($string){
    $cipher="AES-256-CBC";
    $iv=config('common.openssl_cipher_iv');
    $ciphertext_raw=openssl_encrypt($string, $cipher, file_get_contents(config('common.openssl_key')), $options=OPENSSL_RAW_DATA, $iv);
    $hmac=hash_hmac('sha256', $ciphertext_raw, file_get_contents(config('common.openssl_key')), $as_binary=true);
    return base64_encode( $iv.$hmac.$ciphertext_raw );
  }
  function decrypt($string){
    $c=base64_decode($string);
    $cipher="AES-256-CBC";
    $ivlen=16;
    $iv=substr($c, 0, $ivlen);
    $hmac=substr($c, $ivlen, $sha2len=32);
    $ciphertext_raw=substr($c, $ivlen+$sha2len);
    if($ciphertext_raw){
      $original_string=openssl_decrypt($ciphertext_raw, $cipher, file_get_contents(config('common.openssl_key')), $options=OPENSSL_RAW_DATA, $iv);
      $calcmac = hash_hmac('sha256', $ciphertext_raw, file_get_contents(config('common.openssl_key')), $as_binary=true);
      if (hash_equals($hmac, $calcmac))
        return $original_string;
      else
        return null;
    }else{
      return null;
    }
  }
  function setEncryptValue($array_data, $rows, $object=false, $fetch_single_row=false){
    if(!empty($array_data)){
      if($fetch_single_row===true){
        if(!empty($rows)){
          foreach($rows as $row){
            if(isset($array_data->$row) && $array_data->$row && $array_data->$row!=''){
              $array_data->$row=$this->encodeData($array_data->$row);
            }
          }
        }
      }else{
        if(!empty($array_data) && $rows){
          foreach($array_data as $key=>$list){
            foreach($rows as $row){
              if($object){
                if(isset($list->$row))
                  $array_data[$key]->$row=$this->encodeData($list->$row);
                else
                  $array_data[$key]->$row=null;
              }else{
                $array_data[$key][$row]=$this->encodeData($list[$row]);
              }
            }
          }
        }
        ksort($array_data);
      }
    }
    return $array_data;
  }
  function makeShortName($str,$casechange=true){
    $src=array(" and "," ", "?", "&", "=", "/", "-", ".", "'", '"'," / "," - ","---","  ","   ","\n","\t","~","!","@","#","$","%","^","&","*","(",")","_","+","|","\\"," , ",", ",",");
    $rep=array("-","-", "", "", "", "-", "-", "-", "", "","-","-","-","-","-","-","-","","","","","","","","","","","","","","","","-","-","-");
    $str=urlencode(($casechange?strtolower(str_replace($src, $rep, $str)):str_replace($src, $rep, $str)));
    $str=str_replace(array('---','--'),array('-','-'), $str);
    return $str;
  }
  function makeValidateRules($inputs){
    $rules=array();
    $list=array(
      'username' => 'required',
      'password' => 'required',
      'name' => 'required|max:70',
      'email'=>'required|email',
      'date' => 'required|date',
      'mobile' => 'required|numeric|digits:10',
      'phone' => 'required|numeric|digits:10',
      'aadhar' => 'required|numeric|digits:12',
      'pancard' => 'required|string|min:10|max:10|regex:/^[A-Za-z0-9\-\s]+$/',
      'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
    );
    if(!empty($inputs)){
      foreach($inputs as $name=>$value){
        if(isset($list[$name])){
          $rules[$name]=$list[$name];
        }
      }
    }
    return $rules;
  }
  function generateForm($forms){
    echo "<pre>";
      print_r($forms);
    echo "</pre>";
  }
  function saveCdn($ext, $tmp_name, $error, $file_name, $target_dir){
    if($error == UPLOAD_ERR_OK){
      if($this->verifyUploadDirectory($target_dir)){
        $upload_file_path="./$target_dir/$file_name.$ext";
        $i=1;
        while(file_exists($upload_file_path)){
          $upload_file_path="./$target_dir/$file_name-$i.$ext";
          ++$i;
        }
        if(move_uploaded_file($tmp_name, $upload_file_path)){
          $uploaded_url=substr($upload_file_path,2);
          return array('status'=>'S','url'=>$uploaded_url,'ext'=> $ext);
        }
      }
    }
    return array('status'=>'E','url'=>'','ext'=> '');
  }
    function imageCrop($original_image, $target_dir, $file_name, $ext, $width, $height){
    if($this->verifyUploadDirectory($target_dir)){
      $upload_file_path="./$target_dir/$file_name.$ext";
      $i=1;
      while(file_exists($upload_file_path)){
        $upload_file_path="./$target_dir/$file_name-$i.$ext";
        ++$i;
      }
      if (copy($original_image, $upload_file_path)) {
        $this->resizeImage($upload_file_path, $width, $height, ".".$ext);
      }
    }
  }

    function verifyUploadDirectory($path){
    $dir=explode("/",$path);
    $dir_path=".";
    foreach($dir as $d){
      $dir_path.="/$d";
      if(!is_dir($dir_path)){
        mkdir($dir_path);
      }
    }
    return is_dir("./".$path);
  }
    function resizeImage($remoteImage, $maxwidth, $maxheight, $ext){
        $imagepath=$remoteImage;
        $imagedata=getimagesize($imagepath);
        $imgwidth=$imagedata[0];
        $imgheight=$imagedata[1];
        $shrink=1;
            $output_height=$maxheight;
            $output_width=$maxwidth;
          if( $output_height > $maxheight ){
              $shrink = $maxheight / $output_height ;
              $output_width = $shrink * $output_width;
              $output_height = $maxheight;
          }

          switch($ext){
              case ".gif":
                  $src_image = @imagecreatefromgif($imagepath);
                  $dest_image = @imagecreatetruecolor ($output_width, $output_height);
                  imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $output_width, $output_height, $imgwidth, $imgheight);
                  imagegif($dest_image, $imagepath, 80);
              break;
              case ".jpg":
                  $src_image = @imagecreatefromjpeg($imagepath);
                  if($src_image===false)
                    return 0;
                  $dest_image = @imagecreatetruecolor ($output_width, $output_height);
                  imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $output_width, $output_height, $imgwidth, $imgheight);
                  imagejpeg($dest_image, $imagepath, 80);
              break;
              case ".jpeg":
                  $src_image = @imagecreatefromjpeg($imagepath);
                  $dest_image = @imagecreatetruecolor ($output_width, $output_height);
                  imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $output_width, $output_height, $imgwidth, $imgheight);
                  imagejpeg($dest_image, $imagepath, 80);
              break;
              case ".png":
                  $src_image = @imagecreatefrompng($imagepath);
                  $dest_image = @imagecreatetruecolor ($output_width, $output_height);
                  imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $output_width, $output_height, $imgwidth, $imgheight);
                  imagepng($dest_image, $imagepath, 5);
              break;
              case ".PNG":
                $src_image = @imagecreatefrompng($imagepath);
                $dest_image = @imagecreatetruecolor ($output_width, $output_height);
                imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $output_width, $output_height, $imgwidth, $imgheight);
                imagepng($dest_image, $imagepath, 5);
            break;

          }
           return 1;
      }
}
?>
