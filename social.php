<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Razorpay\Api\Api;
require APPPATH . '/libraries/razorpay-php/Razorpay.php';

class ApiController extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Main_model');
  }
  public function adminsingup()
  {
    if ($this->input->post()) {
      $data['name'] = $this->input->post('name');
      $data['email'] = $this->input->post('email');
      $data['is_admin'] = 0;
      $data['username'] = $this->input->post('username') . '@';
      $data['password'] = sha1($this->input->post('password'));
      $data['lang_id'] = $this->input->post('lang_id');
      $data['age'] = $this->input->post('age');
      // $data['weight'] = $this->input->post('weight');
      $data['gender'] = $this->input->post('gender');
      // $data['training_goal'] = $this->input->post('training_goal');
      $data['dev_id'] = $this->input->post('dev_id');
      $data['reg_id'] = $this->input->post('reg_id');
      $data['latitude'] = $this->input->post('latitude');
      $data['longitude'] = $this->input->post('longitude');
      $data['dev_type'] = $this->input->post('dev_type');

      $insert = $this->db->insert('users', $data);
      $get_id = $this->db->insert_id();

      if ($insert == true) {
        $getDetails = $this->db->get_where('users', ['id' => $get_id])->row_array();
        echo json_encode([
          'sucess' => 1,
          'messsage' => "Admin successfully register",
          "insert" => $getDetails
        ]);
      } else {
        echo json_encode([
          'error' => 0,
          'messsage' => "user not register"
        ]);
      }
    } else {
      echo json_encode([
        'error' => 0,
        'messsage' => "all field are"
      ]);
    }
  }

  public function login()
  {
    if ($this->input->post()) {
      $this->form_validation->set_rules('email', 'email');
      $this->form_validation->set_rules(
        'password',
        'Password',
        'required',
        array('required' => 'You must provide a %s.')
      );
      if ($this->form_validation->run() == FALSE) {
        echo json_encode([
          'error' => 0,
          'messsage' => "some error"
        ]);
      } else {
        $result = $this->Main_model->login();
        echo json_encode([
          'sucess' => 1,
          'messsage' => "Admin successfully login",
          "login" => $result['name']
        ]);
      }
    } else {
      echo json_encode([
        'error' => 0,
        'messsage' => "all feild are required"
      ]);
    }
  }

  public function getCountries()
  {

    $getCountries = $this->db->query("SELECT * FROM countries")->result_array();
    if (!empty($getCountries)) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      $message['details'] = $getCountries;
    } else {
      $message['success'] = '0';
      $message['message'] = 'No list found';
    }
    echo json_encode($message);
  }

  //  public function sendOtp()
  //   {
  //     $this->db->delete('verifyOTP', array('phone' => $this->input->post('phone')));
  //     $data['phone'] = $this->input->post('phone');
  //     $otp = rand(1000,9999);
  //     $data['loginOtp'] = $otp;
  //     $insert = $this->db->insert('verifyOTP', $data);
  //     if (!empty($insert)) {
  //       $message['success'] = '1';
  //       $message['message'] = 'OTP sent on your phone number';
  //       $message['otp'] = (string)$otp;
  //     } else {
  //       $message['success'] = '0';
  //       $message['message'] = 'Please try after some time';
  //     }
  //     echo json_encode($message);
  //   }

  public function sendOtp()
  {
    $this->db->delete('verifyOTP', array('phone' => $this->input->post('phone')));
    $data['phone'] = $this->input->post('phone');
    $otp = rand(1000, 9999);
    $data['loginOtp'] = $otp;
    $insert = $this->db->insert('verifyOTP', $data);
    if (!empty($insert)) {
      $message['success'] = '1';
      $message['message'] = 'OTP sent on your phone number';
      $message['otp'] = (string)$otp;
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }



  public function loginRegisterUser()
  {
    $checkOTP = $this->db->get_where('verifyOTP', array('phone' => $this->input->post('phone'), 'loginOtp' => $this->input->post('otp')))->row_array();
    if (!empty($checkOTP)) {
      $checkUser = $this->db->get_where('users', array('phone' => $this->input->post('phone')))->row_array();

      if (!empty($checkUser)) {
          
        $datas = array('reg_id' => $this->input->post('reg_id'));
        $update = $this->db->update('users', $datas, array('id' => $checkUser['id']));

      // print_r($checkUser);exit;

      $checkUser['imageDp'] = null;
      $checkImage = $this->db->select('image')
      ->from('userImages')
      ->where('userId', $checkUser['id'])
      ->order_by('id', 'desc')
      ->get()->row_array();

      if(!!$checkImage){
       $checkUser['imageDp'] = $checkImage['image'];
      }

        $message['success'] = '1';
        $message['message'] = 'User login successully';
        $message['details'] = $checkUser;

      } else {
        $getUserName = $this->db->select('username')->from('users')->order_by('id', 'desc')->get()->row_array();
        
        if(empty($getUserName)){
          $data['username'] = '@500001';
        }else{
          $uname = $getUserName['username'];
          $data['username'] = ++$uname;
        }
        $data['phone'] = $this->input->post('phone');
        $data['Country'] = $this->input->post('Country');
        $data['created_at'] = date('Y-m-d H:i:s');
        $insert = $this->db->insert('users', $data);
        if (!empty($insert)) {
          $lastId = $this->db->insert_id();
          $userInfo = $this->db->get_where('users', array('id' => $lastId))->row_array();
          $message['success'] = '1';
          $message['message'] = 'User Register successully';
          $message['details'] = $userInfo;
        } else {
          $message['success'] = '0';
          $message['message'] = 'Please try after some time';
        }
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'Invalid OTP, Please enter valid OTP';
    }
    echo json_encode($message);
  }

  public function updateUserProfile()
  {

    if (!empty($this->input->post('name'))) {
      $data['name'] = $this->input->post('name');
    }
    if (!empty($this->input->post('gender'))) {
      $data['gender'] = $this->input->post('gender');
    }
    if (!empty($this->input->post('dob'))) {
      $data['dob'] = $this->input->post('dob');
    }
    if (!empty($this->input->post('Country'))) {
      $data['Country'] = $this->input->post('Country');
    }
    if (!empty($this->input->post('bio'))) {
      $data['bio'] = $this->input->post('bio');
    }

    $update = $this->db->update("users", $data, ['id' => $this->input->post('userId')]);

    if ($update == true) {

      if (!empty($_FILES['image']['name'])) {

          $totalImages = count($_FILES['image']['name']);
          for($i = 0; $i < $totalImages; $i++) {
            $tmp_name = $_FILES['image']['tmp_name'][$i];
            if(!empty($tmp_name)){
              $name = time().'_'.$_FILES['image']['name'][$i];
              $image_name = str_replace('','_', $name);
              $tmp_name = $_FILES['image']['tmp_name'][$i];
              $path = 'uploads/adminImg/' . $image_name;
              $basePath = base_url($path);

              move_uploaded_file($tmp_name, $path);
              $datas['image'] = $basePath;
              $datas['userId'] = $this->input->post('userId');
              $datas['created'] = date('Y-m-d');
              $datas['createdTime'] = date('H:i:s');

              $insert = $this->db->insert('userImages', $datas);
            }
          }
      }

      $get = $this->db->get_where("users", ['id' => $this->input->post('userId')])->row_array();

      $getlastimag = $this->db->select('image')->from('userImages')->where('userId', $this->input->post('userId'))->order_by('id', 'desc')->get()->result_array();
      $get['image'] = $getlastimag;

      echo json_encode([

        "message" => "profile edit successfully",
        "success" => "1",
        "details" => $get,
      ]);
      exit;
    } else {
      echo json_encode([

        "message" => "Try after some time",
        "success" => "0",
      ]);
      exit;
    }
  }

//   public function searchUsers()
//   {

//     try {

//       $records = $this->db->from("users");
      

//       if (!!$this->input->post("search"))
//         $records = $records->like("name", $this->input->post("search"));


//       $records = $records->get()->result_array();

//       $final = [];

//       foreach($records as $list){
//         $getImage = $this->db->select('image')
//                              ->from('userImages')
//                              ->where('userId', $list['id'])
//                              ->order_by('id', 'desc')
//                              ->limit(1)
//                              ->get()->row_array();

//                              if(!!$getImage){
//                               $list['UserProfileImage'] = $getImage['image'];
//                              }else{
//                               $list['UserProfileImage'] = null;
//                              }


//                              $final[] = $list;



//       }

//       if (!!$final) {
//         echo json_encode([
//           "success"  =>  "1",
//           "message"  =>  "Record found successfully",
//           "details"  =>  $final,
//         ]);
//         exit;
//       }

//       echo json_encode([
//         "success"  =>  "0",
//         "message"  =>  "Record not found",
//       ]);
//       exit;
//     } catch (Exception $ex) {
//       echo json_encode([
//         "success"  =>  "0",
//         "message"  =>  $ex->getMessage(),
//       ]);
//       exit;
//     }
//   }
  
  public function searchUsers(){
      
      
      $search = $this->input->post("search") ?? "";
      
     $get = $this->db->select("users.*")
      ->from("users")
      ->like("users.name",$search)
      ->get()
      ->result_array();
      
      
    //   print_r($get);
    //   die;
    
    if(!!$get){
        
        $final = [];
        foreach($get as $gets){
            
            $gets['liveStatus'] = false;
            $gets['hideStatus'] = false;
            
            $check = $this->db->get_where('userLive', ['userId' => $gets['id']])->row_array();
            
            if(!empty($check) && $check['status'] == 'live'){
                
                $gets['liveStatus'] = true;
                
                $getHide = $check['live_hideUnhideStatus'];
          
                  if($getHide == '1'){
                      
                    $gets['hideStatus'] = TRUE;
                  }
                  else{
                  $gets['hideStatus'] = FALSE;
                   
                }
                
            }
            
            $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $gets['id'])
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $gets['UserProfileImage'] = $getImage['image'];
                             }else{
                              $gets['UserProfileImage'] = "";
                             }
            
            $final[] = $gets;
        }
        
        echo json_encode([
            
            "success" => "1",
            "message" => "Record found successfully",
            "details" => $final,
            ]);exit;
    }
    else{
        echo json_encode([
        "success"  =>  "0",
        "message"  =>  "Record not found",
      ]);
      exit;
    }
      
  }
  
  public function getLiveUsers(){
      
      $get = $this->db->select("userLive.*,users.name,users.username,users.dob,users.gender")
      ->from("userLive")
      ->join("users","users.id = userLive.userId","left")
      ->where("userLive.userId",$this->input->post("userId"))
      ->where("userLive.status","live")
      ->get()
      ->row_array();
      
    //   print_r($get);
    //   die;
    
    if(!!$get){
 
            $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $get['userId'])
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $get['UserProfileImage'] = $getImage['image'];
                             }else{
                              $get['UserProfileImage'] = "";
                             }
                             
    $checkFollowStatus = $this->db->get_where('kickOutLiveUser',['kickTo' => $this->input->post('kickToId'),'liveId' => $get['id'],'kickBy' => $get['userId']])->row_array();
        if($checkFollowStatus){
          $get['kickOutStatus'] = TRUE;
        }else{
          $get['kickOutStatus'] = FALSE;
        }
        
         $getDetails = $this->db->select("users.gender,users.dob")
        ->from("users")
        ->where("users.id",$this->input->post('userId'))
        ->get()
        ->row_array();
        
        if(!!$getDetails){
            
            $get['user_gender'] = $getDetails['gender'];
            $get['user_dob'] = $getDetails['dob'];
            
        }
        else{
            $get['user_gender'] = "";
            $get['user_dob'] = "";
        }
            
        
        echo json_encode([
            
            "success" => "1",
            "message" => "details found",
            "details" => $get
            ]);exit;
        
    }
    else{
        
        echo json_encode([
            
            "success" => "0",
            "message" => "details not found!"
            ]);exit;
    }
  }
  
  
  public function nearByLiveUser()
  {
    if ($this->input->post()) {

      $getlat = $this->input->post('latitude');
      $getlong = $this->input->post('longitude');
      $getdetails = $this->db->select("userLive.*, users.*, (6731 * acos( cos( radians($getlat) ) * cos( radians( users.latitude ) ) * cos( radians(users.longitude ) - radians($getlong) ) + sin( radians($getlat) ) * sin(radians(users.latitude)) ) ) AS distance")
        ->from("users")
        ->join('userLive', 'userLive.userId = users.id', 'left')
        ->where("users.latitude !=", "")
        ->where("users.longitude !=", "")
        ->having("distance <", 20)
        ->order_by("distance", "ASC")
        ->get()
        ->result_array();


      if (!empty($getdetails)) {
        $message = array(
          'success' => '1',
          'message' => 'List found successfuly',
          "details" => $getdetails
        );
      } else {
        $message['success'] = '0';
        $message['message'] = 'List not found!';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please enter valid parameters!';
    }


    echo json_encode($message);
  }

  public function getNearbyLiveUsers(){
    if($this->input->post()){
      $getlat = $this->input->post('latitude');
      $getlong = $this->input->post('longitude');
      $userId = $this->input->post('userId');

      $getuser = $this->db->query("SELECT userLive.channelName, userLive.token, users.* FROM (SELECT *, (((acos(sin(($getlat*pi()/180)) * sin((`latitude`*pi()/180))+cos(($getlat*pi()/180)) * cos((`latitude`*pi()/180)) * cos((($getlong - `longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance FROM `users`)users left join userLive on userLive.userId = users.id where userLive.userId != $userId AND userLive.status = 'live' and distance <= 62.1371 order by distance DESC ")->result_array();
// print_r($getuser);exit;
      $main = [];
      foreach($getuser as $user){
        $getFollow = $this->db->get_where('followFeed', ['userId' => $this->input->post('userId'), 'followinguserId' => $user['id']])->row_array();
        if(!!$getFollow){

          $user['followStatus'] = true;

        }else{
          $user['followStatus'] = false;
        }

        $main[] = $user;
      }

      if(!!$main){
        echo json_encode([
          'status' => 1,
          'message' => 'list found',
          'deatils' => $main
        ]);exit;
      }

      echo json_encode([
        'status' => 0,
        'message' => 'No Nearby User Found'
      ]);exit;
    

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }
  }
  
  public function nearByUsers() 
	{
		if ($this->input->post()){

			$getlat = $this->input->post('latitude');
			$getlong = $this->input->post('longitude');
			$ID = $this->input->post('userId');
			$gethospital = $this->db->select("users.*,userLive.id userLiveId,userLive.userId,userLive.hostType,userLive.channelName,userLive.token,userLive.latitude,userLive.longitude,userLive.rtmToken,userLive.status,userLive.archivedDate,userLive.endTime,userLive.liveCount,userLive.bool,userLive.password as userLive_Password,userLive.Liveimage,userLive.imageText,userLive.imageTitle,userLive.live_hideUnhideStatus, (6731 * acos( cos( radians($getlat) ) * cos( radians( userLive.latitude ) ) * cos( radians(userLive.longitude ) - radians($getlong) ) + sin( radians($getlat) ) * sin(radians(userLive.latitude)) ) ) AS distance")
				->from("userLive")
				->join("users","users.id = userLive.userId","left")
				->where("userLive.latitude !=", '')
				->where("userLive.userId !=",$ID)
				->where("userLive.status","live")
				->having("distance <", 20)
				->order_by("distance", "ASC")
				->get()
				->result_array();

			if (!empty($gethospital)) {

				$main = [];
                  foreach($gethospital as $user){
                      
                    if(!!$user['Liveimage']){
                      
                    $user['Liveimage'] = $user['Liveimage'];
                    }
                    else{
                        $user['Liveimage'] = "";
                    }
                    
                    $checkImage = $this->db->select('image')
                                  ->from('userImages')
                                  ->where('userId', $user['userId'])
                                  ->order_by('id', 'desc')
                                  ->limit(1)
                                  ->get()->row_array();
    
                      if(!!$checkImage){
                       $user['UserProfileImage'] = $checkImage['image'];
                      }
                    
                    $getFollow = $this->db->get_where('followFeed', ['userId' => $this->input->post('userId'), 'followinguserId' => $user['userLiveId']])->row_array();
                    if(!!$getFollow){
            
                      $user['followStatus'] = true;
            
                    }else{
                      $user['followStatus'] = false;
                    }
                    
                    $getLiveId = $user['userLiveId'];
                    $getuserId = $user['userId'];
                                                    
                    $checkFollowStatus = $this->db->get_where('kickOutLiveUser',['kickTo' => $this->input->post('kickTo'),'liveId' => $getLiveId,'kickBy' => $getuserId])->row_array();
                    if($checkFollowStatus){
                        $user['kickOutStatus'] = TRUE;
                    }else{
                        $user['kickOutStatus'] = FALSE;
                    }
                    
                     $getDetails = $this->db->select("users.gender,users.dob")
                    ->from("users")
                    ->where("users.id",$this->input->post('userId'))
                    ->get()
                    ->row_array();
                    
                    if(!!$getDetails){
                        
                        $user['user_gender'] = $getDetails['gender'];
                        $user['user_dob'] = $getDetails['dob'];
                        
                    }
                    else{
                        $user['user_gender'] = "";
                        $user['user_dob'] = "";
                    }
            
                    $main[] = $user;
                  }
				$message = array(
					'success' => '1',
					'message' => 'Users found successfuly',
					"details" => $main
				);
			} else {
				$message['success'] = '0';
				$message['message'] = 'List not found!';
			}
		} else {
			$message['success'] = '0';
			$message['message'] = 'Please enter valid parameters!';
		}


		echo json_encode($message);
	}

  public function usersLogout()
  {
    if ($this->input->post()) {
      $data['reg_id'] = '';
      $update = $this->db->update('users', $data, array('id' => $this->input->post('userId')));
      if (!empty($update)) {
        $message = array(
          'success' => '1',
          'message' => 'user logout successfully'
        );
      }
    } else {
      $message = array(
        'success' => '0',
        'message' => 'Please enter parameters'
      );
    }
    echo json_encode($message);
  }


  // 	 public function agoraToken(){
  //         // $checkUser = $this->db->get_where('users',array('id' => $this->input->post('userId')))->row_array();
  //         // if(empty($checkUser)){
  //         //   $message['success'] = '0';
  //         //   $message['message'] = 'please logout and login again';
  //         // }
  //         // else{
  //         //   if($checkUser['liveStatus'] == '1'){
  //               require APPPATH.'/libraries/agora/RtcTokenBuilder.php';
  //               require APPPATH.'/libraries/agora/RtmTokenBuilder.php';
  //               // $appID = "0ebf0179ad5f47ef93f32cf7f6851e1b";
  //               // $appCertificate = "0405943eabe04260acb48aedb6102605";
  //                 $appID = "3d2f0f05051541298716f77fd7eab51d";
  //                 $appCertificate = "bbc6c889333948b69d2b987898b33356";
  //                 $channelName = $this->input->post('channelName');
  //                 $uid = '';
  //                 $uidStr = '';
  //                 $role = RtcTokenBuilder::RoleAttendee;
  //                 $expireTimeInSeconds = 10800;
  //                 $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
  //                 $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
  //                 $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);

  //                 $roleb = RtmTokenBuilder::RoleRtmUser;
  //                 $expireTimeInSecondsb = 10800;
  //                 $currentTimestampb = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
  //                 $privilegeExpiredTsb = $currentTimestampb + $expireTimeInSecondsb;
  //                 $userii =  $this->input->post('userId');
  //                 $tokenb = RtmTokenBuilder::buildToken($appID, $appCertificate, $userii, $roleb, $privilegeExpiredTsb);


  //                 if(!empty($token)){
  //                   $data['userId'] = $this->input->post('userId');
  //                   $data['channelName'] = $this->input->post('channelName');
  //                   $data['latitude'] = $this->input->post('latitude');
  //                   $data['longitude'] = $this->input->post('longitude');
  //                   $data['hostType'] = $this->input->post('hostType');
  //         		  //$data['bool'] = $this->input->post('bool');
  //                   $data['token'] = $token;
  //                   $data['rtmToken'] = $tokenb;
  //                   $data['created'] = date('Y-m-d H:i:s');
  //                   $data['status'] = 'live';
  //                   $insert = $this->db->insert('userLive',$data);
  //                   $ids = $this->db->insert_id();
  //                   if(!empty($insert)){

  //                     // $this->db->set('liveCount', 'liveCount +1', false)->where('userId', $this->input->post('userId'))->update("userLive");


  //                     // $checkFollow = $this->db->get_where('userFollow', array('followingUserId' => $this->input->post('userId'),'status' => '1'))->num_rows();

  //         // 			$get = $this->db->get_where("userLive",['id' => $ids])->row_array();

  //         //             $getBool = $get['bool'];

  //         //       			 if(!empty($checkFollow)){
  //         //       				 $outPut['followerCount'] = (string)$checkFollow;
  //         //       			 }
  //         //       			 else{
  //         //       				 $outPut['followerCount'] = '0';
  //         //       			 }

  //                      $userId = $this->input->post('userId');
  //                      // $lists = $this->db->get_where('userFollow',array('followingUserId' => $userId,'status' => '1'))->result_array();
  //                      // if(!empty($lists)){
  //                      //     foreach($lists as $list){
  //                      //         $loginUserDetails = $this->db->get_where('users',array('id' => $userId))->row_array();
  //                      //         $getUserId = $this->db->get_where('users',array('id' => $list['userId']))->row_array();
  //                      //         $regId = $getUserId['reg_id'];
  //                      //         $mess = $loginUserDetails['username'].' Just Live';
  //                      //         if(empty($loginUserDetails['image'])){
  //                      //           $liveuserimage['image'] = base_url().'uploads/no_image_available.png';
  //                      //         }
  //                      //         else{
  //                      //          $liveuserimage['image'] = $loginUserDetails['image'];
  //                      //         }
  //                      //         $liveUsername = $loginUserDetails['username'];
  //                      //         $this->liveNotification($regId,$mess,'liveUser',$list['userId'],$userId,$liveuserimage,$liveUsername,$this->input->post('channelName'),$this->input->post('latitude'),$this->input->post('longitude'),$token,$tokenb);
  //                      //         $notiMess['loginId'] = $userId;
  //                      //         $notiMess['userId'] = $list['userId'];
  //                      //         $notiMess['message'] = $mess;
  //                      //         $notiMess['type'] = 'liveUser';
  //                      //         $notiMess['notiDate'] = date('Y-m-d');
  //                      //         $notiMess['created'] = date('Y-m-d H:i:s');
  //                      //         $this->db->insert('userNotification',$notiMess);
  //                      //     }
  //                      // }
  //                     $userDetails = $this->db->get_where('users',array('id' => $this->input->post('userId')))->row_array();
  //                     // $todyDD = date('Y-m-d');
  //                     // $checkStarStatus = $this->db->get_where('userStar',array('userId' => $this->input->post('userId'),'created' => $todyDD))->row_array();
  //                     // if(!empty($checkStarStatus)){
  //                     //   $starStatus = $checkStarStatus['star'];
  //                     //   $starStatusstarCount = $checkStarStatus['starCount'];
  //                     //   $starListStatus =  $this->db->query("SELECT * FROM starList WHERE starCount BETWEEN 1 AND $starStatusstarCount order by id desc limit 1")->row_array();
  //                     //   if(!empty($starListStatus['box'])){
  //                     //     $starBOX = $starListStatus['box'];
  //                     //   }
  //                     //   else{
  //                     //     $starBOX = 0;
  //                     //   }
  //                     // }
  //                     // else{
  //                     //   $starStatus = '0';
  //                     //   $starBOX = 0;
  //                     // }
  //                     // $todyDD = date('Y-m-d');
  //                     // $mainUserId = $this->input->post('userId');
  //                     // $checkStarStatus12 = $this->db->query("SELECT * FROM `starBoxResult` where userId = $mainUserId and box = $starBOX and date(created) = '$todyDD'")->num_rows();
  //                     // if(!empty($checkStarStatus12)){
  //                     //   $outPut['checkBoxStatus'] = '0';
  //                     // }
  //                     // else{
  //                     //   $outPut['checkBoxStatus'] = '1';
  //                     // }


  //                     $outPut['name'] = $userDetails['name'];
  //                     $outPut['userId'] = $userDetails['id'];
  //                     $outPut['image'] = $userDetails['image'];
  //                     // $outPut['coin'] = $userDetails['coin'];
  //                     // $outPut['userLeval'] = $userDetails['leval'];
  //                  	//$outPut['starCount'] = $starStatus;
  //                     $outPut['toke'] = $token;
  //                     // $outPut['box'] = (string)$starBOX;
  //                     $outPut['channelName'] = $this->input->post('channelName');
  //                     $outPut['rtmToken'] = $tokenb;
  //                     $outPut['mainId'] = (string)$ids;
  //         // 			$outPut['bool'] = $getBool;
  //                     $message['success'] = '1';
  //                     $message['message'] = 'Token Generate Successfully';
  //                     $message['details'] = $outPut;
  //                   }
  //                   else{
  //                     $message['success'] = '0';
  //                     $message['message'] = 'Please try after some time';
  //                   }
  //                 }
  //                 else{
  //                   $message['success'] = '0';
  //                   $message['message'] = 'Please Try after some time';
  //                 }
  //             // }
  //             //   else{
  //             //     $checkRequest = $this->db->get_where('userLiveRequest',array('userId' => $this->input->post('userId')))->row_array();
  //             //     if(!empty($checkRequest)){
  //             //       $message['requestStatus'] = '1';
  //             //     }
  //             //     else{
  //             //       $message['requestStatus'] = '0';
  //             //     }
  //             //     $message['success'] = '0';
  //             //     $message['message'] = 'Your Account is banned for live';
  //             //   }
  //         // }
  //         echo json_encode($message);
  //     }

  public function agoraToken()
  {

    $getDetails = $this->db->get_where("userLive", ['userId' => $this->input->post('userId')])->row_array();

    if (!!$getDetails) {

      require APPPATH . '/libraries/agora/RtcTokenBuilder.php';
      require APPPATH . '/libraries/agora/RtmTokenBuilder.php';
      $appID = "86f31e0182524c3ebc7af02c9a35e0ca";
      $appCertificate = "69704a2c200e46bf8625c13264d1975c";
      $channelName = $this->input->post('channelName');
      $uid = '';
      $uidStr = '';
      $role = RtcTokenBuilder::RoleAttendee;
      $expireTimeInSeconds = 10800;
      $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
      $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
      $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);

      $roleb = RtmTokenBuilder::RoleRtmUser;
      $expireTimeInSecondsb = 10800;
      $currentTimestampb = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
      $privilegeExpiredTsb = $currentTimestampb + $expireTimeInSecondsb;
      $userii =  $this->input->post('userId');
      $tokenb = RtmTokenBuilder::buildToken($appID, $appCertificate, $userii, $roleb, $privilegeExpiredTsb);

      if (!empty($token)) {
        $data['userId'] = $this->input->post('userId');
        $data['channelName'] = $this->input->post('channelName');
        $data['latitude'] = $this->input->post('latitude');
        $data['longitude'] = $this->input->post('longitude');
        $data['hostType'] = $this->input->post('hostType');
        $data['token'] = $token;
        $data['rtmToken'] = $tokenb;
        $data['created'] = date('Y-m-d H:i:s');
        $data['status'] = 'live';
        $update = $this->db->update('userLive', $data, ['userId' => $this->input->post('userId')]);
        if (!empty($update)) {

          $this->db->set('liveCount', 'liveCount +1', false)->where('userId', $this->input->post('userId'))->update("userLive");
          $userDetails = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
          
          $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $this->input->post('userId'))
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

          $outPut['name'] = $userDetails['name'];
           $outPut['username'] = $userDetails['username'];
           $outPut['dob'] = $userDetails['dob'];
           $outPut['gender'] = $userDetails['gender'];
          $outPut['userId'] = $userDetails['id'];
          $outPut['image'] = $getImage['image'];
          $outPut['toke'] = $token;
          $outPut['channelName'] = $this->input->post('channelName');
          $outPut['rtmToken'] = $tokenb;
          $outPut['mainId'] = $getDetails['id'];
          
          $details =  $this->db->query("SELECT users.* FROM users")->result_array();
          foreach ($details as $detailss) {
          $loginUserDetails = $this->db->get_where('users',array('id' => $this->input->post('userId')))->row_array();
          $regId = $detailss['reg_id'];
          $data1['title'] = 'live';
          $data1['userId'] = $detailss['id'];
          $data1['liveUserId'] =  $this->input->post('userId');
          $data1['message'] = $loginUserDetails['name'].' Just Live';
          $data1['type'] = 'userLive';
          $data1['created'] = date('Y-m-d H:i:s');
          $this->sendNotification($regId, $message = $data1['message'], $title = $data1['title'], $type = $data1['type']);
          $insert1 = $this->db->insert('userLiveNotification_records', $data1);
        }

          echo json_encode([

            "message" => 'Token Generate Successfully',
            "success" => '1',
            "details" => $outPut,
          ]);
          exit;
        } else {
          echo json_encode([

            "message" => 'Please try after some time',
            "success" => '0',
          ]);
          exit;
        }
      } else {
        $message['success'] = '0';
        $message['message'] = 'Please Try after some time';
      }
    } else {
      require APPPATH . '/libraries/agora/RtcTokenBuilder.php';
      require APPPATH . '/libraries/agora/RtmTokenBuilder.php';
      $appID = "3d2f0f05051541298716f77fd7eab51d";
      $appCertificate = "bbc6c889333948b69d2b987898b33356";
      $channelName = $this->input->post('channelName');
      $uid = '';
      $uidStr = '';
      $role = RtcTokenBuilder::RoleAttendee;
      $expireTimeInSeconds = 10800;
      $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
      $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
      $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);

      $roleb = RtmTokenBuilder::RoleRtmUser;
      $expireTimeInSecondsb = 10800;
      $currentTimestampb = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
      $privilegeExpiredTsb = $currentTimestampb + $expireTimeInSecondsb;
      $userii =  $this->input->post('userId');
      $tokenb = RtmTokenBuilder::buildToken($appID, $appCertificate, $userii, $roleb, $privilegeExpiredTsb);

      if (!empty($token)) {
        $data['userId'] = $this->input->post('userId');
        $data['channelName'] = $this->input->post('channelName');
        $data['latitude'] = $this->input->post('latitude');
        $data['longitude'] = $this->input->post('longitude');
        $data['hostType'] = $this->input->post('hostType');
        $data['token'] = $token;
        $data['rtmToken'] = $tokenb;
        $data['created'] = date('Y-m-d H:i:s');
        $data['status'] = 'live';
        $insert = $this->db->insert('userLive', $data);
        $ids = $this->db->insert_id();

        if (!empty($insert)) {
          $this->db->set('liveCount', 'liveCount +1', false)->where('userId', $this->input->post('userId'))->update("userLive");
          $userDetails = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
          
          $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $this->input->post('userId'))
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

          $outPut['name'] = $userDetails['name'];
          $outPut['username'] = $userDetails['username'];
           $outPut['dob'] = $userDetails['dob'];
           $outPut['gender'] = $userDetails['gender'];
          $outPut['userId'] = $userDetails['id'];
          $outPut['image'] = $getImage['image'];
          $outPut['toke'] = $token;
          $outPut['channelName'] = $this->input->post('channelName');
          $outPut['rtmToken'] = $tokenb;
          $outPut['mainId'] = (string)$ids;
          
          $details =  $this->db->query("SELECT users.* FROM users")->result_array();
          foreach ($details as $detailss) {
          $loginUserDetails = $this->db->get_where('users',array('id' => $this->input->post('userId')))->row_array();
          $regId = $detailss['reg_id'];
          $data1['title'] = 'live';
          $data1['userId'] = $detailss['id'];
          $data1['liveUserId'] =  $this->input->post('userId');
          $data1['message'] = $loginUserDetails['name'].' Just Live';
          $data1['type'] = 'userLive';
          $data1['created'] = date('Y-m-d H:i:s');
          $this->sendNotification($regId, $message = $data1['message'], $title = $data1['title'], $type = $data1['type']);
          $insert1 = $this->db->insert('userLiveNotification_records', $data1);
        }

          echo json_encode([

            "message" => 'Token Generate Successfully',
            "success" => '1',
            "details" => $outPut,
          ]);
          exit;
        } else {
          echo json_encode([

            "message" => 'Please try after some time',
            "success" => '0',
          ]);
          exit;
        }
      } else {

        echo json_encode([

          "message" => 'Please try after some time',
          "success" => '0',
        ]);
        exit;
      }
    }
  }

  public function archieveLive(){
		if($this->input->post()){

			$data['archivedDate'] = date('Y-m-d');
			$data['endTime'] = date('H:i:s');
			$data['status'] = 'ARCHIEVED';
			$data['Liveimage'] = '';
			$data['imageText'] = '';
			$data['imageTitle'] = '';
			$data['live_hideUnhideStatus'] = '0';

			$update = $this->db->set($data)->where('id', $this->input->post('liveId'))->update('userLive');

			if(!!$update){

				echo json_encode([
					'status' => 1,
					'message' => 'LIVE ARCHIEVED'
				]);exit;

			}else{
				echo json_encode([
					'status' => 0,
					'message' => 'LIVE not ARCHIEVED'
				]);exit;
			}


		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'Enter valid data'
			]);exit;
		}
	  }


  public function getLiveMultiLive()
  {
    $get = $this->db->get_where("userLive", ['hostType' => '3'])->result_array();

    foreach ($get as $row) {
      $id = $row['userId'];
      $getUser = $this->db->get_where("users", array('id' => $id))->row_array();

      if(!!$getUser){
        $row['user'] = $getUser;
      }else{
        $row['user'] = "";
      }
      if (!empty($getUser['image'])) {
        $row['image'] = $getUser['image'];
      } else {
        $row['image'] = "";
      }
      if (!empty($getUser['name'])) {
        $row['name'] = $getUser['name'];
      } else {
        $row['name'] = "";
      }

      $final[] = $row;
    }

    if (!!$final) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      $message['details'] = $final;
    } else {
      $message['success'] = '0';
      $message['message'] = 'List not found';
    }
    echo json_encode($message);
  }

  public function getPopularUserLive()
  {
    //  $get = $this->db->get_where("userLive",['hostType' => '3','status' => 'live'])->result_array();

    $get = $this->db->select("userLive.*,users.name,users.username,users.dob,users.gender")
      ->from("userLive")
      ->join("users","users.id = userLive.userId","left")
      ->where("userLive.hostType", 3)
      ->where("userLive.status", "live")
      ->order_by("liveCount", "DESC")
      ->get()
      ->result_array();

    //  print_r($get);
    //  die;

    // foreach ($get as $row) {
    //   $id = $row['userId'];
    //   $getUser = $this->db->get_where("users", array('id' => $id))->row_array();

    //   if (!empty($getUser['image'])) {
    //     $row['image'] = $getUser['image'];
    //   } else {
    //     $row['image'] = "";
    //   }
    //   if (!empty($getUser['name'])) {
    //     $row['name'] = $getUser['name'];
    //   } else {
    //     $row['name'] = "";
    //   }

    //   $final[] = $row;
    // }
    
    
    $final = [];

      foreach($get as $list){
        $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $list['userId'])
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $list['imageDp'] = $getImage['image'];
                               
                             }else{
                              $list['imageDp'] = "";
                                
                             }
                             
        $checkFollowStatus = $this->db->get_where('kickOutLiveUser',['kickTo' => $this->input->post('userId'),'liveId' => $list['id'],'kickBy' => $list['userId']])->row_array();
        if($checkFollowStatus){
          $list['kickOutStatus'] = TRUE;
        }else{
          $list['kickOutStatus'] = FALSE;
        }
        
        $getDetails = $this->db->select("users.gender,users.dob")
        ->from("users")
        ->where("users.id",$this->input->post('userId'))
        ->get()
        ->row_array();
        
        if(!!$getDetails){
            
            $list['user_gender'] = $getDetails['gender'];
            $list['user_dob'] = $getDetails['dob'];
            
        }
        else{
            $list['user_gender'] = "";
            $list['user_dob'] = "";
        }
                             
         

      $final[] = $list;



      }

    if (!!$final) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      $message['details'] = $final;
    } else {
      $message['success'] = '0';
      $message['message'] = 'List not found';
    }
    echo json_encode($message);
  } 

  // public function getPrimeGift()
  // {
  //   $get = $this->db->get_where("gift", array('status' => 'Approved'))->result_array();
  //   foreach ($get as $row) {
  //     $row['image'] = $row['image'];
  //     $finalData[] = $row;
  //   }
  //   if (!empty($finalData)) {

  //     $message['success'] = "1";
  //     $message['message'] = "List found successfully";
  //     $message['details'] = $finalData;
  //   } else {
  //     $message['success'] = '0';
  //     $message['message'] = 'List not found';
  //   }
  //   echo json_encode($message);
  // }

  public function getPrimeGift(){

    $data['Privilege'] = $this->db->get_where("gift", array('gift_type' => 'Privilege'))->result_array() ?? "";
    $data['Trick'] = $this->db->get_where("gift", array('gift_type' => 'Trick'))->result_array() ?? "";
    $data['EventGifts'] = $this->db->get_where("gift", array('gift_type' => 'EventGifts'))->result_array() ?? "";
    $data['SoundGifts'] = $this->db->get_where("gift", array('gift_type' => 'SoundGifts'))->result_array() ?? "";

    echo json_encode([

      "success" => "1",
      "message" => "List found successfully",
      "details" => $data
    ]);exit;
  }

  // public function sendGift()
  // {
  //   $data['senderId'] = $this->input->post('senderId');
  //   $data['receiverId'] = $this->input->post('receiverId');
  //   $data['diamond'] = $this->input->post('diamond');
  //   $data['giftId'] = $this->input->post('giftId');
  //   $data['liveId'] = $this->input->post('liveId');
  //   $data['created'] = date('Y-m-d H:i:s');


  //   $getSenderData = $this->db->select('myDiamond, totalSendDiamond, myExp')->from('users')->where('id', $this->input->post('senderId'))->get()->row_array();
  //   if(!!$getSenderData){

  //     $getRecieverData = $this->db->select('myRecievedDiamond, myRecieveExperience')->from('users')->where('id', $this->input->post('receiverId'))->get()->row_array();
  //     if(!!$getRecieverData){

  //       $checkSenderCoins = $getSenderData['myDiamond'];
  //       $totalSenderSendCoin = $getSenderData['totalSendDiamond'];
  //       $senderExp = $getSenderData['myExp'];
  //       if($checkSenderCoins < $this->input->post('diamond')){
  //         echo json_encode([

  //           'status' => '0',
  //           'message' => 'Insufficient Balance'
          
  //         ]);exit;
  //       }

  //       $checkSenderCoins -= $this->input->post('diamond');
  //       $totalSenderSendCoin += $this->input->post('diamond');
  //       $senderExp += $this->input->post('diamond');

  //       $updateSenderCoin = $this->db->set(['myDiamond' => $checkSenderCoins, 'totalSendDiamond' => $totalSenderSendCoin, 'myExp' => $senderExp])->where('id', $this->input->post('senderId'))->update('users');
  //       if($updateSenderCoin){

  //       $checkRecieverCoin = $getRecieverData['myRecievedDiamond'];
  //       $recieverExp = $getRecieverData['myRecieveExperience'];

  //       $recievedDiamond = $this->input->post('diamond');

  //       $recievedDiamond *= 5;

  //       $recieverExp += $recievedDiamond;

  //       $checkRecieverCoin += $this->input->post('diamond');

  //       $updateRecieverCoin = $this->db->set(['myRecievedDiamond' => $checkRecieverCoin, 'myRecieveExperience' => $recieverExp])->where('id', $this->input->post('receiverId'))->update('users');

  //       $insert = $this->db->insert("received_gift_coin", $data);
          
  //         echo json_encode([
  //           'status' => '1',
  //           'message' => 'Gift send Successfully'
  //         ]);exit;


  //       }else{
  //         echo json_encode([
  //           'status' => '0',
  //           'message' => 'tech error'
  //         ]);exit;
  //       }


  //     }else{
  //       echo json_encode([
  //         'status' => '0',
  //         'message' => 'receiver Id not exists'
  //       ]);exit;
  //     }


  //   }else{
  //     echo json_encode([
  //       'status' => '0',
  //       'message' => 'sender id not exists'
  //     ]);exit;
  //   }

  // }

  public function sendGift(){

    $checkSenderId = $this->db->get_where("users",['id' => $this->input->post('senderId')])->row_array();

    if(empty($checkSenderId)){

      echo json_encode([

        "status" => "0",
        "message" => "Please enter valid senderId!"
      ]);exit;
    }

    $checkReceiverId = $this->db->get_where("users",['id' => $this->input->post('receiverId')])->row_array();

    if(empty($checkReceiverId)){

      echo json_encode([

        "status" => "0",
        "message" => "Please enter valid receiverId!"
      ]);exit;
    }

    $checkGift = $this->db->get_where("gift",['id' => $this->input->post('giftId')])->row_array();

    if(empty($checkGift)){

      echo json_encode([

        "status" => "0",
        "message" => "Please enter valid giftId!"
      ]);exit;
    }

    $checkWallet = $this->db->get_where("userWallet",['userId' => $this->input->post('senderId')])->row_array();

    if(empty($checkWallet)){

      echo json_encode([

        "status" => "0",
        "message" => "Sender wallet not exist!"
      ]);exit;
    }

    $checkSenderCoins = $checkWallet['wallet_amount'];

    if($checkSenderCoins > $this->input->post('diamond')){

      $data['senderId'] = $this->input->post('senderId');
      $data['receiverId'] = $this->input->post('receiverId');
      $data['diamond'] = $this->input->post('diamond');
      $data['giftId'] = $this->input->post('giftId');
      $data['liveId'] = $this->input->post('liveId');
      $data['created'] = date('Y-m-d H:i:s');

      $sendCoinAsDiamonds = $this->db->insert("received_gift_coin",$data);

      $getId = $this->db->insert_id();

      if($sendCoinAsDiamonds == true){

        // ========= MANAGE DEDUCT COINS AS DIAMONDS HISTORY TYPE =========

        $deduct['senderId'] = $this->input->post('senderId');
        $deduct['receiverId'] = $this->input->post('receiverId');
        $deduct['diamond'] = $this->input->post('diamond');
        $deduct['giftId'] = $this->input->post('giftId');
        $deduct['liveId'] = $this->input->post('liveId');
        $deduct['deduct_history_type'] = 'SendGifts';
        $deduct['created'] = date('Y-m-d H:i:s');

        $this->db->insert("deductCoinsHistory",$deduct);

        // ========= END MANAGE DEDUCT COINS AS DIAMONDS HISTORY TYPE =========

        $deductCoin['wallet_amount'] = $checkSenderCoins - $data['diamond'];

        $this->db->update("userWallet",$deductCoin,['id' => $this->input->post('senderId')]);

        $ReceivedDiamondAsCoins = $this->db->get_where("received_gift_coin",['id' => $getId])->row_array();

        $receivedCoins = $ReceivedDiamondAsCoins['diamond'];

        $myDiamond = $checkReceiverId['myDiamond'];
        $myDiamond += $receivedCoins;

        $myRecievedDiamond = $checkReceiverId['myRecievedDiamond'];
        $myRecievedDiamond += $receivedCoins;

        $totalSendDiamond = $checkSenderId['totalSendDiamond'];
        $totalSendDiamond += $receivedCoins;

        $updateRecieverCoin = $this->db->set(['myRecievedDiamond' => $myRecievedDiamond, 'myDiamond' => $myDiamond])->where('id', $this->input->post('receiverId'))->update('users');
        $updateSenderCoin = $this->db->set(['totalSendDiamond' => $totalSendDiamond])->where('id', $this->input->post('senderId'))->update('users');
        echo json_encode([

          'status' => '1',
          'message' => 'Gift send Successfully'
        
        ]);exit;
      }
      else{
        echo json_encode([

          'status' => '0',
          'message' => 'Something went wrong!'
        
        ]);exit;

      }
    }
    else{
      echo json_encode([

        'status' => '0',
        'message' => 'Insufficient Balance'
      
      ]);exit;
    }



  }

  public function userPostAndVideo()
  {
      
      $checkUser = $this->db->get_where("users",['id' => $this->input->post("userId")])->row_array();
      
      if(empty($checkUser)){
          
          echo json_encode([
              
              "success" => "0",
              "message" => "userId not exit",
              ]);
          exit;
      }

    if ($this->input->post()) {

      $data['description'] = $this->input->post("description");
      $data['userId'] = $this->input->post("userId");
      $data['status'] = $this->input->post("status");
      $data['created'] = date("Y-m-d H:i:s");
      $data['postCreated'] = date("Y-m-d");

      if (!empty($_FILES['image']['name'])) {
        $name1 = time() . '_' . $_FILES["image"]["name"];
        $name = str_replace(' ', '_', $name1);
        $tmp_name = $_FILES['image']['tmp_name'];
        $path = 'uploads/adminImg/' . $name;
        move_uploaded_file($tmp_name, $path);
        $data['image'] = base_url($path);
      }

      $upload = $this->db->insert("userPostAndVideo", $data);

      $getId = $this->db->insert_id();

      if ($upload == true) {

        $getDetails = $this->db->get_where("userPostAndVideo", ['id' => $getId])->row_array();

        echo json_encode([

          "message" => "details added successfuly",
          "success" => "1",
          "details" => $getDetails,
        ]);
        exit;
      } else {
        echo json_encode([

          "message" => "something went wrong!",
          "success" => "0",
        ]);
        exit;
      }
    } else {
      echo json_encode([

        "message" => "please enter valid params!",
        "success" => "0",
      ]);
      exit;
    }
  }

  public function removeUserPost(){

    $check = $this->db->get_where("userPostAndVideo",['id' => $this->input->post("id"),"userId" => $this->input->post("userId")])->row_array();

    if(empty($check)){

      echo json_encode([

        "success" => "0",
        "message" => "Please enter valid details!"
      ]);exit;
    }

    $remove = $this->db->delete("userPostAndVideo",['id' => $this->input->post("id"),"userId" => $this->input->post("userId")]);

    if($remove == true){

      echo json_encode([

        "success" => "1",
        "message" => "Post removed!"
      ]);
      exit;
    }
    else{

      echo json_encode([

        "success" => "0",
        "message" => "Something went wrong!"
      ]);exit;
    }
  }

  public function feedDetails()
  {
    if ($this->input->post()) {

      $get = $this->db->select("userPostAndVideo.id mediaId,userPostAndVideo.userId, userPostAndVideo.image media, userPostAndVideo.description mediaDescription, userPostAndVideo.status mediaStatus, userPostAndVideo.likeCount, userPostAndVideo.commentCount,userPostAndVideo.created postCreateddateTime,userPostAndVideo.postCreated, users.*")
        ->from('userPostAndVideo')
        ->join("users", "users.id = userPostAndVideo.userId", "left")
        ->order_by('created','desc')
        ->where('userPostAndVideo.userId', $this->input->post('userId'))
        ->get()->result_array();
        
    //     print_r($get);
    // die;

      if ($get) {

        $response['status'] = '1';
        $response['message'] = 'list found';


        foreach ($get as $key => $list) {
            
         $postCreated = $list['postCreateddateTime'];

          $getLikeStatus = $this->db->select('status')
            ->from('likeFeed')
            ->where('feedId', $list['mediaId'])
            ->where('userId', $this->input->post('otherId'))
            ->where('status', '1')
            ->get()->row_array();
            
            $check =  $this->timecheck($postCreated);
             
             if ($check) {
                $get[$key]['postTime'] = $check;
              } else {
                $get[$key]['postTime'] = '';
              }

          if ($getLikeStatus) {
            $list = $getLikeStatus;


            $get[$key]['likeStatus'] = TRUE;
          } else {
            $get[$key]['likeStatus'] = FALSE;
          }
        }

        $response['details'] = $get;
        echo json_encode($response);
      } else {
        echo json_encode([
          'status' => '0',
          'message' => 'data not found'
        ]);
      }
    } else {
      echo json_encode([
        'status' => '0',
        'message' => 'enter valid data'
      ]);
    }
  }
  

  public function foundDaysAgo(){
      
      // Get the current timestamp
        $current_timestamp = time();
        
        // Assume that the post was created on Jan 1, 2020 at 12:00 AM
        $post_timestamp = strtotime("2022-10-20 08:54:52");
        
        // Calculate the difference in seconds
        $difference = $current_timestamp - $post_timestamp;
        
        // Divide the difference by the number of seconds in a day to get the number of days
        $days = floor($difference / 86400);
        
        // Calculate the number of hours by dividing the remainder by the number of seconds in an hour
        $hours = floor(($difference % 86400) / 3600);
        
        $hours = round($hours);

        
        // Output the number of days and hours
        echo "This post was created $days days and $hours hours ago.";


  }

  public function likeDislike()
  {
    $check_like = $this->db->get_where('likeFeed', array('userId' => $this->input->post('userId'), 'feedId' => $this->input->post('feedId')))->row_array();

    if ($check_like) {

      if ($check_like['status'] == '0') {
        $status = '1';
      } else {
        $status = '0';
      }

      $id = $check_like['id'];

      $update = $this->db->set(['feedId' => $this->input->post('feedId'), 'userId' => $this->input->post('userId'), 'status' => $status])
        ->where('id', $id)
        ->update('likeFeed');
      if ($update) {

        $getCount = $this->db->select('likeCount')
                              ->from('userPostAndVideo')
                              ->where('id', $this->input->post('feedId'))
                              ->get()->row_array();

        if ($getCount) {


          if ($status == '0') {


            $count = $getCount['likeCount'] ? : 0 - 1;

            $updateCount = $this->db->set('likeCount', $count)
              ->where('id', $this->input->post('feedId'))
              ->update('userPostAndVideo');


            if ($updateCount) {

              $status = 'DisLike';
              echo json_encode([
                'status' => '1',
                'message' => 'like status changed to ' . $status,
                'likeUnLikestatus' => false
              ]);
            } else {
              echo json_encode([
                'status' => '0',
                'message' => 'like not changed'
              ]);
            }
          } else {

            $count = $getCount['likeCount'] + 1;

            $updateCount = $this->db->set('likeCount', $count)
              ->where('id', $this->input->post('feedId'))
              ->update('userPostAndVideo');

            if ($updateCount) {
              $status = 'Like';
              echo json_encode([
                'status' => '1',
                'message' => 'like status changed to ' . $status,
                'likeUnLikestatus' => true
              ]);
            } else {
              echo json_encode([
                'status' => '0',
                'message' => 'like status not changed '
              ]);
            }
          }
        }
      } else {

        echo json_encode([
          'status' => '0',
          'message' => 'try again after some time'
        ]);
      }
    } else {

      $details['feedId'] = $this->input->post('feedId');
      $details['userId'] = $this->input->post('userId');
      $details['status'] = '1';
      $details['created'] = date('Y-m-d H:i:s');

      $insert = $this->db->insert('likeFeed', $details);

      if ($insert) {

        $getCount = $this->db->select('likeCount')
          ->from('userPostAndVideo')
          ->where('id', $this->input->post('feedId'))
          ->get()->row_array();

        if ($getCount) {

          $count = $getCount['likeCount'] + 1;

          $updateCount = $this->db->set('likeCount', $count)
            ->where('id', $this->input->post('feedId'))
            ->update('userPostAndVideo');

          $status = 'Like';
          if ($updateCount) {
            echo json_encode([
              'status' => '1',
              'message' => 'like status changed to ' . $status,
              'likeUnLikestatus' => true
            ]);
          } else {
            echo json_encode([
              'status' => '0',
              'message' => 'like status not changed'
            ]);
          }
        } 
      } else {
        echo json_encode([
          'status' => '0',
          'message' => 'try again after some time'
        ]);
      }
    }
  }

  public function addComment(){
    if($this->input->post()){

      $data['feedId'] = $this->input->post('feedId');
      $data['userId'] = $this->input->post('userId');
      $data['comment'] = $this->input->post('comment');
      $data['created'] = date('Y-m-d H:i:s');

      $insert = $this->db->insert('commentFeed', $data);
      $insertId = $this->db->insert_id();

      if($insert){

        $getCommentCount = $this->db->select('commentCount')
                                    ->from('likeCount')
                                    ->where('feedId', $this->input->post('feedId'))
                                    ->get()->row_array();

        if($getCommentCount){


        $count = $getCommentCount['commentCount'];
        $count++;
        $updateCommentCount = $this->db->set('commentCount', $count)
                                      ->where('feedId', $this->input->post('feedId'))
                                      ->update('likeCount');

        if($updateCommentCount){

          echo json_encode([
            'status' => '1',
            'message' => 'comment added successfully',
            'commentId' => $insertId,
          ]);exit;

        }else{

          echo json_encode([
            'status' => '0',
            'message' => 'comment not added',
          ]);exit;

        }

        }else{
          
          $details['feedId'] = $this->input->post('feedId');
          $details['commentCount'] = '1';

          $insert = $this->db->insert('likeCount', $details);

          if($insert){
            echo json_encode([
              'status' => '1',
              'message' => 'comment added successfully',
              'commentId' => $insertId,
            ]);exit;
          }else{

            echo json_encode([
              'status' => '0',
              'message' => 'comment not added',
            ]);exit;

          }
        }

      }else{
        echo json_encode([
          'status' => '0',
          'message' => 'comment not added, try after some time.'
        ]);exit;
      }

      
 
    }else{
      echo json_encode([
        'status' => '0',
        'message' => 'enter valid data'
      ]);exit;
    }
  }
  
  public function getComments(){
      
      $get = $this->db->select('commentFeed.*,users.name')
      ->from("commentFeed")
      ->join("users","users.id = commentFeed.userId","left")
      ->where("commentFeed.feedId",$this->input->post("feedId"))
      ->get()
      ->result_array();
      
      if(!!$get){
          
          foreach($get as $list){
            
            
            $getImage = $this->db->select('image')
                                 ->from('userImages')
                                 ->where('userId', $list['userId'])
                                  
                                 ->order_by('id', 'desc')
                                 ->limit(1)
                                 ->get()->row_array();
    
                                 if(!!$getImage){
                                  $get['image'] = $getImage['image'];
                                   
                                 }else{
                                  $get['image'] = "";
                                    
                                 }
                                 
                                 $list['image'] = $get['image'];
                                 
             
    
    
                                 $final[] = $list;
     
          }

          
          echo json_encode([
              'status' => '1',
              'message' => 'comments found successfully',
              'details' => $final,
            ]);exit;
          
      }
      else{
          echo json_encode([
              'status' => '0',
              'message' => 'comments not found',
            ]);exit;
      }
  }

  public function deleteComment(){
    if($this->input->post()){

      $checkComment = $this->db->get_where('commentFeed',array('id' => $this->input->post('commentId')))->row_array();

      if($checkComment){

        $delete = $this->db->delete('commentFeed', array('id' => $this->input->post('commentId')));

        if($delete){

          $getCount = $this->db->select('commentCount')
                              ->from('likeCount')
                              ->where('feedId', $this->input->post('feedId'))
                              ->get()->row_array();
          $count = $getCount['commentCount'];
          $count--;

          $updateCount = $this->db->set('commentCount', $count)
                                  ->where('feedId', $this->input->post('feedId'))
                                  ->update('likeCount');

          if($updateCount){
            echo json_encode([
              'status' => '1',
              'message' => 'comment deleted'
            ]);exit;
          }

          
        }else{
          echo json_encode([
            'status' => '0',
            'message' => 'comment not exist'
          ]);exit;
        }

      }else{
        echo json_encode([
          'status' => '0',
          'message' => 'comment not exist'
        ]);exit;
      }


    }else{
      echo json_encode([
        'status' => '0',
        'message' => 'enter valid data'
      ]);
    }
  }

  public function followUnfollow(){
    if($this->input->post()){

      $get = $this->db->get_where('followFeed', array('userId' => $this->input->post('userId'), 'followingUserId' => $this->input->post('followingUserId')))->row_array();

      // if get is not empty user will be unfollowed
      if(!!$get){

        $unfollow = $this->db->where('id', $get['id'])
                             ->delete('followFeed');

        if(!!$unfollow){

          // getting following count

          $getFollowingCountForUser = $this->db->select('following')
                                            ->from('followCount')
                                            ->where('userId', $this->input->post('userId'))
                                            ->get()->row_array();
          $followingCount = $getFollowingCountForUser['following'];
          $followingCount--;

          // updating following count

          $updateFollowingCountForUser = $this->db->set('following', $followingCount)
                                               ->where('userId', $this->input->post('userId'))
                                               ->update('followCount'); 


          // getting follower count

          $getFollowCountForFollowingUser = $this->db->select('followers')
                                            ->from('followCount')
                                            ->where('userId', $this->input->post('followingUserId'))
                                            ->get()->row_array();
          $followerCount = $getFollowCountForFollowingUser['followers'];
          $followerCount--;

          // updating follower count

          $updateFollowCountForFollowingUser = $this->db->set('followers', $followerCount)
                                               ->where('userId', $this->input->post('followingUserId'))
                                               ->update('followCount'); 
          $type = '0';
          $this->checkFriends($this->input->post('userId'), $this->input->post('followingUserId'), $type);

          echo json_encode([
            'status' => '1',
            'message' => $this->input->post('userId'). ' is UnFollowing ' . $this->input->post('followingUserId')
          ]);exit;
        }else{
          echo json_encode([
            'status' => '0',
            'message' => 'try again after some time'
          ]);exit;
        }

        // if get is empty user we be followed
      }else{

        $data['userId'] = $this->input->post('userId');
        $data['followingUserId'] = $this->input->post('followingUserId');
        $data['created'] = date('Y-m-d H:i:s');
        
        $following = $this->db->insert('followFeed', $data);

        if(!!$following){
            
        // push notification to following user
        
        $getfollowBy = $this->db->get_where("users",['id' => $this->input->post('userId')])->row_array();
        $getFollowingUser = $this->db->get_where("users",['id' => $this->input->post('followingUserId')])->row_array();
        
        $regId = $getFollowingUser['reg_id'];
      
        $name = $getfollowBy['name'];
        $folloTo = $getFollowingUser['name'];
        $data1['followingUserId'] = $getFollowingUser['id'];
        $data1['userId'] = $this->input->post('userId');
        $data1['message'] = $name. " Following to " .$folloTo;
        $data1['type'] = "followUnfollow";
        $data1['title'] = "follow";
        $data1['created'] = date('Y-m-d H:i:s');
        $this->notification($regId,$message = $data1['message'], $title = $data1['title'], $type = $data1['type']);
        
        $this->db->insert('followUnFollow_notifications', $data1);

          // check if userId is there
          $checkListUserId = $this->db->get_where('followCount', array('userId' => $this->input->post('userId')))->row_array();
          // if list exist increase the following count
          if(!!$checkListUserId){
            // getting following count

          $getFollowingCountForUser = $this->db->select('following')
                                              ->from('followCount')
                                              ->where('userId', $this->input->post('userId'))
                                              ->get()->row_array();
          $followingCount = $getFollowingCountForUser['following'];
          $followingCount++;

          // updating following count

          $updateFollowingCountForUser = $this->db->set('following', $followingCount)
                                               ->where('userId', $this->input->post('userId'))
                                               ->update('followCount'); 
          }else{

            $details['userId'] = $this->input->post('userId');
            $details['following'] = '1';
            $insertFollowingCount = $this->db->insert('followCount', $details);

          }

          // check if followingUserId is there
          $checkListFollowingUserId = $this->db->get_where('followCount', array('userId' => $this->input->post('followingUserId')))->row_array();

          // if list exist increase the followers count
          if(!!$checkListFollowingUserId){
          // getting follower count

          $getFollowCountForFollowingUser = $this->db->select('followers')
                                            ->from('followCount')
                                            ->where('userId', $this->input->post('followingUserId'))
                                            ->get()->row_array();
          $followerCount = $getFollowCountForFollowingUser['followers'];
          $followerCount++;

          // updating following count

          $updateFollowCountForFollowingUser = $this->db->set('followers', $followerCount)
                                               ->where('userId', $this->input->post('followingUserId'))
                                               ->update('followCount'); 
          }else{

            $details['userId'] = $this->input->post('followingUserId');
            $details['followers'] = '1';
            $insertFollowersCount = $this->db->insert('followCount', $details);

          }

          $type = '1';
          $this->checkFriends($this->input->post('userId'), $this->input->post('followingUserId'), $type);

          echo json_encode([
            'status' => '1',
            'message' => $this->input->post('userId'). ' is Following ' . $this->input->post('followingUserId')
          ]);exit;
        }else{
          echo json_encode([
            'status' => '0',
            'message' => 'try again after some time'
          ]);exit;
        }

      }

    }else{
      echo json_encode([
        'status' => '0',
        'message' => 'enter invalid data'
      ]);
    }
  }
  
  public function sendNotification($regId, $message, $title, $type)
    {
        $registrationIds =  array($regId);

        $k = 'AAAAwYCOeR4:APA91bHpxF1S068bT3KeBYXTkRNngBEBW-gCiKFhqD43NV4M5yabPiaUBZSXFlHKwTwC63dVDz7jyNGy-qjfsZnzxCmmy86A_oc_IGDwN5bwdvyzaV3Ku_k-mV98bhHxh0blX_kM9gze';
        $msg = array(
            'message'     => $message,
            'title'        => $title,
            'type'        => $type,
            'vibrate'    => 1,
            'sound'        => 1,
            'largeIcon'    => 'large_icon',
            'smallIcon'    => 'small_icon',
        );
        $fields = array(
            'registration_ids' => $registrationIds,
            'data'            => $msg
        );

        $headers = array(
            'Authorization: key=' . $k,
            'Content-Type: application/json'
        );
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => $headers

        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        //  print_r($response);
        //  die;
    }
   
    
    public function notification($regId, $message, $title, $type)
  {

    $registrationIds =  array($regId);

    define('API_ACCESS_KEY', 'AAAAwYCOeR4:APA91bHpxF1S068bT3KeBYXTkRNngBEBW-gCiKFhqD43NV4M5yabPiaUBZSXFlHKwTwC63dVDz7jyNGy-qjfsZnzxCmmy86A_oc_IGDwN5bwdvyzaV3Ku_k-mV98bhHxh0blX_kM9gze');
    $msg = array(
      'message'   => $message,
      'title'    => $title,
      'type'    => $type,
      'vibrate'  => 1,
      'sound'    => 1,
      'largeIcon'  => 'large_icon',
      'smallIcon'  => 'small_icon',
    );
    $fields = array(
      'registration_ids' => $registrationIds,
      'data'      => $msg
    );

    $headers = array(
      'Authorization: key=' . API_ACCESS_KEY,
      'Content-Type: application/json'
    );
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($fields),
      CURLOPT_HTTPHEADER => $headers

    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    // print_r($response);
    // die;
  }
  
  

  public function checkFriends($userId, $followingUserId, $type){
    if($type == '0'){
      $checkFriend = $this->db->get_where('friends', ['userId' => $userId, 'followingUserId' => $followingUserId])->row_array();
        if(!!$checkFriend){

            $delete = $this->db->delete('friends', ['userId' => $userId, 'followingUserId' => $followingUserId]);
            if($delete){return TRUE;}else{return FALSE;}

        }else{return FALSE;}
    }else if($type == '1'){ 
      $checkFollowFriend = $this->db->get_where('followFeed', ['userId' => $userId, 'followingUserId' => $followingUserId])->row_array();

      if($checkFollowFriend){

        $checkFriendFollow = $this->db->get_where('followFeed', ['userId' => $followingUserId, 'followingUserId' => $userId])->row_array();

        if($checkFriendFollow){

            $data['userId'] = $userId;
            $data['followingUserId'] = $followingUserId;
            $insert = $this->db->insert('friends', $data);
            if($insert){return TRUE;}else{return FALSE;}

        }else{ return FALSE;}

        }else{return FALSE;}
      }
      
  }

  public function getUserDetails(){
    // echo "hi";
    if($this->input->post()){

      $getOtherUserInfo = $this->db->select('*')
                                   ->from('users')
                                   ->where('id', $this->input->post('otherUserId'))
                                   ->get()->row_array();

      if(!!$getOtherUserInfo){

        $resposne['status'] = '1';
        $resposne['message'] = 'user details found';
        $resposne['details'] = $getOtherUserInfo;

        $getFollowCount = $this->db->select("userId")
                                    ->from('followFeed')
                                    ->where('followingUserId', $this->input->post('otherUserId'))
                                    ->get()->num_rows();

        $getFollowingCount = $this->db->select("followingUserId")
                                    ->from('followFeed')
                                    ->where('userId', $this->input->post('otherUserId'))
                                    ->get()->num_rows();
                                    // print_r($getFollowingCount);exit;
        if(!!$getFollowCount){
          $resposne['details']['followersCount'] = '' .$getFollowCount . '';
          // $resposne['details']['followingCount'] = '' . $getFollowingCount . '';
        }else{
          $resposne['details']['followersCount'] = '0'; 
        // $resposne['details']['followingCount'] =  '0';
        }
        if(!!$getFollowingCount){
          // $resposne['details']['followersCount'] = '' .$getFollowCount . '';
          $resposne['details']['followingCount'] = '' . $getFollowingCount . '';
        }else{
          // $resposne['details']['followersCount'] = '0'; 
        $resposne['details']['followingCount'] =  '0';
        }

        $getVisitors = $this->db->get_where('visitor', ['otherUserId' => $this->input->post('otherUserId'), 'userId != ' => $this->input->post('otherUserId')])->num_rows();
        $resposne['details']['visitorsCount'] = ''. $getVisitors .'';

        $user = $this->input->post('userId');
        $other = $this->input->post('otherUserId');

        $getFriendsCount = $this->db->query("SELECT * FROM friends WHERE userId = $other OR followingUserId = $other")->num_rows(); 
        $get = $this->db->get('users')->result_array();
        $friendsCounts = 0;
        if(empty($get)){
            $friendsCounts = 0;
        }else{
            foreach($get as $gets){
                
                if($this->friendsCheck($this->input->post('otherUserId'), $gets['id']) == true){
                    ++$friendsCounts;
                }
                
            }
            
        }
        $resposne['details']['friendsCount'] = '' .$friendsCounts. '';
        


        $checkFollowStatus = $this->db->get_where('followFeed',['followingUserId' => $this->input->post('otherUserId'),'userId' => $this->input->post('userId')])->row_array();
        if($checkFollowStatus){
          $resposne['details']['followStatus'] = TRUE;
        }else{
          $resposne['details']['followStatus'] = FALSE;
        }

        $checkLiveStatus = $this->db->get_where('userLive',['userId' => $this->input->post('otherUserId'),'status !=' => 'ARCHIEVED'])->row_array();
        if(!!$checkLiveStatus){
          $resposne['details']['liveStatus'] = TRUE;
          $resposne['details']['userLive'] = $checkLiveStatus;
          
          $getHide = $checkLiveStatus['live_hideUnhideStatus'];
          
          if($getHide == '1'){
              
            $resposne['details']['hideStatus'] = TRUE;
          }
          else{
          $resposne['details']['hideStatus'] = FALSE;
           
        }
        
        $getLiveId = $checkLiveStatus['id'];
        $getuserId = $checkLiveStatus['userId'];
                                                    
        $checkFollowStatus = $this->db->get_where('kickOutLiveUser',['kickTo' => $this->input->post('kickTo'),'liveId' => $getLiveId,'kickBy' => $getuserId])->row_array();
        if($checkFollowStatus){
            $resposne['details']['kickOutStatus'] = TRUE;
        }else{
            $resposne['details']['kickOutStatus'] = FALSE;
        }
        }else{
          $resposne['details']['liveStatus'] = FALSE;
          $resposne['details']['hideStatus'] = FALSE;
          $resposne['details']['userLive'] =  null;
          $resposne['details']['kickOutStatus'] = FALSE;
        }
        
        // $getLiveId = $checkLiveStatus['id'];
        // $getuserId = $checkLiveStatus['userId'];
                                                    
        // $checkFollowStatus = $this->db->get_where('kickOutLiveUser',['kickTo' => $this->input->post('kickTo'),'liveId' => $getLiveId,'kickBy' => $getuserId])->row_array();
        // if($checkFollowStatus){
        //     $resposne['details']['kickOutStatus'] = TRUE;
        // }else{
        //     $resposne['details']['kickOutStatus'] = FALSE;
        // }

        $getImage = $this->db->select('image')
                              ->from('userImages')
                              ->where('userId', $this->input->post('otherUserId'))
                              ->order_by('id', 'desc')
                              ->get()->row_array();
        if(!!$getImage) {
          $resposne['details']['profileImage'] = $getImage['image'];
        }else{
          $resposne['details']['profileImage'] = null;
        }

        echo json_encode($resposne);exit;

      }else{
        echo json_encode([
          'status' => '0',
          'message' => 'No data found'
        ]);exit;
      }

    }else{
      echo json_encode([
        'status' => '0',
        'message' => 'enter valid data'
      ]);
    }
  }
  
  
    public function getFollowingDetails(){

    $get = $this->db->select("followFeed.id followFeedId,followFeed.userId,followFeed.followingUserId,users.*")
    ->from("followFeed")
    ->join("users","users.id = followFeed.followingUserId","left")
    ->where("followFeed.userId",$this->input->post("userId"))
    ->get()
    ->result_array();

    if(!!$get){
      
      $final = [];
      foreach($get as $list){

      $get['imageDp'] = null;

      $checkImage = $this->db->select('image')
      ->from('userImages')
      ->where('userId', $list['id'])
      ->order_by('id', 'desc')
      ->limit(1)
      ->get()->row_array();

      if(!!$checkImage){
       $list['imageDp'] = $checkImage['image'];

       $final[] = $list;
      }
    }

      echo json_encode([

        "success" => "1",
        "message" => "details found",
        "details" => $final
      ]);exit;
    }
    else{
      echo json_encode([

        "success" => "0",
        "message" => "details not found",
      ]);exit;
    }

  }


  public function getFollowersDetails(){
    if($this->input->post()){

      $get = $this->db->select('followFeed.id followFeedId, followFeed.userId, users.*')
                      ->from('followFeed')
                      ->join('users', 'users.id = followFeed.userId', 'left')
                      ->where('followFeed.followingUserId', $this->input->post('userId'))
                      ->get()->result_array();

      if(!!$get){

        $final = [];

        foreach($get as $gets){
          $gets['imageDp'] = null;

          $checkImage = $this->db->select('image')
                                  ->from('userImages')
                                  ->where('userId', $gets['userId'])
                                  ->order_by('id', 'desc')
                                  ->limit(1)
                                  ->get()->row_array();
    
          if(!!$checkImage){
           $gets['imageDp'] = $checkImage['image'];
          }

          $final[] = $gets;
        }



        echo json_encode([
          'status' => '1',
          'message' => 'User Found',
          'details' => $final
        ]);exit;
      }else{
        echo json_encode([
          'status' => '0',
          'message' => 'No User Found'
          
        ]);exit;
      }

    }else{
      echo json_encode([
        'status' => '0',
        'message' => 'Enter Valid Data'
      ]);
    }
  }
  
  public function getMyFriends(){
      if($this->input->post()){
          
          $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
          if(empty($user)){
              echo json_encode([
               'status' => '0',
              'message' => 'invalid userId'
                  ]);exit;
          }
          
          $get = $this->db->get_where('friends', ['userId' => $this->input->post('userId')])->result_array();
          if(empty($get)){
              echo json_encode([
                  'status' => '0',
                  'message' => 'no friends found'
                  ]);exit;
          }
          
          $id = [];
          foreach($get as $gets){
              
              $idss = $this->db->get_where('friends', ['userId' => $gets['followingUserId'], 'followingUserId' => $this->input->post('userId')])->row_array();
              if(!!$idss){
                  
              $id[] = $idss;
              }
          }
          
          $final = [];
          foreach($id as $idd){
              $getUser = $this->db->get_where('users', ['id' => $idd['userId']])->row_array();
              
              $final[] = $getUser;
          }
          
          if(empty($final)){
              echo json_encode([
                  'status' => '0',
                  'message' => 'no data found'
                  ]);exit;
          }
          
          echo json_encode([
              'status' => '1',
              'message' => 'data found',
              'details' => $final
              ]);exit;
          
          

          
          
      }else{
          echo json_encode([
              'status' => '0',
              'message' => 'enter valid para'
              ]);exit;
      }
  }
  
  

  public function getFriendsDetails(){
    if($this->input->post()){

    $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
    if(empty($user)){
        echo json_encode([
            'success' => '0',
            'message' => 'inavlid userId'
            ]);exit;
    }
    
    $allUser = $this->db->get('users')->result_array();
    if(empty($allUser)){
        echo json_encode([
            'success' => '0',
            'message' => 'no user found'
            ]);exit;
    }

$users = []; 
    foreach($allUser as $user){
        if($this->friendsCheck($this->input->post('userId'), $user['id']) == true){
            $users[] = $user;
        }
    }

                      if(!!$users){

                        $final = [];
                        foreach($users as $gets){

                          $gets['imageDp'] = null;

                          $checkImage = $this->db->select('image')
                                                  ->from('userImages')
                                                  ->where('userId', $gets['id'])
                                                  ->order_by('id', 'desc')
                                                  ->get()->row_array();
                    
                          if(!!$checkImage){
                           $gets['imageDp'] = $checkImage['image'];
                          }

                          $final[] = $gets;

                        }


                        // SELECT * FROM friends WHERE userId = $other OR followingUserId = $other
                        echo json_encode([
                          'status' => '1',
                          'message' => 'FriendsList Found',
                          'details' => $final
                        ]);exit;

                      }else{
                        echo json_encode([
                          'status' => '0',
                          'message' => 'No Friends Found'
                        ]);exit;
                      }

    }else{
      echo json_encode([
        'status' => '0',
        'message' => 'Please Enter Valid Data'
      ]);
    }
  }

  public function setVisitor(){
    if($this->input->post()){

      $get = $this->db->get_where('visitor', ['userId' => $this->input->post('userId'), 'otherUserId' => $this->input->post('otherUserId')])->row_array();
      if(!!$get){
        echo json_encode([
          'status' => '0',
          'message' => 'user already visited'
        ]);exit;
      }else{

        $data['userId'] = $this->input->post('userId');
        $data['otherUserId'] = $this->input->post('otherUserId');
        $data['visited'] = 'yes';
        $data['created'] = date('Y-m-d H:i:s');

        $insert = $this->db->insert('visitor', $data);
        if($insert){
          echo json_encode([
            'status' => '1',
            'message' => 'not visited'
          ]);exit;
        }else{
          echo json_encode([
            'status' => '0',
            'message' => 'some problem occured'
          ]);exit;
        }

      }

    }else{
      echo json_encode([
        'status' => '0',
        'message' => 'Please Enter Valid Data'
      ]);exit;
    }
  }
  public function getSetVisitor(){

    if($this->input->post()){

      $checksUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
      if(!!$checksUser){

        $getVisit = $this->db->get_where('visitor', ['otherUserId' => $this->input->post('userId'), 'userId != ' => $this->input->post('userId')])->result_array();
        if(!!$getVisit){
          foreach($getVisit as $list){
            $getUser = $this->db->get_where('users', ['id' => $list['userId']])->row_array();


            if($getUser != null || !empty($getUser)){
              $getUser['imageDp'] = null;
              $checkImage = $this->db->select('image')
              ->from('userImages')
              ->where('userId', $list['userId'])
              ->order_by('id', 'desc')
              ->get()->row_array();
        
              if(!!$checkImage){
               $getUser['imageDp'] = $checkImage['image'];
              }
              $users[] = $getUser;
            }
            

          }
          echo json_encode([
            'status' => 1,
            'message' => 'users found',
            'deatils' => $users
          ]);exit;
        }else{
          echo json_encode([
            'status' => 0,
            'message' => 'no visitors found'
          ]);exit;
        }

      }else{
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);exit;
      }

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }

  }

  public function getCoinValue(){
    $get = $this->db->get('coinDetails')->result_array();
    if(!!$get){
      echo json_encode([
        'status' => '1',
        'message' => 'Coins',
        'details' => $get
      ]);exit;
    }else{
      echo json_encode([
        'status' => '0',
        'message' => 'No Value'
      ]);exit;
    }
  }
  
  public function getSilverCoinValue(){
    $get = $this->db->get('silvercCoinDetails')->result_array();
    if(!!$get){
      echo json_encode([
        'status' => '1',
        'message' => 'Coins',
        'details' => $get
      ]);exit;
    }else{
      echo json_encode([
        'status' => '0',
        'message' => 'No Value'
      ]);exit;
    }
  }
  
  public function getEmoji(){

    $get = $this->db->get("addEmoji_fromPanel")->result_array();

    if(!!$get){

      foreach($get as $gets){

        $gets['frame_img'] = $gets['frame_img'];

        $final[] = $gets;
      }
      $message['success'] = '1';
      $message['message'] = 'Emoji found';
      $message['details'] = $final;
    }
    else{
      $message['success'] = '0';
      $message['message'] = 'Emoji not found!';
    }

    echo json_encode($message);


  }
  
    public function getFrames(){


      if($this->input->post()){

        $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
        if(empty($checkuser)){
          echo json_encode([
            'status' => 0,
            'message' => 'Invalid UserId'
          ]);exit;
        }

        $get = $this->db->get("addFrames_fromAdmin")->result_array();

        if(empty($get)){
          echo json_encode([
            'status' => 0,
            'message' => 'No frames found'
          ]);exit;
        }

        $final = [];

        foreach($get as $gets){

          $gets['frame_img'] = base_url().$gets['frame_img'];


          $gets['isMy'] = false;

          $checkFramePurchase = $this->db->get_where('userFrames', ['userId' => $this->input->post('userId'), 'frameId' => $gets['id']])->row_array();

          if(!empty($checkFramePurchase)){
              
             
              $date = date('Y-m-d');
            if($checkFramePurchase['dateTo'] > $date){
                $getdateTo = $checkFramePurchase['dateTo'];
              
                $future = strtotime($getdateTo);
                $now = time();
                $timeleft = $future-$now;
                $daysleft = round((($timeleft/24)/60)/60); 
                
                $gets['remainingDays'] = (string)$daysleft;

              $gets['isMy'] = true;

            }
          }

          $final[] = $gets;

        }

        echo json_encode([
          'status' => 1,
          'message' => 'list found',
          'details' => $final
        ]);exit;


      }else{
        echo json_encode([
          'status' => 0,
          'message' => 'Enter Valid Data'
        ]);exit;
      }



  }
    public function getGifs(){

    $get = $this->db->get("gifs")->result_array();

    if(!!$get){

      foreach($get as $gets){

        $gets['gifUrl'] = $gets['gifUrl'];

        $final[] = $gets;
      }
      $message['success'] = '1';
      $message['message'] = 'Frames found';
      $message['details'] = $final;
    }
    else{
      $message['success'] = '0';
      $message['message'] = 'Gifs not found!';
    }

    echo json_encode($message);


  }
  
  public function getLuckyId(){

    if($this->input->post()){

      $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
      if(empty($checkuser)){
        echo json_encode([
          'status' => 0,
          'message' => 'Invalid UserId'
        ]);exit;
      }

      $get = $this->db->get("Ep_luckyId")->result_array();

      if(empty($get)){
        echo json_encode([
          'status' => 0,
          'message' => 'No luckyId found'
        ]);exit;
      }

      $final = [];

      foreach($get as $gets){

        $gets['image'] = base_url().'/' . $gets['image'];


        $gets['isMy'] = false;

        $checkFramePurchase = $this->db->get_where('userLuckyId', ['userId' => $this->input->post('userId'), 'luckyId' => $gets['id']])->row_array();
        
        

        if(!empty($checkFramePurchase)){
            
          if($checkFramePurchase['dateTo'] > date('Y-m-d')){
              
              
            $getdateTo = $checkFramePurchase['dateTo'];
              
                $future = strtotime($getdateTo);
                $now = time();
                $timeleft = $future-$now;
                $daysleft = round((($timeleft/24)/60)/60); 
                
                $gets['remainingDays'] = (string)$daysleft;

            $gets['isMy'] = true;

          }
        }

        $final[] = $gets;

      }

      echo json_encode([
        'status' => 1,
        'message' => 'list found',
        'details' => $final
      ]);exit;


    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter Valid Data'
      ]);exit;
    }



}
 
	
 public function userWallet(){

	if($this->input->post()){
	
	$checkCoinValueId = $this->db->get_where("coinDetails",['id' => $this->input->post("wallet_amount")])->row_array();
	
	if(empty($checkCoinValueId)){
	    
	    echo json_encode([
	
					"success" => "0",
					"message" => "Please enter valid CoinValueId!"
				]);exit;
	}
	
	$checkUser = $this->db->get_where("users",['id' => $this->input->post("userId")])->row_array();
	
	if(empty($checkUser)){
	    
	    echo json_encode([
	
					"success" => "0",
					"message" => "Please enter valid userId!"
				]);exit;
	}
	
	$checkCurrentDate = $this->db->get_where("userWallet",['userId' => $this->input->post("userId")])->row_array();
	
	if(!!$checkCurrentDate){
	    
	    $getAmount = $checkCurrentDate['wallet_amount'];
	    
	    $walletAmount = $checkCoinValueId['coinValue'];
	    
	    $data['wallet_amount'] = $walletAmount + $getAmount;
		$data['userId'] = $this->input->post("userId");
		$data['razorpay_order_id'] = $this->input->post('razorpay_order_id');
        $data['razorpay_payment_id'] = $this->input->post('razorpay_payment_id');
        $data['razorpay_signature'] = $this->input->post('razorpay_signature');
        $data['pay_verifyStatus'] = '1';
		$data['updated'] = date("Y-m-d H:i:s");

		$updated = $this->db->update("userWallet",$data,['userId' => $this->input->post("userId")]);

		if($updated == true){

			$getEditDetails = $this->db->get_where("userWallet",['userId' => $this->input->post("userId")])->row_array();

			echo json_encode([

				"success" => "1",
				"message" => "wallet updated successfully",
				"details" => $getEditDetails,
			]);exit;
		}
		else{
			echo json_encode([

				"success" => "0",
				"message" => "wallet not updated-something went wrong!",
			]);exit;

		}
	} 
	else{
			$data['userId'] = $this->input->post("userId");
            $data['wallet_amount'] = $checkCoinValueId['coinValue'];
            $data['razorpay_order_id'] = $this->input->post('razorpay_order_id');
            $data['razorpay_payment_id'] = $this->input->post('razorpay_payment_id');
            $data['razorpay_signature'] = $this->input->post('razorpay_signature');
            $data['pay_verifyStatus'] = '1';
            $data['created'] = date("Y-m-d H:i:s");
	
			$upload = $this->db->insert("userWallet",$data);
			
			$lastId = $this->db->insert_id();
	
			if($upload == true){
	
				$getDetails = $this->db->get_where("userWallet",['id' => $lastId])->row_array();
	
				echo json_encode([
	
					"success" => "1",
					"message" => "details added successfully",
					"details" => $getDetails,
				]);exit;
			}
			else{
	
				echo json_encode([
	
					"success" => "0",
					"message" => "something went wrong!"
				]);exit;
			}	
			}
		}
		else{
			
			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid params!"
			]);exit;
		}
			
	}
  
  public function getUserWallet(){

    $getDetails = $this->db->get_where("userWallet",['userId' => $this->input->post("userId")])->result_array();

    if(!!$getDetails){

      echo json_encode([

        "success" => "1",
        "message" => "details found",
        "details" => $getDetails,
      ]);exit;

    }
    else{
      echo json_encode([

        "success" => "0",
        "message" => "details not found!",
      ]);exit;
      
    }
  }
   
	
	public function purchaseSilverCoin(){

	if($this->input->post()){
	    
	    $checkSilverCoinValueId = $this->db->get_where("silvercCoinDetails",['id' => $this->input->post("coinValue")])->row_array();
		
		if(empty($checkSilverCoinValueId)){
			
			echo json_encode([
		
						"success" => "0",
						"message" => "Please enter valid silverCoinId!"
					]);exit;
		}
		
		$checkUser = $this->db->get_where("users",['id' => $this->input->post("userId")])->row_array();
		
		if(empty($checkUser)){
			
			echo json_encode([
		
						"success" => "0",
						"message" => "Please enter valid userId!"
					]);exit;
		}
		
		$checkUserWallet = $this->db->get_where("userWallet",['userId' => $this->input->post("userId")])->row_array();
		
		if(empty($checkUserWallet)){
			
			echo json_encode([
		
						"success" => "0",
						"message" => "user wallet not exist!"
					]);exit;
		}
		
		$coinValue = $checkSilverCoinValueId['coinValue'];
	
		$walletCoinValue = $checkUserWallet['wallet_amount'];
			
		if($walletCoinValue < $coinValue){
			
			echo json_encode([
		
						"success" => "0",
						"message" => "Insufficient Balance!"
					]);exit;
		}

	$checkCurrentDate = $this->db->get_where("purchaseSilverCoin",['userId' => $this->input->post("userId")])->row_array();
 
	if(!!$checkCurrentDate){
	    
	    $getAmount = $checkCurrentDate['coinValue'];
	    
	    $walletAmount = $checkSilverCoinValueId['moneyValue'];
	    
	    $data['coinValue'] = $walletAmount + $getAmount;
		$data['userId'] = $this->input->post("userId");
		$data['updated'] = date("Y-m-d H:i:s");

		$updated = $this->db->update("purchaseSilverCoin",$data,['userId' => $this->input->post("userId")]);

		if($updated == true){

			$getEditDetails = $this->db->get_where("purchaseSilverCoin",['userId' => $this->input->post("userId")])->row_array();
			
			$walletCoinValue -= $coinValue;
			
 		    $this->db->set(['wallet_amount' => $walletCoinValue])->where('userId', $this->input->post('userId'))->update('userWallet');
		
			echo json_encode([

				"success" => "1",
				"message" => "Purchased silverCoin updated",
				"details" => $getEditDetails,
			]);exit;
		}
		else{
			echo json_encode([

				"success" => "0",
				"message" => "Purchased silverCoin not updated-something went wrong!",
			]);exit;

		}
	} 
	else{
			$data['userId'] = $this->input->post("userId");
            $data['coinValue'] = $checkSilverCoinValueId['moneyValue'];;
            $data['created'] = date("Y-m-d H:i:s");
	
			$upload = $this->db->insert("purchaseSilverCoin",$data);
			
			$lastId = $this->db->insert_id();
	
			if($upload == true){
			    
			    $walletCoinValue -= $coinValue;
			
 		        $this->db->set(['wallet_amount' => $walletCoinValue])->where('userId', $this->input->post('userId'))->update('userWallet');
	
				$getDetails = $this->db->get_where("purchaseSilverCoin",['id' => $lastId])->row_array();
	
				echo json_encode([
	
					"success" => "1",
					"message" => "Silver coin purchased",
					"details" => $getDetails,
				]);exit;
			}
			else{
	
				echo json_encode([
	
					"success" => "0",
					"message" => "something went wrong!"
				]);exit;
			}	
			}
		}
		else{
			
			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid params!"
			]);exit;
		}
			
	}
	
	
	public function purchaseFrames(){

    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'Invalid userId'
        ]);exit;
      }

      $checkFrame = $this->db->get_where('addFrames_fromAdmin', ['id' => $this->input->post('frameId')])->row_array();

      if(empty($checkFrame)){
        echo json_encode([
          'status' => 0,
          'message' => 'Invalid frameId'
        ]);exit;
      }

      $checkWallet = $this->db->get_where("userWallet",['userId' => $this->input->post('userId')])->row_array();

      if(empty($checkWallet)){
  
        echo json_encode([
  
          "status" => "0",
          "message" => "user wallet not exist!"
        ]);exit;
      }

      $checkFramePurchase = $this->db->get_where('userFrames', ['userId' => $this->input->post('userId'), 'frameId' => $this->input->post('frameId')])->row_array();

      if(!empty($checkFramePurchase)){

        $date = date('Y-m-d');
        if($checkFramePurchase['dateTo'] > $date){
          echo json_encode([
            'status' => 0,
            'message' => 'frame already pruchased'
          ]);exit;
        }
      }

      $frameAmount = $checkFrame['price'];
      $coinBalance = $checkWallet['wallet_amount'];
      $userExp = $checkUser['myExp'];

      if($coinBalance < $frameAmount){
        echo json_encode([
          'status' => 0,
          'message' => 'Insufficient Balance'
        ]);exit;
      }

    $coinBalance -= $frameAmount;
    $userExp += $frameAmount;

    $expDate = strtotime("+".$checkFrame['validity']." day");
    $dateTo = date('Y-m-d', $expDate);

     $buyFrame['userId'] = $this->input->post('userId');
     $buyFrame['frameId'] = $this->input->post('frameId');
     $buyFrame['price'] = $frameAmount;
     $buyFrame['dateFrom'] = date('Y-m-d');
     $buyFrame['dateTo'] = $dateTo;

     $insert = $this->db->insert('userFrames', $buyFrame);

     $buyFrameHistory['senderId'] = $this->input->post('userId');
     $buyFrameHistory['frameId'] = $this->input->post('frameId');
     $buyFrameHistory['price'] = $frameAmount;
     $buyFrameHistory['deduct_history_type'] = 'purchaseFrames';
     $buyFrameHistory['created'] = date("Y-m-d H:i:s");

     $this->db->insert('deductCoinsHistory', $buyFrameHistory); // Purchased frame history.
     $updateUserExp = $this->db->set(['myExp' => $userExp])->where('id', $this->input->post('userId'))->update('users');
     $updateUserCoinwallet = $this->db->set(['wallet_amount' => $coinBalance])->where('userId', $this->input->post('userId'))->update('userWallet');

     if($insert && $updateUserExp && $updateUserCoinwallet){

      $checkUserFrames = $this->db->select('*')
                                  ->from('userFrames')
                                  ->where('userId', $this->input->post('userId'))
                                  ->get()->result_array();

      $final = [];
      foreach($checkUserFrames as $frames){

              if($frames['dateTo'] >= date('Y-m-d')){

                $getFrame = $this->db->get_where('addFrames_fromAdmin', ['id' => $frames['frameId']])->row_array();
                $frames['frameIMage'] = base_url() . $getFrame['frame_img'];
                $final[] = $frames;

              }

      }

      echo json_encode([
        'status' => 1,
        'message' => 'Frame Purchased',
        'details' => $final
      ]);exit;

     }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Tech Error'
      ]);exit;
     }
     
    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }

	}

