<?php
namespace App\Http\Controllers\controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Models\Admin\Mproducts;
use App\Models\Admin\Morder;
use App\Models\Admin\Musers;
use App\Models\Admin\Mcountry;
use App\Models\Admin\Mstate;
use App\Models\Admin\Mcity;
use App\Models\Admin\Mbanner;

use Session;
use Helper;
use Illuminate\Support\Facades\Redirect;

class FrontendController extends Controller
{
    private $mproducts;
    private $mbanner;
    private $morder;
    private $muser;
    private $mcountry;
    private $mstate;
    private $mcity;
    private $data;
    private $method;
    private $helper;
    private $_user;
    public function __construct(){
      $this->mproducts = new Mproducts;
      $this->morder = new Morder;
      $this->muser = new Musers;
      $this->mcountry = new Mcountry;
      $this->mstate = new Mstate;
      $this->mcity = new Mcity;
      $this->mbanner = new Mbanner;
      $this->data=array();
      $this->_user = array();
      $this->helper = new Helper();
      $url = Route::getCurrentRoute()->getActionName();
      $res = explode("@",$url);
      $this->method = $this->urlTokenizer($res[1]);
      $this->middleware(function ($request, $next) {
        if((!session()->has('auth_token')) && $this->method != "home" && $this->method != "faq" && $this->method != "contactus" && $this->method != "aboutus" && $this->method != "privacy_policy" && $this->method != "terms_condition" && $this->method != "user_login" && $this->method != "user_register" && $this->method != "user_cart" && $this->method != "product_detail" && $this->method != "product" && $this->method != 'return_policy'){
          Redirect::to('/')->send();
          }
          if(session()->has('auth_token'))
          {
           $this->_user = $this->helper->getAuthentication();

          }
        return $next($request);
      });
      }
    function urlTokenizer($method){
      return $uri_method=str_replace(" ", '', lcfirst(ucwords(str_replace("-"," ",$method))));
    }
    function home(){

        // if(session()->has('auth_token')){
        //     $user_id = session('user_id');
        //     // echo $user_id;
        //     $wishlistcnt = $this->mproducts->getwishlistcntbyuserid($user_id);
        //     // print_r($wishlistcnt);
        //     // exit;
        //     if($wishlistcnt != 0){
        //         $this->data['wishcnt']=$wishlistcnt;
        //     }else{
        //         $this->data['wishcnt']= 0;
        //     }
        // }else{
        //     $this->data['wishcnt']= 0;
        // }



        $products = $this->mproducts->getallProducts();
        if(!empty($products)){
            foreach ($products as $key => $value) {
                $value->list_id=$value->id?$this->helper->encodeData($value->id):null;
                $value->cat_name=$value->category_name?$this->helper->encodeData($value->category_name):null;
            }
            $this->data['products']=$products;
        }else{
            $this->data['products']=[];
        }

        $categoryCnt = $this->mproducts->getallProductsByCategoryCount();
        if(!empty($categoryCnt)){
            foreach ($categoryCnt as $key => $value) {
                $value->cat_name=$value->category_name?$this->helper->encodeData($value->category_name):null;
            }
            $this->data['categoryCnt']=$categoryCnt;
        }else{
            $this->data['categoryCnt']=[];
        }

        $banner = $this->mbanner->getAllbanner();
        if(!empty($categoryCnt)){
            $this->data['banner']=$banner;
        }else{
            $this->data['banner']=[];
        }




      return view('frontend.index', $this->data);
    }

    function faq(){
        return view('frontend.common.faq', $this->data);
      }

      function privacy_policy(){
        return view('frontend.common.privacy_policy', $this->data);
      }

      function contactus(){
        return view('frontend.common.contact_us', $this->data);
      }

      function aboutus(){
        return view('frontend.common.about_us', $this->data);
      }

      function terms_condition(){
        return view('frontend.common.terms_condition', $this->data);
      }

      function return_policy(){
        return view('frontend.common.return_policy', $this->data);
      }

      function user_login(){
        return view('frontend.common.user_login', $this->data);
      }

