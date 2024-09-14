<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Session;
use Illuminate\Http\Response;
use App\Models\User;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const HTTP_OK = 200;
    const HTTP_NOT_FOUND = 404;
    const HTTP_BAD_REQUEST = 400;
	const HTTP_UNAUTHORIZED = 401;



    protected function verifyAccesss($cds_access_token)
    {

        if ($cds_access_token) {
		    $authorization = $cds_access_token;
        } else {

            if( isset($_SESSION["cds_access_token"]) ){

                $authorization = $_SESSION["cds_access_token"];
                $type = $_SESSION["user_type"];
                $login = $_SESSION["user_loggedin"];
            }else{
                $authorization = "";
                $type = "";
                $login = "";
            }

            if ($type == 'B') {
                if ($login != 1) {
                    $authorization = false;
                }
            }
        }
        $authorized = false;
        $token_expired = false;
        if ($authorization) {
            $authorization_details = explode('|', $this->my_decrypt($authorization));
            //print_r($authorization_details);
            if (!empty($authorization_details) && sizeof($authorization_details) >= 6 && $authorization_details[4] == "test") {
                $authorized = true;
            } else {
                $authorized = false;
            }
        }
        if ($authorized) {
            $this->setUserInfo($authorization_details);
            return;
        } else if ($token_expired) {
            $this->sendTokenExpire();
        } else {
           return  $this->sendUnauthorized();
        }
    }
    public function setUserInfo($user_details)
    {

        if (sizeof($user_details) >= 6) {
            $this->_user = array('user_id' => isset($user_details[0]) ? $user_details[0] : "", 'buyer_id' => isset($user_details[1]) ? $user_details[1] : "", 'dperson_id' => isset($user_details[2]) ? $user_details[2] : "", 'role' => isset($user_details[3]) ? $user_details[3] : "", 'tknsitename' => isset($user_details[4]) ? $user_details[4] : "", 'log_time' => isset($user_details[5]) ? $user_details[5] : "");
        } else {
            $this->_user = array('user_id' => null, 'buyer_id' => null, 'dperson_id' => null, 'role' => null, 'tknsitename' => null, 'log_time' => null);
        }
    }
    public function sendUnauthorized()
    {

        //return json_encode(array('status' => 'FAILED', 'error_code' => '401', 'message' => "Unauthorized"));
      return "401";
    }

    function my_encrypt($string) {
		$output = false;
		$encrypt_method = "AES-256-CBC";
		$secret_key = 'dRkZ3G7yLiVzZ+f4Rl9qNK2PSIYwJIaaNAgz+2XdiII=';
		$secret_iv = 'KOMSToxitsPHDxD4OJoiUXT0rovD';
		// hash
		$key = hash('sha256', $secret_key);
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = base64_encode($output);
		return $output;
	}

    function my_decrypt($string) {
		$output = false;
		$encrypt_method = "AES-256-CBC";
		$secret_key = 'dRkZ3G7yLiVzZ+f4Rl9qNK2PSIYwJIaaNAgz+2XdiII=';
		$secret_iv = 'KOMSToxitsPHDxD4OJoiUXT0rovD';
		// hash
		$key = hash('sha256', $secret_key);
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
		$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		//$output = openssl_decrypt($string, $encrypt_method, $key, 0, $iv);
		return $output;
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

    function getMainCategories(){
        $nav_main_categories = $this->users->getAllHomeCategory();

    }
}