//   public function sendFrames(){
//     if($this->input->post()){

//       $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

//       if(empty($checkUser)){
//         echo json_encode([
//           'status' => 0,
//           'message' => 'invalid userId'
//         ]);exit;
//       }

//       $checkOtherUser = $this->db->get_where('users', ['id' => $this->input->post('otherUserId')])->row_array();

//       if(empty($checkOtherUser)){
//         echo json_encode([
//           'status' => 0,
//           'message' => 'invalid otherUserId'
//         ]);exit;
//       }

//       $checkFrame = $this->db->get_where('addFrames_fromAdmin', ['id' => $this->input->post('frameId')])->row_array();

//       if(empty($checkFrame)){
//         echo json_encode([
//           'status' => 0,
//           'message' => 'invalid frameId'
//         ]);exit;
//       }

//       $checkFramePurchase = $this->db->get_where('userFrames', ['userId' => $this->input->post('otherUserId'), 'frameId' => $this->input->post('frameId')])->row_array();

//       if(!empty($checkFramePurchase)){
//         if($checkFramePurchase['dateTo'] > date('Y-m-d')){
//           echo json_encode([
//             'status' => 0,
//             'message' => 'frame already purchased'
//           ]);exit;
//         }
//       }

//       $userCoins = $checkUser['myCoin'];
//       $framePrice = $checkFrame['price'];