      function user_register(){
        $country = $this->mcountry->getallCountry();
        if(!empty($country)){
            $this->data['country']=$country;
        }else{
            $this->data['country']= [];
        }
        $state = $this->mstate->getallState();
        if(!empty($state)){
            $this->data['state']=$state;
        }else{
            $this->data['state']= [];
        }
        $city = $this->mcity->getallCity();
        if(!empty($city)){
            $this->data['city']=$city;
        }else{
            $this->data['city']= [];
        }

        return view('frontend.common.user_register', $this->data);
      }

      function user_profile(){
        $user_id = session('user_id');
        $user = $this->muser->getUsersListById($user_id);
        if(!empty($user)){
            $this->data['users']=$user;
        }else{
            $this->data['users']= [];
        }
        $country = $this->mcountry->getallCountry();
        if(!empty($country)){
            $this->data['country']=$country;
        }else{
            $this->data['country']= [];
        }
        $state = $this->mstate->getallState();
        if(!empty($state)){
            $this->data['state']=$state;
        }else{
            $this->data['state']= [];
        }
        $city = $this->mcity->getallCity();
        if(!empty($city)){
            $this->data['city']=$city;
        }else{
            $this->data['city']= [];
        }
        return view('frontend.user.user_profile', $this->data);
      }

      function user_wishlist(){
        if(session()->has('auth_token')){
            $user_id = session('user_id');
        $wislist = $this->mproducts->getallwishlist($user_id);
        if(!empty($wislist)){
            $this->data['wishlist']=$wislist;
        }else{
            $this->data['wishlist']= [];
        }

        }


        return view('frontend.user.mywishlist', $this->data);
      }

      function user_history(){
        $user_id = session('user_id');
        $item=$this->morder->getorderByuserid($user_id);
        foreach ($item['data'] as $key => $value) {
            $value->id=$value->id?$this->helper->encodeData($value->id):null;
        }
        if($item['status'] == 'S') {
            $this->data['record']=$item['data'];
            }else{
              $this->data['record']="";
          }
        return view('frontend.user.myorders', $this->data);
      }

      function user_cart(){
        $session = "";
        if(session()->has('user_id')){
            $user_id = session('user_id');
        }else{
            $user_id = "";
        }


        if(!empty($user_id)){
            if(session()->has('session_id')){
                $session = session('session_id');
                $item=$this->mproducts->getcart($session);
                if($item['status'] == 'S') {
                    $this->data['record']=$item['data'];

                    }else{
                      $this->data['record']="";
                  }
            }else{
            $item = $this->mproducts->getcartByuserid($user_id);
            // print_r($item);
            // exit;
                if($item['status'] == 'S') {
                    $session = $item['data'][0]->session_id;
                    $this->data['record']=$item['data'];

                    }else{
                      $this->data['record']="";
                    }
                }
        }else{
            if(session()->has('session_id')){
                $session = session('session_id');

        $item=$this->mproducts->getcart($session);

        if($item['status'] == 'S') {
            $this->data['record']=$item['data'];
            }else{
                $this->data['record']="";
          }
        }
        }
        $this->data['session'] = $session;
        $this->data['pagetype'] = 'cart';
        return view('frontend.order.cart', $this->data);
      }

      function checkout(){
        return view('frontend.order.checkout', $this->data);
      }

      function product($keys){

        $temp = $this->helper->decodeData($keys);

        // $encodedt = $this->helper->encodeData($key);
        // echo $encodedt;
        // exit;

            $products = $this->mproducts->getallProducts();
        if(!empty($products)){
            foreach ($products as $key => $value) {
                $value->list_id=$value->id?$this->helper->encodeData($value->id):null;
            }
            $this->data['products']=$products;
            $this->data['category']=$temp;
        }else{
            $this->data['products']=[];
            $this->data['category']='';

        }

        return view('frontend.products.product', $this->data);
      }

      function product_detail($key){
        $prod_id=$this->helper->decodeData($key);
        $item=$this->mproducts->getProductsListById($prod_id);
        if(!empty($item)){
            $this->data['products']=$item;
            $lists=$this->mproducts->get_Product_images($prod_id);
            $this->data['images']=$lists;

        }else{
            $this->data['products']=[];
            $this->data['images']=[];

        }
        return view('frontend.products.product_detail', $this->data);
      }

}