//       if($userCoins < $framePrice) {
//         echo json_encode([
//           'status' => 0,
//           'message' => 'insufficient balance'
//         ]);exit;
//       }

//       $userCoins -= $framePrice;

//       $expDate = strtotime("+".$checkFrame['validity']." day");
//       $dateTo = date('Y-m-d', $expDate);

//       $buyFrame['userId'] = $this->input->post('otherUserId');
//       $buyFrame['frameId'] = $this->input->post('frameId');
//       $buyFrame['price'] = $framePrice;
//       $buyFrame['dateFrom'] = date('Y-m-d');
//       $buyFrame['dateTo'] = $dateTo;
 
//       $insert = $this->db->insert('userFrames', $buyFrame);
//       $updateUserCoin = $this->db->set('myCoin', $userCoins)->where('id', $this->input->post('userId'))->update('users');

//       if($insert && $updateUserCoin){
//         echo json_encode([
//           'status' => 1,
//           'message' => 'Frame sent successfully'
//         ]);exit;
//       }else{
//         echo json_encode([
//           'status' => 0,
//           'message' => 'tech error'
//         ]);exit;
//       }

//     }else{
//       echo json_encode([
//         'status' => 0,
//         'message' => 'Enter valid data'
//       ]);exit;
//     }
//   }


	
	public function getPurchaseFrame(){
	    

    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'Invalid userId'
        ]);exit;
      }

      $checkUserFrames = $this->db->select('*')
                                  ->from('userFrames')
                                  ->where('userId', $this->input->post('userId'))
                                  ->get()->result_array();

                                  $final = [];
                                  foreach($checkUserFrames as $frames){

                                          if($frames['dateTo'] >= date('Y-m-d')){


                                            $getFrame = $this->db->get_where('addFrames_fromAdmin', ['id' => $frames['frameId']])->row_array();
                                            $frames['frameIMage'] = base_url() . $getFrame['frame_img'];


                                            $frames['isApplied'] = false;

                                            if($frames['id'] == $checkUser['myFrame']){
                                              $frames['isApplied'] = true;
                                            }
                                            
                                            $final[] = $frames;

                                          }

                                  }

                                  if(empty($final)){

                                    echo json_encode([
                                      'status' => 0,
                                      'message' => 'No Frames Found'
                                    ]);exit;

                                  }else{

                                    echo json_encode([
                                      'status' => 1,
                                      'message' => 'Frames Found',
                                      'details' => $final
                                    ]);exit;

                                  }

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }
 	    
	 
	}

  public function getAppliedFrame(){
    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'userId not valid'
        ]);exit;
      }

      if($checkUser['myFrame'] == 0){
        echo json_encode([
          'status' => 0,
          'message' => 'no frame applied'
        ]);exit;
      }

      $checkFrame = $this->db->get_where('addFrames_fromAdmin', ['id' => $checkUser['myFrame']])->row_array();

      $checkFrame['frame_img'] = base_url() . $checkFrame['frame_img'];

      echo json_encode([
        'status' => 1,
        'message' => 'Frame found',
        'details' => $checkFrame
      ]);exit;

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter Valid data'
      ]);exit;
    }
  }

  public function applyFrame(){
    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'Invalid userId'
        ]);exit;
      }

      $checkFrame = $this->db->get_where('addFrames_fromAdmin', ['id' => $this->input->post('frameId')])->row_array();

      if(empty($checkFrame)){
        echo json_encode([
          'status' => 0,
          'message' => 'Invalid frameId'
        ]);exit;
      }

      $checkFramePurchase = $this->db->get_where('userFrames', ['userId' => $this->input->post('userId'), 'frameId' => $this->input->post('frameId')])->row_array();

      if(empty($checkFramePurchase)){

          echo json_encode([
            'status' => 0,
            'message' => 'frame not pruchased'
          ]);exit;

      }

      if(!!$checkFramePurchase){
        if($checkFramePurchase['dateTo'] < date('Y-m-d')){
          echo json_encode([
            'status' => 0,
            'message' => 'frame Expired'
          ]);exit;
        }
      }

      if($this->db->set('myFrame', $this->input->post('frameId'))->where('id', $this->input->post('userId'))->update('users')){
        echo json_encode([
          'status' => 1,
          'message' => 'frame applied',
          // 'details' => $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
        ]);exit;
      }




    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }
  }

	
	public function purchaseLuckyId(){

    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'Invalid userId'
        ]);exit;
      }

      $checkLuckyId = $this->db->get_where('Ep_luckyId', ['id' => $this->input->post('luckyId')])->row_array();

      if(empty($checkLuckyId)){
        echo json_encode([
          'status' => 0,
          'message' => 'Invalid luckyId'
        ]);exit;
      }

      $checkWallet = $this->db->get_where("userWallet",['userId' => $this->input->post('userId')])->row_array();

      if(empty($checkWallet)){
  
        echo json_encode([
  
          "status" => "0",
          "message" => "user wallet not exist!"
        ]);exit;
      }

      $checkLuckyIdPurchase = $this->db->get_where('userLuckyId', ['userId' => $this->input->post('userId'), 'luckyId' => $this->input->post('luckyId')])->row_array();

      if(!empty($checkLuckyIdPurchase)){
        $date = date('Y-m-d');
        if($checkLuckyIdPurchase['dateTo'] > $date){
          echo json_encode([
            'status' => 0,
            'message' => 'luckyId already purchased'
          ]);exit;
        }
      }

      $frameAmount = $checkLuckyId['price'];
      $coinBalance = $checkWallet['wallet_amount'];
      $userExp = $checkUser['myExp'];

      if($coinBalance < $frameAmount){
        echo json_encode([
          'status' => 0,
          'message' => 'Insufficient Balance'
        ]);exit;
      }

    $coinBalance -= $frameAmount;
    $userExp += $frameAmount;

    $expDate = strtotime("+".$checkLuckyId['validity']." day");
    $dateTo = date('Y-m-d', $expDate);

     $buyFrame['userId'] = $this->input->post('userId');
     $buyFrame['luckyId'] = $this->input->post('luckyId');
     $buyFrame['price'] = $frameAmount;
     $buyFrame['dateFrom'] = date('Y-m-d');
     $buyFrame['dateTo'] = $dateTo;

     $insert = $this->db->insert('userLuckyId', $buyFrame);

     $buyLuckyHistory['senderId'] = $this->input->post('userId');
     $buyLuckyHistory['luckyId'] = $this->input->post('luckyId');
     $buyLuckyHistory['price'] = $frameAmount;
     $buyLuckyHistory['deduct_history_type'] = 'purchaseLuckyId';
     $buyLuckyHistory['created'] = date("Y-m-d H:i:s");

     $this->db->insert('deductCoinsHistory', $buyLuckyHistory); // Purchased LuckyId history.
     $updateUserExp = $this->db->set(['myExp' => $userExp])->where('id', $this->input->post('userId'))->update('users');
     $updateUserCoinwallet = $this->db->set(['wallet_amount' => $coinBalance])->where('userId', $this->input->post('userId'))->update('userWallet');

     if($insert && $updateUserExp && $updateUserCoinwallet){

      $checkUserLuckyId = $this->db->select('*')
                                  ->from('userLuckyId')
                                  ->where('userId', $this->input->post('userId'))
                                  ->get()->result_array();

      $final = [];
      foreach($checkUserLuckyId as $luckyId){

        $date = date('Y-m-d');

              if($luckyId['dateTo'] >= $date){

                $getFrame = $this->db->get_where('Ep_luckyId', ['id' => $luckyId['luckyId']])->row_array();
                $frames['image'] = base_url() . $getFrame['image'];
                $final[] = $frames;

              }

      }

      echo json_encode([
        'status' => 1,
        'message' => 'LuckyId Purchased',
        'details' => $final
      ]);exit;

     }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Tech Error'
      ]);exit;
     }
     
    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }

		
	}
	
	public function getPurchaseLuckyId(){
	    
    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'Invalid userId'
        ]);exit;
      }

      $checkUserLuckyId = $this->db->select('*')
                                  ->from('userLuckyId')
                                  ->where('userId', $this->input->post('userId'))
                                  ->get()->result_array();

                                  $final = [];
                                  foreach($checkUserLuckyId as $luckyId){

                                          if($luckyId['dateTo'] >= date('Y-m-d')){


                                            $getFrame = $this->db->get_where('Ep_luckyId', ['id' => $luckyId['luckyId']])->row_array();
                                            $luckyId['frameIMage'] = base_url() . $getFrame['image'];


                                            $luckyId['isApplied'] = false;

                                            if($luckyId['id'] == $checkUser['myFrame']){
                                              $luckyId['isApplied'] = true;
                                            }
                                            
                                            $final[] = $luckyId;

                                          }

                                  }

                                  if(empty($final)){

                                    echo json_encode([
                                      'status' => 0,
                                      'message' => 'No luckyId Found'
                                    ]);exit;

                                  }else{

                                    echo json_encode([
                                      'status' => 1,
                                      'message' => 'luckyId Found',
                                      'details' => $final
                                    ]);exit;

                                  }

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }
 	    

	}

  public function getAppliedLuckyId(){
    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'userId not valid'
        ]);exit;
      }

      if($checkUser['myLuckyId'] == null){
        echo json_encode([
          'status' => 0,
          'message' => 'no myLuckyId applied'
        ]);exit;
      }

      $checkFrame = $this->db->get_where('Ep_luckyId', ['id' => $checkUser['myLuckyId']])->row_array();

      $checkFrame['image'] = base_url() . $checkFrame['image'];

      echo json_encode([
        'status' => 1,
        'message' => 'myLuckyId found',
        'details' => $checkFrame
      ]);exit;

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter Valid data'
      ]);exit;
    }
  }

  public function applyLuckyId(){
    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'Invalid userId'
        ]);exit;
      }

      $checkLuckyId = $this->db->get_where('Ep_luckyId', ['id' => $this->input->post('luckyId')])->row_array();

      if(empty($checkLuckyId)){
        echo json_encode([
          'status' => 0,
          'message' => 'Invalid luckyId'
        ]);exit;
      }

      $checkLuckyIdPurchase = $this->db->get_where('userLuckyId', ['userId' => $this->input->post('userId'), 'luckyId' => $this->input->post('luckyId')])->row_array();

      if(empty($checkLuckyIdPurchase)){

          echo json_encode([
            'status' => 0,
            'message' => 'luckyId not pruchased'
          ]);exit;

      }

      if(!!$checkLuckyIdPurchase){
        if($checkLuckyIdPurchase['dateTo'] < date('Y-m-d')){
          echo json_encode([
            'status' => 0,
            'message' => 'luckyId Expired'
          ]);exit;
        }
      }

      if($this->db->set('myLuckyId', $this->input->post('luckyId'))->where('id', $this->input->post('userId'))->update('users')){
        echo json_encode([
          'status' => 1,
          'message' => 'luckyId applied',
          // 'details' => $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
        ]);exit;
      }




    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }
  }

//   public function sendLuckyId(){
//     if($this->input->post()){

//       $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

//       if(empty($checkUser)){
//         echo json_encode([
//           'status' => 0,
//           'message' => 'invalid userId'
//         ]);exit;
//       }

//       $checkOtherUser = $this->db->get_where('users', ['id' => $this->input->post('otherUserId')])->row_array();

//       if(empty($checkOtherUser)){
//         echo json_encode([
//           'status' => 0,
//           'message' => 'invalid otherUserId'
//         ]);exit;
//       }

//       $checkLuckyId = $this->db->get_where('Ep_luckyId', ['id' => $this->input->post('luckyId')])->row_array();

//       if(empty($checkLuckyId)){
//         echo json_encode([
//           'status' => 0,
//           'message' => 'invalid luckyId'
//         ]);exit;
//       }

//       $checkLuckyIdPurchase = $this->db->get_where('userLuckyId', ['userId' => $this->input->post('otherUserId'), 'luckyId' => $this->input->post('luckyId')])->row_array();

//       if(!empty($checkLuckyIdPurchase)){
//         if($checkLuckyIdPurchase['dateTo'] > date('Y-m-d')){
//           echo json_encode([
//             'status' => 0,
//             'message' => 'frame already purchased'
//           ]);exit;
//         }
//       }

//       $userCoins = $checkUser['myCoin'];
//       $framePrice = $checkLuckyId['price'];

//       if($userCoins < $framePrice) {
//         echo json_encode([
//           'status' => 0,
//           'message' => 'insufficient balance'
//         ]);exit;
//       }

//       $userCoins -= $framePrice;

//       $expDate = strtotime("+".$checkLuckyId['validity']." day");
//       $dateTo = date('Y-m-d', $expDate);

//       $buyLuckyId['userId'] = $this->input->post('otherUserId');
//       $buyLuckyId['luckyId'] = $this->input->post('luckyId');
//       $buyLuckyId['price'] = $framePrice;
//       $buyLuckyId['dateFrom'] = date('Y-m-d');
//       $buyLuckyId['dateTo'] = $dateTo;
 
//       $insert = $this->db->insert('userLuckyId', $buyLuckyId);
//       $updateUserCoin = $this->db->set('myCoin', $userCoins)->where('id', $this->input->post('userId'))->update('users');

//       if($insert && $updateUserCoin){
//         echo json_encode([
//           'status' => 1,
//           'message' => 'luckyId sent successfully'
//         ]);exit;
//       }else{
//         echo json_encode([
//           'status' => 0,
//           'message' => 'tech error'
//         ]);exit;
//       }

//     }else{
//       echo json_encode([
//         'status' => 0,
//         'message' => 'Enter valid data'
//       ]);exit;
//     }
//   }

  public function getUserLevels(){

    $getLevels = $this->db->get("user_levels")->result_array();

    if(!!$getLevels){

        foreach($getLevels as $gets){

            $gets['image'] = $gets['image'];

            $final[] = $gets;
        }

        $message['success'] = '1';
        $message['message'] = 'details found';
        $message['details'] = $final;
    }
    else{
        $message['success'] = '0';
        $message['message'] = 'details not found!';
     }

     echo json_encode($message);
}

    public function getUserTalentLevels(){
    
    $getLevels = $this->db->get("user_talent_levels")->result_array();
    
    if(!!$getLevels){
    
      foreach($getLevels as $gets){
    
        $gets['image'] = $gets['image'];
    
        $final[] = $gets;
      }
    
      $message['success'] = '1';
      $message['message'] = 'details found';
      $message['details'] = $final;
    }
    else{
      $message['success'] = '0';
      $message['message'] = 'details not found!';
     }
    
     echo json_encode($message);
    }
    
    public function purchaseHistory()
	{

		$userId = $this->input->post('userId');
		$gets = $this->db->get_where('purchase_luckyId', ['userId' => $userId])->row_array();
		$getss = $this->db->get_where('yy_user_room_tools', ['userId' => $userId])->row_array();
		$getsss = $this->db->get_where('purchaseSilverCoin', ['userId' => $userId])->row_array();
		 
		if (!!$gets || !!$getss || !!$getsss) {
			 
				$data["purchase_luckyId_history"] = $this->db->select("purchase_luckyId.*")
					->from("purchase_luckyId")
					 
					->where("purchase_luckyId.userId", $userId)
					->get()
					->result_array() ?? [];
	
				$data["purchase_frame_history"] = $this->db->select("yy_user_room_tools.*")
					->from("yy_user_room_tools")
					->where("yy_user_room_tools.userId", $userId)
					->get()
					->result_array() ?? [];
					
			    $data["purchaseSilverCoin_history"] = $this->db->select("purchaseSilverCoin.*")
					->from("purchaseSilverCoin")
					->where("purchaseSilverCoin.userId", $userId)
					->get()
					->result_array() ?? [];
 

				$message['success'] = '1';
				$message['message'] = 'details found';
				$message['details'] = $data;
			 
		} else {
			$message['success'] = '0';
			$message['message'] = 'details not found!';
		}

		echo json_encode($message);
	}
	
	public function removeUserAccount(){

    $check = $this->db->get_where("users",['id' => $this->input->post("userId")])->row_array();

    if(!!$check){

      $remove = $this->db->delete("users",['id' => $this->input->post("userId")]);

      if($remove == true){

        echo json_encode([

          "success" => "1",
          "message" => "user account removed",
        ]);exit;
      }
      else{
        echo json_encode([

          "success" => "0",
          "message" => "something went wrong!",
        ]);exit;
      }
    }
    else{

      echo json_encode([

        "success" => "0",
        "message" => "Please enter valid details!",
      ]);exit;
    }
  }
  
  public function phoneNumUpdate(){
    if($this->input->post()){

      if(empty($this->input->post('otp'))){

        $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
        if(!!$checkUser){

          $phoneUpOtp['phoneUpOtp'] = rand(1000, 9999);
          $setOtp = $this->db->set($phoneUpOtp)->where('id', $this->input->post('userId'))->update('users');
          if($setOtp){

            echo json_encode([
              'status' => '1',
              'message' => 'OTP generated',
              'otp' => $phoneUpOtp['phoneUpOtp']
            ]);exit;

          }else{
            echo json_encode([
              'status' => '0',
              'message' => 'technical error'
            ]);exit;
          }

        }else{
          echo json_encode([
            'status' => '0',
            'message' => 'userId not exist'
          ]);exit;
        } 

      }else{

        $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
        if(!!$checkUser){

          $checkOtp = $this->db->get_where('users', ['phoneUpOtp' => $this->input->post('otp')])->row_array();
          if(!!$checkOtp){

            if(empty($this->input->post('newPhone'))){
              echo json_encode([
                'status' => '0',
                'message' => 'Enter Phone Number'
              ]);exit;
            }
            
            $phone['phone'] = $this->input->post('newPhone');

            $updatePhone = $this->db->set($phone)->where('id', $this->input->post('userId'))->update('users');
            if($updatePhone){

              echo json_encode([
                'status' => '1',
                'message' => 'Phone Number Updated'
              ]);exit;

            }else{
              echo json_encode([
                'status' => '0',
                'message' => 'technical error occured'
              ]);exit;
            }

          }else{
            echo json_encode([
              'status' => '0',
              'message' => 'Wrong OTP'
            ]);exit;
          }

        }else{
          echo json_encode([
            'status' => '0',
            'message' => 'userId not Exist'
          ]);exit;
        }

      }

    }else{

      echo json_encode([
        'status' => '0',
        'message' => 'Please Enter Valid Parameters'
      ]);exit;

    }
  }
  
  public function getFollowing(){
      
      
      $getDetails = $this->db->get_where("followFeed",['userId' => $this->input->post("userId")])->result_array();
      
      if(!!$getDetails){

        $final = [];
          
          foreach($getDetails as $get){
              
              $getId = $get['followingUserId'];
              
              $getDetails = $this->db->select("userLive.id userLiveId,userLive.hostType,userLive.userId,userLive.channelName,userLive.token,userLive.latitude LiveLat,userLive.longitude LiveLong,userLive.rtmToken,userLive.status,userLive.archivedDate,userLive.endTime,userLive.liveCount,userLive.password LivePassword,userLive.Liveimage,userLive.imageText,userLive.imageTitle,userLive.created,userLive.bool,userLive.live_hideUnhideStatus,userLive.live_hideUnhideExpTime,userLive.totaltimePerLive,userLive.createdDate,userLive.createdTime,users.*, userImages.image as imageDp")
              ->from("userLive")
              ->join("users","users.id = userLive.userId","left")
              ->join("userImages", 'userImages.userId = userLive.userId', 'left')
              ->where("userLive.userId",$getId)
              ->where("userLive.status","live")
              ->order_by('userImages.id', 'desc')
              ->limit(1)
              ->get()
              ->row_array();



              
               if(!empty($getDetails)){
                   
                    $getLiveId = $getDetails['id'];
                                                    $getuserId = $getDetails['userId'];
                                                    
                                                    $checkFollowStatus = $this->db->get_where('kickOutLiveUser',['kickTo' => $this->input->post('kickTo'),'liveId' => $getLiveId,'kickBy' => $getuserId])->row_array();
                                                    if($checkFollowStatus){
                                                      $getDetails['kickOutStatus'] = TRUE;
                                                    }else{
                                                      $getDetails['kickOutStatus'] = FALSE;
                                                    }
                                                    
        $getRecords = $this->db->select("users.gender,users.dob")
        ->from("users")
        ->where("users.id",$this->input->post('userId'))
        ->get()
        ->row_array();
        
        if(!!$getRecords){
            
            $getDetails['user_gender'] = $getRecords['gender'];
            $getDetails['user_dob'] = $getRecords['dob'];
            
        }
        else{
            $getDetails['user_gender'] = "";
            $getDetails['user_dob'] = "";
        }

               $final[] = $getDetails;
             }
             else{

             }
              
          }

          if(!empty($final)){

            echo json_encode([
              
              "success" => "1",
              "message" => "details found",
              "details" => $final,
              ]);exit;

          }else{
              echo json_encode([
              
                "success" => "0",
                "message" => "details not found"

            ]);exit;
          }


          


      }
      else{
          
          echo json_encode([
              
              "success" => "0",
              "message" => "details not found!"
              ]);exit;
      }
     
      
      
  }
  
  public function getFollowingFriends(){
      
      
      $getDetails = $this->db->get_where("friends",['userId' => $this->input->post("userId")])->result_array();
      
      if(!!$getDetails){
          
          $final = [];
          
          foreach($getDetails as $get){
              
              $getId = $get['followingUserId'];
              
              $getDetails = $this->db->select("users.*,userLive.*, userImages.image as imageDp")
              ->from("userLive")
              ->join("users","users.id = userLive.userId","left")
              ->join('userImages', 'userImages.userId = userLive.userId', 'left')
              ->where("userLive.userId",$getId)
              ->where("userLive.status","live")
              ->order_by('userImages.id', 'desc')
              ->limit(1)
              ->get()
              ->row_array();
              
              
                
               if(!!$getDetails){
                   
                   $getUser = $getDetails['userId'];
              
                  $getiD = $getDetails['id'];
                  
                  $checkFollowStatus = $this->db->get_where('kickOutLiveUser',['kickTo' => $this->input->post('kickTo'),'liveId' => $getiD,'kickBy' => $getUser])->row_array();
                  
              
                    if($checkFollowStatus){
                      $getDetails['kickOutStatus'] = TRUE;
                    }else{
                      $getDetails['kickOutStatus'] = FALSE;
                    }
                    
                     $getRecords = $this->db->select("users.gender,users.dob")
                    ->from("users")
                    ->where("users.id",$this->input->post('userId'))
                    ->get()
                    ->row_array();
                    
                    if(!!$getRecords){
                        
                        $getDetails['user_gender'] = $getRecords['gender'];
                        $getDetails['user_dob'] = $getRecords['dob'];
                        
                    }
                    else{
                        $getDetails['user_gender'] = "";
                        $getDetails['user_dob'] = "";
                    }

               $final[] = $getDetails;
             }
             else{

             }
  
          }
          if(!!$final){
          
          echo json_encode([
              
              "success" => "1",
              "message" => "details found",
              "details" => $final,
              ]);exit;
              
          }
          else{
          
          echo json_encode([
              
              "success" => "0",
              "message" => "details not found!"
              ]);exit;
      }

      }
      else{
          
          echo json_encode([
              
              "success" => "0",
              "message" => "details not found!"
              ]);exit;
      }
     
      
      
  }
  
//   public function checkLikeStatus(){

//       if($this->input->post("userId") == null){

//           echo json_encode([

//               "success" => "0",
//               "message" => "Something went wrong! param cannot be null",
//               ]);exit;
//       }

       
//       $checkStatus = $this->db->select("friends.*")
//       ->from("friends")
//       ->where("friends.userId",$this->input->post('userId'))
//       ->get()
//       ->result_array();
      
//     //   print_r($checkStatus);die;
      

//       if(!!$checkStatus){

//           $final = [];

//           foreach($checkStatus as $get){

//               $getiD = $get['followingUserId'];
//               $getuiD = $get['userId'];


//                   $getOtherUser = $this->db->select("friends.id friendsId,friends.userId,friends.followingUserId,users.*, userImages.image as imageDp")
//                                       ->from("friends")
//                                       ->join("users","users.id = friends.userId","left")
//                                       ->join('userImages', 'userImages.userId = friends.userId', 'left')
//                                       ->where("friends.userId",$getiD)
//                                       ->where("friends.followingUserId",$getuiD)
//                                       ->order_by('userImages.id', 'desc')
//                                       ->limit(1)
//                                       ->get()
//                                       ->row_array();
                                  

//                 if(!!$getOtherUser){

//                   $final[] = $getOtherUser;
//                  }
//                  else{
    
//                  }
          

//           }
//             if(!!$final){
          
//               echo json_encode([
                  
//                   "success" => "1",
//                   "message" => "details found",
//                   "details" => $final,
//                   ]);exit;
                  
//           }
//           else{
          
//           echo json_encode([
              
//               "success" => "0",
//               "message" => "details not found!"
//               ]);exit;
//       }

//       }
//       else{
//           echo json_encode([

//               "success" => "0",
//               "message" => "details not found!",
//               ]);exit;
//       }

//   }

  public function getLiveFriendsAndFollowing(){
    if($this->input->post()){

      $where = "userId = '" . $this->input->post('userId') . "' or followingUserId = '" . $this->input->post('userId') . "'";
      $getFriends = $this->db->select('followingUserId , userId')
                               ->from('friends')
                               ->where($where)
                               ->get()->result_array();

                                // print_r($getFriends);exit;
                              if(!!$getFriends){
                                $id = [];
                                foreach($getFriends as $list){
                                 if($list['followingUserId'] == $this->input->post('userId')){
                                   $id[] = $list['userId'];
                                 }else if($list['userId'] == $this->input->post('userId')){
                                   $id[] = $list['followingUserId'];
                                 }
                                }
                                

                                $ids = implode(",",$id);


                                $finalList = [];
                                foreach($id as $i){
                                  $getLiveFriendsDetails = $this->db->select('userLive.*, users.*, userImages.image as imageDp')
                                                                    ->from('userLive')
                                                                    ->join('users', 'users.id = userLive.userId', 'left')
                                                                    ->join('userImages', 'userImages.userId = userLive.userId', 'left')
                                                                    ->where('userLive.userId', $i)
                                                                    ->where('userLive.status', 'live')
                                                                    ->group_by('userLive.userId')
                                                                    ->order_by('userLive.id', 'desc')
                                                                    ->order_by('userImages.id', 'desc')
                                                                    ->get()->row_array();
                                                                    
                                                                    // print_r($getLiveFriendsDetails);
                                                                    // die;

                                                                    if(!empty($getLiveFriendsDetails)){
                                                                      $finalList[] = $getLiveFriendsDetails;
                                                                    }

                                }

      $where = "followingUserId NOT IN ($ids)";
      $getFollowing = $this->db->select('followingUserId')
                               ->from('followFeed')
                               ->where('userId', $this->input->post('userId'))
                               ->where($where)
                               ->get()->result_array();

                            //   print_r($this->db->last_query());exit;

                               foreach($getFollowing as $followinguser){
                                $getLiveFollowingDetails = $this->db->select('userLive.*, users.*, userImages.image as imageDp')
                                                                    ->from('userLive')
                                                                    ->join('users', 'users.id = userLive.userId', 'left')
                                                                    ->join('userImages', 'userImages.userId = userLive.userId', 'left')
                                                                    ->where('userLive.userId', $followinguser['followingUserId'])
                                                                    ->where('userLive.status', 'live')
                                                                    ->group_by('userLive.userId')
                                                                    ->order_by('userLive.id', 'desc')
                                                                    ->order_by('userImages.id', 'desc')
                                                                    ->get()->row_array();

                                                                    if(!empty($getLiveFollowingDetails)){
                                                                      $finalList[] = $getLiveFollowingDetails;
                                                                    }
                               }


                               if(empty($finalList)){

                                echo json_encode([
                                  'status' => 0,
                                  'message' => 'List not found'
                                 ]);exit; 

                               }

                               echo json_encode([
                                'status' => 1,
                                'message' => 'List found',
                                'details' => $finalList
                               ]);exit;

                              }else{


                                $getFollowing = $this->db->select('followingUserId')
                                                        ->from('followFeed')
                                                        ->where('userId', $this->input->post('userId'))
                                                        ->get()->result_array();
                                                        
                                                        if(empty($getFollowing)){
                                                            echo json_encode([
                                                                'status' => 0,
                                                                'message' => 'no user found'
                                                                ]);exit;
                                                        }
                                                        
 
                                $finalList = [];
                                foreach($getFollowing as $followinguser){
                                 $getLiveFollowingDetails = $this->db->select('userLive.*, users.*, userImages.image as imageDp')
                                                                     ->from('userLive')
                                                                     ->join('users', 'users.id = userLive.userId', 'left')
                                                                     ->join('userImages', 'userImages.userId = userLive.userId', 'left')
                                                                     ->where('userLive.userId', $followinguser['followingUserId'])
                                                                     ->where('userLive.status', 'live')
                                                                     ->group_by('userLive.userId')
                                                                     
                                                                     ->order_by('userLive.id', 'desc')
                                                                     ->order_by('userImages.id', 'desc')
                                                                     ->get()->row_array();
 
                                                                     if(!empty($getLiveFollowingDetails)){
                                                                       $finalList[] = $getLiveFollowingDetails;
                                                                     }

                                                                     if(empty($finalList)){

                                                                      echo json_encode([
                                                                        'status' => 0,
                                                                        'message' => 'List not found'
                                                                       ]);exit; 

                                                                     }


                                                                     echo json_encode([
                                                                      'status' => 1,
                                                                      'message' => 'List found',
                                                                      'details' => $finalList
                                                                     ]);exit;                       
                              }
                            }


    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }
  }
  

  
      
    public function userReport(){
    
      if($this->input->post()){
    
        $data['userReport_catId'] = $this->input->post("userReport_catId");
        $data['userReport_SubcatId'] = $this->input->post("userReport_SubcatId");
        $data['userId'] = $this->input->post("userId");
        $data['otherUserId'] = $this->input->post("otherUserId");
        
        $data['created'] = date("Y-m-d H:i:s");
    
        $upload = $this->db->insert("userReport",$data);
    
        $getId = $this->db->insert_id();
    
        if(!!$upload == true){
    
          $getdetails = $this->db->get_where("userReport",['id' => $getId])->row_array();
    
          echo json_encode([
    
            "success" => "1",
            "message" => "user report added",
            "details" => $getdetails,
          ]);exit;
        }
        else{
    
          echo json_encode([
    
            "success" => "0",
            "message" => "something went wrong!"
          ]);
          exit;
        }
      }
      else{
    
        echo json_encode([
    
          "success" => "0",
          "message" => "Please enter valid params!"
        ]);exit;
      }
    }
    
    
  public function getUserReportTypeSubCategories(){


    $getDetails = $this->db->get("userReportType_Subcategories_fromAdmin")->result_array();
    
    if(!!$getDetails){
        
        
        
        echo json_encode([
    
          "success" => "1",
          "message" => "details found",
          "details" => $getDetails,
        ]);exit;
        
        
    }
    else{
        echo json_encode([
    
          "success" => "0",
          "message" => "details not found!",
        ]);exit;
        
    }

   
  }
  
  public function getUserReportTypeCategories(){


    $getDetails = $this->db->get("userReporType_categories_fromAdmin")->result_array();
    
    if(!!$getDetails){
      
        
        echo json_encode([
    
          "success" => "1",
          "message" => "details found",
          "details" => $getDetails,
        ]);exit;
        
        
    }
    else{
        echo json_encode([
    
          "success" => "0",
          "message" => "details not found!",
        ]);exit;
        
    }

   
  }

  public function getUserImages(){
    if($this->input->post()){

      // $checkUser = $this->db->get_where('userImages', ['userId' => $this->input->post('userId')])->result_array();
      $checkUser = $this->db->select('image')
                            ->from('userImages')
                            ->where('userId', $this->input->post('userId'))
                            ->order_by('id', 'desc')
                            ->get()->result_array();
      if(!!$checkUser){

        echo json_encode([
          'status' => 1,
          'message' => 'Image List Found',
          'details' => $checkUser
        ]);exit;

      }else{
        echo json_encode([
          'status' => 0,
          'message' => 'No Images Found For the User'
        ]);exit;
      }

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter Valid DATA'
      ]);exit;
    }
  }

  public function socialLogin(){
    if($this->input->post()){

      if(!$this->input->post('social_id')){
        echo json_encode([
          'status' => 0,
          'message' => 'social id required'
        ]);exit;
      }

      $checkRegister = $this->db->get_where('users', ['social_id' => $this->input->post('social_id')])->row_array();
      if(!!$checkRegister){

        $register['reg_id'] = $this->input->post('reg_id');

        $up = $this->db->set($register)->where('id', $checkRegister['id'])->update('users');

        $checkImage = $this->db->select('image')
                               ->from('userImages')
                               ->where('userId', $checkRegister['id'])
                               ->order_by('id', 'desc')
                               ->get()->row_array();
                               
                  
                               if(!!$checkImage){
                                $checkRegister['image'] = $checkImage['image'];
                               }
                               else{
                                   $checkRegister['image'] =  "";
                               }

        if($up){

          echo json_encode([
            'status' => 1,
            'message' => 'User LogedIn',
            'details' => $checkRegister
          ]);exit;

        }else{
          echo json_encode([
            'status' => 0,
            'message' => 'technical error'
          ]);exit;
        }

      }
 
      $checkEmail = $this->db->get_where('users', ['email' => $this->input->post('email')])->row_array();
      if(!!$checkEmail){
        echo json_encode([
          'status' => 0,
          'message' => 'Email Is already in use'
        ]);exit;
      }

      $data['social_id'] = $this->input->post('social_id');
      $data['reg_id'] = $this->input->post('reg_id');
      $data['dev_id'] = $this->input->post('dev_id');
      $data['dev_type'] = $this->input->post('dev_type');
      $data['phone'] = $this->input->post('phone');
      $data['name'] = $this->input->post('name');
      $data['email'] = $this->input->post('email');
      $data['Country'] = $this->input->post('Country');

      if (!empty($_FILES['image']['name'])) {
        $name1 = time() . '_' . $_FILES["image"]["name"];
        $name = str_replace(' ', '_', $name1);
        $tmp_name = $_FILES['image']['tmp_name'];
        $path = 'uploads/adminImg/' . $name;
        move_uploaded_file($tmp_name, $path);
        $data['image'] = base_url($path);
      }

      $getUserName = $this->db->select('username')->from('users')->order_by('id', 'desc')->get()->row_array();
      if(empty($getUserName)){
        $data['username'] = '@500001';
      }else{
        $uname = $getUserName['username'];
        $data['username'] = ++$uname;
      }

      if($this->db->insert('users', $data)){

        $inId = $this->db->insert_id();

        $userInfo = $this->db->get_where('users', ['id' => $inId])->row_array();

        echo json_encode([
          'status' => 1,
          'message' => 'user register successfully',
          'details' => $userInfo
        ]);exit;

      }else{
        echo json_encode([
          'status' => 0,
          'message' => 'User Not Registered'
        ]);exit;
      }







    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter Valid DATA'
      ]);exit;
    }
  }

  public function getWalletDetails(){

    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(!!$checkUser){

        $myWallet['myCoin'] = $checkUser['myCoin'];
        $myWallet['myDiamond'] = $checkUser['myDiamond'];
        $myWallet['myRecievedDiamond'] = $checkUser['myRecievedDiamond'];

        echo json_encode([
          'status' => '1',
          'message' => 'Wallet Details Found',
          'details' => $myWallet
        ]);exit;

      }

      echo json_encode([
        'status' => '0',
        'message' => 'Invalid UserId'
      ]);exit;

    }else{
      echo json_encode([
        'status' => '0',
        'message' => 'Enter valid data'
      ]);exit;
    }

  }

  public function exchangeCoins(){

    if($this->input->post()){

      $checkCoins = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(!!$checkCoins){

        if($this->input->post('myDiamond') > $checkCoins['myDiamond']){
          echo json_encode([
            'status' => '0',
            'message' => 'Insufficient Diamond Balance : ' . $checkCoins['myDiamond']
          ]);exit;
        }


        $checkCoins['myDiamond'] -= $this->input->post('myDiamond');
        $checkCoins['myCoin'] += $this->input->post('myDiamond');
        $checkingCoin = $checkCoins['myCoin'];

        // print_r($checkingCoin);exit;

        // $vipLevel = $this->checkVip($checkingCoin);

        $up = $this->db->set(['myCoin' => $checkCoins['myCoin'], 'myDiamond' => $checkCoins['myDiamond']])
                       ->where('id', $this->input->post('userId'))
                       ->update('users');

                       if($up){

                        $userInfo = $this->db->get_where('users',['id' => $this->input->post('userId')])->row_array();

                        echo json_encode([
                          'status' => '1',
                          'message' => 'Diamond Coverted to Coins',
                          'details' => $userInfo
                        ]);exit;

                       }else{
                        echo json_encode([
                          'status' => '0',
                          'message' => 'tech error'
                        ]);exit;
                       }


      }else{
        echo json_encode([
          'status' => '0',
          'message' => 'Invalid UserId'
        ]);exit;
      }

    }else{
      echo json_encode([
        'status' => '0',
        'message' => 'Enter valid data'
      ]);
    }

  }


  

  // private function checkVip($coins){
  //   $get = $this->db->get('vip')->result_array();
  //   $message = 0;
  //   foreach($get as $list){
  //     if($coins >= $list['coins'] && $coins <= $list['coinsTo']){
  //       $message = $list['id'];
  //     }
  //   }
  //   return $message;
  // }



  public function dailyTasks(){
    if($this->input->post()){

      $date = date('Y-m-d');
      $getUserTaskInfo = $this->db->select('day1, day2, day3, day4, day5, day6, day7, dateFrom, dateTo')
                                  ->from('dailyTask')
                                  ->where('userId', $this->input->post('userId'))
                                  ->where('dateFrom <=', $date)
                                  ->where('dateTo >=', $date)
                                  ->order_by('id', 'desc')
                                  ->get()->row_array();

      if(empty($getUserTaskInfo)){
        echo json_encode([
          'status' => 0,
          'message' => 'user has not not started any task'
        ]);exit;
      }

      // $getTaskDetails = $this->db->get('dailyTaskReward')->result_array();
      $getTaskDetails = $this->db->select('reward, type rewardType')->from('dailyTaskReward')->get()->result_array();

      $final = [];
      $x = 1;
      foreach($getTaskDetails as $task){

        $d = 'day'.$x;

        $task['daytype'] = $getUserTaskInfo[$d];
        $task['day'] = $x;

        $x++;

        $final[] = $task;

      }

      if(!empty($final)){
        echo json_encode([
          'status' => 1,
          'message' => 'List found',
          'details' => $final
        ]);exit;
      }else{
        echo json_encode([
          'status' => 0,
          'message' => 'tasks not found'
        ]);exit;
      }

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }
  }

  public function setDailytask(){
    if($this->input->post()){

      $date = date('Y-m-d');
      $getUserTaskInfo = $this->db->select('id, day1, day2, day3, day4, day5, day6, day7, dateFrom, dateTo')
                                  ->from('dailyTask')
                                  ->where('userId', $this->input->post('userId'))
                                  ->where('dateFrom <=', $date)
                                  ->where('dateTo >=', $date)
                                  ->order_by('id', 'desc')
                                  ->get()->row_array();
      if(empty($getUserTaskInfo)){

        $expDate = strtotime("+1 week");
        $dateTo = date('Y-m-d', $expDate);

        $data['day1'] = 1;
        $data['userId'] = $this->input->post('userId');
        $data['dateFrom'] =  $date;
        $data['dateTo'] = $dateTo;

        if($this->db->insert('dailyTask', $data)){

          $inId = $this->db->insert_id();
          $showData = $this->db->get_where('dailyTask', ['id' => $inId])->row_array();
          echo json_encode([
            'status' => 1,
            'message' => 'Task Added',
            'details' => $showData
          ]);exit;
        }else{
          echo json_encode([
            'status' => 0,
            'message' => 'Tech Error'
          ]);exit;
        }

      }

      $day = 'day'.$this->input->post('day');
      
      $updata[$day] = 1;
      if($this->db->set($updata)->where('id', $getUserTaskInfo['id'])->update('dailyTask')){

        $get = $this->db->get_where('dailyTask', ['id' => $getUserTaskInfo['id']])->row_array();

        echo json_encode([
          'status' => 1,
          'message' => 'Task Updated',
          'details' => $get
        ]);exit;

      }else{
        echo json_encode([
          'status' => 0,
          'message' => 'Tech Error'
        ]);exit;
      }
    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }
  }

  public function getCarsByLevel(){
    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid UserId'
        ]);exit;
      }

      $get = $this->db->get('carsPerLevel')->result_array();

      if(empty($get)){
        echo json_encode([
          'status' => 0,
          'message' => 'Empty DB'
        ]);exit;
      }

      $final = [];
      foreach($get as $gets){

        $gets['available'] = false;

        $gets['image'] = $gets['image'];

        if($gets['level'] <= $checkUser['myLevel']){
            
             $getPurchaseLuckyId = $this->db->select("userLuckyId.userId,userLuckyId.luckyId,concat('" . base_url() . "', Ep_luckyId.image) as LuckyIdImage")
             ->from("userLuckyId")
             ->join("Ep_luckyId","Ep_luckyId.id = userLuckyId.luckyId","left")
             ->where("userLuckyId.userId",$this->input->post('userId'))
             ->get()
             ->result_array();
      
        
        if(!!$getPurchaseLuckyId){
            $gets['luckyId'] = $getPurchaseLuckyId;
            
        }
        else{
            $gets['luckyId'] = '';
        }
        
        $getLuckyReward = $this->db->select("deductCoinsHistory.receiverId userId,deductCoinsHistory.luckyId,Ep_luckyId.id,concat('" . base_url() . "', Ep_luckyId.image) as luckyIdImage")
             ->from("deductCoinsHistory")
             ->join("Ep_luckyId","Ep_luckyId.id = deductCoinsHistory.luckyId","left")
             ->where("deductCoinsHistory.receiverId",$this->input->post('userId'))
             ->where("deductCoinsHistory.deduct_history_type",'sendLuckyIdReward')
             ->get()
             ->result_array();
             
            if(!!$getLuckyReward){
            $gets['luckyIdReward'] = $getLuckyReward;
            
            }
            else{
                $gets['luckyIdReward'] = '';
            }
    
          $gets['available'] = true;
        }
        
        $final[] = $gets;

      }

      echo json_encode([
        'status' => 1,
        'message' => 'list found',
        'details' => $final
      ]);exit;

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid data'
      ]);exit;
    }
  }

  public function getFrameByLevel(){

    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid UserId'
        ]);exit;
      }

      $get = $this->db->get('framesPerLevel')->result_array();

      if(empty($get)){
        echo json_encode([
          'status' => 0,
          'message' => 'Empty DB'
        ]);exit;
      }

      $final = [];
      foreach($get as $gets){

        $gets['available'] = false;

        $gets['image'] = $gets['image'];

        if($gets['level'] <= $checkUser['myLevel']){
           
        
         $getPurchaseFrameId = $this->db->select("userFrames.userId,userFrames.frameId,concat('" . base_url() . "', addFrames_fromAdmin.frame_img) as FrameImage")
             ->from("userFrames")
             ->join("addFrames_fromAdmin","addFrames_fromAdmin.id = userFrames.frameId","left")
             ->where("userFrames.userId",$this->input->post('userId'))
             ->get()
             ->result_array();
             
            if(!!$getPurchaseFrameId){
            $gets['Frames'] = $getPurchaseFrameId;
            
            }
            else{
                $gets['Frames'] = '';
            }
            
            $getFrameReward = $this->db->select("deductCoinsHistory.receiverId userId,deductCoinsHistory.frameId,addFrames_fromAdmin.id,concat('" . base_url() . "', addFrames_fromAdmin.frame_img) as frame_img")
             ->from("deductCoinsHistory")
             ->join("addFrames_fromAdmin","addFrames_fromAdmin.id = deductCoinsHistory.frameId","left")
             ->where("deductCoinsHistory.receiverId",$this->input->post('userId'))
             ->where("deductCoinsHistory.deduct_history_type",'sendFrameReward')
             ->get()
             ->result_array();
             
            if(!!$getFrameReward){
            $gets['FrameReward'] = $getFrameReward;
            
            }
            else{
                $gets['FrameReward'] = '';
            }
          $gets['available'] = true;
        }

        $final[] = $gets;

      }

      echo json_encode([
        'status' => 1,
        'message' => 'list found',
        'details' => $final
      ]);exit;

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid data'
      ]);exit;
    }

  }

  public function getColorByLevel(){

    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid UserId'
        ]);exit;
      }

      $get = $this->db->get('colorPerLevel')->result_array();

      if(empty($get)){
        echo json_encode([
          'status' => 0,
          'message' => 'Empty DB'
        ]);exit;
      }

      $final = [];
      foreach($get as $gets){

        $gets['available'] = false;

        $gets['image'] = $gets['image'];

        if($gets['level'] <= $checkUser['myLevel']){
           
            $getPurchaseThemeId = $this->db->select("userPurchasedTheme.userId,userPurchasedTheme.themeId,concat('" . base_url() . "', add_themesByAdmin.theme) as theme")
             ->from("userPurchasedTheme")
             ->join("add_themesByAdmin","add_themesByAdmin.id = userPurchasedTheme.themeId","left")
             ->where("userPurchasedTheme.userId",$this->input->post('userId'))
             ->get()
             ->result_array();
             
            if(!!$getPurchaseThemeId){
            $gets['Themes'] = $getPurchaseThemeId;
            
            }
            else{
                $gets['Themes'] = '';
            }
            
            $getThemeReward = $this->db->select("deductCoinsHistory.receiverId userId,deductCoinsHistory.themeId,add_themesByAdmin.id,concat('" . base_url() . "', add_themesByAdmin.theme) as theme")
             ->from("deductCoinsHistory")
             ->join("add_themesByAdmin","add_themesByAdmin.id = deductCoinsHistory.themeId","left")
             ->where("deductCoinsHistory.receiverId",$this->input->post('userId'))
             ->where("deductCoinsHistory.deduct_history_type",'sendThemeReward')
             ->get()
             ->result_array();
             
             if(!!$getThemeReward){
            $gets['themeReward'] = $getThemeReward;
            
            }
            else{
                $gets['themeReward'] = '';
            }
             
          $gets['available'] = true;
        }

        $final[] = $gets;

      }

      echo json_encode([
        'status' => 1,
        'message' => 'list found',
        'details' => $final
      ]);exit;

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid data'
      ]);exit;
    }

  }

  public function buyVip(){
    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'Invalid userId'
        ]);exit;
      }

      $checkVip = $this->db->get_where('vip', ['id' => $this->input->post('vipId')])->row_array();

      if(empty($checkVip)){
        echo json_encode([
          'status' => 0,
          'message' => 'Invalid vip Id'
        ]);exit;
      }

      $checkWallet = $this->db->get_where("userWallet",['userId' => $this->input->post('userId')])->row_array();

      if(empty($checkWallet)){
  
        echo json_encode([
  
          "status" => "0",
          "message" => "user wallet not exist!"
        ]);exit;
      }

      // checkBalance

      if($checkWallet['wallet_amount'] < $checkVip['coins']){
        echo json_encode([
          'status' => 0,
          'message' => 'Insufficient funds'
        ]);exit;
      }

      $checkWallet['wallet_amount'] -= $checkVip['coins'];
      $dataa['wallet_amount'] = $checkWallet['wallet_amount'];
      
      $expDate = strtotime("+".$checkVip['valid']." day");
      $data['vipLevel'] = $this->input->post('vipId');
      $data['vipFrom'] = date('Y-m-d');
      $data['vipTo'] = date('Y-m-d', $expDate);

      $buyVip['vipLevel'] = $this->input->post('vipId');
      $buyVip['wallet_amount'] = $checkWallet['wallet_amount'];
      $buyVip['userId'] = $this->input->post('userId');
      $buyVip['vipFrom'] = date('Y-m-d');
      $buyVip['vipTo'] = date('Y-m-d', $expDate);
      
      $this->db->insert("userBuyVip",$buyVip);

      $buyViphistory['vipLevel'] = $this->input->post('vipId');
      $buyViphistory['wallet_amount'] = $checkWallet['wallet_amount'];
      $buyViphistory['senderId'] = $this->input->post('userId');
      $buyViphistory['deduct_history_type'] = 'buyVip';
      $buyViphistory['created'] = date("Y-m-d H:i:s");

      $this->db->insert("deductCoinsHistory",$buyViphistory);// purchased vip History.

      if($this->db->set($data)->where('id', $this->input->post('userId'))->update('users') && $this->db->set($dataa)->where('userId', $this->input->post('userId'))->update('userWallet')){
        $userInfo = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
        echo json_encode([
          'status' => 1,
          'message' => 'vip purchased successfully',
          'details' => $userInfo
        ]);exit;
      }else{
        echo json_encode([
          'status' => 0,
          'message' => 'tech error'
        ]);exit;
      }



    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter Valid Data'
      ]);exit;
    }
  }

  public function getVip(){
    $get = $this->db->get('vip')->result_array();

    if(!empty($get)){
      echo json_encode([
        'status' => 1,
        'message' => 'details found',
        'details' => $get
      ]);exit;
    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'no details found'
      ]);exit;
    }
  }

  public function getTopSender(){

    $checkTopSender = $this->db->select_sum('diamond')
                               ->select('senderId')
                               ->group_by('senderId')
                               ->order_by('diamond', 'desc')
                               ->limit(3)
                               ->get('received_gift_coin')->result_array();

                              $i = 1;
                              foreach($checkTopSender as $check){
                                $this->db->set(['userId' => $check['senderId'], 'coins' => $check['diamond']])->where('id', $i)->update('topSender');
                                $i++;
                              }

                              $getTopSenders = $this->db->get('topSender')->result_array();

                              echo json_encode([
                                'status' => 1,
                                'message' => 'List Found',
                                'details' => $getTopSenders
                              ]);exit;

  }

  public function getTopReciever(){
    $checkTopReciever = $this->db->select_sum('diamond')
                                ->select('receiverId')
                                ->group_by('receiverId')
                                ->order_by('diamond', 'desc')
                                ->limit(3)
                                ->get('received_gift_coin')->result_array();

                              $i = 1;
                              foreach($checkTopReciever as $check){
                                $this->db->set(['userId' => $check['receiverId'], 'coins' => $check['diamond']])->where('id', $i)->update('topReciever');
                                $i++;
                              }

                              $getTopReceiver = $this->db->get('topReciever')->result_array();

                              echo json_encode([
                                'status' => 1,
                                'message' => 'List Found',
                                'details' => $getTopReceiver
                              ]);exit;

  }

  public function getBadges(){
    $getParentBadge = $this->db->get('parentBadge')->result_array();

    $final = [];
    foreach($getParentBadge as $badge){

      $badge['child'] = $this->db->get_where('childBadge', ['parentId' => $badge['id']])->result_array();

      $final[] = $badge;

    }

      echo json_encode([
        'status' => 1,
        'message' => 'details found',
        'details' => $final
      ]);exit;
   }

   public function createFamily(){

    if($this->input->post()){

      $checkEligible = $this->db->get('users')->row_array();

      if(empty($checkEligible)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);exit;
      }

      if($checkEligible['myLevel'] < 200){
        echo json_encode([
          'status' => 0,
          'message' => 'user not eligible'
        ]);exit;
      }

      $checkFamily = $this->db->get_where('families', ['leaderId' => $this->input->post('userId')])->row_array();

      if(!!$checkFamily){
        echo json_encode([
          'status' => 0,
          'message' => 'user can not create more than one family'
        ]);exit;
      }

      $data['familyName'] = $this->input->post('familyName');
      $data['leaderId'] = $this->input->post('userId');
      if (!empty($_FILES['image']['name'])) {
        $name1 = time() . '_' . $_FILES["image"]["name"];
        $name = str_replace(' ', '_', $name1);
        $tmp_name = $_FILES['image']['tmp_name'];
        $path = 'uploads/adminImg/' . $name;
        move_uploaded_file($tmp_name, $path);
        $data['image'] = base_url($path);
      }
      $data['created_at'] = date('Y-m-d H:i:s');

      if($this->db->insert('families', $data)){

        $id = $this->db->insert_id();

        $this->db->set(['familyId'=>$id, 'isFamilyLeader'=>1])->where('id', $this->input->post('userId'))->update('users');

        $details = $this->db->get_where('families', ['id' => $id])->row_array();

        echo json_encode([
          'status' => 1,
          'message' => 'family created',
          'details' => $details
        ]);exit;

      }else{
        echo json_encode([
          'status' => 0,
          'message' => 'technical error'
        ]);exit;
      }

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }

   }

   public function getFamilies(){

    $get = $this->db->get('families')->result_array();

    if(!empty($get)){

      echo json_encode([
        'status' => 1,
        'message' => 'families found',
        'details' => $get
      ]);exit;

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'No families found'
      ]);exit;
    }

   }

   public function sendInvitation(){

    if($this->input->post()){

      if(!$this->input->post('userId')){
        echo json_encode([
          'status' => 0,
          'message' => 'userId required'
        ]);exit;
      }

      if(!$this->input->post('familyId')){
        echo json_encode([
          'status' => 0,
          'message' => 'familyId required'
        ]);exit;
      }

      $checkUserId = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUserId)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid users'
        ]);exit;
      }

      if($checkUserId['familyId'] != 0){
        echo json_encode([
          'status' => 0,
          'message' => 'user can not join more than one family'
        ]);exit;
      }

      $checkFamily = $this->db->get_where('families', ['id' => $this->input->post('familyId')])->row_array();

      if(empty($checkFamily)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid family id'
        ]);exit;
      }

      $checkRequest = $this->db->get_where('familyMember', ['userId' => $this->input->post('userId')])->row_array();

      if(!!$checkRequest){

        if($checkRequest['familyId'] == $this->input->post('familyId')){
          echo json_encode(['status' => 0, 'message' => 'request already sent']);exit;
        }
      }

      $data['familyId'] = $this->input->post('familyId');
      $data['userId'] = $this->input->post('userId');
      $data['type'] = 1;
      $data['status'] = 1;
      $data['date'] = date('Y-m-d H:i:s');

      if($this->db->insert('familyMember', $data)){

        echo json_encode(['status' => 1, 'message' => 'request sent']);exit;

      }else{
        echo json_encode(['status' => 0, 'message' => 'tech error']);exit;
      }


    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid data' 
      ]);exit;
    }

   }

   public function sendJoinRequest(){

    if($this->input->post()){

      if(!$this->input->post('userId')){
        echo json_encode([
          'status' => 0,
          'message' => 'userId required'
        ]);exit;
      }

      if(!$this->input->post('familyId')){
        echo json_encode([
          'status' => 0,
          'message' => 'familyId required'
        ]);exit;
      }

      $checkUserId = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUserId)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid users'
        ]);exit;
      }

      if($checkUserId['familyId'] != 0){
        echo json_encode([
          'status' => 0,
          'message' => 'request can not be sent, user already in a family'
        ]);exit;
      }

      $checkFamily = $this->db->get_where('families', ['id' => $this->input->post('familyId')])->row_array();

      if(empty($checkFamily)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid family id'
        ]);exit;
      }

      $checkRequest = $this->db->get_where('familyMember', ['userId' => $this->input->post('userId')])->row_array();

      if(!!$checkRequest){

        if($checkRequest['familyId'] == $this->input->post('familyId')){
          echo json_encode(['status' => 0, 'message' => 'request already sent']);exit;
        }
      }

      $data['familyId'] = $this->input->post('familyId');
      $data['userId'] = $this->input->post('userId');
      $data['type'] = 2;
      $data['status'] = 1;
      $data['date'] = date('Y-m-d H:i:s');

      if($this->db->insert('familyMember', $data)){

        echo json_encode(['status' => 1, 'message' => 'request sent']);exit;

      }else{
        echo json_encode(['status' => 0, 'message' => 'tech error']);exit;
      }


    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid data'
      ]);exit;
    }

   }

   

   public function responseInvitation(){

    if($this->input->post()){

      $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
      if(empty($checkuser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);exit;
      }


      $checkRequest = $this->db->get_where('familyMember', ['id' => $this->input->post('requestId'), 'userId' => $this->input->post('userId')])->row_array();

      if(empty($checkRequest)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid request Id'
        ]);exit;
      }

      if($this->input->post('status') == '2')
      {
            if($checkuser['familyId'] != 0){
                echo json_encode([
                  'status' => 0,
                  'message' => 'user can not join more than one family'
                ]);exit;
            }
            
            $dataUser['isFamilyMember'] = 1;
            $dataUser['familyId'] = $checkRequest['id'];

            $data['status'] = 2;

           $getFamily = $this->db->get_where('families', ['id' => $checkRequest['familyId']])->row_array();

           $members = $getFamily['members']; 

           $members ++;

           $familyData['members'] = $members;

            if($this->db->set($data)->where('id', $checkRequest['id'])->update('familyMember') && $this->db->set($dataUser)->where('id', $checkuser['id'])->update('users') && $this->db->set($familyData)->where('id', $checkRequest['familyId'])->update('families')){

              $getdata = $this->db->where('status', 2)
                                  ->order_by('id', 'desc')
                                  ->get('familyMember')->result_array();

              echo json_encode([
                'status' => 2,
                'message' => 'family joined',
                'details' => $getdata
              ]);exit;
            }else{
              echo json_encode([
                'status' => 0,
                'message' => 'tech error'
              ]);exit;
            }
    }else if($this->input->post('status') == '3'){

      $data['status'] = 3;

      if($this->db->set($data)->where('id', $checkRequest['id'])->update('familyMember')){

        $getdata = $this->db->where('status', 2)
                            ->order_by('id', 'desc')
                            ->get('familyMember')->result_array();

        echo json_encode([
          'status' => 3,
          'message' => 'join request rejected'
        ]);exit;
      }else{
        echo json_encode([
          'status' => 0,
          'message' => 'tech error'
        ]);exit;
      }

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'invalid status'
      ]);exit;
    }
       

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }

   }

   public function getInvitations(){
    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid users'
        ]);exit;
      }

      // $getRequest = $this->db->get_where('familyMember', ['userId' => $this->input->post('userId'), 'status' => 1])->result_array();
    //   $getRequest = $this->db->where('userId', $this->input->post('userId'))
    //                          ->where('status', 1)
    //                          ->order_by('id', 'desc')
    //                          ->get('familyMember')->result_array();
                             
    $getRequest = $this->db->select("familyMember.*,families.image,families.description,families.familyName")
    ->from("familyMember")
    ->join("families","families.id = familyMember.familyId","left")
    ->where("familyMember.userId",$this->input->post('userId'))
    ->where('familyMember.status', 1)
    ->order_by('id', 'desc')
    ->get()
    ->result_array();

      if(!!$getRequest){

        echo json_encode([
          'status' => 1,
          'message' => 'request found',
          'details' => $getRequest
        ]);exit;

      }else{
        echo json_encode([
          'status' => 0,
          'message' => 'no request found'
        ]);exit;
      }



    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid data'
      ]);exit;
    }
   }
   
   public function getJoinRequest(){
    if($this->input->post()){

      $checkUser = $this->db->get_where('families', ['leaderId' => $this->input->post('leaderId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'success' => "0",
          'message' => 'invalid leaderId'
        ]);exit;
      }

    //   // $getRequest = $this->db->get_where('familyMember', ['userId' => $this->input->post('userId'), 'status' => 1])->result_array();
    //   $getRequest = $this->db->where('userId', $this->input->post('userId'))
    //                          ->where('status', 1)
    //                          ->order_by('id', 'desc')
    //                          ->get('familyMember')->result_array();
                             
                             
     $getRequest = $this->db->select("families.leaderId,familyMember.*,users.name,users.username,users.dob,users.myCoin,users.myDiamond")
     ->from("families")
     ->join("familyMember","familyMember.familyId = families.id")
     ->join("users","users.id = familyMember.userId","left")
     ->where("families.leaderId",$this->input->post('leaderId'))
     ->where("familyMember.type",'2')
     ->where("familyMember.status",'1')
     ->get()
     ->result_array();
    
      if(!!$getRequest){
          
          $final = [];

      foreach($getRequest as $list){
        $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $list['userId'])
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $list['imageDp'] = $getImage['image'];
                               
                             }else{
                              $list['imageDp'] = "";
                                
                             }
                             
         

                             $final[] = $list;



      }
   

        echo json_encode([
          'success' => "1",
          'message' => 'request found',
          'details' => $final
        ]);exit;

      }else{
        echo json_encode([
          'success' => "0",
          'message' => 'no request found'
        ]);exit;
      }



    }else{
      echo json_encode([
        'success' => "0",
        'message' => 'enter valid data'
      ]);exit;
    }
   }

   public function responseJoinRequest(){

    if($this->input->post()){

      $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
      if(empty($checkuser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);exit;
      }


      $checkRequest = $this->db->get_where('familyMember', ['id' => $this->input->post('requestId'), 'userId' => $this->input->post('userId')])->row_array();

      if(empty($checkRequest)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid request Id'
        ]);exit;
      }

      if($this->input->post('status') == '2')
      {
            if($checkuser['familyId'] != 0){
                echo json_encode([
                  'status' => 0,
                  'message' => 'user can not join more than one family'
                ]);exit;
            }
            
            $dataUser['isFamilyMember'] = 1;
            $dataUser['familyId'] = $checkRequest['id'];

            $data['status'] = 2;

           $getFamily = $this->db->get_where('families', ['id' => $checkRequest['familyId']])->row_array();

           $members = $getFamily['members']; 

           $members ++;

           $familyData['members'] = $members;

            if($this->db->set($data)->where('id', $checkRequest['id'])->update('familyMember') && $this->db->set($dataUser)->where('id', $checkuser['id'])->update('users') && $this->db->set($familyData)->where('id', $checkRequest['familyId'])->update('families')){

              $getdata = $this->db->where('status', 2)
                                  ->order_by('id', 'desc')
                                  ->get('familyMember')->row_array();

              echo json_encode([
                'status' => 2,
                'message' => 'family joined',
                'details' => $getdata
              ]);exit;
            }else{
              echo json_encode([
                'status' => 0,
                'message' => 'tech error'
              ]);exit;
            }
    }else if($this->input->post('status') == '3'){

      $data['status'] = 3;

      if($this->db->set($data)->where('id', $checkRequest['id'])->update('familyMember')){

        $getdata = $this->db->where('status', 2)
                            ->order_by('id', 'desc')
                            ->get('familyMember')->row_array();

        echo json_encode([
          'status' => 3,
          'message' => 'join request rejected'
        ]);exit;
      }else{
        echo json_encode([
          'status' => 0,
          'message' => 'tech error'
        ]);exit;
      }

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'invalid status'
      ]);exit;
    }
       

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }

   }
   
   public function getFamiliesDetails(){
       
       $get = $this->db->get_where("families",['id' => $this->input->post('familyId')])->row_array();
       
       if(!!$get){
           
           $id = $get['id'];
         
            $getDetails = $this->db->get_where("families",['leaderId' => $this->input->post('userId'),'id' => $id])->row_array();
            
            if(!!$getDetails){
                
                $get['family_create_status'] = true;
                
            }
            else{
                 $get['family_create_status'] = false;
            }
           
           $getJoiners = $this->db->select("familyMember.id familyMemberId,familyMember.familyId,familyMember.userId,familyMember.type,familyMember.status,users.name")
           ->from("familyMember")
           ->join("users","users.id = familyMember.userId","left")
           ->where("familyMember.familyId",$id)
           ->where("familyMember.status","2")
           ->get()
           ->result_array();
           
           $final = [];

        foreach($getJoiners as $list){
            
            
        $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $list['userId'])
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $getJoiners['UserProfileImage'] = $getImage['image'];
                               
                             }else{
                              $getJoiners['UserProfileImage'] = "";
                                
                             }
                             
                             $list['UserProfileImage'] = $getJoiners['UserProfileImage'];
                             
         


                             $final[] = $list;
 
      }
           
           $get['joiner'] = $final;
            
           
           echo json_encode([
               
               "success" => "1",
               "message" => "details found",
               "details" => $get
               ]);exit;
       }
       else{
           
           echo json_encode([
               
               "success" => "0",
               "message" => "details not found!"
               ]);exit;
       }
     
       
       
   }
   
   public function getLiveJoiners(){
       
       $getDetails = $this->db->select("familyMember.familyId,familyMember.userId uId,userLive.*,users.name,users.username,users.gender,users.dob")
       ->from("familyMember")
       ->join("userLive","userLive.userId = familyMember.userId","left")
       ->join("users","users.id = userLive.userId","left")
       ->where("familyMember.familyId",$this->input->post('familyId'))
       ->where("familyMember.status",'2')
       ->where("userLive.status",'live')
       ->get()
       ->result_array();
       
    //   print_r($getDetails);
    //   die;
       
       if(!!$getDetails){
           $main = [];
           foreach($getDetails as $gets){
               
               $id = $gets['userId'];
              
               $status = $this->db->get_where("followFeed",['userId' => $this->input->post('userId'),'followingUserId' => $id])->row_array();
               
               if(!!$status){
                   
                   $gets['followStatus'] = true;
               }
               else{
                   $gets['followStatus'] = false;
               }
               
               $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $id)
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $gets['imageDp'] = $getImage['image'];
                               
                             }else{
                              $gets['imageDp'] = "";
                                
                             }
                             
        $checkFollowStatus = $this->db->get_where('kickOutLiveUser',['kickTo' => $this->input->post('kickTo'),'liveId' => $gets['id'],'kickBy' => $gets['userId']])->row_array();
        if($checkFollowStatus){
          $gets['kickOutStatus'] = TRUE;
        }else{
          $gets['kickOutStatus'] = FALSE;
        }
        
         $getDetails = $this->db->select("users.gender,users.dob")
        ->from("users")
        ->where("users.id",$this->input->post('userId'))
        ->get()
        ->row_array();
        
        if(!!$getDetails){
            
            $gets['user_gender'] = $getDetails['gender'];
            $gets['user_dob'] = $getDetails['dob'];
            
        }
        else{
            $gets['user_gender'] = "";
            $gets['user_dob'] = "";
        }
               
               $main[] = $gets;
               
           }
           
           echo json_encode([
               
               "success" => "1",
               "message" => "details found",
               "details" => $main
               ]);exit;
       }
       else{
           echo json_encode([
               
               "success" => "0",
               "message" => "details not found!"
               ]);exit;
       }
     
   }

   public function blockUnblock(){
    if($this->input->post()){

      if(!$this->input->post('blocker')){
        echo json_encode([
          'status' => 0,
          'message' => 'blocker id required',
        ]);exit;
      }

      if(!$this->input->post('blockerTo')){
        echo json_encode([
          'status' => 0,
          'message' => 'blockerTo id required'
        ]);exit;
      }

      $checkBlocker = $this->db->get_where('users', ['id' => $this->input->post('blocker')])->row_array();

      if(empty($checkBlocker)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid blockerId'
        ]);exit;
      }

      $checkBlockerTo = $this->db->get_where('users', ['id' => $this->input->post('blockerTo')])->row_array();

      if(empty($checkBlockerTo)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid blockerTo id'
        ]);exit;
      }

      $checkBlock = $this->db->get_where('blockUser', ['blocker' => $this->input->post('blocker'), 'toBlocker' => $this->input->post('blockerTo')])->row_array();

      if(empty($checkBlock)){
        $data['blocker'] = $this->input->post('blocker');
        $data['toBlocker'] = $this->input->post('blockerTo');
        $data['created'] = date('Y-m-d H:i:s');

        if($this->db->insert('blockUser', $data)){
          echo json_encode([
            'status' => 1,
            'message' => 'user blocked'
          ]);exit;
        }else{
          echo json_encode([
            'status' => 0,
            'message' => 'user not blocked'
          ]);exit;
        }
      }else{

        if($this->db->where('id', $checkBlock['id'])->delete('blockUser')){

          echo json_encode([
            'status' => 2,
            'message' => 'user unblocked'
          ]);exit;

        }else{
          echo json_encode([
            'status' => 0,
            'message' => 'tech error'
          ]);exit;
        }

      }
    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid data'
      ]);exit;
    }
   }
   
   public function getReceiverGiftHistory(){
       
       $get = $this->db->select_sum("cust.diamond")
       ->select("cust.receiverId")
       ->select("cust.senderId")
       ->select("cust.giftId")
       ->select("cust.liveId")
       ->select("sender.name sender_name")
       ->select("sender.username sender_username")
       ->select("receiver.name receiver_name")
       ->select("receiver.username receiver_username")
       ->from("received_gift_coin cust")
       ->join("users sender","sender.id = cust.receiverId")
       ->join("users receiver","receiver.id = cust.senderId")
       ->where("cust.receiverId",$this->input->post('receiverId'))
       ->where("cust.liveId",$this->input->post('liveId'))
        ->group_by("cust.liveId")
        ->group_by("cust.receiverId")
        ->group_by("cust.senderId")
        ->order_by('diamond', 'desc')
       ->get()
       ->result_array();
       
       if(!!$get){
           
           $final = [];

      foreach($get as $list){
        $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $list['senderId'])
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $list['senderImage'] = $getImage['image'];
                               
                             }else{
                              $list['senderImage'] = "";
                                
                             }
                             
        $getImagee = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $list['receiverId'])
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();
                             
                             if(!!$getImagee){
                              $list['receiverImage'] = $getImagee['image'];
                               
                             }else{
                              $list['receiverImage'] = "";
                                
                             }


                             $final[] = $list;



      }
      
      echo json_encode([
          
          "success" => "1",
          "message" => "details found",
          "details" => $final
          ]);exit;
           
       }
       else{
           
           echo json_encode([
               "success" => "0",
               "message" => "details not found!"
               ]);exit;
       }
       
       
   }
   
   public function setLiveImage(){
       
       $checkUser = $this->db->get_where("userLive",['userId' => $this->input->post('userId'),'id' => $this->input->post('liveId')])->row_array();
       
       if(empty($checkUser)){
           
           echo json_encode([
               "success" => "0",
               "message" => "Please enter valid details!"
               ]);exit;
       }
       
       if (!empty($_FILES['Liveimage']['name'])) {
        $name1 = time() . '_' . $_FILES["Liveimage"]["name"];
        $name = str_replace(' ', '_', $name1);
        $tmp_name = $_FILES['Liveimage']['tmp_name'];
        $path = 'uploads/adminImg/' . $name;
        move_uploaded_file($tmp_name, $path);
        $data['Liveimage'] = base_url($path);
      }
      $data['imageText'] = $this->input->post('imageText') ?? "";
      $data['imageTitle'] = $this->input->post('imageTitle') ?? "";
      
      $edit = $this->db->update("userLive",$data,['userId' => $this->input->post('userId'),'id' => $this->input->post('liveId')]);
      
      if($edit == true){
          
          $getdETAILS = $this->db->get_where("userLive",['userId' => $this->input->post('userId'),'id' => $this->input->post('liveId')])->row_array();
          
          echo json_encode([
               "success" => "1",
               "message" => "image set successfully",
               "details" => $getdETAILS
               ]);exit;

      }
      else{
          echo json_encode([
               "success" => "0",
               "message" => "something went wrong!"
               ]);exit;
      }
       
       
   }
   
    public function lockUserLive(){
       
       $checkUser = $this->db->get_where("userLive",['userId' => $this->input->post('userId'),'id' => $this->input->post('liveId')])->row_array();
       
       if(empty($checkUser)){
           
           echo json_encode([
               "success" => "0",
               "message" => "Please enter valid details!"
               ]);exit;
       }
       
       
      $data['password'] = $this->input->post('password') ?? "";
      
      $edit = $this->db->update("userLive",$data,['userId' => $this->input->post('userId'),'id' => $this->input->post('liveId')]);
      
      if($edit == true){
          
          $getdETAILS = $this->db->get_where("userLive",['userId' => $this->input->post('userId'),'id' => $this->input->post('liveId')])->row_array();
          
          echo json_encode([
               "success" => "1",
               "message" => "liveId locked",
               "details" => $getdETAILS
               ]);exit;

      }
      else{
          echo json_encode([
               "success" => "0",
               "message" => "something went wrong!"
               ]);exit;
      }
       
       
   }
   
   public function getImages(){
       
       $get = $this->db->get("image_from_admin")->result_array();
       
       if(!!$get){
           echo json_encode([
               "success" => "1",
               "message" => "details found",
               "details" => $get
               ]);exit;
           
       }
       else{
            echo json_encode([
               "success" => "0",
               "message" => "details not found!"
               ]);exit;
       }
   }
   
   public function giftingLiveUser(){
       $get = $this->db->select('senderId')
                       ->from('received_gift_coin')
                       ->group_by('senderId')
                       ->get()->result_array();
                       
                    //   print_r($get);
                       
                       $arr = [];
                       foreach($get as $gets){
                           $arr[] = $gets['senderId'];
                       }
                       
                       $id = implode(",", $arr);
                       
                       $where = "receiverId NOT IN ($id)";
                       $getSender = $this->db->select('receiverId')
                                             ->from('received_gift_coin')
                                             ->group_by('receiverId')
                                             ->where($where)
                                             ->get()->result_array();
                                             
                                             $send = [];
                       foreach($getSender as $sender){
                           $send[] = $sender['receiverId'];
                       }
                       
                       $ids = implode(",", $send);
                       
                       
                       $con = $id . ',' . $ids;
                       
                        $str_arr = preg_split ("/\,/", $con); 
                        // print_r($str_arr);
                        
                        $final = [];
                        foreach($str_arr as $idss){
                            $getuser = $this->db->select('ul.*')
                            ->select('usr.username,usr.dob,usr.name,usr.gender,usr.id usrId')
                                                ->from('userLive ul')
                                                ->join('users usr','usr.id = ul.userId',"left")
                                                ->where('userId', $idss)
                                                ->where('status', 'live')
                                                ->order_by('id', 'desc')
                                                ->get()->row_array();
                                                
        $getImage = $this->db->select('image')
            ->from('userImages')
            ->where('userId', $idss)
            ->order_by('id', 'desc')
            ->limit(1)
            ->get()->row_array();

            if(!!$getImage){
             $str_arr['imageDp'] = $getImage['image'];
              
            }else{
             $str_arr['imageDp'] = "";
               
            }
            
            
                                                
                                                
                                      
                                                if(!!$getuser){
                                                    
                                                    $getLiveId = $getuser['id'];
                                                    $getuserId = $getuser['userId'];
                                                    
                                                    $checkFollowStatus = $this->db->get_where('kickOutLiveUser',['kickTo' => $this->input->post('kickTo'),'liveId' => $getLiveId,'kickBy' => $getuserId])->row_array();
                                                    if($checkFollowStatus){
                                                      $getuser['kickOutStatus'] = TRUE;
                                                    }else{
                                                      $getuser['kickOutStatus'] = FALSE;
                                                    }
                                                 
                                                    $getuser['imageDp'] = $str_arr['imageDp'];
                                                    $final[] = $getuser;
                                                }
                                                // else{
                                                //     $final[] = '';
                                                // }
                        }
                        
                        if(!!$final){
                            echo json_encode([
                               "success" => "1",
                               "message" => "details found",
                               "details" => $final
                               ]);exit;
                                            
                        }
                        else{
                        echo json_encode([
                               "success" => "0",
                               "message" => "details not found!"
                               ]);exit;
                        }
                        
                        // print_r($final);
   }
   
   public function getSenderReceiverGifting(){
       
       $get = $this->db->select_sum("cust.diamond")
       ->select("cust.receiverId")
       ->select("cust.senderId")
       ->select("sender.name sender_name")
       ->select("sender.username sender_username")
       ->select("receiver.name receiver_name")
       ->select("receiver.username receiver_username")
    //   ->select("uLive.*")
       ->from("received_gift_coin cust")
       ->join("users sender","sender.id = cust.receiverId")
       ->join("users receiver","receiver.id = cust.senderId")
    //   ->join("userLive uLive","uLive.userId = cust.receiverId")
    //   ->where("uLive.status",'live')
        ->group_by("cust.receiverId")
        ->group_by("cust.senderId")
        ->order_by('diamond', 'desc')
       ->get()
       ->result_array();
       
       if(!!$get){
           
           $final = [];

      foreach($get as $list){
        $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $list['senderId'])
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $list['senderImage'] = $getImage['image'];
                               
                             }else{
                              $list['senderImage'] = "";
                                
                             }
                             
        $getImagee = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $list['receiverId'])
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();
                             
                             if(!!$getImagee){
                              $list['receiverImage'] = $getImagee['image'];
                               
                             }else{
                              $list['receiverImage'] = "";
                                
                             }


                             $final[] = $list;



      }
           
         
      
      echo json_encode([
          
          "success" => "1",
          "message" => "details found",
          "details" => $final
          ]);exit;
           
       }
       else{
           
           echo json_encode([
               "success" => "0",
               "message" => "details not found!"
               ]);exit;
       }
   }
   
   public function getNewLiveUsers(){

									  
			$dateLimit = date("Y-m-d", strtotime("-1 week"));
		 

		    
						$weekly = $this->db->select('userLive.*,users.username,users.name')
									  ->from('userLive')
									  ->join("users","users.id = userLive.userId","left")
									  ->where('created >=', $dateLimit)
									   ->where('userLive.status', 'live')
									  ->get()->result_array();
							 

			if(!!$weekly){

				$final = [];

      foreach($weekly as $list){
        $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $list['userId'])
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $list['imageDp'] = $getImage['image'];
                               
                             }else{
                              $list['imageDp'] = "";
                                
                             }
                             
         


                             $final[] = $list;



      }


				echo json_encode([
					'success' => '1',
					'message' => 'details found',
					'details' => $final
				]);exit;

			}else{
				echo json_encode([
					'success' => '0',
					'message' => 'details not found!'
				]);exit;
			}
 
	 

	  }
	  
	  public function getLiveUser(){
	      
	      $get = $this->db->select("users.id ids,users.country,users.name,users.username,users.dob,users.gender,userLive.*")
                	      ->from("users")
                	      ->join("userLive","userLive.userId = users.id")
                	      ->where("users.country",$this->input->post('country'))
                	      ->where("users.id !=",$this->input->post('userId'))
                	      ->get()
                	      ->result_array(); 
                	      
                	   //   print_r($get);
                	   //   die;
                	   
                	   
            if(!!$get){
                 $main = [];
                  foreach($get as $user){
                    $getFollow = $this->db->get_where('followFeed', ['userId' => $this->input->post('userId'), 'followinguserId' => $user['userId']])->row_array();
                    if(!!$getFollow){
            
                      $user['followStatus'] = true;
            
                    }else{
                      $user['followStatus'] = false;
                    }
                    
                    $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $user['userId'])
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $user['imageDp'] = $getImage['image'];
                               
                             }else{
                              $user['imageDp'] = "";
                                
                             }
                             
        $checkFollowStatus = $this->db->get_where('kickOutLiveUser',['kickTo' => $this->input->post('kickTo'),'liveId' => $user['id'],'kickBy' => $user['userId']])->row_array();
        if($checkFollowStatus){
          $user['kickOutStatus'] = TRUE;
        }else{
          $user['kickOutStatus'] = FALSE;
        }
        
         $getDetails = $this->db->select("users.gender,users.dob")
        ->from("users")
        ->where("users.id",$this->input->post('userId'))
        ->get()
        ->row_array();
        
        if(!!$getDetails){
            
            $user['user_gender'] = $getDetails['gender'];
            $user['user_dob'] = $getDetails['dob'];
            
        }
        else{
            $user['user_gender'] = "";
            $user['user_dob'] = "";
        }
            
                    $main[] = $user;
                  }
                  
                  
                  
                  echo json_encode([
					'success' => '1',
					'message' => 'details found',
					"details" => $main
				]);exit;
                
            }
            else{
                echo json_encode([
					'success' => '0',
					'message' => 'details not found!'
				]);exit;
            }
	  }
	  
	  public function hideUnHideCountry(){
	      
	      $checkUser = $this->db->get_where("users",['id' => $this->input->post('userId')])->row_array();
	      
	      if(empty($checkUser)){
	          
	          echo json_encode([
	              
	              "success" => "0",
	              "message" => "please enter valid userid"
	              ]);exit;
	      }
	      
	      $getSt = $this->db->select("users.id,users.country_showUnshow")
	      ->from("users")
	      ->where("users.id",$this->input->post('userId'))
	      ->get()
	      ->row_array();
	      
	      $getStatus = $getSt['country_showUnshow'];
	      
	      if($getStatus == "0"){
	          
	          $data['country_showUnshow'] = "1";
	          
	          $edit = $this->db->update("users",$data,['id' => $this->input->post('userId')]);
	          
	          if($edit == true){
	              
	              $getdETAILS = $this->db->select("users.id,users.country_showUnshow")
                        	      ->from("users")
                        	      ->where("users.id",$this->input->post('userId'))
                        	      ->get()
                        	      ->row_array();
	              echo json_encode([
					'success' => '1',
					'message' => 'status updated',
					'details' => $getdETAILS
				]);exit;
	              
	          }
	          else{
	            
                echo json_encode([
					'success' => '0',
					'message' => 'something went wrong!'
				]);exit;
	    
	      }
 
	  }
	  elseif($getStatus == "1"){
	          
	          $data['country_showUnshow'] = "0";
	          
	          $edit = $this->db->update("users",$data,['id' => $this->input->post('userId')]);
	          
	          if($edit == true){
	              
	              $getdETAILS = $this->db->select("users.id,users.country_showUnshow")
                        	      ->from("users")
                        	      ->where("users.id",$this->input->post('userId'))
                        	      ->get()
                        	      ->row_array();
	              echo json_encode([
					'success' => '1',
					'message' => 'status updated',
					'details' => $getdETAILS
				]);exit;
	              
	          }
	          else{
	            
                echo json_encode([
					'success' => '0',
					'message' => 'something went wrong!'
				]);exit;
	    
	      }
 
	  }
	  
	  }
	  
// 	  public function getBlockUsers(){
	      
// 	      $get = $this->db->select("blockUser.id blockUserId,blockUser.blocker userId")
// 	      ->from("blockUser")
// 	      ->where("blockUser.blocker",$this->input->post('userId'))
// 	      ->get()
// 	      ->result_array();
	      
// 	      print_r($get);
// 	      die;
// 	  }
	  
	  public function getBlockUsers(){

       $getDetails = $this->db->select("blockUser.id blockUserId,blockUser.blocker,blockUser.toBlocker,users.*")
       ->from("blockUser")
       ->join("users","users.id = blockUser.toBlocker","left")
       ->where("blockUser.blocker",$this->input->post('blocker'))
       ->get()
       ->result_array();

       if(!!$getDetails){
           
           $final = [];

      foreach($getDetails as $list){
        $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $list['id'])
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $list['imageDp'] = $getImage['image'];
                               
                             }else{
                              $list['imageDp'] = "";
                                
                             }
                             
         

                             $final[] = $list;



      }



           echo json_encode([

               "success" => "1",
               "message" => "details found",
               "details" => $final
               ]);exit;
       }
       else{
            echo json_encode([
            'success' => "0",
            'message' => 'details not found!'
          ]);exit;

       }
   }
   
    
   
   public function getAllEvents(){
       
       $userid = $this->input->post('userId') ?? "";
       
       $get = $this->db->select("Events.*,users.name,users.username,users.dob")
       ->from("Events")
       ->join("users","users.id = Events.eventCreaterId","left")
       ->order_by('event_startTime', 'asc')
       ->get()
       ->result_array();
       
       if(!!$get){
           
           $final = [];

      foreach($get as $list){
        $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $list['eventCreaterId'])
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $list['imageDp'] = $getImage['image'];
                               
                             }else{
                              $list['imageDp'] = "";
                                
                             }
                             
         

                             $final[] = $list;



      }
           echo json_encode([
               
               "status" => "1",
               "message" => "details found",
               "details" => $final
               ]);
           exit;
       }
       else{
           echo json_encode([
               
               "status" => "0",
               "message" => "details not found!",
               ]);
           exit;
           
       }
       
      
   }
   
   public function createEvent(){

    if($this->input->post()){

      $checkEligible = $this->db->get('users')->row_array();

      if(empty($checkEligible)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);exit;
      }

      $checkFamily = $this->db->get_where('Events', ['eventCreaterId' => $this->input->post('userId')])->row_array();

      if(!!$checkFamily){
        echo json_encode([
          'status' => 0,
          'message' => 'user can not create more than one event'
        ]);exit;
      }
      
       $data['eventCreaterId'] = $this->input->post('userId');
       $data['event_topic'] = $this->input->post('event_topic');
       $data['description'] = $this->input->post('description');
       $data['event_startTime'] = $this->input->post('event_startTime');
       $data['event_Type'] = $this->input->post('event_Type');
       $data['created_at'] = date('Y-m-d H:i:s');
       
       if (!empty($_FILES['event_image']['name'])) {
        $name1 = time() . '_' . $_FILES["event_image"]["name"];
        $name = str_replace(' ', '_', $name1);
        $tmp_name = $_FILES['event_image']['tmp_name'];
        $path = 'uploads/adminImg/' . $name;
        move_uploaded_file($tmp_name, $path);
        $data['event_image'] = base_url($path);
      } 
      if($this->db->insert('Events', $data)){

        $id = $this->db->insert_id();

        $this->db->set(['eventId'=>$id, 'isEventCreater'=>1])->where('id', $this->input->post('userId'))->update('users');

        // $details = $this->db->get_where('Events', ['id' => $id])->row_array();
        
        $getdetails = $this->db->select("Events.*,users.name,users.username")
          ->from("Events")
          ->join("users","users.id = Events.eventCreaterId","left")
          ->where("Events.id",$id)
          ->get()
          ->row_array();
          
          $getIdd = $getdetails['eventCreaterId'];
          
          $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $getIdd)
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $getdetails['imageDp'] = $getImage['image'];
                               
                             }else{
                              $getdetails['imageDp'] = "";
                                
                             }

        echo json_encode([
          'status' => 1,
          'message' => 'Event created',
          'details' => $getdetails
        ]);exit;

      }else{
        echo json_encode([
          'status' => 0,
          'message' => 'technical error'
        ]);exit;
      }

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }

   }
   
   public function sendEventInvitation(){

    if($this->input->post()){

      if(!$this->input->post('userId')){
        echo json_encode([
          'success' => 0,
          'message' => 'userId required'
        ]);exit;
      }

      if(!$this->input->post('eventId')){
        echo json_encode([
          'success' => 0,
          'message' => 'eventId required'
        ]);exit;
      }

      $checkUserId = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUserId)){
        echo json_encode([
          'success' => 0,
          'message' => 'invalid users'
        ]);exit;
      }

      if($checkUserId['eventId'] != 0){
        echo json_encode([
          'success' => 0,
          'message' => 'user can not join more than one event'
        ]);exit;
      }

      $checkFamily = $this->db->get_where('Events', ['id' => $this->input->post('eventId')])->row_array();

      if(empty($checkFamily)){
        echo json_encode([
          'success' => 0,
          'message' => 'invalid event id'
        ]);exit;
      }

      $checkRequest = $this->db->get_where('eventJoiner', ['userId' => $this->input->post('userId')])->row_array();

      if(!!$checkRequest){

        if($checkRequest['eventId'] == $this->input->post('eventId')){
          echo json_encode(['success' => 0, 'message' => 'request already sent']);exit;
        }
      }

      $data['eventId'] = $this->input->post('eventId');
      $data['userId'] = $this->input->post('userId');
      $data['type'] = 1;
      $data['status'] = 1;
      $data['created_at'] = date('Y-m-d H:i:s');

      if($this->db->insert('eventJoiner', $data)){

        echo json_encode(['success' => 1, 'message' => 'request sent']);exit;

      }else{
        echo json_encode(['success' => 0, 'message' => 'tech error']);exit;
      }


    }else{
      echo json_encode([
        'success' => 0,
        'message' => 'enter valid data' 
      ]);exit;
    }

   }
   
   public function getEventInvitations(){
       
       $get = $this->db->select("eventJoiner.*,users.name,users.username,users.dob,users.myCoin,users.myDiamond")
       ->from("eventJoiner")
       ->join("users","users.id = eventJoiner.userId","left")
       ->where("eventJoiner.userId",$this->input->post('userId'))
       ->where("eventJoiner.type","1")
       ->where("eventJoiner.status","1")
       ->get()
      ->result_array();
      
    //   print_r($get);
    //   die;
      
      if(!!$get){
          
          $final = [];

      foreach($get as $list){
        $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $list['userId'])
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $list['imageDp'] = $getImage['image'];
                               
                             }else{
                              $list['imageDp'] = "";
                                
                             }
                             
         

                             $final[] = $list;



      }
      
      echo json_encode([
          
          "success" => 1,
          "message" => "request found",
          "details" => $final
          ]);
      exit;
      }
      else{
           echo json_encode([
            'success' => 0,
            'message' => 'request not found!' 
          ]);exit;
      }
   }
   
   public function responseEventInvitation(){

    if($this->input->post()){

      $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
      if(empty($checkuser)){
        echo json_encode([
          'success' => 0,
          'message' => 'invalid userId'
        ]);exit;
      }


      $checkRequest = $this->db->get_where('eventJoiner', ['id' => $this->input->post('requestId'), 'userId' => $this->input->post('userId')])->row_array();

      if(empty($checkRequest)){
        echo json_encode([
          'success' => 0,
          'message' => 'invalid request Id'
        ]);exit;
      }

      if($this->input->post('status') == '2')
      {
            if($checkuser['eventId'] != 0){
                echo json_encode([
                  'success' => 0,
                  'message' => 'user can not join more than one event'
                ]);exit;
            }
            
            $dataUser['isEventSubscriber'] = 1;
            $dataUser['eventId'] = $checkRequest['id'];

            $data['status'] = 2;

           $getFamily = $this->db->get_where('Events', ['id' => $checkRequest['eventId']])->row_array();

           $members = $getFamily['eventSubscriber_counts']; 

           $members ++;

           $familyData['eventSubscriber_counts'] = $members;

            if($this->db->set($data)->where('id', $checkRequest['id'])->update('eventJoiner') && $this->db->set($dataUser)->where('id', $checkuser['id'])->update('users') && $this->db->set($familyData)->where('id', $checkRequest['eventId'])->update('Events')){

              $getdata = $this->db->where('status', 2)
                                  ->order_by('id', 'desc')
                                  ->get('eventJoiner')->row_array();

              echo json_encode([
                'success' => 1,
                'message' => 'event subscribe successfully',
                'details' => $getdata
              ]);exit;
            }else{
              echo json_encode([
                'success' => 0,
                'message' => 'tech error'
              ]);exit;
            }
    }else if($this->input->post('status') == '3'){

      $data['status'] = 3;

      if($this->db->set($data)->where('id', $checkRequest['id'])->update('eventJoiner')){

        $getdata = $this->db->where('status', 2)
                            ->order_by('id', 'desc')
                            ->get('eventJoiner')->row_array();

        echo json_encode([
          'success' => 1,
          'message' => 'join request rejected'
        ]);exit;
      }else{
        echo json_encode([
          'success' => 0,
          'message' => 'tech error'
        ]);exit;
      }

    }else{
      echo json_encode([
        'success' => 0,
        'message' => 'invalid status'
      ]);exit;
    }
       

    }else{
      echo json_encode([
        'success' => 0,
        'message' => 'Enter valid data'
      ]);exit;
    }

   }
   
   public function eventSubscriberDetails(){
       
       $getEvent = $this->db->select("Events.*,users.name,users.username,")
       ->from("Events")
       ->join("users","users.id = Events.eventCreaterId","left")
       ->where("Events.id",$this->input->post('eventId'))
       ->get()
       ->row_array();
       
       if(!!$getEvent){
           
          $getIdd = $getEvent['eventCreaterId'];
          
          $eventiD = $getEvent['id'];
          
          $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $getIdd)
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $getEvent['imageDp'] = $getImage['image'];
                               
                             }else{
                              $getEvent['imageDp'] = "";
                                
                             }
                             
          $getDetails = $this->db->select("eventJoiner.*,users.name,users.username")
                                  ->from("eventJoiner")
                                  ->join("users","users.id = eventJoiner.userId","left")
                                  ->where("eventJoiner.eventId",$eventiD)
                                  ->where("eventJoiner.type","1")
                                  ->where("eventJoiner.status","2")
                                  ->get()
                                  ->result_array();
                                  
                                  $final = [];
                    
                 foreach($getDetails as $gets){
                     
                     $id = $gets['eventId'];
                     $getIddd = $gets['userId'];
                     
                     $get = $this->db->get_where("eventJoiner",['userId' => $this->input->post('userId'),'eventId' => $id,'type' => '1','status' => '2'])->row_array();
                     
                     if(!!$get){
                         
                         $getEvent['subscriberStatus'] = true;
                     }
                     else{
                         $getEvent['subscriberStatus'] = false;
                     }
                     
                     
                     $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $getIddd)
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $getDetails['imageDp'] = $getImage['image'];
                               
                             }else{
                              $getDetails['imageDp'] = "";
                                
                             }
                             
                             $gets['imageDp'] = $getDetails['imageDp'];
                            //  $gets['subscriberStatus'] = $getEvent['subscriberStatus'];
                             
                             $final[] = $gets;
                 }                 
                             
              $getEvent['eventSubscribers'] = $final;             
                                  
          
           echo json_encode([
               "success" => 1,
               "message" => "details found",
               "details" => $getEvent
               ]);exit;
           
       }
       else{
           
           echo json_encode([
               "success" => 0,
               "message" => "details not found!"
               ]);exit;
       }
    
   }
   
   public function subscribeUnSubscribeEvent(){
       
       $checkEvent = $this->db->get_where("Events",['id' => $this->input->post('eventId')])->row_array();
       
       if(empty($checkEvent)){
           
           echo json_encode([
               
               "success" => "0",
               "message" => "Please enter valid eventId!"
               ]);exit;
       }
       
       $checkUser = $this->db->get_where("users",['id' => $this->input->post('userId')])->row_array();
       
       if(empty($checkUser)){
           
           echo json_encode([
               
               "success" => "0",
               "message" => "Please enter valid userId!"
               ]);exit;
       }
       
       $getSubscriber = $this->db->get_where("eventJoiner",['eventId' => $this->input->post('eventId'),'userId' => $this->input->post('userId'),'type' => '1','status' => '2'])->row_array();
       
       if(!!$getSubscriber){
           
           $unSubscribe = $this->db->delete("eventJoiner",['eventId' => $this->input->post('eventId'),'userId' => $this->input->post('userId'),'type' => '1','status' => '2']);
           
           if($unSubscribe == true){
               
               
               $this->db->set('eventSubscriber_counts', 'eventSubscriber_counts -1', false)->where('id', $this->input->post('eventId'))->update("Events");
               
               $data['eventId'] = '0';
               $data['isEventSubscriber'] = '0';
               $data['isEventCreater'] = '0';
               
               $this->db->update("users",$data,['id' => $this->input->post('userId')]);
               
               echo json_encode([
                   
                   "success" => "1",
                   "message" => "Event UnSubscribe",
                   "subscribe_status" => false
                   ]);exit;
               
           }
           else{
               
               echo json_encode([
                   
                   "success" => "0",
                   "message" => "something went wrong!"
                   ]);exit;
           }
           
       }
       else{
           $dataa['eventId'] = $this->input->post('eventId');
           $dataa['userId'] = $this->input->post('userId');
           $dataa['type'] = '1';
           $dataa['status'] = '2';
           $dataa['created_at'] = date("Y-m-d");
           
           $subscribeEvent = $this->db->insert("eventJoiner",$dataa);
           
           $getid = $this->db->insert_id();
           
           if($subscribeEvent == true){
               
               $this->db->set('eventSubscriber_counts', 'eventSubscriber_counts +1', false)->where('id', $this->input->post('eventId'))->update("Events");
               
               $getDetails = $this->db->get_where("eventJoiner",['id' => $getid])->row_array();
               
               $updatee['eventId'] = $getDetails['eventId'];
               $updatee['isEventSubscriber'] = '1';
               
               $this->db->update('users',$updatee,['id' => $this->input->post('userId')]);
               
               echo json_encode([
                   
                   "success" => "1",
                   "message" => "Event Subscribe succesfully",
                   "subscribe_status" => true
                   ]);exit;
           }
           else{
                echo json_encode([
                   
                   "success" => "0",
                   "message" => "something went wrong!"
                   ]);exit;
           }
           
       }
       
       
   }
   
   public function leavefamilyGroup(){
       
       $checkFamily = $this->db->get_where("familyMember",['familyId' => $this->input->post('familyId')])->row_array();
       
       if(empty($checkFamily)){
           
           echo json_encode([
               
               "success" => "0",
               "message" => "Please enter valid familyId!"
               ]);
           exit;
       }
       
       $checkjoiner = $this->db->get_where("familyMember",['userId' => $this->input->post('userId')])->row_array();
       
       if(empty($checkFamily)){
           
           echo json_encode([
               
               "success" => "0",
               "message" => "Please enter valid userId!"
               ]);
           exit;
       }
       
       $get = $this->db->get_where("familyMember",['userId' => $this->input->post('userId'),'familyId' => $this->input->post('familyId'),'status' => "2"])->row_array();
       
       if(!!$get){
           
           $unSubscribe = $this->db->delete("familyMember",['familyId' => $this->input->post('familyId'),'userId' => $this->input->post('userId'),'status' => '2']);
           
           if($unSubscribe == true){
               
               
               $this->db->set('members', 'members -1', false)->where('id', $this->input->post('familyId'))->update("families");
               
               $data['familyId'] = '0';
               $data['isFamilyLeader'] = '0';
               $data['isFamilyMember'] = '0';
               
               $this->db->update("users",$data,['id' => $this->input->post('userId')]);
               
               echo json_encode([
                   
                   "success" => "1",
                   "message" => "Joiner removed",
                   
                   ]);exit;
               
           }
           else{
               
               echo json_encode([
                   
                   "success" => "0",
                   "message" => "something went wrong!"
                   ]);exit;
           }
           
           
       }else{
           
           echo json_encode([
               
               "success" => "0",
               "message" => "something went wrong!"
               ]);exit;
       }
       
       
   }
   
   public function getTopGifter(){
		if($this->input->post()){

		// 1 for daily 
		// 2 for weekly 
		// 3 for monthly 

		if($this->input->post('type') == '1'){

			$getLeader = $this->db->select('leaderId')
	                            ->get('families')->result_array();
	                            
	                            
	                           // 
	                           
	                           $reciever = [];
	                           foreach($getLeader as $leader){
	                               $getDiamonds = $this->db->select_sum('diamond')
	                               ->select('receiverId')
									  ->from('received_gift_coin')
								 	  ->where('received_gift_coin.receiverId', $leader['leaderId'])
									  ->where('received_gift_coin.created', date('Y-m-d'))
									  ->group_by('receiverId')
									  ->order_by('received_gift_coin.diamond', 'asc')
									  ->get()
									  ->row_array();
									  
									  if(empty($getDiamonds)){
									      $getDiamonds['diamond'] = '0';
									      $getDiamonds['receiverId'] = $leader['leaderId'];
									     
									  }
									  
									  $reciever[] = $getDiamonds;
                                    
	                           }
	                           
	                           rsort($reciever);
	                           
	                           $last = [];
	                           
	                           $final = [];
	                           foreach($reciever as $recieve){
	                               $getUserInfo = $this->db->get_where('families', ['leaderId' => $recieve['receiverId']])->row_array();
	                               
	                               $last['userInfo'] = $getUserInfo;
	                               $last['userInfo']['total'] = $recieve['diamond'];
	                               
	                               $final[] = $last['userInfo'];
	                           }
	                           
	                           echo json_encode([
	                               
	                               'status' => "1",
	                               'message' => 'daily details found',
	                               'details' => $final
	                               
	                               ]);

		}else if($this->input->post('type') == '2'){

			$dateLimit = date("Y-m-d", strtotime("-1 week"));

			$getLeader = $this->db->select('leaderId')
	                            ->get('families')->result_array();
	                            
	                            
	                           // 
	                           
	                           $reciever = [];
	                           foreach($getLeader as $leader){
	                               $getDiamonds = $this->db->select_sum('diamond')
	                               ->select('receiverId')
									  ->from('received_gift_coin')
								 	  ->where('received_gift_coin.receiverId', $leader['leaderId'])
								// 	  ->where('received_gift_coin.created', date('Y-m-d'))
									  ->where('received_gift_coin.created >=', $dateLimit)
									  ->group_by('receiverId')
									  ->order_by('received_gift_coin.diamond', 'asc')
									  ->get()
									  ->row_array();
									  
									  if(empty($getDiamonds)){
									      $getDiamonds['diamond'] = '0';
									      $getDiamonds['receiverId'] = $leader['leaderId'];
									     
									  }
									  
									  $reciever[] = $getDiamonds;
                                    
	                           }
	                           
	                           rsort($reciever);
	                           
	                           $last = [];
	                           
	                           $final = [];
	                           foreach($reciever as $recieve){
	                               $getUserInfo = $this->db->get_where('families', ['leaderId' => $recieve['receiverId']])->row_array();
	                               
	                               $last['userInfo'] = $getUserInfo;
	                               $last['userInfo']['total'] = $recieve['diamond'];
	                               
	                               $final[] = $last['userInfo'];
	                           }
	                           
	                           echo json_encode([
	                               
	                               'status' => "1",
	                               'message' => 'weekly details found',
	                               'details' => $final
	                               
	                               ]);


		}else if($this->input->post('type') == '3'){

			$dateLimit = date("Y-m-d", strtotime("-1 month"));

			$getLeader = $this->db->select('leaderId')
	                            ->get('families')->result_array();
	                            
	                            
	                           // 
	                           
	                           $reciever = [];
	                           foreach($getLeader as $leader){
	                               $getDiamonds = $this->db->select_sum('diamond')
	                               ->select('receiverId')
									  ->from('received_gift_coin')
								 	  ->where('received_gift_coin.receiverId', $leader['leaderId'])
								// 	  ->where('received_gift_coin.created', date('Y-m-d'))
									  ->where('received_gift_coin.created >=', $dateLimit)
									  ->group_by('receiverId')
									  ->order_by('received_gift_coin.diamond', 'asc')
									  ->get()
									  ->row_array();
									  
									  if(empty($getDiamonds)){
									      $getDiamonds['diamond'] = '0';
									      $getDiamonds['receiverId'] = $leader['leaderId'];
									     
									  }
									  
									  $reciever[] = $getDiamonds;
                                    
	                           }
	                           
	                           rsort($reciever);
	                           
	                           $last = [];
	                           
	                           $final = [];
	                           foreach($reciever as $recieve){
	                               $getUserInfo = $this->db->get_where('families', ['leaderId' => $recieve['receiverId']])->row_array();
	                               
	                               $last['userInfo'] = $getUserInfo;
	                               $last['userInfo']['total'] = $recieve['diamond'];
	                               
	                               $final[] = $last['userInfo'];
	                           }
	                           
	                           echo json_encode([
	                               
	                               'status' => "1",
	                               'message' => 'monthly details found',
	                               'details' => $final
	                               
	                               ]);

		}else{
			echo json_encode([
				'status' => '0',
				'message' => 'Enter valid type'
			]);exit;
		}

		}else{
			echo json_encode([
				'status' => '0',
				'message' => 'Enter Valid Data!'
			]);
		}
	  }
	  
	  
	  public function getttttt(){
	      
	      if($this->input->post('type') == '1'){
	          
	          $getFamily = $this->db->get("families")->result_array();
	          


			if(!!$getFamily){
			    
			    $final = [];
			    foreach($getFamily as $get){
			        
			        	          $getUserByDate = $this->db->select_sum('diamond')
									  ->from('received_gift_coin')
								 	  ->where('received_gift_coin.receiverId', $get['leaderId'])
									  ->where('received_gift_coin.created', date('Y-m-d'))
									  ->group_by('receiverId')
									  ->order_by('received_gift_coin.diamond', 'asc')
									  ->get()
									  ->row_array();
			        
			        
				
									  if(empty($getUserByDate)){
									      
									      $getUserByDate['diamond'] = '0';
									  }
									  
								// 	  print_r($getUserByDate);
								 
									  
                      $get['total'] = $getUserByDate['diamond'];
                      
                      
			          $final[] = $get;
			    }
			 //   die;
			    
				echo json_encode([
					'status' => '1',
					'message' => 'Giftings Found for Today',
					'details' => $final
				]);exit;

			}else{
				echo json_encode([
					'status' => '0',
					'message' => 'No gifting done Today'
				]);exit;
			}

		}
	      
	      
	  }
	  
	  public function sdasd(){
	      
	      $getLeader = $this->db->select('leaderId')
	                            ->get('families')->result_array();
	                            
	                            
	                           // 
	                           
	                           $reciever = [];
	                           foreach($getLeader as $leader){
	                               $getDiamonds = $this->db->select_sum('diamond')
	                               ->select('receiverId')
									  ->from('received_gift_coin')
								 	  ->where('received_gift_coin.receiverId', $leader['leaderId'])
									  ->where('received_gift_coin.created', date('Y-m-d'))
									  ->group_by('receiverId')
									  ->order_by('received_gift_coin.diamond', 'asc')
									  ->get()
									  ->row_array();
									  
									  if(empty($getDiamonds)){
									      $getDiamonds['diamond'] = '0';
									      $getDiamonds['receiverId'] = $leader['leaderId'];
									     
									  }
									  
									  $reciever[] = $getDiamonds;
                                    
	                           }
	                           
	                           rsort($reciever);
	                           
	                           $last = [];
	                           
	                           $final = [];
	                           foreach($reciever as $recieve){
	                               $getUserInfo = $this->db->get_where('families', ['leaderId' => $recieve['receiverId']])->row_array();
	                               
	                               $last['userInfo'] = $getUserInfo;
	                               $last['userInfo']['total'] = $recieve['diamond'];
	                               
	                               $final[] = $last['userInfo'];
	                           }
	                           
	                           echo json_encode([
	                               
	                               'success' => 1,
	                               'message' => 'details found',
	                               'details' => $final
	                               
	                               ]);
	                           
	                           
	  }
	  
	  
	   public function gettfsdfsdfsdtttt(){
	      
	      if($this->input->post('type') == '1'){
	          
	          $getFamily = $this->db->get("families")->result_array();

			if(!!$getFamily){
			    
			 //   $final = [];
			 //   foreach($getFamily as $get){
			        
			 //       $getUserByDate = $this->db->select_sum('diamond')
				// 					  ->from('received_gift_coin')
				// 				 	  ->where('received_gift_coin.receiverId', $get['leaderId'])
				// 					  ->where('received_gift_coin.created', date('Y-m-d'))
				// 					  ->group_by('receiverId')
				// 					  ->order_by('received_gift_coin.diamond', 'asc')
				// 					  ->get()
				// 					  ->row_array();
				
				// 					  if(empty($getUserByDate)){
									      
				// 					      $getUserByDate['diamond'] = '0';
				// 					  }
									  
				// 				// 	  print_r($getUserByDate);
								 
									  
    //                   $get['diamond'] = $getUserByDate['diamond'];
                      
                      
			 //         $final[] = $get;
			 //   }
			 //   die;
			    
			    
									  
				
			    
				echo json_encode([
					'status' => '1',
					'message' => 'Giftings Found for Today',
					'details' => $getFamily
				]);exit;

			}else{
				echo json_encode([
					'status' => '0',
					'message' => 'No gifting done Today'
				]);exit;
			}

		}
	      
	      
	  }
	  
	  public function hideUnHideLiveUser(){
	      
	      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

          if(empty($checkUser)){
            echo json_encode([
              'success' => "0",
              'message' => 'Invalid userId'
            ]);exit;
          }

          $checkWallet = $this->db->get_where("userWallet",['userId' => $this->input->post('userId')])->row_array();

          if(empty($checkWallet)){
      
            echo json_encode([
      
              "success" => "0",
              "message" => "user wallet not exist!"
            ]);exit;
          }
          
          $checkCoin = $checkWallet['wallet_amount'];
          
          if($checkCoin >= 1000){
              
              $checkLiveUser = $this->db->get_where("userLive",['userId' => $this->input->post('userId'),'status' => "live",'password !=' => ""])->row_array();
              
              if(!!$checkLiveUser){
                  
                  date_default_timezone_set('Asia/Kolkata');
                  $startTime = date("H:i:s");
	                $data = date('H:i:s',strtotime('+30 minutes',strtotime($startTime)));
                  
                  $hideStatus['live_hideUnhideStatus'] = '1';
                  $hideStatus['live_hideUnhideExpTime'] = $data;

                  $deductCoinsAsDiamonds = 1000;

                  $checkCoin -= $deductCoinsAsDiamonds;
                
                  $updateUserCoinwallet = $this->db->set(['wallet_amount' => $checkCoin])->where('userId', $this->input->post('userId'))->update('userWallet');

                  $update = $this->db->update("userLive",$hideStatus,['userId' => $this->input->post('userId'),'status' => "live",'password !=' => ""]);
                  
                  if($update == true){

                    $deductHistory['price'] = $deductCoinsAsDiamonds;
                    $deductHistory['deduct_history_type'] = 'hideUnHideLiveUser';
                    $deductHistory['senderId'] = $this->input->post('userId');
                    $deductHistory['created'] = date("Y-m-d H:i:s");

                    $this->db->insert("deductCoinsHistory",$deductHistory);
                      
                      $getData = $this->db->get_where("userLive",['userId' => $this->input->post('userId'),'status' => "live",'password !=' => ""])->row_array();
                      
                      echo json_encode([
                          
                          "success" => "1",
                          "message" => "liveUser hide successfully",
                          "details" => $getData
                          ]);exit;
                      
                  }
                  else{
                      echo json_encode([
                  
                      "success" => "0",
                      "message" => "Something went wrong!",
                      ]);exit;
                  }
              }
              else{
                  echo json_encode([
                  
                  "success" => "0",
                  "message" => "Something went wrong! - please set userLive password",
                  ]);exit;
                  
              }
      
          }
          else{
              
              echo json_encode([
                  
                  "success" => "0",
                  "message" => "Insufficient Coins!"
                  ]);exit;
          }
	  }
	  
	 public function getTopGiftReceiver(){
	      
    $get = $this->db->select_sum("cust.diamond")
                     ->select("cust.receiverId")
                     ->select("custt.*")
                     ->from("received_gift_coin cust")
                     ->join("users custt","custt.id = cust.receiverId","left")
                     ->group_by("cust.receiverId")
                     ->order_by('diamond', 'desc')
                     ->get()
                     ->result_array();
                     
                     
      if(!!$get){
          
              $final = [];

                foreach($get as $list){
                  $getImage = $this->db->select('image')
                                       ->from('userImages')
                                       ->where('userId', $list['id'])
                                        
                                       ->order_by('id', 'desc')
                                       ->limit(1)
                                       ->get()->row_array();
          
                                       if(!!$getImage){
                                        $list['imageDp'] = $getImage['image'];
                                         
                                       }else{
                                        $list['imageDp'] = "";
                                          
                                       }
                                       
                   
          
                                       $final[] = $list;



}

echo json_encode([
    
    "success" => "1",
    "message" => "details found",
    "details" => $final
    ]);exit;
          
          
      }
      else{
          
          echo json_encode([
              
              "success" => "0",
              "message" => "details not found!"
              ]);exit;
      }
                     
                 
                    
}

   

    public function getCoins(){
        
        //type => 1 for luckyBag.
        //type => 2 for superluckyBag.
        
        $type = $this->input->post('type');
        
        if($type == null){
            
            echo json_encode([
                
                "success" => "0",
                "message" => "type cannot be null!"
                ]);
            exit;
        }
        
        if($type == '1'){
            
            $get['coins'] = $this->db->get("addCoins_fromadmin")->result_array();
            $get['Quantity'] = $this->db->get("addCoinsQuantity_fromadmin")->result_array();
        
        
            echo json_encode([
        
                "success" => "1",
                "message" => "details found",
                "details" => $get ?? ""
                ]);exit;
            
        }
        elseif($type == '2'){
            
            $get['coins'] = $this->db->get("addSuperLuckyBagCoins_fromadmin")->result_array();
            $get['Quantity'] = $this->db->get("addSuperLuckyBagQuantity_fromadmin")->result_array();
        
        
            echo json_encode([
        
                "success" => "1",
                "message" => "SuperLuckyBag details found",
                "details" => $get ?? ""
                ]);exit;
        }
        else{
            echo json_encode([
        
                "success" => "0",
                "message" => "please enter valid type",
            
                ]);exit;
            
        }
        
         
    
    }
    
    public function percentage(){
        
        $dcoins = $this->input->post('coins');
        
        $found = 10/100*$dcoins;
        
        $dcoins -= $found;
        echo $found;echo '.......';
        echo $dcoins;
    }

public function deductCoins(){
    
   $type = $this->input->post('type');
        
        if($type == null){
            
            echo json_encode([
                
                "success" => "0",
                "message" => "type cannot be null!"
                ]);
            exit;
        }
        
    if($type == '1'){
        
    $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

    if(empty($checkUser)){
      echo json_encode([
        'status' => 0,
        'message' => 'Invalid userId'
      ]);exit;
    }

    $checkWallet = $this->db->get_where("userWallet",['userId' => $this->input->post('userId')])->row_array();

      if(empty($checkWallet)){
  
        echo json_encode([
  
          "status" => "0",
          "message" => "user wallet not exist!"
        ]);exit;
      }
    
    $checkCoin = $checkWallet['wallet_amount'];
    
    if($checkCoin == 0){
        
        echo json_encode([
        'status' => 0,
        'message' => 'Something went wrong! - user have no WALLET_COINS!',
      ]);exit;
        
    }
    
    if($this->input->post('coins') == null || $this->input->post('quantity') == null){
        echo json_encode([
        'status' => 0,
        'message' => 'param cannot be null!',
      ]);exit;
        
    }
    
    if($checkCoin > $this->input->post('coins')){
        
        $dcoins = $this->input->post('coins');
        $quant = $this->input->post('quantity');
        
        
        
        $data['deduct_coins'] = $this->input->post('coins');
        $data['userId'] = $this->input->post('userId');
        $data['quantity'] = $this->input->post('quantity');
        $data['deductType'] = 'luckyBagCoins';
        $data['perShare'] = $dcoins/$quant;
        $data['created'] = date("Y-m-d H:i:s");
        
        
        $upload = $this->db->insert("users_deductCoins",$data);
        
        $geiD = $this->db->insert_id();
        
        if($upload == true){
            
            $getDetails = $this->db->get_where("users_deductCoins",['id' => $geiD])->row_array();
            
            $deductCoins = $checkCoin - $data['deduct_coins'];
            
            $update['wallet_amount'] = $deductCoins;
            
            $this->db->update("userWallet",$update,['userId' => $this->input->post('userId')]);
            
            echo json_encode([
            
            "status" => 1,
            "message" => "Coins deducted",
            "details" => $getDetails
            ]);exit;

        }
        else{
            echo json_encode([
            
            "status" => 0,
            "message" => "Something went wrong!"
            ]);exit;
        }
    }
    else{
        echo json_encode([
            
            "status" => 0,
            "message" => "Insufficient Coins!"
            ]);exit;
        
    }
    }
    elseif($type == '2'){
        $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

    if(empty($checkUser)){
      echo json_encode([
        'status' => 0,
        'message' => 'Invalid userId'
      ]);exit;
    }

    $checkWallet = $this->db->get_where("userWallet",['userId' => $this->input->post('userId')])->row_array();

      if(empty($checkWallet)){
  
        echo json_encode([
  
          "status" => "0",
          "message" => "user wallet not exist!"
        ]);exit;
      }
    
    $checkCoin = $checkWallet['wallet_amount'];
    
    if($checkCoin == 0){
        
        echo json_encode([
        'status' => 0,
        'message' => 'Something went wrong! - user have no WALLET_COINS!',
      ]);exit;
        
    }
    
    if($this->input->post('coins') == null || $this->input->post('quantity') == null){
        echo json_encode([
        'status' => 0,
        'message' => 'param cannot be null!',
      ]);exit;
        
    }
    
    if($checkCoin > $this->input->post('coins')){
        
        $dcoins = $this->input->post('coins');
        $quant = $this->input->post('quantity');
        
        // 10% of coins
        
        $found = 10/100*$dcoins;
        
        $dcoins -= $found;
        
        $data['deduct_coins'] = $dcoins;
        $data['userId'] = $this->input->post('userId');
        $data['quantity'] = $this->input->post('quantity');
        $data['deductType'] = 'SuperluckyBagCoins';
        $data['perShare'] = $dcoins/$quant;
        $data['created'] = date("Y-m-d H:i:s");
        
        
        $upload = $this->db->insert("users_deductCoins",$data);
        
        $geiD = $this->db->insert_id();
        
        if($upload == true){
            
            
            $deductCoins = $checkCoin - $data['deduct_coins'];
            
            $update['wallet_amount'] = $deductCoins;
            
            $this->db->update("userWallet",$update,['userId' => $this->input->post('userId')]);
            
            $admin['adminCoins'] = $found;
            
            $this->db->update("admin",$admin,['id' => '1']);
            
            echo json_encode([
            
            "status" => 1,
            "message" => "superLuckyBagCoins deducted",
            "details" => $this->db->get_where("users_deductCoins",['id' => $geiD])->row_array()
            ]);exit;

        }
        else{
            echo json_encode([
            
            "status" => 0,
            "message" => "Something went wrong!"
            ]);exit;
        }
    }
    else{
        echo json_encode([
            
            "status" => 0,
            "message" => "Insufficient Coins!"
            ]);exit;
        
    }
        
    }
    else{
         echo json_encode([
            
            "status" => 0,
            "message" => "Please enter valid type!"
            ]);exit;
        
        
    }
}

// public function divideShare(){
//     if($this->input->post()){
        
//         $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
        
//         if(empty($checkUser)){
//             echo json_encode([
//                 'success' => '0',
//                 'message' => 'invalid userId'
//                 ]);exit;
//         }
        
//          $checkdeduct = $this->db->get_where('users_deductCoins', ['id' => $this->input->post('deductId')])->row_array();
  
//             if(empty($checkdeduct)){
//               echo json_encode([
//                 'success' => 0,
//                 'message' => 'Invalid deductId'
//               ]);exit;
//             }
            
            
//             if($checkdeduct['quantity'] == $checkdeduct['userCount']){
//                 echo json_encode([
//                     'success' => '0',
//                     'message' => 'khtm hogya',
//                     "status" => false
//                     ]);exit;
//             }
            
//             $checkEligibility = $this->db->get_where('deductUser', ['userId' => $this->input->post('userId'), 'deductId' => $this->input->post('deductId')])->row_array();
            
//             if(!!$checkEligibility){
//                 echo json_encode([
//                     'success' => '0',
//                     'message' => 'user no more eligible',
//                     "status" => true
//                     ]);exit;
//             }
            
//             $data['myDiamond'] = $checkUser['myDiamond'];
//             $data['myDiamond'] += $checkdeduct['perShare'];

//             $data['myRecievedDiamond'] = $checkUser['myRecievedDiamond'];
//             $data['myRecievedDiamond'] += $checkdeduct['perShare'];
            
//             $addDiamond = $checkdeduct['perShare'];
//             // $history['diamond'] += $addDiamond;
            
//           //   print_r($data);exit;
            
//             $deductData['userCount'] = $checkdeduct['userCount'];
//             $deductData['userCount'] ++;
            
//             $dusers['userId']= $this->input->post('userId');
//             $dusers['deductId']= $this->input->post('deductId');
//             $dusers['created']= date('Y-m-d H:i:s');
//             $history = [];
//             $history['diamond'] = $data['myRecievedDiamond'];
//             $history['receiverId']= $this->input->post('userId');
//             $history['deduct_history_type']= 'divide share';
//             $history['created']= date('Y-m-d H:i:s');
            
//             if($this->db->set($data)->where('id', $this->input->post('userId'))->update('users') && $this->db->set($deductData)->where('id', $this->input->post('deductId'))->update('users_deductCoins') && $this->db->insert('deductUser', $dusers) && $this->db->insert('deductCoinsHistory', $history)){
                
//                 echo json_encode([
//                     'success' => '1',
//                     'message' => 'coins shared'
//                     ]);exit;
                
//             }else{
//                 echo json_encide([
//                     'success' => '0',
//                     'message' => 'tech error'
//                     ]);exit;
//             }
            
//             print_r($data);exit;
            
        
//     }else{
//         echo json_encode([
//             'success' => "0",
//             'message' => 'enter valid parameters'
//             ]);exit;
//     }
// }


   public function divideShare(){
       
       //type => 1 for luckyBag.
       //type => 2 for superluckyBag.
       
       $type = $this->input->post('type');
        
        if($type == null){
            
            echo json_encode([
                
                "success" => "0",
                "message" => "type cannot be null!"
                ]);
            exit;
        }
        
    if($type == '1'){
    if($this->input->post()){
        
        $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
        
        if(empty($checkUser)){
            echo json_encode([
                'success' => '0',
                'message' => 'invalid userId'
                ]);exit;
        }
        
         $checkdeduct = $this->db->get_where('users_deductCoins', ['id' => $this->input->post('deductId')])->row_array();
  
            if(empty($checkdeduct)){
              echo json_encode([
                'success' => 0,
                'message' => 'Invalid deductId'
              ]);exit;
            }
            
            
            if($checkdeduct['quantity'] == $checkdeduct['userCount']){
                echo json_encode([
                    'success' => '0',
                    'message' => 'khtm hogya',
                    "status" => false
                    ]);exit;
            }
            
            $checkEligibility = $this->db->get_where('deductUser', ['userId' => $this->input->post('userId'), 'deductId' => $this->input->post('deductId')])->row_array();
            
            if(!!$checkEligibility){
                echo json_encode([
                    'success' => '0',
                    'message' => 'user no more eligible',
                    "status" => true
                    ]);exit;
            }
            
            $data['myDiamond'] = $checkUser['myDiamond'];
            $data['myDiamond'] += $checkdeduct['perShare'];

            $data['myRecievedDiamond'] = $checkUser['myRecievedDiamond'];
            $data['myRecievedDiamond'] += $checkdeduct['perShare'];
            
            $addDiamond = $checkdeduct['perShare'];
            // $history['diamond'] += $addDiamond;
            
          //   print_r($data);exit;
            
            $deductData['userCount'] = $checkdeduct['userCount'];
            $deductData['userCount'] ++;
            
            $dusers['userId']= $this->input->post('userId');
            $dusers['deductId']= $this->input->post('deductId');
            $dusers['deductType'] = 'luckyBagCoins';
            $dusers['created']= date('Y-m-d H:i:s');
            $history = [];
            $history['diamond'] = $data['myRecievedDiamond'];
            $history['receiverId']= $this->input->post('userId');
            $history['deductType'] = 'luckyBagCoins';
            $history['deduct_history_type']= 'divide share';
            $history['created']= date('Y-m-d H:i:s');
            
            if($this->db->set($data)->where('id', $this->input->post('userId'))->update('users') && $this->db->set($deductData)->where('id', $this->input->post('deductId'))->update('users_deductCoins') && $this->db->insert('deductUser', $dusers) && $this->db->insert('deductCoinsHistory', $history)){
                
                echo json_encode([
                    'success' => '1',
                    'message' => 'coins shared'
                    ]);exit;
                
            }else{
                echo json_encide([
                    'success' => '0',
                    'message' => 'tech error'
                    ]);exit;
            }
            
            // print_r($data);exit;
            
        
    }else{
        echo json_encode([
            'success' => "0",
            'message' => 'enter valid parameters'
            ]);exit;
    }
    }
    elseif($type == '2'){
    if($this->input->post()){
        
        $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
        
        if(empty($checkUser)){
            echo json_encode([
                'success' => '0',
                'message' => 'invalid userId'
                ]);exit;
        }
        
         $checkdeduct = $this->db->get_where('users_deductCoins', ['id' => $this->input->post('deductId')])->row_array();
  
            if(empty($checkdeduct)){
              echo json_encode([
                'success' => 0,
                'message' => 'Invalid deductId'
              ]);exit;
            }
            
            
            if($checkdeduct['quantity'] == $checkdeduct['userCount']){
                echo json_encode([
                    'success' => '0',
                    'message' => 'khtm hogya',
                    "status" => false
                    ]);exit;
            }
            
            $checkEligibility = $this->db->get_where('deductUser', ['userId' => $this->input->post('userId'), 'deductId' => $this->input->post('deductId')])->row_array();
            
            if(!!$checkEligibility){
                echo json_encode([
                    'success' => '0',
                    'message' => 'user no more eligible',
                    "status" => true
                    ]);exit;
            }
            
            $data['myDiamond'] = $checkUser['myDiamond'];
            $data['myDiamond'] += $checkdeduct['perShare'];

            $data['myRecievedDiamond'] = $checkUser['myRecievedDiamond'];
            $data['myRecievedDiamond'] += $checkdeduct['perShare'];
            
            $addDiamond = $checkdeduct['perShare'];
            // $history['diamond'] += $addDiamond;
            
          //   print_r($data);exit;
            
            $deductData['userCount'] = $checkdeduct['userCount'];
            $deductData['userCount'] ++;
            
            $dusers['userId']= $this->input->post('userId');
            $dusers['deductId']= $this->input->post('deductId');
            $dusers['deductType'] = 'SuperluckyBagCoins';
            $dusers['created']= date('Y-m-d H:i:s');
            $history = [];
            $history['diamond'] = $data['myRecievedDiamond'];
            $history['receiverId']= $this->input->post('userId');
            $history['deductType'] = 'SuperluckyBagCoins';
            $history['deduct_history_type']= 'divide share';
            $history['created']= date('Y-m-d H:i:s');
            
            if($this->db->set($data)->where('id', $this->input->post('userId'))->update('users') && $this->db->set($deductData)->where('id', $this->input->post('deductId'))->update('users_deductCoins') && $this->db->insert('deductUser', $dusers) && $this->db->insert('deductCoinsHistory', $history)){
                
                echo json_encode([
                    'success' => '1',
                    'message' => 'coins shared'
                    ]);exit;
                
            }else{
                echo json_encide([
                    'success' => '0',
                    'message' => 'tech error'
                    ]);exit;
            }
            
            // print_r($data);exit;
            
        
    }else{
        echo json_encode([
            'success' => "0",
            'message' => 'enter valid parameters'
            ]);exit;
    }
    }
    else{
        echo json_encode([
            'success' => "0",
            'message' => 'enter valid type!'
            ]);exit;
    }
}
 

/**
 * GET Themes Api.(Added by admin side from panel)
 */

public function getThemes(){

  if($this->input->post()){

    $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

    if(empty($checkUser)){
      echo json_encode([
        'status' => 0,
        'message' => 'invalid UserId'
      ]);exit;
    }

    // $get = $this->db->query("SELECT add_themesByAdmin.* FROM add_themesByAdmin order by valditity desc")->result_array();
    
    $get = $this->db->select('add_themesByAdmin.*')
    ->from("add_themesByAdmin")
    ->order_by('valditity', 'ASC')
    ->get()
    ->result_array();
    
    // echo $this->db->last_query();
    
  
    // sort($get);
    // print_r($get);
    // die;


    if(empty($get)){
      echo json_encode([
        'success' => 0,
        'message' => 'Empty DB'
      ]);exit;
    }

    $final = [];

    foreach($get as $gets){

        $getId = $gets['id'];

        $date = date('Y-m-d');

        $gets['theme'] = base_url() . $gets['theme'];


        $getStatus = $this->db->select("userPurchasedTheme.*")
        ->from("userPurchasedTheme")
        ->where("userPurchasedTheme.userId",$this->input->post('userId'))
        ->where("userPurchasedTheme.themeId",$getId)
        ->where("userPurchasedTheme.dateTo >=",$date)
        ->get()
        ->row_array();

        if(!!$getStatus){
             $getdateTo = $getStatus['dateTo'];
           
             $future = strtotime($getdateTo);
             $now = time();
             $timeleft = $future-$now;
             $daysleft = round((($timeleft/24)/60)/60); 
             
            $gets['purchasedType'] = true;
            $gets['remainingDays'] = (string)$daysleft;

        }
        else{
            $gets['purchasedType'] = false;
             $gets['remainingDays'] = '';
        }
        $final[] = $gets;
    }
  //   die;

    echo json_encode([
      'success' => 1,
      'message' => 'list found',
      'details' => $final
    ]);exit;

  }else{
    echo json_encode([
      'success' => 0,
      'message' => 'enter valid data'
    ]);exit;
  }

}

public function purchaseThemes(){

  if($this->input->post()){

    $checkUserWallet = $this->db->get_where('userWallet', ['userId' => $this->input->post('userId')])->row_array();

    if(empty($checkUserWallet)){
      echo json_encode([
        'success' => 0,
        'message' => 'wallet amount not exist for this user!'
      ]);exit;
    }

    $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

    if(empty($checkUser)){
      echo json_encode([
        'success' => 0,
        'message' => 'Invalid userId'
      ]);exit;
    }

    $checkFrame = $this->db->get_where('add_themesByAdmin', ['id' => $this->input->post('themeId')])->row_array();

    if(empty($checkFrame)){
      echo json_encode([
        'success' => 0,
        'message' => 'Invalid themeId!'
      ]);exit;
    }

    $checkThemePurchase = $this->db->get_where('userPurchasedTheme', ['userId' => $this->input->post('userId'), 'themeId' => $this->input->post('themeId')])->row_array();

    $date = date('Y-m-d');

    if(!empty($checkThemePurchase)){
      if($checkThemePurchase['dateTo'] > $date){
        echo json_encode([
          'success' => 0,
          'message' => 'theme already purchased!'
        ]);exit;
      }
    }

    $frameAmount = $checkFrame['price'];
    // $coinBalance = $checkUser['purchasedCoin'];
    $walletBal = $checkUserWallet['wallet_amount'];

    if($walletBal < $frameAmount){
      echo json_encode([
        'success' => 0,
        'message' => 'Insufficient Balance'
      ]);exit;
    }

  $walletBal -= $frameAmount;

  $expDate = strtotime("+".$checkFrame['valditity']." day");
  $dateTo = date('Y-m-d', $expDate);

   $buyFrame['userId'] = $this->input->post('userId');
   $buyFrame['themeId'] = $this->input->post('themeId');
   $buyFrame['price'] = $frameAmount;
   $buyFrame['dateFrom'] = date('Y-m-d');
   $buyFrame['dateTo'] = $dateTo;

   $insert = $this->db->insert('userPurchasedTheme', $buyFrame);

   $buyThemeHistory['senderId'] = $this->input->post('userId');
   $buyThemeHistory['themeId'] = $this->input->post('themeId');
   $buyThemeHistory['deduct_history_type'] = 'purchaseThemes';
   $buyThemeHistory['price'] = $frameAmount;

   $this->db->insert('deductCoinsHistory', $buyThemeHistory);

   $updateUserCoin = $this->db->set(['wallet_amount' => $walletBal])->where('id', $this->input->post('userId'))->update('userWallet');

   if($insert && $updateUserCoin){

    $checkUserFrames = $this->db->select('*')
                                ->from('userPurchasedTheme')
                                ->where('userId', $this->input->post('userId'))
                                ->get()->result_array();

    $final = [];
    foreach($checkUserFrames as $frames){

            if($frames['dateTo'] >= date('Y-m-d')){

              $getFrame = $this->db->get_where('add_themesByAdmin', ['id' => $frames['themeId']])->row_array();
              $frames['theme'] = base_url() . $getFrame['theme'];
              $final[] = $frames;

            }

    }

    echo json_encode([
      'success' => 1,
      'message' => 'theme Purchased',
      'details' => $final
    ]);exit;

   }else{
    echo json_encode([
      'success' => 0,
      'message' => 'Tech Error'
    ]);exit;
   }

  }else{
    echo json_encode([
      'success' => 0,
      'message' => 'Enter valid data'
    ]);exit;
  }

}

public function emoji(){
    
    $data['emoji'] = $this->input->post('emoji');
    
    $dd = $this->db->insert("emojiiii",$data);
    
    if($dd == true){
        echo json_encode([
      'success' => 1,
      'message' => 'inserted'
    ]);exit; 
        
    }
    else{
         echo json_encode([
      'success' => 0,
      'message' => 'errorrrrr'
    ]);exit;
        
    }
    
    
}

public function getVipImages(){

  $get = $this->db->get("add_VipImagesByAdmin")->result_array();

  if(!!$get){

    foreach($get as $gets){

      if(!!$gets['vip_image']){
        $gets['vip_image'] = base_url().$gets['vip_image'];

      }
      else{
        $gets['vip_image'] = "";
      }

    

      $final[] = $gets;
    }
    $message['success'] = '1';
    $message['message'] = 'details found';
    $message['details'] = $final;
  }
  else{
    $message['success'] = '0';
    $message['message'] = 'details not found!';
  }

  echo json_encode($message);


}


public function getLeaderBoard(){
    if($this->input->post()){
        
        if($this->input->post('get') == 'reciever'){
            
            $col = 'receiverId';
            
        }else if($this->input->post('get') == 'sender'){
            
            $col = 'senderId';
            
        }
        
        // $where = "";
        
        if($this->input->post('type') == '1'){
            
            // today
            $date = date('Y-m-d');
            $where = "created = '" . $date . "'";
            
        }else if($this->input->post('type') == '2'){
            
            $date = date('Y-m-d', strtotime('-1 week'));
            $dateTo = date('Y-m-d');
            $where = "created >= '" .$date. "' AND created <= '" .$dateTo. "'";
            
        }else if($this->input->post('type') == '3'){
            
            $date = date('Y-m-d', strtotime('-1 month'));
            $dateTo = date('Y-m-d');
            $where = "created >= '" .$date. "' AND created <= '" .$dateTo. "'";
            
        }else{
            echo json_encode([
                'success' => '0',
                'message' => 'inavlid type'
                ]);exit;
        }
        
        // print_r($where);exit;
        $get = $this->db->select_sum('diamond')
                        ->select($col)
                        ->from('deductCoinsHistory')
                        ->where($where)
                        ->group_by($col)
                        ->order_by('diamond', 'desc')
                        ->get()->result_array();
                        
                        // print_r($get);
                        
                        
                        if(empty($get)){
                            echo json_encode([
                                'success' => '0',
                                'message' => 'no data found'
                                ]);exit;
                        }
                        $final = [];
                        foreach($get as $gets){
                            $dat = $this->db->get_where('users', ['id' => $gets[$col]])->row_array();
                            if(empty($dat)){
                                
                            }else{
                                
                            $dat['profileImage'] = $this->db->select('image')
                                                  ->from('userImages')
                                                  ->where('userId', $gets[$col])
                                                  ->order_by('id', 'desc')
                                                  ->get()->row_array();
                            
                            $gets['userDetails'] = $dat;
                            $final[] = $gets;
                            }
                        }
                        
                        echo json_encode([
                            
                            'success' => '1',
                            'message' => 'data found',
                            'data' => $final
                            ]);exit;

        
    }else{
        echo json_encode([
            'success' => '0',
            'message' => 'enter valid parameters'
            ]);exit;
    }
}

// ============= Razorpay inmplement =================

/*
		ORDER ID GENERATE API
		
*/

    public function orderIdGenerate(){

		if($this->input->post('amount') == null){

			echo json_encode([

				"success" => "0",
				"message" => "Param cannot be null!"
			]);exit;
		}
		$api_key = 'rzp_test_usEmd5LTJQKCTA';
		$api_secret = 'TYqcPPnkIMXyFWG3zbLHpBTC';
		$api = new Api($api_key, $api_secret);
		$amount = $this->input->post('amount') * 100;
		$receipt = date('YmdHis');
		$order  = $api->order->create(array('receipt' => $receipt, 'amount' => $amount, 'currency' => 'INR')); // Creates order
		$orderId = $order['id']; // Get the created Order ID

		$message['success'] = '1';
		$message['message'] = 'Order id Generate Successfully';
		$message['orderId'] = $orderId;
        $message['key'] = $api_key;
        $message['amount'] = $this->input->post('amount') ?? '';

		echo json_encode($message);
	}
	
	// ============== Gallery Apis ================
	
	 public function getGallery(){

     if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid UserId'
        ]);exit;
      }

      $get = $this->db->get('gallary_purchasePermissionByAdmin')->result_array();

      if(empty($get)){
        echo json_encode([
          'status' => 0,
          'message' => 'Empty DB'
        ]);exit;
      }

      $final = [];
      foreach($get as $gets){
          
          $check = $this->db->get_where("userPurchaseGallery",['userId' => $this->input->post('userId'),'galleryPermissionId' => $gets['id']])->row_array();
          
          if(!!$check){
              
              $getdateTo = $check['dateTo'];
              
                $future = strtotime($getdateTo);
                $now = time();
                $timeleft = $future-$now;
                $daysleft = round((($timeleft/24)/60)/60); 
                $gets['purStatus'] = true;
                $gets['remainingDays'] = (string)$daysleft;
              
          }
          else{
              $gets['purStatus'] = false;
              $gets['remainingDays'] = '';
          }

        $final[] = $gets;

      }

      echo json_encode([
        'status' => 1,
        'message' => 'list found',
        'details' => $final
      ]);exit;

    }else{
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid data'
      ]);exit;
    }

  }
  
  public function purchaseGallery(){
      
      if($this->input->post()){
          
          $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
	
		  if(empty($checkUser)){
			echo json_encode([
			  'status' => 0,
			  'message' => 'Invalid userId'
			]);exit;
		  }
		  
		  $checkGalleryPermissionId = $this->db->get_where('gallary_purchasePermissionByAdmin', ['id' => $this->input->post('permissionId')])->row_array();
	
		  if(empty($checkGalleryPermissionId)){
			echo json_encode([
			  'status' => 0,
			  'message' => 'Invalid permissionId!'
			]);exit;
		  }
		  
		  $checkWallet = $this->db->get_where("userWallet",['userId' => $this->input->post('userId')])->row_array();
	
		  if(empty($checkWallet)){
	  
			echo json_encode([
	  
			  "status" => "0",
			  "message" => "user wallet not exist!"
			]);exit;
		  }
		  
		  $checkPurchasePermission = $this->db->get_where('userPurchaseGallery', ['userId' => $this->input->post('userId'), 'galleryPermissionId' => $this->input->post('permissionId')])->row_array();
	
		  if(!empty($checkPurchasePermission)){
	
			$date = date('Y-m-d');
			if($checkPurchasePermission['dateTo'] > $date){
			  echo json_encode([
				'status' => 0,
				'message' => 'Gallery Permission already pruchased!'
			  ]);exit;
			}
		  }
		  
    		$coinValue = $checkGalleryPermissionId['coins'];
    			
    		$walletCoinValue = $checkWallet['wallet_amount'];
					
			if($walletCoinValue < $coinValue){
					
				echo json_encode([
				
			    	"success" => "0",
					"message" => "Insufficient Balance!"
					]);exit;
			}
			
			$walletCoinValue -= $coinValue;
     
    		$expDate = strtotime("+".$checkGalleryPermissionId['validity']." day");
    		$dateTo = date('Y-m-d', $expDate);
    	
    		 $buyGallery['userId'] = $this->input->post('userId');
    		 $buyGallery['galleryPermissionId'] = $this->input->post('permissionId');
    		 $buyGallery['price'] = $coinValue;
    		 $buyGallery['dateFrom'] = date('Y-m-d');
    		 $buyGallery['dateTo'] = $dateTo;
    		 
    		 $insert = $this->db->insert('userPurchaseGallery', $buyGallery);
    		 
    		 $buyGalleryHistory['senderId'] = $this->input->post('userId');
    		 $buyGalleryHistory['galleryPermissionId'] = $this->input->post('permissionId');
    		 $buyGalleryHistory['price'] = $coinValue;
    		 $buyGalleryHistory['deduct_history_type'] = 'userPurchaseGallery';
    		 $buyGalleryHistory['created'] = date("Y-m-d H:i:s");
    		 
    		 $this->db->insert('deductCoinsHistory', $buyGalleryHistory); // Purchased buyGalleryHistory history.
    		 
    		 $updateUserCoinwallet = $this->db->set(['wallet_amount' => $walletCoinValue])->where('userId', $this->input->post('userId'))->update('userWallet');
    		 
    		 if($insert && $updateUserCoinwallet){
	
    		  $checkUserGallery = $this->db->select('*')
    									  ->from('userPurchaseGallery')
    									  ->where('userId', $this->input->post('userId'))
    									  ->get()->result_array();
    	
    		  //$final = [];
    		  //foreach($checkUserFrames as $frames){
    	
    				//   if($frames['dateTo'] >= date('Y-m-d')){
    	
    				// 	$getFrame = $this->db->get_where('addFrames_fromAdmin', ['id' => $frames['frameId']])->row_array();
    				// 	$frames['frameIMage'] = base_url() . $getFrame['frame_img'];
    				// 	$final[] = $frames;
    	
    				//   }
    	
    		  //}
    	
    		  echo json_encode([
    			'status' => 1,
    			'message' => 'Gallery Purchased',
    			'details' => $checkUserGallery
    		  ]);exit;
    	
    		 }else{
    		  echo json_encode([
    			'status' => 0,
    			'message' => 'Tech Error'
    		  ]);exit;
    		 }
          
          
      }
      else{
          echo json_encode([
			'status' => 0,
			'message' => 'Enter valid data'
		  ]);exit;
      }
          
  }
  
  public function sendGallery(){
    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);exit;
      }

      $checkOtherUser = $this->db->get_where('users', ['id' => $this->input->post('otherUserId')])->row_array();

      if(empty($checkOtherUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid otherUserId'
        ]);exit;
      }

      $checkGalleryPermissionId = $this->db->get_where('gallary_purchasePermissionByAdmin', ['id' => $this->input->post('permissionId')])->row_array();
	
		  if(empty($checkGalleryPermissionId)){
			echo json_encode([
			  'status' => 0,
			  'message' => 'Invalid permissionId!'
			]);exit;
		  }
		  
		  $checkWallet = $this->db->get_where("userWallet",['userId' => $this->input->post('userId')])->row_array();
	
		  if(empty($checkWallet)){
	  
			echo json_encode([
	  
			  "status" => "0",
			  "message" => "sender wallet not exist!"
			]);exit;
		  }

      $checkSendRewards = $this->db->get_where('sendRewards', ['userId' => $this->input->post('userId'),'otherUserId' => $this->input->post('otherUserId'), 'galleryPermissionId' => $this->input->post('permissionId')])->row_array();

      if(!empty($checkSendRewards)){
        if($checkSendRewards['dateTo'] > date('Y-m-d')){
          echo json_encode([
            'status' => 0,
            'message' => 'reward already sent!'
          ]);exit;
        }
      }
      
      $coinValue = $checkGalleryPermissionId['coins'];
    			
    		$walletCoinValue = $checkWallet['wallet_amount'];
					
			if($walletCoinValue < $coinValue){
					
				echo json_encode([
				
			    	"success" => "0",
					"message" => "Insufficient Balance!"
					]);exit;
			}
			
			$walletCoinValue -= $coinValue;
 

          	$expDate = strtotime("+".$checkGalleryPermissionId['validity']." day");
            $dateTo = date('Y-m-d', $expDate);

             $sendReward['userId'] = $this->input->post('userId');
             $sendReward['otherUserId'] = $this->input->post('otherUserId');
    		 $sendReward['galleryPermissionId'] = $this->input->post('permissionId');
    		 $sendReward['price'] = $coinValue;
    		 $sendReward['rewardType'] = 'sendGalleryReward';
    		 $sendReward['dateFrom'] = date('Y-m-d');
    		 $sendReward['dateTo'] = $dateTo;
 
             $insert = $this->db->insert('sendRewards', $sendReward);
          
             $sendGalleryRewardHistory['senderId'] = $this->input->post('userId');
             $sendGalleryRewardHistory['receiverId'] = $this->input->post('otherUserId');
    		 $sendGalleryRewardHistory['galleryPermissionId'] = $this->input->post('permissionId');
    		 $sendGalleryRewardHistory['price'] = $coinValue;
    		 $sendGalleryRewardHistory['deduct_history_type'] = 'sendGalleryReward';
    		 $sendGalleryRewardHistory['created'] = date("Y-m-d H:i:s");
    		 
    		 $this->db->insert('deductCoinsHistory', $sendGalleryRewardHistory); // send sendGalleryReward history.
    		 
    		 $updateUserCoinwallet = $this->db->set(['wallet_amount' => $walletCoinValue])->where('userId', $this->input->post('userId'))->update('userWallet');
    		 
 
             if($insert && $updateUserCoinwallet){
        	
    		  $checkUserGallery = $this->db->select('*')
    									  ->from('sendRewards')
    									  ->where('userId', $this->input->post('userId'))
    									  ->get()->result_array();
    	
    		  //$final = [];
    		  //foreach($checkUserFrames as $frames){
    	
    				//   if($frames['dateTo'] >= date('Y-m-d')){
    	
    				// 	$getFrame = $this->db->get_where('addFrames_fromAdmin', ['id' => $frames['frameId']])->row_array();
    				// 	$frames['frameIMage'] = base_url() . $getFrame['frame_img'];
    				// 	$final[] = $frames;
    	
    				//   }
    	
    		  //}
    	
    		  echo json_encode([
    			'status' => 1,
    			'message' => 'Reward send successfully',
    			'details' => $checkUserGallery
    		  ]);exit;
    	
    		 }else{
    		  echo json_encode([
    			'status' => 0,
    			'message' => 'Tech Error'
    		  ]);exit;
    		 }

        }else{
          echo json_encode([
            'status' => 0,
            'message' => 'Enter valid data'
          ]);exit;
        }
      }
      
      //================= END Gallery's Apis ======================
      
      
 public function sendLuckyId(){
    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);exit;
      }

      $checkOtherUser = $this->db->get_where('users', ['id' => $this->input->post('otherUserId')])->row_array();

      if(empty($checkOtherUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid otherUserId'
        ]);exit;
      }

      $checkLuckyId = $this->db->get_where('Ep_luckyId', ['id' => $this->input->post('luckyId')])->row_array();

      if(empty($checkLuckyId)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid luckyId'
        ]);exit;
      }
		  
		  $checkWallet = $this->db->get_where("userWallet",['userId' => $this->input->post('userId')])->row_array();
	
		  if(empty($checkWallet)){
	  
			echo json_encode([
	  
			  "status" => "0",
			  "message" => "sender wallet not exist!"
			]);exit;
		  }

      $checkSendRewards = $this->db->get_where('sendRewards', ['userId' => $this->input->post('userId'),'otherUserId' => $this->input->post('otherUserId'), 'luckyId' => $this->input->post('luckyId')])->row_array();

      if(!empty($checkSendRewards)){
        if($checkSendRewards['dateTo'] > date('Y-m-d')){
          echo json_encode([
            'status' => 0,
            'message' => 'reward already sent!'
          ]);exit;
        }
      }
      
      $coinValue = $checkLuckyId['price'];
    			
    		$walletCoinValue = $checkWallet['wallet_amount'];
					
			if($walletCoinValue < $coinValue){
					
				echo json_encode([
				
			    	"success" => "0",
					"message" => "Insufficient Balance!"
					]);exit;
			}
			
			$walletCoinValue -= $coinValue;
 

          	$expDate = strtotime("+".$checkLuckyId['validity']." day");
            $dateTo = date('Y-m-d', $expDate);

             $sendReward['userId'] = $this->input->post('userId');
             $sendReward['otherUserId'] = $this->input->post('otherUserId');
    		 $sendReward['luckyId'] = $this->input->post('luckyId');
    		 $sendReward['price'] = $coinValue;
    		 $sendReward['rewardType'] = 'sendLuckyIdReward';
    		 $sendReward['dateFrom'] = date('Y-m-d');
    		 $sendReward['dateTo'] = $dateTo;
 
             $insert = $this->db->insert('sendRewards', $sendReward);
          
             $sendGalleryRewardHistory['senderId'] = $this->input->post('userId');
             $sendGalleryRewardHistory['receiverId'] = $this->input->post('otherUserId');
    		 $sendGalleryRewardHistory['luckyId'] = $this->input->post('luckyId');
    		 $sendGalleryRewardHistory['price'] = $coinValue;
    		 $sendGalleryRewardHistory['deduct_history_type'] = 'sendLuckyIdReward';
    		 $sendGalleryRewardHistory['created'] = date("Y-m-d H:i:s");
    		 
    		 $this->db->insert('deductCoinsHistory', $sendGalleryRewardHistory); // send sendGalleryReward history.
    		 
    		 $updateUserCoinwallet = $this->db->set(['wallet_amount' => $walletCoinValue])->where('userId', $this->input->post('userId'))->update('userWallet');
    		 
 
             if($insert && $updateUserCoinwallet){
        	
    		  $checkUserGallery = $this->db->select('sendRewards.id,sendRewards.otherUserId,sendRewards.luckyId,sendRewards.price,sendRewards.rewardType,sendRewards.dateFrom,sendRewards.dateTo')
    									  ->from('sendRewards')
    									  ->where('userId', $this->input->post('userId'))
    									  ->where('sendRewards.rewardType','sendLuckyIdReward')
    									  ->get()->result_array();
    	
    		  //$final = [];
    		  //foreach($checkUserFrames as $frames){
    	
    				//   if($frames['dateTo'] >= date('Y-m-d')){
    	
    				// 	$getFrame = $this->db->get_where('addFrames_fromAdmin', ['id' => $frames['frameId']])->row_array();
    				// 	$frames['frameIMage'] = base_url() . $getFrame['frame_img'];
    				// 	$final[] = $frames;
    	
    				//   }
    	
    		  //}
    	
    		  echo json_encode([
    			'status' => 1,
    			'message' => 'Reward send successfully',
    			'details' => $checkUserGallery
    		  ]);exit;
    	
    		 }else{
    		  echo json_encode([
    			'status' => 0,
    			'message' => 'Tech Error'
    		  ]);exit;
    		 }

        }else{
          echo json_encode([
            'status' => 0,
            'message' => 'Enter valid data'
          ]);exit;
        }
      }
      
      // ================== SEND THEMES API=====================
      
           
 public function sendThemes(){
    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);exit;
      }

      $checkOtherUser = $this->db->get_where('users', ['id' => $this->input->post('otherUserId')])->row_array();

      if(empty($checkOtherUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid otherUserId'
        ]);exit;
      }

       $checkFrame = $this->db->get_where('add_themesByAdmin', ['id' => $this->input->post('themeId')])->row_array();

        if(empty($checkFrame)){
          echo json_encode([
            'success' => 0,
            'message' => 'Invalid themeId!'
          ]);exit;
        }
		  
		  $checkWallet = $this->db->get_where("userWallet",['userId' => $this->input->post('userId')])->row_array();
	
		  if(empty($checkWallet)){
	  
			echo json_encode([
	  
			  "status" => "0",
			  "message" => "sender wallet not exist!"
			]);exit;
		  }

      $checkSendRewards = $this->db->get_where('sendRewards', ['userId' => $this->input->post('userId'),'otherUserId' => $this->input->post('otherUserId'), 'themeId' => $this->input->post('themeId')])->row_array();

      if(!empty($checkSendRewards)){
        if($checkSendRewards['dateTo'] > date('Y-m-d')){
          echo json_encode([
            'status' => 0,
            'message' => 'reward already sent!'
          ]);exit;
        }
      }
      
      $coinValue = $checkFrame['price'];
    			
    		$walletCoinValue = $checkWallet['wallet_amount'];
					
			if($walletCoinValue < $coinValue){
					
				echo json_encode([
				
			    	"success" => "0",
					"message" => "Insufficient Balance!"
					]);exit;
			}
			
			$walletCoinValue -= $coinValue;
 

          	$expDate = strtotime("+".$checkFrame['valditity']." day");
            $dateTo = date('Y-m-d', $expDate);

             $sendReward['userId'] = $this->input->post('userId');
             $sendReward['otherUserId'] = $this->input->post('otherUserId');
    		 $sendReward['themeId'] = $this->input->post('themeId');
    		 $sendReward['price'] = $coinValue;
    		 $sendReward['rewardType'] = 'sendThemeReward';
    		 $sendReward['dateFrom'] = date('Y-m-d');
    		 $sendReward['dateTo'] = $dateTo;
 
             $insert = $this->db->insert('sendRewards', $sendReward);
          
             $sendGalleryRewardHistory['senderId'] = $this->input->post('userId');
             $sendGalleryRewardHistory['receiverId'] = $this->input->post('otherUserId');
    		 $sendGalleryRewardHistory['themeId'] = $this->input->post('themeId');
    		 $sendGalleryRewardHistory['price'] = $coinValue;
    		 $sendGalleryRewardHistory['deduct_history_type'] = 'sendThemeReward';
    		 $sendGalleryRewardHistory['created'] = date("Y-m-d H:i:s");
    		 
    		 $this->db->insert('deductCoinsHistory', $sendGalleryRewardHistory); // send sendGalleryReward history.
    		 
    		 $updateUserCoinwallet = $this->db->set(['wallet_amount' => $walletCoinValue])->where('userId', $this->input->post('userId'))->update('userWallet');
    		 
 
             if($insert && $updateUserCoinwallet){
        	
    		  $checkUserGallery = $this->db->select('sendRewards.id,sendRewards.otherUserId,sendRewards.themeId,sendRewards.price,sendRewards.rewardType,sendRewards.dateFrom,sendRewards.dateTo')
    									  ->from('sendRewards')
    									  ->where('userId', $this->input->post('userId'))
    									  ->where('sendRewards.rewardType','sendThemeReward')
    									  ->get()->result_array();
    	
    		  //$final = [];
    		  //foreach($checkUserFrames as $frames){
    	
    				//   if($frames['dateTo'] >= date('Y-m-d')){
    	
    				// 	$getFrame = $this->db->get_where('addFrames_fromAdmin', ['id' => $frames['frameId']])->row_array();
    				// 	$frames['frameIMage'] = base_url() . $getFrame['frame_img'];
    				// 	$final[] = $frames;
    	
    				//   }
    	
    		  //}
    	
    		  echo json_encode([
    			'status' => 1,
    			'message' => 'Reward send successfully',
    			'details' => $checkUserGallery
    		  ]);exit;
    	
    		 }else{
    		  echo json_encode([
    			'status' => 0,
    			'message' => 'Tech Error'
    		  ]);exit;
    		 }

        }else{
          echo json_encode([
            'status' => 0,
            'message' => 'Enter valid data'
          ]);exit;
        }
      }
      
      // ============= send frame Api =================
      
      
    public function sendFrames(){
    if($this->input->post()){

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if(empty($checkUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);exit;
      }

      $checkOtherUser = $this->db->get_where('users', ['id' => $this->input->post('otherUserId')])->row_array();

      if(empty($checkOtherUser)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid otherUserId'
        ]);exit;
      }

       $checkFrame = $this->db->get_where('addFrames_fromAdmin', ['id' => $this->input->post('frameId')])->row_array();

          if(empty($checkFrame)){
            echo json_encode([
              'status' => 0,
              'message' => 'invalid frameId'
            ]);exit;
          }
		  
		  $checkWallet = $this->db->get_where("userWallet",['userId' => $this->input->post('userId')])->row_array();
	
		  if(empty($checkWallet)){
	  
			echo json_encode([
	  
			  "status" => "0",
			  "message" => "sender wallet not exist!"
			]);exit;
		  }

      $checkSendRewards = $this->db->get_where('sendRewards', ['userId' => $this->input->post('userId'),'otherUserId' => $this->input->post('otherUserId'), 'frameId' => $this->input->post('frameId')])->row_array();

      if(!empty($checkSendRewards)){
        if($checkSendRewards['dateTo'] > date('Y-m-d')){
          echo json_encode([
            'status' => 0,
            'message' => 'reward already sent!'
          ]);exit;
        }
      }
      
      $coinValue = $checkFrame['price'];
    			
    		$walletCoinValue = $checkWallet['wallet_amount'];
					
			if($walletCoinValue < $coinValue){
					
				echo json_encode([
				
			    	"success" => "0",
					"message" => "Insufficient Balance!"
					]);exit;
			}
			
			$walletCoinValue -= $coinValue;
 

          	$expDate = strtotime("+".$checkFrame['validity']." day");
            $dateTo = date('Y-m-d', $expDate);

             $sendReward['userId'] = $this->input->post('userId');
             $sendReward['otherUserId'] = $this->input->post('otherUserId');
    		 $sendReward['frameId'] = $this->input->post('frameId');
    		 $sendReward['price'] = $coinValue;
    		 $sendReward['rewardType'] = 'sendFrameReward';
    		 $sendReward['dateFrom'] = date('Y-m-d');
    		 $sendReward['dateTo'] = $dateTo;
 
             $insert = $this->db->insert('sendRewards', $sendReward);
          
             $sendGalleryRewardHistory['senderId'] = $this->input->post('userId');
             $sendGalleryRewardHistory['receiverId'] = $this->input->post('otherUserId');
    		 $sendGalleryRewardHistory['frameId'] = $this->input->post('frameId');
    		 $sendGalleryRewardHistory['price'] = $coinValue;
    		 $sendGalleryRewardHistory['deduct_history_type'] = 'sendFrameReward';
    		 $sendGalleryRewardHistory['created'] = date("Y-m-d H:i:s");
    		 
    		 $this->db->insert('deductCoinsHistory', $sendGalleryRewardHistory); // send sendGalleryReward history.
    		 
    		 $updateUserCoinwallet = $this->db->set(['wallet_amount' => $walletCoinValue])->where('userId', $this->input->post('userId'))->update('userWallet');
    		 
 
             if($insert && $updateUserCoinwallet){
        	
    		  $checkUserGallery = $this->db->select('sendRewards.id,sendRewards.otherUserId,sendRewards.frameId,sendRewards.price,sendRewards.rewardType,sendRewards.dateFrom,sendRewards.dateTo')
    									  ->from('sendRewards')
    									  ->where('userId', $this->input->post('userId'))
    									  ->where('sendRewards.rewardType','sendFrameReward')
    									  ->get()->result_array();
    	
    		  //$final = [];
    		  //foreach($checkUserFrames as $frames){
    	
    				//   if($frames['dateTo'] >= date('Y-m-d')){
    	
    				// 	$getFrame = $this->db->get_where('addFrames_fromAdmin', ['id' => $frames['frameId']])->row_array();
    				// 	$frames['frameIMage'] = base_url() . $getFrame['frame_img'];
    				// 	$final[] = $frames;
    	
    				//   }
    	
    		  //}
    	
    		  echo json_encode([
    			'status' => 1,
    			'message' => 'Reward send successfully',
    			'details' => $checkUserGallery
    		  ]);exit;
    	
    		 }else{
    		  echo json_encode([
    			'status' => 0,
    			'message' => 'Tech Error'
    		  ]);exit;
    		 }

        }else{
          echo json_encode([
            'status' => 0,
            'message' => 'Enter valid data'
          ]);exit;
        }
      }
      
      
      
      
      public function getRemainingDays(){
          
       $future = strtotime('2022-12-28');
        $now = time();
        $timeleft = $future-$now;
        $daysleft = round((($timeleft/24)/60)/60); 
        
        echo $daysleft;
      }
      
public function timecheck($datee)
  {
    $timeDiff = time() - strtotime($datee);
    $nYears = (int)($timeDiff / (60 * 60 * 24 * 365));
    $nMonths = (int)(($timeDiff % (60 * 60 * 24 * 365)) / (60 * 60 * 24 * 30));
    $nDays = (int)((($timeDiff % (60 * 60 * 24 * 365)) % (60 * 60 * 24 * 30)) / (60 * 60 * 24));
    $nHours = (int)(((($timeDiff % (60 * 60 * 24 * 365)) % (60 * 60 * 24 * 30)) % (60 * 60 * 24)) / (60 * 60));
    $nMinutes = (int)((((($timeDiff % (60 * 60 * 24 * 365)) % (60 * 60 * 24 * 30)) % (60 * 60 * 24)) % (60 * 60)) / (60));

    $timeMsg = "";

    if ($nYears > 0) {
      $yearWord = "years";
      if ($nYears == 1) {
        $yearWord = "year";
      }
      $timeMsg = "$nYears $yearWord ago";
    } elseif ($nMonths > 0) {
      $monthWord = "months";
      if ($nMonths == 1) {
        $monthWord = "month";
      }
      $timeMsg = "$nMonths $monthWord ago";
    } elseif ($nDays > 0) {
      $dayWord = "days";
      if ($nDays == 1) {
        $dayWord = "day";
      }
      $timeMsg = "$nDays $dayWord ago";
    } elseif ($nHours > 0) {
      $hourWord = "hour";
      if ($nHours == 1) {
        $hourWord = "hour";
      }
      $timeMsg = "$nHours $hourWord ago";
    } elseif ($nMinutes > 0) {
      $minuteWord = "minute";
      if ($nMinutes == 1) {
        $minuteWord = "minute";
      }
      $timeMsg = "$nMinutes $minuteWord ago";
    } else {
      $timeMsg = "just now";
    }
    return $timeMsg;
  }
  

  public function kickOutLiveUser(){
      
      $checkKickBy = $this->db->get_where("userLive",['userId' => $this->input->post("kickById")])->row_array();
      
      if(empty($checkKickBy)){
          
          echo json_encode([
              
              "success" => "0",
              "message" => "Kick by user not found!"
              ]);exit;
      }
      
      $checkkickToLiveId = $this->db->get_where("userLive",['id' => $this->input->post("liveId")])->row_array();
      
      if(empty($checkkickToLiveId)){
          
          echo json_encode([
              
              "success" => "0",
              "message" => "kickOutByLiveId not found!"
              ]);exit;
      }
      
      
      $checkKickTo = $this->db->get_where("users",['id' => $this->input->post("kickToId")])->row_array();
      
      if(empty($checkKickTo)){
          
          echo json_encode([
              
              "success" => "0",
              "message" => "Kick to user not found!"
              ]);exit;
      }
      
      $checkKickUser = $this->db->get_where("kickOutLiveUser",['kickTo' => $this->input->post("kickToId"),'liveId' => $this->input->post("liveId"),'kickBy' => $this->input->post("kickById")])->row_array();
      
      if(!!$checkKickUser){
          
          $datas['liveId'] = $this->input->post("liveId");
          $datas['kickBy'] = $this->input->post("kickById");
          $datas['kickTo'] = $this->input->post("kickToId");
          
          $update = $this->db->update("kickOutLiveUser",$datas,['kickTo' => $this->input->post("kickToId"),'liveId' => $this->input->post("liveId"),'kickBy' => $this->input->post("kickById")]);
          
          if($update == true){
              
              $getKickOutUser  = $this->db->get_where("kickOutLiveUser",['kickTo' => $this->input->post("kickToId"),'liveId' => $this->input->post("liveId"),'kickBy' => $this->input->post("kickById")])->row_array();
              
         

                  echo json_encode([
                      
                      "success" => "1",
                      "message" => "liveUser Kick out successfully",
                      "details" => $getKickOutUser
                      ]);exit;
                  
          }
              else{
                   echo json_encode([
                      
                      "success" => "0",
                      "message" => "Something went wrong!"
                      ]);exit;
                  
              }
      
        
      }
      else{
          $datas['liveId'] = $this->input->post("liveId");
          $datas['kickBy'] = $this->input->post("kickById");
          $datas['kickTo'] = $this->input->post("kickToId");
          $insert = $this->db->insert("kickOutLiveUser",$datas);
          
          $get = $this->db->insert_id();
          
          if($insert == true){
              
              $getKickOutUser  = $this->db->get_where("kickOutLiveUser",['id' => $get])->row_array();
              
              echo json_encode([
                      
                      "success" => "1",
                      "message" => "liveUser Kick out successfully",
                      "details" => $getKickOutUser
                      ]);exit;
          }
           else{
                   echo json_encode([
                      
                      "success" => "0",
                      "message" => "Something went wrong!"
                      ]);exit;
                  
        }
   
      }
  }
  
  public function getAllUserPost(){
      
      
      $getPostDetails = $this->db->select("userPostAndVideo.*,users.name")
      ->from("userPostAndVideo")
      ->join("users","users.id = userPostAndVideo.userId","left")
      ->order_by("created",'desc')
      ->get()
      ->result_array();
      
      if(!!$getPostDetails){
          
          $final = [];
          
          foreach($getPostDetails as $get){
              
            $postId = $get['id'];
            
            $postCreated = $get['created'];
              
            $checkFollowStatus = $this->db->get_where('likeFeed',['userId' => $this->input->post('userId'),'feedId' => $postId,'status' => '1'])->row_array();
            if($checkFollowStatus){
              $get['likeStatus'] = TRUE;
            }else{
              $get['likeStatus'] = FALSE;
            }
            
             $getImage = $this->db->select('image')
                             ->from('userImages')
                             ->where('userId', $get['userId'])
                              
                             ->order_by('id', 'desc')
                             ->limit(1)
                             ->get()->row_array();

                             if(!!$getImage){
                              $get['imageDp'] = $getImage['image'];
                               
                             }else{
                              $get['imageDp'] = "";
                                
                             }
            
             $check =  $this->timecheck($postCreated);
             
             if ($check) {
                $get['postTime'] = $check;
              } else {
                $get['postTime'] = '';
              }
            
            $final[] = $get;
            
            
          }
          
          echo json_encode([
              "success" => "1",
              "message" => "details found",
              "details" => $final
              ]);
          exit;

      }
      else{
          
          echo json_encode([
              
              "success" => "0",
              "message" => "details not found!"
              ]);exit;
      }
       
      
      
  }
  
  public function friendsCheck($userId, $otherUserId){

    $get = $this->db->get_where('followFeed', ['userId' => $userId, 'followingUserId' => $otherUserId])->row_array();
    if(empty($get)){
        return false;
    }else{
        $getTwo = $this->db->get_where('followFeed', ['userId' => $otherUserId, 'followingUserId' => $userId])->row_array();
        if(empty($getTwo)){
            return false;
        }else{
            return true;
        }
    }

  }
  
  public function getFriendsPosts(){
      if($this->input->post()){
          
          $user = $this->db->get_where('users' , ['id' => $this->input->post('userId')])->row_array();
          if(empty($user)){
              echo json_encode([
                  'success' => '0',
                  'message' => 'inavlid userId'
                  ]);exit;
          }
          
          $get = $this->db->get_where('followFeed', ['userId' => $this->input->post('userId')])->result_array();
          if(empty($get)){
              echo json_encode([
                  'success' => '0',
                  'message' => 'no following found'
                  ]);exit;
          }
          
        //   print_r($get);
        $friend = [];
        foreach($get as $gets){
            $friends = $this->db->get_where('followFeed', ['followingUserId' => $this->input->post('userId'), 'userId' => $gets['followingUserId']])->row_array();
            if(!!$friends){
                
            $friend[] = $friends['userId'];
            }
            
        }
        
        if(empty($friend)){
            echo json_encode([
                  'success' => '0',
                  'message' => 'no details found'
                  ]);exit;
        }
        
$here = implode(",", $friend);
        // print_r(json_encode($here));
        
        $id = $this->input->post('userId');
        $where = "userId = $id AND followingUserId NOT IN ($here)";
    $test = $this->db->select('*')->from('followFeed')->where($where)->get()->result_array();
          if(empty($test)){
              echo json_encode([
                  'success' => '0',
                  'message' => 'no following found'
                  ]);exit;
          }
          
          $following = [];
          foreach($test as $getss){
              $following[] = $getss['followingUserId'];
          }
          $hers2 = implode(",", $following);
          
          $alids = $here . ",". $hers2;
          
  $aa =   explode(",", $alids);
  
  $final = [];
          
          foreach($aa as $ids){
                    $getPostDetails = $this->db->select("userPostAndVideo.*,users.name")
                                                  ->from("userPostAndVideo")
                                                  ->join("users","users.id = userPostAndVideo.userId","left")
                                                  ->where('userPostAndVideo.userId', $ids)
                                                  ->order_by("created",'desc')
                                                  ->get()
                                                  ->result_array();
                                                  
                                                  if(!!$getPostDetails){
                                                      foreach($getPostDetails as $post){
                                                          
                                                          $images = $this->db->select('*')->from('userImages')->where('userId', $ids)->order_by('id', 'desc')->get()->row_array();
                                                          if(!!$images){
                                                              $post['userImage'] = $images;
                                                          }else{
                                                              $post['userImage'] = [];
                                                          }
                                                          
                                                          $likeStatus = $this->db->get_where('likeFeed', ['userId' => $this->input->post('userId'), 'feedId' => $post['id'], 'status' => '1'])->row_array();
                                                          if(empty($likeStatus)){
                                                              
                                                              $post['likestatus'] = false;
                                                              
                                                          }else{
                                                              
                                                              $post['likestatus'] = true;
                                                              
                                                          }
                                                      $final[] = $post;
                                                      }
                                                  }
          }
          
          if(empty($final)){
              echo json_encode([
                  'success' => '0',
                  'message' => 'no data found'
                  ]);exit;
          }
          
          echo json_encode([
              'success' => '1',
              'message' => 'data found',
              'details' => $final
              ]);exit;
          
          
      }else
      echo json_encode([
          'success' => '0',
          'message' => 'method not allowed'
          ]);exit;
  }
  
  public function PushNotificationToUser(){
      
      $userId = $this->input->post("userId");
      $otheruserId = $this->input->post("otheruserId");
      
      if($userId == null && $otheruserId == null){
          
          echo json_encode([
              
              "success" => "0",
              "message" => "param cannot be null!"
              ]); 
          exit;
      }
      
      $checkUser = $this->db->get_where("users",['id' => $this->input->post("userId")])->row_array();
      
      if(empty($checkUser)){
          
          echo json_encode([
              
              "success" => "0",
              "message" => "user not validl!"
              ]); 
          exit;
      }
      
      $checkOtherUser = $this->db->get_where("users",['id' => $this->input->post("otheruserId")])->row_array();
      
      if(empty($checkOtherUser)){
          
          echo json_encode([
              
              "success" => "0",
              "message" => "otheruserId not validl!"
              ]); 
          exit;
      }
      
      $getRegId = $this->db->get_where("users",['id' => $this->input->post("otheruserId")])->row_array();
      
    //   print_r($regId);
    //   die;
     
        $userTo = $getRegId['name'];
        $userBy = $checkUser['name'];
        $noti = $this->notify($getRegId["reg_id"],[
            "type"  =>  "chatRequest",
            "message"  => $userBy. " send chat request to " .$userTo,
            "title"  =>  "Request received",          
          ]);
       
            echo json_encode([
            
            "success" => "1",
            "message" => "notification send successfully"
            ]);exit;
 
      
  }
  
  public function notify($regId, $data = [])
  {
    $regID = $regId;

    $kkk = 'AAAAwYCOeR4:APA91bHpxF1S068bT3KeBYXTkRNngBEBW-gCiKFhqD43NV4M5yabPiaUBZSXFlHKwTwC63dVDz7jyNGy-qjfsZnzxCmmy86A_oc_IGDwN5bwdvyzaV3Ku_k-mV98bhHxh0blX_kM9gze';
    $msg = array(
      'vibrate'  => 1,
      'sound'    => 1,
      'largeIcon'  => 'large_icon',
      'smallIcon'  => 'small_icon',
    );

    $msg = array_merge($msg, $data);

    $fields = array(
      'registration_ids'   => array($regID),
      'data'      => $msg
    );
    $headers = array(
      'Authorization: key=' . $kkk,
      'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    //   print_r($response);
    //   die;
  }
  
  public function getTotalSilverCoins(){
      
      $get = $this->db->get_where("purchaseSilverCoin",['userId' => $this->input->post("userId")])->row_array();
      
      if(!!$get){
          
          echo json_encode([
              
              "success" => "1",
              "message" => "details found",
              "details" => $get
              ]);
          exit;
      }
      else{
          echo json_encode([
              
              "success" => "0",
              "message" => "details not found!",
              ]);
          exit;
          
      }
  }
  
  




 
 




 





 


// latest code end till 9-12-22.....
   
   





} 