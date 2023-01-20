<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Razorpay\Api\Api;
use Aws\S3\S3Client;
use Twilio\Rest\Client;

require APPPATH . '/libraries/razorpayli/autoload.php';

class EpAPIS extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    error_reporting(0);
    $this->load->model('api/Common_Model');
    $this->load->model('api/User_model');
    date_default_timezone_set('Asia/Kolkata');
  }

  public function getCountryFlags()
  {
    $list = $this->db->get_where('getCountryFlags')->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      foreach ($list as $lists) {
        $lists['image'] = base_url() . $lists['image'];
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No list found';
    }
    echo json_encode($message);
  }

  public function getCountries()
  {

    $getCountries = $this->db->get("country")->result_array();
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

  //  public function liveuserlistCountry(){
  //   $checkusedId = $this->db->get_where('userLive',array('userId' => $this->input->post('userId')))->row_array();
  //
  //   if(!empty($checkusedId)){
  //     $country = $this->input->post("country");
  //     $userId = $this->input->post("userId");
  //     $date = date("Y-m-d H:i:s");
  //     // $listlive =  $this->db->query("SELECT users.id,users.username,users.coin,users.name,users.leval,users.image,users.followerCount, users.country, users.liveStatus,userLive.* from userLive join users on users.id = userLive.userId where users.liveStatus = '1' and users.country = '$country' and users.id != $userId and userLive.created >= '$date'")->result_array();
  //
  //     $listlive = $this->db->select("users.id,users.username,users.coin,users.name,users.leval,users.image,users.followerCount, users.country, users.liveStatus,userLive.*")
  //                           ->from("userLive")
  //                           ->join("users","users.id = userLive.userId")
  //                           // ->where("users.liveStatus",'1')
  //                           ->where("users.id !=",$userId)
  //                           ->where("users.country",$country)
  //                           ->where("userLive.created >=",$date)
  //                           ->order_by("created", "DESC")
  //                           ->get()
  //                           ->result_array();
  //
  //
  //     $message['success'] = '1';
  //     $message['message'] = 'Live user found by country';
  //     $message['details'] = $listlive;
  //   }else{
  //     $message['success'] = '0';
  //     $message['message'] = 'Soemthing went worng';
  //   }
  //   echo json_encode($message);
  //
  // }

  public function liveuserlistCountry()
  {


    // $get = $this->db->sele("users",['country' => $this->input->post("country")])->result_array();
    $date = date("Y-m-d H:i:s");
    $listlive = $this->db->select("users.id,users.username,users.coin,users.name,users.leval,users.image,users.followerCount, users.country, users.liveStatus,userLive.*")
      ->from("userLive")
      ->join("users", "users.id = userLive.userId")
      // ->where("users.liveStatus",'1')
      // ->where("users.id !=",$userId)
      ->where("users.country", $this->input->post("country"))
      ->where("userLive.created <=", $date)
      ->order_by("created", "DESC")
      ->get()
      ->result_array();

    if (!!$listlive) {
      $message['success'] = '1';
      $message['message'] = 'Live user found by country';
      $message['details'] = $listlive;
    } else {
      $message['success'] = '0';
      $message['message'] = 'Soemthing went worng';
    }
    echo json_encode($message);
  }

  public function getLiveUserListCountry()
  {

    $checkusedId = $this->db->get_where("users", ['id' => $this->input->post("userId"), 'country' => $this->input->post("country"), 'liveStatus' => '1'])->row_array();

    if (!!$checkusedId) {

      $getId = $checkusedId['id'];

      $getUserLive = $this->db->select("userLive.*,users.name,users.username,users.image")
        ->from("userLive")
        ->join("users", "users.id = userLive.userId", "left")
        ->where("userLive.userId", $getId)
        ->get()
        ->result_array();

      if (!!$getUserLive) {
        echo json_encode([

          "message" => "Live user found by country",
          "success" => "1",
          "details" => $getUserLive,
        ]);
        exit;
      } else {
        echo json_encode([

          "message" => "Something went worng",
          "success" => "0",
        ]);
        exit;
      }
    } else {

      echo json_encode([

        "message" => "please enter valid userId & country",
        "success" => "0",
      ]);
      exit;
    }
  }

  public function liveOtherUserListCountry()
  {
    $list = $this->db->select("users.*")
      ->from("users")
      ->where("users.id !=", $this->input->post("userId"))
      ->where("users.country", $this->input->post("country"))
      ->where("users.liveStatus=", "1")
      ->get()
      ->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      foreach ($list as $lists) {

        $getId = $lists['id'];
        $getUserLive = $this->db->select("userLive.hostType,userLive.userId,userLive.channelName,userLive.token,userLive.rtmToken,userLive.status  userLive_status,userLive.archivedDate,userLive.count")
          ->from("userLive")
          ->where("userLive.userId", $getId)
          ->get()
          ->result_array();
        $lists[] = $getUserLive;
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No list found';
    }
    echo json_encode($message);
  }

  function asdasdasd()
  {

    $get = $this->db->select("users.*")
      ->from("users")
      ->where("users.id !=", $this->input->post("userId"))
      ->where("users.country", $this->input->post("country"))
      ->where("users.liveStatus=", "1")
      ->get()
      ->result_array();

    if (!empty($get)) {

      foreach ($get as $key => $value) {
        $id = $get[$key]['id'];

        $getDetails = $this->db->select("userLive.hostType,userLive.userId,userLive.channelName,userLive.token,userLive.rtmToken,userLive.status  userLive_status,userLive.archivedDate,userLive.count")
          ->from("userLive")
          ->where("userLive.userId", $id)
          ->get()
          ->result_array();

        $get[$key]['Videos'] = $getDetails;
      }
      $message['success'] = '1';
      $message['message'] = 'Tags found successfully';
      $message['details'] = $get;
    } else {
      $message['success'] = '0';
      $message['message'] = 'error...!';
    }

    echo json_encode($message);
  }



  public function allLiveOtherUserList()
  {

    $get = $this->db->select("userLive.*,users.name,users.username,users.image")
      ->from("userLive")
      ->join("users", "users.id = userLive.userId", "left")
      ->where("userLive.userId !=", $this->input->post("userId"))
      ->get()
      ->result_array();

    if (!!$get) {
      echo json_encode([

        "message" => "Live user found",
        "success" => "1",
        "details" => $get,
      ]);
      exit;
    } else {
      echo json_encode([
        "message" => "Live not found",
        "success" => "0",
      ]);
      exit;
    }
  }

  public function  CountryList()
  {

    $listlive =  $this->db->query("SELECT countries.name,countries.emoji,countries.emojiU,countries.iso2 FROM countries JOIN users on users.country = countries.name GROUP BY countries.id ")->result_array();

    $message['success'] = '1';
    $message['message'] = 'country list found ';
    $message['details'] = $listlive;
    echo json_encode($message);
  }

  public function sliderList()
  {
    $list = $this->db->get_where('slider', array('status' => 'Approved', 'type' => 0))->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      foreach ($list as $lists) {
        $lists['image'] = base_url() . $lists['image'];
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No list found';
    }
    echo json_encode($message);
  }

  public function homeSlider()
  {
    $list = $this->db->get_where('slider', array('status' => 'Approved', 'type' => 1))->result_array();
    // echo $this->db->last_query();
    // die;
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      foreach ($list as $lists) {
        $lists['image'] = base_url() . $lists['image'];
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No list found';
    }
    echo json_encode($message);
  }

  public function stest()
  {
    require APPPATH . '/libraries/vendor/autoload.php';
    $s3 = new Aws\S3\S3Client([
      'version' => 'latest',
      'region'  => 'us-east-1',
      'credentials' => [
        'key'    => 'AKIA5I2DFBLYNF2IC5UT',
        'secret' => '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'
      ]
    ]);
    $bucket = 'singlzevideo';
    $newName = time() . $_FILES['image']['name'];
    $upload = $s3->upload($bucket, $newName, fopen($_FILES['image']['name'], 'rb'), 'public-read');
    echo  $url = $upload->get('ObjectURL');
  }


  public function uploadVideosNew()
  {
    require APPPATH . '/libraries/vendor/autoload.php';
    $getUserDetails =  $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    $usernameWater = $getUserDetails['username'];
    $regId = $getUserDetails['reg_id'];
    $usernameMall = '';
    $sound =  0;

    $data['userId'] = $this->input->post('userId');
    if (!empty($this->input->post('hashTag'))) {
      $data['hashTag'] = $this->hastTagIds($this->input->post('hashTag'), $this->input->post('userId'));
    } else {
      $data['hashTag'] = '';
    }
    $data['allowDownloads']  = $this->input->post('allowDownloads');
    $data['description'] = $this->input->post('description');
    $data['allowComment'] = $this->input->post('allowComment');
    $data['allowDuetReact'] = $this->input->post('allowDuetReact');
    $data['soundId']  = $sound;
    $data['status'] = '3';
    $data['viewVideo']  = $this->input->post('viewVideo');
    $data['created'] = date('Y-m-d H:i:s');

    $s3 = new Aws\S3\S3Client([
      'version' => 'latest',
      'region'  => 'ap-south-1',
      'credentials' => [
        'key'    => 'AKIA3L2TB5JIUEWHXL3L',
        'secret' => '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'
      ]
    ]);
    $bucket = 'zebovideos';

    $upload = $s3->upload($bucket, $_FILES['videoPath']['name'], fopen($_FILES['videoPath']['tmp_name'], 'rb'), 'public-read');
    $url = $upload->get('ObjectURL');
    if (!empty($url)) {
      $data['videoPath'] = 'http://d2jnk8mn26fih3.cloudfront.net/' . $_FILES['videoPath']['name'];
      $data['downloadPath'] = 'http://d2jnk8mn26fih3.cloudfront.net/' . $_FILES['videoPath']['name'];
    } else {
      $data['videoPath'] = '';
      $data['downloadPath'] = '';
    }

    $upload2 = $s3->upload($bucket, $_FILES['thumbnail']['name'], fopen($_FILES['thumbnail']['tmp_name'], 'rb'), 'public-read');
    $url2 = $upload2->get('ObjectURL');
    if (!empty($url2)) {
      $data['thumbnail'] = 'http://d2jnk8mn26fih3.cloudfront.net/' . $_FILES['thumbnail']['name'];
    } else {
      $data['thumbnail'] = '';
    }

    $insert = $this->db->insert('userVideos', $data);
    if (!empty($insert)) {
      $vIDs = $this->db->insert_id();
      $checkData = $this->db->get_where('userProfileInformation', array('userId' => $this->input->post('userId')))->row_array();
      if (!empty($checkData)) {
        $userProfile['userId'] = $this->input->post('userId');
        $userProfile['videoCount'] = 1;
        $this->db->insert('userProfileInformation', $userProfile);
      } else {
        $userProfile['videoCount'] = 1 + $checkData['videoCount'];
        $update = $this->Common_Model->update('userProfileInformation', $userProfile, 'id', $checkData['id']);
      }


      //$this->videoNotification($vIDs,$this->input->post('userId'));


      $videoId = $vIDs;
      $userId = $this->input->post('userId');
      $lists = $this->db->get_where('userFollow', array('followingUserId' => $userId, 'status' => '1'))->result_array();
      if (!empty($lists)) {
        if ($this->input->post('viewVideo') == 0) {
          foreach ($lists as $list) {
            $loginUserDetails = $this->db->get_where('users', array('id' => $userId))->row_array();
            $getUserId = $this->db->get_where('users', array('id' => $list['userId']))->row_array();
            $regId = $getUserId['reg_id'];
            $mess = $loginUserDetails['username'] . ' uploaded new video';
            if ($getUserId['videoNotification'] == '1') {
              $this->notification($regId, $mess, 'video', $list['userId'], $userId);
            }
            $notiMess['loginId'] = $userId;
            $notiMess['userId'] = $list['userId'];
            $notiMess['videoId'] = $videoId;
            $notiMess['message'] = $mess;
            $notiMess['type'] = 'video';
            $notiMess['notiDate'] = date('Y-m-d');
            $notiMess['created'] = date('Y-m-d H:i:s');
            $this->db->insert('userNotification', $notiMess);
          }
        }
      }

      $message['success'] = '1';
      $message['message'] = 'Video Upload Successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
      $this->notification($regId, $message['message'], 'video', $this->input->post('userId'), $this->input->post('userId'));
    }

    echo json_encode($message);
  }

  function addWaterMarkPrivate($video_file, $watermark, $username, $basename_file)
  {


    // $video_file = "uploads/abc_main.mp4";

    $watermark = "uploads/watermark.gif";
    $new_watermark = $basename_file . "2.gif";
    // $new_watermark = "uploads/12.gif";
    $text = $username;
    $first_sec = "9";


    $video_split_1 = $basename_file . "split_1.mp4";
    $video_split_1_watermark =  $basename_file . "split_1_watermark.mp4";
    $video_split_2 =  $basename_file . "split_2.mp4";
    $video_split_2_watermark = $basename_file . "split_2_watermark.mp4";
    $video_merged = $basename_file . "_download.mp4";

    // $video_split_1 = "uploads/1split_1.mp4";
    // $video_split_1_watermark =  "uploads/1_watermark.mp4";
    // $video_split_2 =  "uploads/1split_2.mp4";
    // $video_split_2_watermark = "uploads/2_watermark.mp4";
    // $video_merged = "uploads/downloadvideo.mp4";

    $frames_split_1 = "200";
    $frames_split_2 = "200";

    $commad_get_seconds = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . $video_file;
    // $command_split_1 = "ffmpeg -i " .$video_file ." -ss 0"." -t ". $first_sec. " " . $video_split_1;
    // $command_split_2 = "ffmpeg -i " .$video_file. " -ss ". $first_sec." -t ". $first_sec ." ". $video_split_2;
    // $add_username_gif = "convert " . $watermark. " –gravity southwest –pointsize 18 –fill white -annotate +5+0 "."testuser"." ".$new_watermark;
    $text = $username;


    //    $add_username_gif = "convert ".$watermark." \
    // -gravity south \
    // -pointsize 18 \
    // -fill white    -annotate +5+0 ".$text."   ".$new_watermark;
    // convert uploads/watermark.gif \
    // -gravity south \
    // -pointsize 18 \
    // -fill white    -annotate +5+0 'username'   uploads/anno_splice2.gif

    // "convert uploads/watermark.gif –gravity southwest –pointsize 18 –fill white -annotate +5+0 testuser uploads/1234.gif";
    // "convert uploads/watermark.gif   –gravity southwest   –pointsize 18   –fill white   -annotate +5+0   testuser uploads/1234.gif";

    exec($commad_get_seconds, $seconds);
    $first_sec = $seconds[0] / 2;

    $command_split_1 = "ffmpeg -i " . $video_file . " -ss 0 -t " . $first_sec . " " . $video_split_1;
    $command_split_2 = "ffmpeg -i " . $video_file . " -ss " . $first_sec . " -t " . $first_sec . " " . $video_split_2;


    exec($command_split_1);
    exec($command_split_2);
    $get_frame_split1 = "ffprobe -v error -select_streams v:0 -show_entries stream=nb_frames -of default=nokey=1:noprint_wrappers=1 " . $video_split_1;
    $get_frame_split2 = "ffprobe -v error -select_streams v:0 -show_entries stream=nb_frames -of default=nokey=1:noprint_wrappers=1 " . $video_split_2;


    exec($get_frame_split1, $frames_1);
    exec($get_frame_split2, $frames_2);
    $frames_split_1 = $frames_1[0];
    $frames_split_2 = $frames_2[0];
    $add_username_gif = "convert " . $watermark . "  -gravity south  -pointsize 18  -fill white  -annotate +5+0 username  " . $new_watermark;
    // $add_username_gif1 ="convert ".$watermark."  -gravity south  -pointsize 18  -fill white  -annotate +5+0 username  uploads/watermark_new11.gif";

    // shell_exec($add_username_gif);
    // shell_exec($add_username_gif1);
    $new_watermark = $this->addtextImage($new_watermark, $watermark, $text);
    $watermark_to_split_1 = "ffmpeg -i " . $video_split_1 . " -ignore_loop 0 -i " . $new_watermark . " -filter_complex overlay=0:0 -frames:v " . $frames_split_1 . " " . $video_split_1_watermark;
    $watermark_to_split_2 = "ffmpeg -i " . $video_split_2 . " -ignore_loop 0 -i " . $new_watermark . " -filter_complex overlay=W-w-5:H-h-5 -frames:v " . $frames_split_2 . " " . $video_split_2_watermark;
    exec($watermark_to_split_1);
    exec($watermark_to_split_2);
    $combine_command = "MP4Box -add " . $video_split_1_watermark . " -cat " . $video_split_2_watermark . " " . $video_merged;



    exec($combine_command);



    @unlink($video_split_1);
    @unlink($video_split_1_watermark);
    @unlink($video_split_2);
    @unlink($video_split_2_watermark);
    @unlink($new_watermark);

    return $video_merged;
  }
  function addtextImage($gifpath, $watermarkpath, $textwrite)
  {
    $gmagick = new Gmagick($watermarkpath);
    // echo "23";
    $gmagick = $gmagick->coalesceImages();
    // Create a GmagickDraw object
    $draw = new GmagickDraw();

    // Set the fill color
    $draw->setFillColor('white');

    // Set the font size
    $draw->setfontsize(20);
    $draw->setfont('uploads/poppins.ttf');
    // $draw->setfont('times.ttf');
    // $draw->setfontstyle(\Gmagick::STYLE_NORMAL);

    // Annotate a text
    $gmagick->annotateImage($draw, 15, 75, 0, $textwrite);

    // Use of drawimage function
    // $gmagick->drawImage($draw);

    // Display the output image
    header('Content-Type: image/gif');
    // echo $gmagick->getImageBlob();

    file_put_contents($gifpath, $gmagick->getImageBlob());

    return $gifpath;
  }




  // public function otpTestingMall(){
  //
  //   $curl = curl_init();
  //
  //           $phone = "+917901759085";
  //           $otp =  "123456";
  //           $message12 = "Hi Simran, OTP Testing Twilio account ".$otp;
  //
  //
  //            $a = $phone;
  //     //require dirname(dirname(dirname(_FILE_))).'/libraries/twilio-php-master/Twilio/autoload.php';
  //     //require APPPATH.'/libraries/twilio/twilio-php-master/Twilio/autoload.php';
  //     require APPPATH.'/libraries/twilio-php-master/Twilio/autoload.php';
  //     $sid    = "AC28cbb8b04a32be13f3f97e165452c1a7";
  //     $token  = "5091b9d944422d906bcbd4c7c268e1a7";
  //     $twilio = new Client($sid, $token);
  //     $message23 = $twilio->messages
  //       ->create($a, // to
  //          array(
  //            "from" => "+16182055887",
  //            "body" =>  $message12,
  //         )
  //     );
  //
  //     print_r($message23);
  //     die;
  //
  // }
  //
  // public function pawan(){
  //   $this->load->view('liveVideo');
  // }

  public function checkLeval()
  {

    $val = $this->input->post('val');
    $leval =  $this->input->post('leval');
    $nextLeval = $leval + 1;
    $levalData = $this->db->get_where('leval', array('leval' => $nextLeval))->row_array();
    if ($val >= $levalData['expCount']) {
      echo $nextLeval;
    } else {
      echo $leval;
    }
  }

  public function story()
  {
    require APPPATH . '/libraries/vendor/autoload.php';
    $data['userId'] = $this->input->post('userId');
    $data['description'] = $this->input->post('description');
    $data['type'] = $this->input->post('type');
    $data['created'] = date('Y-m-d H:i:s');
    $todayDate = date('Y-m-d H:i:s');
    $datetime = new DateTime($todayDate);
    $datetime->modify('+1 day');
    $data['endDate'] =  $datetime->format('Y-m-d H:i:s');

    require APPPATH . '/libraries/vendor/autoload.php';
    $s3 = new Aws\S3\S3Client([
      'version' => 'latest',
      'region'  => 'ap-south-1',
      'credentials' => [
        'key'    => 'AKIA3L2TB5JIUEWHXL3L',
        'secret' => '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'
      ]
    ]);
    $bucket = 'zebovideos';

    if (!empty($_FILES['image']['name'])) {
      $upload = $s3->upload($bucket, $_FILES['image']['name'], fopen($_FILES['image']['tmp_name'], 'rb'), 'public-read');
      $url = $upload->get('ObjectURL');
      if (!empty($url)) {
        $data['image'] = $url;
      } else {
        $data['image'] = '';
      }
    }

    $insert = $this->db->insert('story', $data);
    if (!empty($insert)) {
      $message['success'] = '1';
      $message['message'] = 'Story uploaded successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function getstories()
  {
    $todayDates = date('Y-m-d H:i:s');
    $list = $this->db->get_where('story', array('userId' => $this->input->post('userId'), 'endDate >' => $todayDates))->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'list found successfully';
      $message['storyCount'] = (string)count($list);
      foreach ($list as $lists) {
        $lists['storyTime'] = $this->getTime($lists['created']);
        $lists['viewCount'] = '0';
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'no list found';
    }
    echo json_encode($message);
  }

  public function storiesDelete()
  {
    $delete = $this->db->delete('story', array('id' => $this->input->post('storyid')));
    if ($delete) {
      $message['success'] = '1';
      $message['message'] = 'Story Delete Successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function followUserStories()
  {
    $selectFollowProvider = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'status' => '1'))->result_array();
    $todayDates = date('Y-m-d H:i:s');
    $checkUserPost1 = $this->db->get_where('story', array('userId' => $this->input->post('userId'), 'endDate >' => $todayDates))->row_array();
    if (!empty($selectFollowProvider)) {
      foreach ($selectFollowProvider as $selectFollowProviders) {
        $providerids[] = $selectFollowProviders['followingUserId'];
      }
      $finalProviderId = implode(',', $providerids);
      $todayDate = date('Y-m-d H:i:s');
      $orderProviderId = $this->Common_Model->orderByPoroviderId($finalProviderId, $todayDate);
      if (!empty($orderProviderId)) {
        $message['success'] = '1';
        $message['message'] = 'list found successfully';
        foreach ($orderProviderId as $orderProviderId) {
          $list = $this->db->get_where('story', array('userId' => $orderProviderId['userId'], 'endDate >' => $todayDates))->num_rows();
          $providerDetails = $this->db->get_where('users', array('id' => $orderProviderId['userId']))->row_array();
          $datasss['userId'] = $providerDetails['id'];
          $datasss['username'] = $providerDetails['username'];
          $datasss['name'] = $providerDetails['name'];
          $datasss['userphone'] = $providerDetails['phone'];
          if (!empty($providerDetails['image'])) {
            if (filter_var($providerDetails['image'], FILTER_VALIDATE_URL)) {
              $datasss['image'] = $providerDetails['image'];
            } else {
              $datasss['image'] = base_url() . $providerDetails['image'];
            }
          } else {
            $datasss['image'] = '';
          }
          $datasss['storyCount'] = (string)$list;
          $anuradha[] = $datasss;
        }
        $checkUserPost = $this->db->get_where('story', array('userId' => $this->input->post('userId'), 'endDate >' => $todayDate))->row_array();
        if (!empty($checkUserPost)) {
          $list = $this->db->get_where('story', array('userId' => $this->input->post('userId'), 'endDate >' => $todayDates))->num_rows();
          $providerDetails1 = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
          if (!empty($providerDetails1['image'])) {
            if (filter_var($providerDetails1['image'], FILTER_VALIDATE_URL)) {
              $userImage = $providerDetails1['image'];
            } else {
              $userImage = base_url() . $providerDetails1['image'];
            }
          } else {
            $userImage = '';
          }
          $mall[] = array('userId' => $providerDetails1['id'], 'username' => $providerDetails1['name'], 'userphone' =>  $providerDetails1['phone'], 'image' => $userImage, 'storyCount' => (string)$list);
          $addsDetail = array_merge($mall, $anuradha);
          $message['details'] = $addsDetail;
        } else {
          $message['details'] = $anuradha;
        }
      } elseif (!empty($checkUserPost1)) {
        $message['success'] = '1';
        $message['message'] = 'list found successfully';
        $list = $this->db->get_where('story', array('userId' => $this->input->post('userId'), 'endDate >' => $todayDates))->num_rows();
        $providerDetails1 = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
        if (!empty($providerDetails1['image'])) {
          if (filter_var($providerDetails1['image'], FILTER_VALIDATE_URL)) {
            $userImage = $providerDetails1['image'];
          } else {
            $userImage = base_url() . $providerDetails1['image'];
          }
        } else {
          $userImage = '';
        }
        $mall[] = array('userId' => $providerDetails1['id'], 'username' => $providerDetails1['name'], 'userphone' =>  $providerDetails1['phone'], 'image' => $userImage, 'storyCount' => (string)$list);
        $message['details'] = $mall;
      } else {
        $message['success'] = '0';
        $message['message'] = 'no list found';
      }
    } elseif (!empty($checkUserPost1)) {
      $message['success'] = '1';
      $message['message'] = 'list found successfully';
      $list = $this->db->get_where('story', array('userId' => $this->input->post('userId')))->num_rows();
      $providerDetails1 = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
      if (!empty($providerDetails1['image'])) {
        if (filter_var($providerDetails1['image'], FILTER_VALIDATE_URL)) {
          $userImage = $providerDetails1['image'];
        } else {
          $userImage = base_url() . $providerDetails1['image'];
        }
      } else {
        $userImage = '';
      }
      $mall[] = array('userId' => $providerDetails1['id'], 'username' => $providerDetails1['name'], 'userphone' =>  $providerDetails1['phone'], 'image' => $userImage, 'storyCount' => (string)$list);
      $message['details'] = $mall;
    } else {
      $message['success'] = '0';
      $message['message'] = 'no list found';
    }
    echo json_encode($message);
  }

  public function liveStory()
  {
    require APPPATH . '/libraries/vendor/autoload.php';
    $data['userId'] = $this->input->post('userId');
    $data['description'] = $this->input->post('description');
    $data['type'] = $this->input->post('type');
    $data['created'] = date('Y-m-d H:i:s');
    $todayDate = date('Y-m-d H:i:s');
    $datetime = new DateTime($todayDate);
    $datetime->modify('+1 day');
    $data['endDate'] =  $datetime->format('Y-m-d H:i:s');

    require APPPATH . '/libraries/vendor/autoload.php';
    $s3 = new Aws\S3\S3Client([
      'version' => 'latest',
      'region'  => 'ap-south-1',
      'credentials' => [
        'key'    => 'AKIA3L2TB5JIUEWHXL3L',
        'secret' => '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'
      ]
    ]);
    $bucket = 'zebovideos';

    if (!empty($_FILES['image']['name'])) {
      $upload = $s3->upload($bucket, $_FILES['image']['name'], fopen($_FILES['image']['tmp_name'], 'rb'), 'public-read');
      $url = $upload->get('ObjectURL');
      if (!empty($url)) {
        $data['image'] = $url;
      } else {
        $data['image'] = '';
      }
    }

    $insert = $this->db->insert('liveStory', $data);
    if (!empty($insert)) {
      $message['success'] = '1';
      $message['message'] = 'Story uploaded successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function getLivestories()
  {
    $todayDates = date('Y-m-d H:i:s');
    $list = $this->db->get_where('liveStory', array('userId' => $this->input->post('userId'), 'endDate >' => $todayDates))->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'list found successfully';
      $message['storyCount'] = (string)count($list);
      foreach ($list as $lists) {
        $lists['storyTime'] = $this->getTime($lists['created']);
        $lists['viewCount'] = '0';
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'no list found';
    }
    echo json_encode($message);
  }

  public function liveStoriesDelete()
  {
    $delete = $this->db->delete('liveStory', array('id' => $this->input->post('storyid')));
    if ($delete) {
      $message['success'] = '1';
      $message['message'] = 'Story Delete Successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function followUserLiveStories()
  {
    $selectFollowProvider = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'status' => '1'))->result_array();
    $todayDates = date('Y-m-d H:i:s');
    $checkUserPost1 = $this->db->get_where('liveStory', array('userId' => $this->input->post('userId'), 'endDate >' => $todayDates))->row_array();
    if (!empty($selectFollowProvider)) {
      foreach ($selectFollowProvider as $selectFollowProviders) {
        $providerids[] = $selectFollowProviders['followingUserId'];
      }
      $finalProviderId = implode(',', $providerids);
      $todayDate = date('Y-m-d H:i:s');
      $orderProviderId = $this->Common_Model->orderByPoroviderId1($finalProviderId, $todayDate);
      if (!empty($orderProviderId)) {
        $message['success'] = '1';
        $message['message'] = 'list found successfully';
        foreach ($orderProviderId as $orderProviderId) {
          $list = $this->db->get_where('liveStory', array('userId' => $orderProviderId['userId'], 'endDate >' => $todayDates))->num_rows();
          $providerDetails = $this->db->get_where('users', array('id' => $orderProviderId['userId']))->row_array();
          $datasss['userId'] = $providerDetails['id'];
          $datasss['username'] = $providerDetails['username'];
          $datasss['name'] = $providerDetails['name'];
          $datasss['userphone'] = $providerDetails['phone'];
          if (!empty($providerDetails['image'])) {
            if (filter_var($providerDetails['image'], FILTER_VALIDATE_URL)) {
              $datasss['image'] = $providerDetails['image'];
            } else {
              $datasss['image'] = base_url() . $providerDetails['image'];
            }
          } else {
            $datasss['image'] = '';
          }
          $datasss['storyCount'] = (string)$list;
          $anuradha[] = $datasss;
        }
        $checkUserPost = $this->db->get_where('liveStory', array('userId' => $this->input->post('userId'), 'endDate >' => $todayDate))->row_array();
        if (!empty($checkUserPost)) {
          $list = $this->db->get_where('liveStory', array('userId' => $this->input->post('userId'), 'endDate >' => $todayDates))->num_rows();
          $providerDetails1 = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
          if (!empty($providerDetails1['image'])) {
            if (filter_var($providerDetails1['image'], FILTER_VALIDATE_URL)) {
              $userImage = $providerDetails1['image'];
            } else {
              $userImage = base_url() . $providerDetails1['image'];
            }
          } else {
            $userImage = '';
          }
          $mall[] = array('userId' => $providerDetails1['id'], 'username' => $providerDetails1['name'], 'userphone' =>  $providerDetails1['phone'], 'image' => $userImage, 'storyCount' => (string)$list);
          $addsDetail = array_merge($mall, $anuradha);
          $message['details'] = $addsDetail;
        } else {
          $message['details'] = $anuradha;
        }
      } elseif (!empty($checkUserPost1)) {
        $message['success'] = '1';
        $message['message'] = 'list found successfully';
        $list = $this->db->get_where('liveStory', array('userId' => $this->input->post('userId'), 'endDate >' => $todayDates))->num_rows();
        $providerDetails1 = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
        if (!empty($providerDetails1['image'])) {
          if (filter_var($providerDetails1['image'], FILTER_VALIDATE_URL)) {
            $userImage = $providerDetails1['image'];
          } else {
            $userImage = base_url() . $providerDetails1['image'];
          }
        } else {
          $userImage = '';
        }
        $mall[] = array('userId' => $providerDetails1['id'], 'username' => $providerDetails1['name'], 'userphone' =>  $providerDetails1['phone'], 'image' => $userImage, 'storyCount' => (string)$list);
        $message['details'] = $mall;
      } else {
        $message['success'] = '0';
        $message['message'] = 'no list found';
      }
    } elseif (!empty($checkUserPost1)) {
      $message['success'] = '1';
      $message['message'] = 'list found successfully';
      $list = $this->db->get_where('liveStory', array('userId' => $this->input->post('userId')))->num_rows();
      $providerDetails1 = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
      if (!empty($providerDetails1['image'])) {
        if (filter_var($providerDetails1['image'], FILTER_VALIDATE_URL)) {
          $userImage = $providerDetails1['image'];
        } else {
          $userImage = base_url() . $providerDetails1['image'];
        }
      } else {
        $userImage = '';
      }
      $mall[] = array('userId' => $providerDetails1['id'], 'username' => $providerDetails1['name'], 'userphone' =>  $providerDetails1['phone'], 'image' => $userImage, 'storyCount' => (string)$list);
      $message['details'] = $mall;
    } else {
      $message['success'] = '0';
      $message['message'] = 'no list found';
    }
    echo json_encode($message);
  }



  public function giftHistory()
  {
    $type = $this->input->post('type');
    $userId = $this->input->post('userId');
    $url = base_url();
    $userInfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    $dolloerHist = $this->db->get_where('beansExchangeToDollar', array('id' => 1))->row_array();
    // print_r($dolloerHist);
    // die;
    $message['dollarBeans'] = $dolloerHist['beans'];
    $message['dollar'] = $dolloerHist['dollar'];
    $message['minmumBeans'] = $dolloerHist['minimumBeans'];
    if ($type == 'sent') {
      $lists =  $this->db->query("select users.username,users.image,users.phone,users.name,gift.title as giftTitle,gift.primeAccount as giftCoin,concat('$url',gift.image) as giftImage, userGiftHistory.giftUserId,userGiftHistory.userId,userGiftHistory.created from userGiftHistory left JOIN users on users.id = userGiftHistory.giftUserId left join gift on gift.id = userGiftHistory.giftId where userGiftHistory.userId = $userId")->result_array();
      if (!empty($userInfo['purchasedCoin'])) {
        $message['beans'] =   $userInfo['purchasedCoin'];
      } else {
        $message['beans'] = '';
      }
    } else {
      $lists =  $this->db->query("select users.username,users.image,users.phone,users.name,gift.title as giftTitle,gift.primeAccount as giftCoin,concat('$url',gift.image) as giftImage    , userGiftHistory.userId,userGiftHistory.created from userGiftHistory left JOIN users on users.id = userGiftHistory.userId left join gift on gift.id = userGiftHistory.giftId where userGiftHistory.giftUserId = $userId")->result_array();
      if (!empty($userInfo['coin'])) {

        $message['beans']  = $userInfo['coin'];
      } else {
        $message['beans']  =  '';
      }
    }
    if (!empty($lists)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      foreach ($lists as $list) {
        if (empty($list['image'])) {
          $list['image'] = base_url() . 'uploads/no_image_available.png';
        }
        $list['created'] = $this->sohitTime($list['created']);
        $message['details'][] = $list;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No List found';
    }
    echo json_encode($message);
  }

  //  public function sendGift(){
  //   $data['userId'] = $this->input->post('userId');
  //   $data['giftUserId'] = $this->input->post('giftUserId');
  //   $data['giftId'] = $this->input->post('giftId');
  //   $data['coin'] = $this->input->post('coin');
  //   $data['created'] = date('Y-m-d H:i:s');
  //   $insert = $this->db->insert('userGiftHistory',$data);
  //   if(!empty($insert)){
  //     $loginUserDetails = $this->db->get_where('users',array('id' => $this->input->post('userId')))->row_array();
  //       $expCoin = $loginUserDetails['expCoin'];
  //     $loginUpdateCoin['purchasedCoin'] = $loginUserDetails['purchasedCoin'] - $this->input->post('coin');
  //       $calcuLateExpCoin = $this->input->post('coin') * 5;
  //      $loginUpdateCoin['expCoin'] = $expCoin + $calcuLateExpCoin;
  //      $allExpCoin = $loginUpdateCoin['expCoin'];
  //      $levalList  =  $this->db->query("SELECT * FROM leval WHERE expCount BETWEEN 300 AND $allExpCoin order by id desc limit 1")->row_array();
  //      $loginUpdateCoin['leval'] = $levalList['leval'];
  //     $this->Common_Model->update('users',$loginUpdateCoin,'id',$this->input->post('userId'));


  //     $giftUserDetails = $this->db->get_where('users',array('id' => $this->input->post('giftUserId')))->row_array();
  //      $expCoin1 = $giftUserDetails['expCoin'];
  //     $giftUserUpdate['coin'] = $giftUserDetails['coin'] + $this->input->post('coin');
  //      $calcuLateExpCoin1 = $this->input->post('coin') * 3;
  //      $giftUserUpdate['expCoin'] = $expCoin1 + $calcuLateExpCoin1;
  //      $allExpCoin1 = $giftUserUpdate['expCoin'];
  //      $levalList1  =  $this->db->query("SELECT * FROM leval WHERE expCount BETWEEN 300 AND $allExpCoin1 order by id desc limit 1")->row_array();
  //      $giftUserUpdate['leval'] = $levalList1['leval'];
  //     $this->Common_Model->update('users',$giftUserUpdate,'id',$this->input->post('giftUserId'));


  //     $regId = $giftUserDetails['reg_id'];
  //      if(!empty($loginUserDetails['name'])){
  //       $manavName = $loginUserDetails['name'];
  //      }
  //      else{
  //       $manavName = $loginUserDetails['username'];
  //      }
  //     $mess = 'You received a gift from '.$manavName;
  //      $purchasedCoinstotal = $giftUserDetails['purchasedCoin'];
  //      $receivedCointotal = $giftUserUpdate['coin'];
  //     $this->giftNotification($regId,$mess,'gift',$this->input->post('userId'),$this->input->post('giftUserId'),$purchasedCoinstotal,$receivedCointotal);

  //     $notiMess['loginId'] = $this->input->post('userId');
  //     $notiMess['userId'] = $this->input->post('giftUserId');
  //     $notiMess['message'] = $mess;
  //     $notiMess['type'] = 'gift';
  //     $notiMess['notiDate'] = date('Y-m-d');
  //     $notiMess['created'] = date('Y-m-d H:i:s');
  //     $this->db->insert('userNotification',$notiMess);


  //      $outMess['myLevel'] =  $loginUpdateCoin['leval'] ;
  //      $outMess['liveLevel'] =  $giftUserUpdate['leval'] ;
  //      $outMess['myStar'] =  '0';
  //      $outMess['liveStar'] =  '0' ;
  //     $message['success'] = '1';
  //     $message['message'] = 'Gift send successfully';
  //      $message['details'] = $outMess;
  //   }
  //   else{
  //     $message['success'] = '0';
  //     $message['message'] = 'Please try after some time';
  //   }
  //   echo json_encode($message);
  //  }



  public function sendLiveGift()
  {

    if ($this->input->post()) {

      $checksuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
      $checksuserother = $this->db->get_where('users', ['id' => $this->input->post('giftUserId')])->row_array();

      if (empty($checksuser) || empty($checksuserother)) {

        echo json_encode([
          'status' => 0,
          'message' => 'invalid userIds'
        ]);
        exit;
      }

      $data['userId'] = $this->input->post('userId');
      $data['giftUserId'] = $this->input->post('giftUserId');
      $data['giftId'] = $this->input->post('giftId');
      $data['coins'] = $this->input->post('coin');
      $data['type'] = 1;
      $data['created_at'] = date('Y-m-d H:i:s');

      if (!empty($this->input->post('pkHostId'))) {
        $data['pkHostId'] = $this->input->post('pkHostId');
      }
      if (!empty($this->input->post('liveId'))) {
        $data['liveId'] = $this->input->post('liveId');
      }
      if (!empty($this->input->post('pkId'))) {
        $data['pkId'] = $this->input->post('pkId');
        $data['type'] = 2;
      }





      // ======================= sender part =================================================

      // $senderDetails = $this->db->select('monthlySendCoins, total_send_coin, purchasedCoin, monthlyPurchasedCoins')
      $senderDetails = $this->db->select('purchasedCoin, exp, total_send_coin')
        ->from('users')
        ->where('id', $this->input->post('userId'))
        ->get()->row_array();


      // check balance
      $senderCoins = $senderDetails['purchasedCoin'];

      $totalSendCoins = $senderDetails['total_send_coin'];

      $exp = $senderDetails['exp'];

      if ($senderCoins < $data['coins']) {
        echo json_encode([
          'status' => 0,
          'message' => 'Insufficient funds'
        ]);
        exit;
      }

      $senderCoins -= $data['coins'];

      $totalSendCoins += $data['coins'];

      $divCoin = $data['coins'];

      $divCoin /= 2;

      $exp += $divCoin;

      $level = $this->countLevel($exp, $totalSendCoins, '1');

      $this->db->set(['purchasedCoin' => $senderCoins, 'total_send_coin' => $totalSendCoins, 'exp' => $exp, 'my_level' => $level])
        ->where('id', $this->input->post('userId'))
        ->update('users');


      // ====================== reciever part ===================================================


      // $recieverDetails = $this->db->select('monthlyCoins, coin')
      $recieverDetails = $this->db->select('coin, exp')
        ->from('users')
        ->where('id', $this->input->post('giftUserId'))
        ->get()->row_array();

      $recieverMonthlyCoin = $recieverDetails['coin'];
      $recieverMonthlyCoin += $data['coins'];
      $experience = $recieverDetails['exp'];
      $experience += $divCoin;

      $tlevel = $this->countLevel($experience, $recieverMonthlyCoin, '2');



      $this->db->set(['coin' => $recieverMonthlyCoin, 'exp' => $experience, 'talent_level' => $tlevel])
        ->where('id', $this->input->post('giftUserId'))
        ->update('users');


      $countStar = $this->db->select_sum('coins')
        ->from('userGiftHistory')
        ->where('giftUserId', $this->input->post('giftUserId'))
        ->where('created_at', date('Y-m-d'))
        ->get()->row_array();


      if (!empty($this->input->post('liveId'))) {
        $recieveCoins = $this->db->select_sum('coins')
          ->from('userGiftHistory')
          ->where('giftUserId', $this->input->post('giftUserId'))
          ->where('liveId', $this->input->post('liveId'))
          ->get()->row_array();
      }

      if (!empty($this->input->post('pkId'))) {
        $recieveCoins = $this->db->select_sum('coins')
          ->from('userGiftHistory')
          ->where('giftUserId', $this->input->post('giftUserId'))
          ->where('pkId', $this->input->post('pkId'))
          ->get()->row_array();
      }


      $insert = $this->db->insert('userGiftHistory', $data);




      //  $talentImage = $this->db->select('image')->from('user_talent_levels')->where('level', $myLevel['talent_level'])->get()->row_array();

      //  $outMess['myLevel'] =  $myLevel['my_level'];
      //  $outMess['liveLevel'] =  $myLevel['talent_level'];
      //  $outMess['talentImage'] =  $talentImage['image'];
      $outMess['myStar'] =  $countStar['coin'] ?: '0';
      $outMess['coinsRecieved'] = $recieveCoins['coin'] ?: '0';
      $outMess['liveStar'] =  '0';
      $outMess['liveBox'] =  '0';
      $message['status'] = '1';
      $message['message'] = 'Gift send successfully';
      $message['details'] = $outMess;

      echo json_encode($message);
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'Enter Valid data'
      ]);
      exit;
    }
  }

  private function countLevel($xp, $coins, $type)
  {
    if ($type == '1') {

      $table = 'setsenderLevel';
    } else {
      $table = 'setRecieverLevel';
    }
    $get = $this->db->get($table)->result_array();

    $last = [];
    foreach ($get as $gets) {

      if (($gets['xpFrom'] < $xp && $gets['xpTo'] > $xp) && ($gets['coinsFrom'] < $coins && $gets['coinsTo'] > $coins)) {
        $last['final'] = $gets['levelFrom'];
        break;
      }
      if (($gets['xpFrom'] < $xp && $gets['xpTo'] > $xp) || ($gets['coinsFrom'] < $coins && $gets['coinsTo'] > $coins)) {
        $lastValue = $this->db->get_where($table, ['id' => $gets['id']])->row_array();

        $last['final'] = $lastValue['levelFrom'];
        break;
      }
    }
    // print_r($last);exit;

    return $last['final'];
  }

  public function giftNotification($regId, $message, $type, $loginId, $userId, $purchasedCoinstotal, $receivedCointotal)
  {
    $checkMuteNotifiaton = $this->db->get_where('muteUserNotification', array('userId' => $userId, 'muteId' => $loginId, 'status' => '1'))->row_array();
    if (empty($checkMuteNotifiaton)) {
      $registrationIds =  array($regId);
      define('API_ACCESS_KEY', 'AAAA0WgbM-c:APA91bGsdT5rx9u_QmaG9cjyapHiAY6NrzMw_WbMXgEuQi0f7ZbZ6-GCQZUDU6PvHY52KBBYwqC_Dxc9a22la0ad84p1tblRcK2evrYM2ONKU-Oa0xuL9d9jgkMrTynDadMO2OL1VIJz');
      $msg = array(
        'message'   => $message,
        'title'   => 'LiveBazaar',
        'type'    => $type,
        'subtitle'  => $type,
        'loginId' => $loginId,
        'userId' => $userId,
        'purchasedCoins' => $purchasedCoinstotal,
        'receivedCoin' => $receivedCointotal,
        'vibrate' => 1,
        'sound'   => 1,
        'largeIcon' => 'large_icon',
        'smallIcon' => 'small_icon',
      );
      $fields = array(
        'registration_ids'  => $registrationIds,
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
    }
  }

  public function purchaseCoin()
  {
    $data['userId'] = $this->input->post('userId');
    $data['coin'] = $this->input->post('coin');
    $data['coinPrice'] = $this->input->post('coinPrice');
    $data['orderId'] = $this->input->post('orderId');
    $data['transactionId'] = $this->input->post('transactionId');
    $data['created'] = date('Y-m-d H:i:s');
    $insert = $this->db->insert('userCoinHistory', $data);
    if (!empty($insert)) {
      $checkCoin = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
      $updateCoin['purchasedCoin'] = $this->input->post('coin') + $checkCoin['purchasedCoin'];
      $update = $this->Common_Model->update('users', $updateCoin, 'id', $this->input->post('userId'));
      $message['success'] = '1';
      $message['message'] = 'Coin added successfully';
      $message['coin'] = (string)$updateCoin['purchasedCoin'];
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function gift()
  {
    $list = $this->db->order_by('primeAccount', 'asc')->get_where('gift', array('status' => 'Approved'))->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      $coinList = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
      $message['coin'] = (string)$coinList['purchasedCoin'];
      foreach ($list as $lists) {
        if (empty($lists['image'])) {
          $lists['image'] = base_url() . 'uploads/no_image_available.png';
        } else {
          $lists['image'] = base_url() . $lists['image'];
        }
        $lists['merePaise'] = (string)$coinList['purchasedCoin'];
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No details found';
    }
    echo json_encode($message);
  }

  public function liveGiftCategory()
  {
    $list = $this->db->get_where('liveGiftCategory', array('status' => 'Approved'))->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      $message['details'] = $list;
    } else {
      $message['success'] = '0';
      $message['message'] = 'no list found';
    }
    echo json_encode($message);
  }

  public function liveGift()
  {

    $list = $this->db->get_where('livegift', array('status' => 'Approved', 'giftCategoryId' => $this->input->post('giftCategoryId')))->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      $coinList = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
      $message['coin'] = (string)$coinList['purchasedCoin'];
      foreach ($list as $lists) {
        if (empty($lists['image'])) {
          $lists['image'] = base_url() . 'uploads/no_image_available.png';
        } else {
          $urlCeck = $lists['image'];

          if (filter_var($urlCeck, FILTER_VALIDATE_URL)) {
            $lists['image'] = $lists['image'];
          } else {
            $lists['image'] = base_url() . $lists['image'];
          }
        }
        if (empty($lists['thumbnail'])) {
          $lists['thumbnail'] = base_url() . 'uploads/no_image_available.png';
        } else {
          $lists['thumbnail'] = $lists['thumbnail'];
        }
        $lists['merePaise'] = (string)$coinList['purchasedCoin'];
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No details found';
    }
    echo json_encode($message);
  }

  public function coinList()
  {
    $list = $this->db->order_by('coin', 'asc')->get_where('coin', array('status' => 'Approved'))->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      $message['key'] = 'rzp_live_2RIKadYieK4eVR';
      // $message['key'] = 'rzp_test_rVeycL8ovVMX2J';
      foreach ($list as $lists) {
        if (empty($lists['image'])) {
          $lists['image'] = base_url() . 'uploads/no_image_available.png';
        } else {
          $lists['image'] = base_url() . $lists['image'];
        }
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No details found';
    }
    echo json_encode($message);
  }

  public function getLive()
  {
    $list = $this->db->order_by('id', 'desc')->get_where('liveBroadcast')->result_array();
    if (!empty($list)) {
      $messgae['success'] = '1';
      $messgae['message'] = 'list found Successfully';
      $messgae['details'] = $list;
    } else {
      $messgae['success'] = '0';
      $messgae['message'] = 'No details found';
    }
    echo json_encode($messgae);
  }


  public function storeLiveBrodcasting()
  {
    if ($this->input->post()) {
      $url = "https://api.bambuser.com/broadcasts/" . $this->input->post('broadcast_id');
      $ch = curl_init();
      // Disable SSL verification
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      // Will return the response, if false it print the response
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // Set the url
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type:application/json',
        'Authorization:Bearer UeFDApXnoJcwP3q543TpTp',
        'Accept:application/vnd.bambuser.v1+json'
      ));
      curl_setopt($ch, CURLOPT_URL, $url);
      // Execute
      $result = curl_exec($ch);


      // Closing
      curl_close($ch);
      $resultss = json_decode($result);
      //print_r($resultss);die;
      $resultss->broadcast_id = $this->input->post('broadcast_id');
      $resultss->user_id = $this->input->post('userId');
      unset($resultss->id);
      $this->db->insert('liveBroadcast', $resultss);
      $insert_id = $this->db->insert_id();
      if ($insert_id) {
        $message = [
          'message' => 'live broadcast create successfully',
          'success' => '1'
        ];
      }
    } else {
      $message = [
        'message' => 'please enter parameters', // Automatically generated by the model
      ];
    }
    //header('Content-Type: application/json');
    echo json_encode($message);
  }




  public function followerBroadCast()
  {
    $follwerList = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'status' => '1'))->result_array();
    if (!empty($follwerList)) {
      foreach ($follwerList as $follwerLists) {
        $idList[] = $follwerLists['followingUserId'];
      }
      $url = "https://api.bambuser.com/broadcasts";
      $ch = curl_init();
      // Disable SSL verification
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      // Will return the response, if false it print the response
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // Set the url
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type:application/json',
        'Authorization:Bearer UeFDApXnoJcwP3q543TpTp',
        'Accept:application/vnd.bambuser.v1+json'
      ));
      curl_setopt($ch, CURLOPT_URL, $url);
      // Execute
      $result = curl_exec($ch);
      $json = json_decode($result);
      $bData = $json->results;
      foreach ($bData as $bDatas) {

        if ($bDatas->type == 'live' && in_array($bDatas->author, $idList)) {
          $bId = $bDatas->id;
          $list =  $this->db->query("SELECT liveBroadcast.*,users.name,users.username,users.image FROM liveBroadcast  left join users on users.id = liveBroadcast.user_id where liveBroadcast.broadcast_id = '$bId'")->row_array();
          if (empty($list['image'])) {
            $list['image'] = base_url() . 'uploads/no_image_available.png';
          }
          $finalData[] = $list;
        }
      }
      if (!empty($finalData)) {
        $message['success'] = '1';
        $message['message'] = 'List found successfully';
        $message['details'] = $finalData;
      } else {
        $message['success'] = '0';
        $message['message'] = 'No List Found';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No List Found';
    }
    echo json_encode($message);
  }



  public function stopLiveBroadcast()
  {
    $data['type'] = 'archived';
    $this->Common_Model->update('liveBroadcast', $data, 'broadcast_id', $this->input->post('broadcast_id'));
    $message['success'] = '1';
    $message['message'] = 'Status Update succssfully';
    echo json_encode($message);
  }


  public function getLiveBroadcast()
  {
    //$userId = $this->input->post('userId');
    // $list =  $this->db->query("SELECT liveBroadcast.*,users.name,users.username,users.image,userFollow.followingUserId FROM liveBroadcast left join userFollow on userFollow.followingUserId = liveBroadcast.user_id left join users on users.id = userFollow.followingUserId where userFollow.userId = $userId and userFollow.status = '1' and liveBroadcast.type = 'live' order by liveBroadcast.id desc")->result_array();
    // $list =  $this->db->query("SELECT liveBroadcast.*,users.name,users.username,users.image FROM liveBroadcast  left join users on users.id = liveBroadcast.user_id where liveBroadcast.type = 'live' order by liveBroadcast.id desc")->result_array();
    // if(!empty($list)){
    //   $message['success'] = '1';
    //   $message['message'] = 'list found successfully';
    //   foreach($list as $lists){
    //     if(empty($lists['image'])){
    //         $lists['image'] = base_url().'uploads/no_image_available.png';
    //     }
    //     $message['details'][] = $lists;
    //   }
    // }
    // else{
    //   $message['success'] = '0';
    //   $message['message'] = 'No list found';
    // }
    // echo json_encode($message);


    $url = "https://api.bambuser.com/broadcasts";
    $ch = curl_init();
    // Disable SSL verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // Will return the response, if false it print the response
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Set the url
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type:application/json',
      'Authorization:Bearer UeFDApXnoJcwP3q543TpTp',
      'Accept:application/vnd.bambuser.v1+json'
    ));
    curl_setopt($ch, CURLOPT_URL, $url);
    // Execute
    $result = curl_exec($ch);
    $json = json_decode($result);
    $bData = $json->results;
    foreach ($bData as $bDatas) {
      if ($bDatas->type == 'live') {
        $bId = $bDatas->id;
        $list =  $this->db->query("SELECT liveBroadcast.*,users.name,users.username,users.image FROM liveBroadcast  left join users on users.id = liveBroadcast.user_id where liveBroadcast.broadcast_id = '$bId'")->row_array();
        if (empty($list['image'])) {
          $list['image'] = base_url() . 'uploads/no_image_available.png';
        }
        $finalData[] = $list;
      }
    }
    if (!empty($finalData)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      $message['details'] = $finalData;
    } else {
      $message['success'] = '0';
      $message['message'] = 'No List Found';
    }
    echo json_encode($message);
  }


  public function banner()
  {
    $list = $this->db->get_where('slider', array('status' => 'Approved'))->result_array();
    if (!empty($list)) {
      $messgae['success'] = '1';
      $messgae['message'] = 'list found Successfully';
      foreach ($list as $lists) {
        if (!empty($lists['image'])) {
          $lists['image'] = base_url() . $lists['image'];
        } else {
          $lists['image'] = '';
        }
        $messgae['details'][] = $lists;
      }
    } else {
      $messgae['success'] = '0';
      $messgae['message'] = 'No details found';
    }
    echo json_encode($messgae);
  }

  public function categoryList()
  {
    $list = $this->db->get_where('category', array('status' => 'Approved'))->result_array();
    if (!empty($list)) {
      $messgae['success'] = '1';
      $messgae['message'] = 'list found Successfully';
      foreach ($list as $lists) {
        if (!empty($lists['image'])) {
          $lists['image'] = base_url() . $lists['image'];
        } else {
          $lists['image'] = '';
        }
        $messgae['details'][] = $lists;
      }
    } else {
      $messgae['success'] = '0';
      $messgae['message'] = 'No details found';
    }
    echo json_encode($messgae);
  }



  public function Photofit()
  {
    $photofit = $this->db->get_where('badges')->result_array();
    if (!empty($photofit)) {
      foreach ($photofit as $list) {
        $totalLikes = $list['likes'];
        $totalFollowers = $list['totalFollowers'];
        $userList = $this->db->query("SELECT * FROM `userProfileInformation` where likes >= $totalLikes and followers >= $totalFollowers ")->result_array();
        if (!empty($userList)) {
          foreach ($userList as $userL) {
            $type['badge'] = $list['title'];
            $update = $this->Common_Model->update('users', $type, 'id', $userL['userId']);
          }
        }
      }
    }
  }

  public function deleteAccountRequest()
  {
    $userId['userId'] = $this->input->post('userId');
    $userId['created'] = date('Y-m-d H:i:s');
    $this->db->insert('deleteAccountRequest', $userId);
    $message['success'] = '1';
    $message['message'] = 'Delete Account Request sent successfully';
    echo json_encode($message);
  }

  public function deleteAccountOtp()
  {
    $getUserDetails =  $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    $otp = rand(1000, 9999);
    if (!empty($getUserDetails['phone'])) {
      $phone = $getUserDetails['phone'];
      $mess = 'HI+' . $getUserDetails['username'] . ',+your+OTP+for+Delete+Account+is:+' . $otp;
      $created = date('Y-m-d+H:s:i');
      $url = "http://164.52.195.161/API/SendMsg.aspx?uname=20180144&pass=2kSqRn9p&send=INFSMS&dest=$phone&msg=$mess&priority=1&schtm=$created";
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $output = curl_exec($ch);
      curl_close($ch);
      $var = 'Phone Number';
    } else {
      $var = 'Email';
    }
    $message['success'] = '1';
    $message['message'] = 'OTP sent on your ' . $var;
    $message['otp'] = (string)$otp;
    echo json_encode($message);
  }

  public function privateAccountStatus()
  {
    $getUserDetails =  $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    if ($getUserDetails['privateAccount'] == 0) {
      $finalData['privateAccount'] = false;
    } else {
      $finalData['privateAccount'] = true;
    }
    if ($getUserDetails['followingUser'] == 1) {
      $finalData['followingViewStatus'] = true;
    } else {
      $finalData['followingViewStatus'] = false;
    }
    if ($getUserDetails['profilePhotoStatus'] == 1) {
      $finalData['profilePhotoStatus'] = true;
    } else {
      $finalData['profilePhotoStatus'] = false;
    }
    if ($getUserDetails['likeVideo'] == 0) {
      $finalData['likeVideo'] = false;
    } else {
      $finalData['likeVideo'] = true;
    }
    $message['success'] = '1';
    $message['message'] = 'List found successfully';
    $message['details'] = $finalData;
    echo json_encode($message);
  }

  public function videoNotification($vId, $uId)
  {
    $videoId = $vId;
    $userId = $uId;
    $lists = $this->db->get_where('userFollow', array('followingUserId' => $userId))->result_array();
    if (!empty($lists)) {
      foreach ($lists as $list) {
        $loginUserDetails = $this->db->get_where('users', array('id' => $userId))->row_array();
        $getUserId = $this->db->get_where('users', array('id' => $list['userId']))->row_array();
        $regId = $getUserId['reg_id'];
        $mess = $loginUserDetails['username'] . ' uploaded new video';
        if ($loginUserDetails['videoNotification'] == '1') {
          $this->notification($regId, $mess, 'video', $list['userId'], $userId);
        }
        $notiMess['loginId'] = $userId;
        $notiMess['userId'] = $list['userId'];
        $notiMess['videoId'] = $videoId;
        $notiMess['message'] = $mess;
        $notiMess['type'] = 'video';
        $notiMess['notiDate'] = date('Y-m-d');
        $notiMess['created'] = date('Y-m-d H:i:s');
        $this->db->insert('userNotification', $notiMess);
      }
    }
    return $userId;
  }

  public function forgotPass()
  {
    $emailPhone = $this->input->post('phone');
    $check = $this->db->get_where('users', array('phone' => $emailPhone))->row_array();
    if (!empty($check)) {
      $message['success'] = '1';
      $message['message'] = 'Otp sent to your ' . $type;
      $message['otp'] = (string)rand(1000, 9999);
    } else {
      $message['success'] = '0';
      $message['message'] = $type . ' doesnt exists';
    }
    echo json_encode($message);
  }

  public function updatePassword()
  {
    $emailPhone = $this->input->post('phone');
    $data['password'] = md5($this->input->post('password'));
    $update = $this->Common_Model->update('users', $data, 'phone', $emailPhone);
    if (!empty($update)) {
      $message['success'] = '1';
      $message['message'] = 'Password updated successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }



  public function addFavoriteSounds()
  {
    if ($this->input->post()) {
      $deta = $this->db->get_where("favouriteSoundList", array('soundId' => $this->input->post('soundId'), 'userId' => $this->input->post('userId')))->row_array();
      $data['userId'] = $this->input->post('userId');
      $data['soundId'] = $this->input->post('soundId');
      if (empty($deta)) {
        $data['status'] = '1';
        $data['created'] = date("Y-m-d H:i:s");
        $in = $this->db->insert("favouriteSoundList", $data);
      } else {
        $data['status'] = ($deta['status'] == '0') ? '1' : '0';
        $data['updated'] = date("Y-m-d H:i:s");
        $in = $this->db->update("favouriteSoundList", $data, array('soundId' => $this->input->post('soundId'), 'userId' => $this->input->post('userId')));
      }
      if ($in) {
        $message['success'] = '1';
        $message['message'] = 'Added to favorites';
      } else {
        $message['success'] = '0';
        $message['message'] = 'Please try again';
      }
    } else {
      $message['message'] = 'Please enter parameters';
    }
    echo json_encode($message);
  }

  public function followerDelete()
  {
    $delte = $this->db->delete('userFollow', array('id' => $this->input->post('id')));
    if (!empty($delte)) {
      $message['success'] = '1';
      $message['message'] = 'User Remove from list successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function otherFollowingList()
  {
    $lists = $this->Common_Model->followingUser($this->input->post('userId'));
    if (!empty($lists)) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      foreach ($lists as $list) {
        $checkStataus = $this->db->get_where('userFollow', array('followingUserId' => $list['followingUserId'], 'userId' => $this->input->post('ownerId'), 'status' => '1'))->row_array();
        if (!empty($checkStataus)) {
          $list['friendStatus'] = true;
        } else {
          $list['friendStatus'] = false;
        }
        if (empty($list['image'])) {
          $list['image'] =  base_url() . 'uploads/no_image_available.png';
        }
        $message['details'][] = $list;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'no list found';
    }
    echo json_encode($message);
  }

  public function otherFollowerList()
  {
    $lists = $this->Common_Model->followerUser($this->input->post('userId'));
    if (!empty($lists)) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      foreach ($lists as $list) {
        $checkStataus = $this->db->get_where('userFollow', array('followingUserId' => $this->input->post('ownerId'), 'userId' => $list['userId'], 'status' => '1'))->row_array();
        if (!empty($checkStataus)) {
          $list['friendStatus'] = true;
        } else {
          $list['friendStatus'] = false;
        }
        if (empty($list['image'])) {
          $list['image'] =  base_url() . 'uploads/no_image_available.png';
        }
        $message['details'][] = $list;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'no list found';
    }
    echo json_encode($message);
  }

  public function followingList()
  {
    $lists = $this->Common_Model->followingUser($this->input->post('userId'));
    if (!empty($lists)) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      foreach ($lists as $list) {
        $checkStataus = $this->db->get_where('userFollow', array('userId' => $list['followingUserId'], 'followingUserId' => $this->input->post('userId'), 'status' => '1'))->row_array();
        $userInfo = $this->db->get_where('userProfileInformation', array('userId' => $list['followingUserId']))->row_array();
        $list['likeCount'] = $userInfo['likes'];
        if (!empty($checkStataus)) {
          $list['friendStatus'] = true;
        } else {
          $list['friendStatus'] = false;
        }
        if (empty($list['image'])) {
          $list['image'] =  base_url() . 'uploads/no_image_available.png';
        }
        $message['details'][] = $list;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'no list found';
    }
    echo json_encode($message);
  }


  public function followerList()
  {
    $lists = $this->Common_Model->followerUser($this->input->post('userId'));
    if (!empty($lists)) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      foreach ($lists as $list) {
        $checkStataus = $this->db->get_where('userFollow', array('userId' => $list['followingUserId'], 'followingUserId' => $list['userId'], 'status' => '1'))->row_array();
        if (!empty($checkStataus)) {
          $list['friendStatus'] = true;
        } else {
          $list['friendStatus'] = false;
        }
        if (empty($list['image'])) {
          $list['image'] = base_url() . 'uploads/no_image_available.png';
        }
        $message['details'][] = $list;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'no list found';
    }
    echo json_encode($message);
  }

  public function videoDelete()
  {
    $delte = $this->db->delete('userVideos', array('id' => $this->input->post('videoId')));
    if (!empty($delte)) {
      $this->db->delete('videoComments', array('videoId' => $this->input->post('videoId')));
      $this->db->delete('videoLikeOrUnlike', array('videoId' => $this->input->post('videoId')));
      $this->db->delete('videoSubComment', array('videoId' => $this->input->post('videoId')));
      $this->db->delete('viewVideo', array('videoId' => $this->input->post('videoId')));
      $this->db->delete('userNotification', array('videoId' => $this->input->post('videoId')));
      $message['success'] = '1';
      $message['message'] = 'Video Delete successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }
  public function commentDelete()
  {
    $delte = $this->db->delete('videoComments', array('id' => $this->input->post('commentId')));
    if (!empty($delte)) {
      $this->db->delete('videoCommentsLikeOrUnlike', array('commentId' => $this->input->post('commentId')));
      // $checkCommentCount = $this->db->get_where('userVideos',array('id' =>$this->input->post('videoId')))->row_array();
      // $checkSubCommentCount = $this->db->get_where('videoSubComment',array('commentId' =>$this->input->post('commentId')))->num_rows();
      // if(!empty($checkSubCommentCount)){
      //  $finalCommentCount =  $checkCommentCount['commentCount'] + $checkSubCommentCount;
      // }
      // else{
      //  $finalCommentCount =  $checkCommentCount['commentCount'];
      // }
      $this->db->set('commentCount', 'commentCount -1', false)->where('id', $this->input->post('videoId'))->update("userVideos");
      $getdata = $this->db->get_where('userVideos', ['id' => $this->input->post('videoId')])->row_array();
      $this->db->delete('videoSubComment', array('commentId' => $this->input->post('commentId')));
      $message['success'] = '1';
      $message['message'] = 'Comment Delete successfully';
      $message['details'] = $getdata;
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }


  public function sohitTime($time)
  {
    $timeDiff = time() - strtotime($time);
    $nYears = (int)($timeDiff / (60 * 60 * 24 * 365));
    $nMonths = (int)(($timeDiff % (60 * 60 * 24 * 365)) / (60 * 60 * 24 * 30));
    $nDays = (int)((($timeDiff % (60 * 60 * 24 * 365)) % (60 * 60 * 24 * 30)) / (60 * 60 * 24));
    $nHours = (int)(((($timeDiff % (60 * 60 * 24 * 365)) % (60 * 60 * 24 * 30)) % (60 * 60 * 24)) / (60 * 60));
    $nMinutes = (int)((((($timeDiff % (60 * 60 * 24 * 365)) % (60 * 60 * 24 * 30)) % (60 * 60 * 24)) % (60 * 60)) / (60));
    $timeMsg = "";
    if ($nYears > 0) {
      $yearWord = "year";
      if ($nYears == 1) {
        $yearWord = "year";
      }
      $timeMsg = "$nYears $yearWord";
    } elseif ($nMonths > 0) {
      $monthWord = "month";
      if ($nMonths == 1) {
        $monthWord = "month";
      }
      $timeMsg = "$nMonths $monthWord";
    } elseif ($nDays > 0) {
      $dayWord = "day";
      if ($nDays == 1) {
        $dayWord = "day";
      }
      $timeMsg = "$nDays $dayWord";
    } elseif ($nHours > 0) {
      $hourWord = "hour";
      if ($nHours == 1) {
        $hourWord = "hour";
      }
      $timeMsg = "$nHours $hourWord";
    } elseif ($nMinutes > 0) {
      $minuteWord = "min";
      if ($nMinutes == 1) {
        $minuteWord = "min";
      }
      $timeMsg = "$nMinutes $minuteWord";
    } else {
      $timeMsg = "just now";
    }
    return $timeMsg;
  }


  public function getTime($time)
  {
    $timeDiff = time() - strtotime($time);
    $nYears = (int)($timeDiff / (60 * 60 * 24 * 365));
    $nMonths = (int)(($timeDiff % (60 * 60 * 24 * 365)) / (60 * 60 * 24 * 30));
    $nDays = (int)((($timeDiff % (60 * 60 * 24 * 365)) % (60 * 60 * 24 * 30)) / (60 * 60 * 24));
    $nHours = (int)(((($timeDiff % (60 * 60 * 24 * 365)) % (60 * 60 * 24 * 30)) % (60 * 60 * 24)) / (60 * 60));
    $nMinutes = (int)((((($timeDiff % (60 * 60 * 24 * 365)) % (60 * 60 * 24 * 30)) % (60 * 60 * 24)) % (60 * 60)) / (60));
    $timeMsg = "";
    if ($nYears > 0) {
      $yearWord = "y";
      if ($nYears == 1) {
        $yearWord = "y";
      }
      $timeMsg = "$nYears $yearWord";
    } elseif ($nMonths > 0) {
      $monthWord = "m";
      if ($nMonths == 1) {
        $monthWord = "m";
      }
      $timeMsg = "$nMonths $monthWord";
    } elseif ($nDays > 0) {
      $dayWord = "d";
      if ($nDays == 1) {
        $dayWord = "d";
      }
      $timeMsg = "$nDays $dayWord";
    } elseif ($nHours > 0) {
      $hourWord = "h";
      if ($nHours == 1) {
        $hourWord = "h";
      }
      $timeMsg = "$nHours $hourWord";
    } elseif ($nMinutes > 0) {
      $minuteWord = "min";
      if ($nMinutes == 1) {
        $minuteWord = "min";
      }
      $timeMsg = "$nMinutes $minuteWord";
    } else {
      $timeMsg = "just now";
    }
    return $timeMsg;
  }


  public function manavTime($time)
  {
    $timeDiff = time() - strtotime($time);
    $nYears = (int)($timeDiff / (60 * 60 * 24 * 365));
    $nMonths = (int)(($timeDiff % (60 * 60 * 24 * 365)) / (60 * 60 * 24 * 30));
    $nDays = (int)((($timeDiff % (60 * 60 * 24 * 365)) % (60 * 60 * 24 * 30)) / (60 * 60 * 24));
    $nHours = (int)(((($timeDiff % (60 * 60 * 24 * 365)) % (60 * 60 * 24 * 30)) % (60 * 60 * 24)) / (60 * 60));
    $nMinutes = (int)((((($timeDiff % (60 * 60 * 24 * 365)) % (60 * 60 * 24 * 30)) % (60 * 60 * 24)) % (60 * 60)) / (60));
    $timeMsg = "";
    if ($nYears > 0) {
      $yearWord = "year";
      if ($nYears == 1) {
        $yearWord = "year";
      }
      $timeMsg = "$nYears $yearWord";
    } elseif ($nMonths > 0) {
      $monthWord = "month";
      if ($nMonths == 1) {
        $monthWord = "month";
      }
      $timeMsg = "$nMonths $monthWord";
    } elseif ($nDays > 0) {
      $dayWord = "day";
      if ($nDays == 1) {
        $dayWord = "day";
      }
      $timeMsg = "$nDays $dayWord";
    } elseif ($nHours > 0) {
      $hourWord = "hour";
      if ($nHours == 1) {
        $hourWord = "hour";
      }
      $timeMsg = "$nHours $hourWord";
    } elseif ($nMinutes > 0) {
      $minuteWord = "min";
      if ($nMinutes == 1) {
        $minuteWord = "min";
      }
      $timeMsg = "$nMinutes $minuteWord";
    } else {
      $timeMsg = "just now";
    }
    return $timeMsg;
  }

  public function userNotification()
  {
    $countMessage = $this->db->get_where('conversation', array('reciver_id' => $this->input->post('userId'), 'readStatus' => 0))->num_rows();
    if (!empty($countMessage)) {
      $message['messageCount'] = (string)$countMessage;
    } else {
      $message['messageCount'] = "0";
    }
    $upsttt['status'] = 1;
    $update = $this->Common_Model->update('userNotification', $upsttt, 'userId', $this->input->post('userId'));
    $lists = $this->Common_Model->userNotification($this->input->post('userId'), $this->input->post('type'));
    if (!empty($lists)) {
      $todayDate = date('Y-m-d');
      $datetime = new DateTime($todayDate);
      $datetime->modify('-1 day');
      $yestradyDate =  $datetime->format('Y-m-d');
      foreach ($lists as $list) {
        $message['success'] = '1';
        $message['message'] = 'Details Found Successfully';
        if ($list['notiDate'] == $todayDate) {
          if (!empty($this->input->post('type'))) {
            $noti = $this->db->order_by('id', 'desc')->get_where('userNotification', array('notiDate' => $list['notiDate'], 'userId' => $this->input->post('userId'), 'type' => $this->input->post('type')))->result_array();
          } else {
            $noti = $this->db->order_by('id', 'desc')->get_where('userNotification', array('notiDate' => $list['notiDate'], 'userId' => $this->input->post('userId')))->result_array();
          }

          foreach ($noti as $notis) {
            $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $notis['loginId']))->row_array();
            if (!empty($checkFollow)) {
              if ($checkFollow['status'] == '1') {
                $notis['followStatus'] = true;
              } else {
                $notis['followStatus'] = false;
              }
            } else {
              $notis['followStatus'] = false;
            }
            $notis['time'] = $this->manavTime($notis['created']);
            $userDetails = $this->db->get_where('users', array('id' => $notis['loginId']))->row_array();
            if (!empty($userDetails['username'])) {
              $notis['username'] = $userDetails['username'];
            } else {
              $notis['username']  = $userDetails['name'];
            }
            if (empty($userDetails['image'])) {
              $notis['image'] = base_url() . 'uploads/no_image_available.png';
            } else {
              $notis['image'] = $userDetails['image'];
            }
            $videoDetails = $this->db->get_where('userVideos', array('id' => $notis['videoId']))->row_array();
            if (empty($videoDetails['videoPath'])) {
              $notis['video'] = '';
            } else {
              $notis['video'] = $videoDetails['videoPath'];
            }
            $manav[] = $notis;
          }
          $data['day'] = 'Today';
          $data['listdetails'] = $manav;
          unset($manav);
        } elseif ($list['notiDate'] == $yestradyDate) {
          if (!empty($this->input->post('type'))) {
            $noti = $this->db->get_where('userNotification', array('notiDate' => $list['notiDate'], 'userId' => $this->input->post('userId'), 'type' => $this->input->post('type')))->result_array();
          } else {
            $noti = $this->db->get_where('userNotification', array('notiDate' => $list['notiDate'], 'userId' => $this->input->post('userId')))->result_array();
          }


          foreach ($noti as $notis) {
            $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $notis['loginId']))->row_array();
            if (!empty($checkFollow)) {
              if ($checkFollow['status'] == '1') {
                $notis['followStatus'] = true;
              } else {
                $notis['followStatus'] = false;
              }
            } else {
              $notis['followStatus'] = false;
            }
            $notis['time'] = $this->manavTime($notis['created']);
            $userDetails = $this->db->get_where('users', array('id' => $notis['loginId']))->row_array();
            if (!empty($userDetails['username'])) {
              $notis['username'] = $userDetails['username'];
            } else {
              $notis['username']  = $userDetails['name'];
            }
            if (empty($userDetails['image'])) {
              $notis['image'] =  base_url() . 'uploads/no_image_available.png';
            } else {
              $notis['image'] = $userDetails['image'];
            }
            $videoDetails = $this->db->get_where('userVideos', array('id' => $notis['videoId']))->row_array();
            if (empty($videoDetails['videoPath'])) {
              $notis['video'] = '';
            } else {
              $notis['video'] = $videoDetails['videoPath'];
            }
            $manav[] = $notis;
          }
          $data['day'] = 'Yesterday';
          $data['listdetails'] = $manav;
          unset($manav);
        } else {
          if (!empty($this->input->post('type'))) {
            $noti = $this->db->get_where('userNotification', array('notiDate' => $list['notiDate'], 'userId' => $this->input->post('userId'), 'type' => $this->input->post('type')))->result_array();
          } else {
            $noti = $this->db->get_where('userNotification', array('notiDate' => $list['notiDate'], 'userId' => $this->input->post('userId')))->result_array();
          }
          foreach ($noti as $notis) {
            $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $notis['loginId']))->row_array();
            if (!empty($checkFollow)) {
              if ($checkFollow['status'] == '1') {
                $notis['followStatus'] = true;
              } else {
                $notis['followStatus'] = false;
              }
            } else {
              $notis['followStatus'] = false;
            }
            $notis['time'] = $this->manavTime($notis['created']);
            $userDetails = $this->db->get_where('users', array('id' => $notis['loginId']))->row_array();
            if (!empty($userDetails['username'])) {
              $notis['username'] = $userDetails['username'];
            } else {
              $notis['username']  = $userDetails['name'];
            }
            if (empty($userDetails['image'])) {
              $notis['image'] =  base_url() . 'uploads/no_image_available.png';
            } else {
              $notis['image'] = $userDetails['image'];
            }
            $videoDetails = $this->db->get_where('userVideos', array('id' => $notis['videoId']))->row_array();
            if (empty($videoDetails['videoPath'])) {
              $notis['video'] = '';
            } else {
              $notis['video'] = $videoDetails['videoPath'];
            }
            $manav[] = $notis;
          }
          $date11 = date_create($list['notiDate']);
          $dateTitle =  date_format($date11, "d M Y");
          $data['day'] = $this->manavTime($list['created']);;
          $data['listdetails'] = $manav;
          unset($manav);
        }

        $message['details'][] = $data;
        unset($data);
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No List found';
    }
    echo json_encode($message);
  }


  public function notification($regId, $message, $type, $loginId, $userId)
  {
    $checkMuteNotifiaton = $this->db->get_where('muteUserNotification', array('userId' => $userId, 'muteId' => $loginId, 'status' => '1'))->row_array();
    if (empty($checkMuteNotifiaton)) {
      $registrationIds =  array($regId);
      define('API_ACCESS_KEY', 'AAAA0WgbM-c:APA91bGsdT5rx9u_QmaG9cjyapHiAY6NrzMw_WbMXgEuQi0f7ZbZ6-GCQZUDU6PvHY52KBBYwqC_Dxc9a22la0ad84p1tblRcK2evrYM2ONKU-Oa0xuL9d9jgkMrTynDadMO2OL1VIJz');
      $msg = array(
        'message'   => $message,
        'title'   => 'LiveBazaar',
        'type'    => $type,
        'subtitle'  => $type,
        'loginId' => $loginId,
        'userId' => $userId,
        'vibrate' => 1,
        'sound'   => 1,
        'largeIcon' => 'large_icon',
        'smallIcon' => 'small_icon',
      );
      $fields = array(
        'registration_ids'  => $registrationIds,
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
    }
  }

  public function userFollow()
  {

    $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
    if (empty($checkUser)) {
      echo json_encode([
        'status' => '0',
        'message' => 'invalid userId'
      ]);
      exit;
    }

    $get = $this->db->get_where('users', ['id' => $this->input->post('followingUserId')])->row_array();
    if (empty($get)) {
      echo json_encode([
        'status' => '0',
        'message' => 'invalid followingUserId'
      ]);
      exit;
    }


    // ================ if following userId account is private ===================== 

    if ($this->input->post('type') == 1) {

      $checkRequest = $this->db->get_where('userFollowRequest', ['userId' => $this->input->post('userId'), 'AcceptUserId' => $this->input->post('followingUserId')])->row_array();
      if (!!$checkRequest) {
        echo json_encode([
          'success' => '0',
          'message' => 'Request Sent Already',
          'requsetId' => $checkRequest['id']
        ]);
        exit;
      }

      $requestData = [
        'userId' => $this->input->post('userId'),
        'AcceptUserId' =>  $this->input->post('followingUserId'),
      ];

      $this->db->insert('userFollowRequest', $requestData);
      $insertId = $this->db->insert_id();

      if ($insertId) {

        $title = $checkUser['name'];
        $message = $checkUser['username'] . " sent you follow request.";
        $type = 'follow_request';
        $imgpath = $checkUser['image'];
        pushNotification($get['reg_id'], $message, $title, $type, $imgpath);

        echo json_encode([
          'success' => '1',
          'message' => 'follow request sent',
          'requsetId' => $insertId
        ]);
        exit;
      }
    }

    // ================== if following userId account is public ===================


    $check_like =  $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $this->input->post('followingUserId')))->row_array();
    if (!empty($check_like)) {
      if ($check_like['status'] == '0') {
        $status = '1';
      } else {
        $status = '0';
      }
      $data = array(
        'userId' => $this->input->post('userId'),
        'followingUserId' => $this->input->post('followingUserId'),
        'status' => $status,
        'updated' => date('y-m-d h:i:s')
      );
      $update = $this->Common_Model->update('userFollow', $data, 'id', $check_like['id']);
    } else {
      $status = '1';
      $data = array(
        'userId' => $this->input->post('userId'),
        'followingUserId' => $this->input->post('followingUserId'),
        'status' => $status,
        'created' => date('y-m-d h:i:s')
      );
      $insert = $this->db->insert('userFollow', $data);
      $insert_id = $this->db->insert_id();
    }
    $likeInformation = $this->db->get_where('userProfileInformation', array('userId' => $this->input->post('followingUserId')))->row_array();
    if (empty($check_like)) {
      $userProfile['followers'] = 1 + $likeInformation['followers'];
      $message123 = 'user following successfully';
      $sendStatus = true;
    } else {
      if ($status == '0') {
        $userProfile['followers'] = $likeInformation['followers'] - 1;
        $message123 = 'user unfollowing successfully';
        $sendStatus = false;
      } else {
        $userProfile['followers'] = 1 + $likeInformation['followers'];
        $message123 = 'user following successfully';
        $sendStatus = true;
      }
    }
    $UserDetails = $this->db->get_where('users', array('id' => $this->input->post('followingUserId')))->row_array();
    if ($status == '1') {
      $loginUserDetails = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
      $mess = $loginUserDetails['username'] . " started following you";
      $regId = $UserDetails['reg_id'];
      if ($loginUserDetails['followersNotification'] == '1') {

        $title = $loginUserDetails['name'];
        $message = $loginUserDetails['username'] . " started following you";
        $type = 'follow_request';
        $imgpath = $loginUserDetails['image'];
        pushNotification($regId, $message, $title, $type, $imgpath);
      }
      $notiMess['loginId'] = $this->input->post('userId');
      $notiMess['userId'] = $this->input->post('followingUserId');
      $notiMess['message'] = $mess;
      $notiMess['type'] = 'follow';
      $notiMess['notiDate'] = date('Y-m-d');
      $notiMess['created'] = date('Y-m-d H:i:s');
      $this->db->insert('userNotification', $notiMess);

      $upFollowStatus['followerCount'] = $UserDetails['followerCount'] + 1;
    } else {
      $upFollowStatus['followerCount'] = $UserDetails['followerCount'] - 1;
    }

    $this->Common_Model->update('users', $upFollowStatus, 'id', $this->input->post('followingUserId'));


    $update = $this->Common_Model->update('userProfileInformation', $userProfile, 'id', $likeInformation['id']);
    $likeCount = $this->db->get_where('userFollow', array('followingUserId' => $this->input->post('followingUserId'), 'status' => '1'))->num_rows();
    $successmessage = array(
      'success' => '1',
      'message' => $message123,
      'following_status' => $sendStatus,
      'following_count' => (string)$likeCount
    );
    echo json_encode($successmessage);
  }

  public function followRequestAccept()
  {
    if ($this->input->post()) {

      if ($this->input->post('type') == '1') {
        $data['type'] = '1';

        $update = $this->db->set($data)->where(['userId' => $this->input->post('userId'), 'AcceptUserId' => $this->input->post('AcceptUserId')])->update('userFollowRequest');
        // print_r($this->db->last_query());exit;

        if ($update) {

          $where = '(status = "1" or status = "0")';
          // $checkFollowHistory = $this->db->get_where('userFollow', ['userId' => $this->input->post('AcceptUserId'), 'followingUserId' => $this->input->post('userId'), 'status' => '1'])->row_array();
          $checkFollowHistory = $this->db->select('userFollow.*')
            ->from('userFollow')
            ->where('userId', $this->input->post('AcceptUserId'))
            ->where('followingUserId', $this->input->post('userId'))
            ->where($where)
            ->get()->row_array();
          //  print_r($this->db->last_query());exit;
          if (!!$checkFollowHistory) {

            // print_r("hi");exit;
            $updata['status'] = '1';
            $updateuserfollow = $this->db->set($updata)->where(['userId' => $this->input->post('AcceptUserId'), 'followingUserId' => $this->input->post('userId')])->update('userFollow');

            if ($updateuserfollow) {
              echo json_encode([
                'status' => 1,
                'message' => 'Request Accepted'
              ]);
              exit;
            }
          }
          $mainData['userId'] = $this->input->post('userId');
          $mainData['followingUserId'] = $this->input->post('AcceptUserId');
          $mainData['status'] = '1';

          if ($this->db->insert('userFollow', $mainData)) {
            echo json_encode([
              'status' => 1,
              'message' => 'Request Accepted'
            ]);
            exit;
          }
        }
      } else if ($this->input->post('type') == '2') {

        $data['type'] = 2;

        $update = $this->db->set($data)->where(['userId' => $this->input->post('userId'), 'AcceptUserId' => $this->input->post('AcceptUserId')])->update('userFollowRequest');

        if ($update) {

          echo json_encode([
            'status' => 2,
            'message' => 'Request Rejected'
          ]);
          exit;
        }
      }
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'post request required'
      ]);
    }
  }

  public function getFollowRequest()
  {
    if ($this->input->post()) {

      // $get = $this->db->get_where('userFollowRequest', ['userId' => $this->input->post('userId'), 'type' => '0'])->result_array();
      $get = $this->db->select('userFollowRequest.id requestID, userFollowRequest.AcceptUserId, users.*')
        ->from('userFollowRequest')
        ->join('users', 'users.id = userFollowRequest.AcceptUserId', 'left')
        ->where('userId', $this->input->post('userId'))
        ->where('type', '0')
        ->get()->result_array();

      if (!!$get) {

        echo json_encode([
          'status' => 1,
          'message' => 'Follow Request List Found',
          'details' => $get
        ]);
        exit;
      }

      echo json_encode([
        'status' => 0,
        'message' => 'No Request found'
      ]);
      exit;
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid data'
      ]);
    }
  }

  public function userInfo()
  {
    $countNotification = $this->db->get_where('userNotification', array('userId' => $this->input->post('userId'), 'status' => 0))->num_rows();
    if (!empty($countNotification)) {
      $message['notificationCount'] = (string)$countNotification;
    } else {
      $message['notificationCount'] = '0';
    }
    $getUserDetails =  $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    if (empty($getUserDetails) || $getUserDetails['status'] == 'pending') {
      $message['success'] = '5';
      $message['message'] = 'Your Account has been declined. Please contact support@LiveBazaar.com';
    } else {
      $checkMuteNotification =  $this->db->get_where('muteUserNotification', array('userId' => $this->input->post('loginId'), 'muteId' =>  $this->input->post('userId'), 'status' => '1'))->row_array();
      if (!empty($checkMuteNotification)) {
        $finalData['muteStatus'] = true;
      } else {
        $finalData['muteStatus'] = false;
      }
      if (!empty($getUserDetails['coin'])) {
        $finalData['coin'] = $getUserDetails['coin'];
      } else {
        $finalData['coin'] = '0';
      }
      if (!empty($getUserDetails['purchasedCoin'])) {
        $finalData['purchasedCoin'] = $getUserDetails['purchasedCoin'];
      } else {
        $finalData['purchasedCoin'] = '0';
      }
      $userId = $this->input->post('userId');
      $sendBeans =  $this->db->query("SELECT sum(coins) as beans FROM `userGiftHistory` where userId = $userId")->row_array();
      if (!empty($sendBeans)) {
        $finalData['sendBeans'] = $sendBeans['beans'];
      } else {
        $finalData['sendBeans'] = '0';
      }
      if (!empty($getUserDetails['dob'])) {
        $from = new DateTime($getUserDetails['dob']);
        $to   = new DateTime('today');
        $finalData['age'] = (string)$from->diff($to)->y;
      } else {
        $finalData['age'] = '0';
      }
      $todyDD = date('Y-m-d');
      $checkStarStatus = $this->db->get_where('userStar', array('userId' => $this->input->post('userId'), 'created' => $todyDD))->row_array();
      if (!empty($checkStarStatus)) {
        $starStatus = $checkStarStatus['star'];
      } else {
        $starStatus = '0';
      }

      if (!empty($getUserDetails['leval'])) {
        $levalImages = $this->db->get_where('leval', array('leval' => $getUserDetails['leval']))->row_array();
        if (!empty($levalImages['image'])) {
          $finalData['animationImage'] = base_url() . $levalImages['image'];
          $finalData['animationThumbnail'] = base_url() . $levalImages['thumbnail'];
          $finalData['animationTitle'] = $levalImages['title'];
        } else {
          $finalData['animationImage'] = '';
          $finalData['animationThumbnail'] = '';
          $finalData['animationTitle'] = '';
        }
      } else {
        $finalData['animationImage'] = '';
        $finalData['animationThumbnail'] = '';
        $finalData['animationTitle'] = '';
      }
      $checkLiveStatus = $this->db->order_by('id', 'desc')->get_where('userLive', array('userId' => $this->input->post('userId'), 'status' => 'live'))->row_array();
      if (!empty($checkLiveStatus)) {
        $finalData['liveStatus'] = '1';
        $finalData['liveId'] = $checkLiveStatus['id'];
        $finalData['livehostType'] = $checkLiveStatus['hostType'];
        $finalData['livechannelName'] = $checkLiveStatus['channelName'];
        $finalData['livetoken'] = $checkLiveStatus['token'];
        $finalData['livelatitude'] = $checkLiveStatus['latitude'];
        $finalData['livelongitude'] = $checkLiveStatus['longitude'];
        $finalData['livertmToken'] = $checkLiveStatus['rtmToken'];
      } else {
        $finalData['liveStatus'] = '0';
        $finalData['liveId'] = '';
        $finalData['livehostType'] = '';
        $finalData['livechannelName'] = '';
        $finalData['livetoken'] = '';
        $finalData['livelatitude'] = '';
        $finalData['livelongitude'] = '';
        $finalData['livertmToken'] = '';
      }

      $gifSelect = $this->db->get_where('logo', array('id' => 3))->row_array();

      $finalData['username'] = $getUserDetails['username'];
      $finalData['usernameStatus'] = $getUserDetails['usernameChangeStatus'];
      $finalData['name'] = $getUserDetails['name'];
      $finalData['phone'] = $getUserDetails['phone'];
      $finalData['bio'] = $getUserDetails['bio'];
      $finalData['userLeval'] = $getUserDetails['leval'];
      $finalData['gender'] = $getUserDetails['gender'];
      $finalData['country'] = $getUserDetails['country'];
      $finalData['dob'] = $getUserDetails['dob'];
      $chekVerifactionStatus = $this->db->get_where('registerUserInfo', array('userId' => $this->input->post('userId')))->row_array();
      if (!empty($chekVerifactionStatus)) {
        $finalData['verifactionStatus'] = $chekVerifactionStatus['status'];
      } else {
        $finalData['verifactionStatus'] = '0';
      }

      $finalData['starCount'] = $starStatus;
      $finalData['skipImage'] = base_url() . $gifSelect['img'];
      //$finalData['skipImage'] = 'https://i.pinimg.com/originals/6d/b9/88/6db988869c105086253a0c388796e1ea.gif';
      if (!empty($getUserDetails['badge'])) {
        $badgeinfo = $this->db->get_where('badges', array('id' => $getUserDetails['badge']))->row_array();
        $finalData['badgeStatus'] = true;
        $finalData['badgeImage'] = $badgeinfo['image'];
        $finalData['badgeTitle'] = $badgeinfo['title'];
      } else {
        $finalData['badgeStatus'] = false;
        $finalData['badgeImage'] = '';
        $finalData['badgeTitle'] = '';
      }
      if (!empty($getUserDetails['crown'])) {
        $crowninfo = $this->db->get_where('crown', array('id' => $getUserDetails['crown']))->row_array();
        $finalData['crownStatus'] = true;
        $finalData['crownImage'] = $crowninfo['image'];
        $finalData['crownTitle'] = $crowninfo['title'];
      } else {
        $finalData['crownStatus'] = false;
        $finalData['crownImage'] = '';
        $finalData['crownTitle'] = '';
      }
      if ($getUserDetails['followingUser'] == 1) {
        $finalData['followingViewStatus'] = false;
      } else {
        $finalData['followingViewStatus'] = true;
      }
      if ($getUserDetails['profilePhotoStatus'] == 1) {
        $finalData['profilePhotoStatus'] = false;
      } else {
        $finalData['profilePhotoStatus'] = true;
      }
      if (empty($getUserDetails['image'])) {
        $finalData['image'] = base_url() . 'uploads/no_image_available.png';
      } else {
        $finalData['image'] = $getUserDetails['image'];
      }
      if (!empty($getUserDetails['video'])) {
        $finalData['video'] = $getUserDetails['video'];
      } else {
        $finalData['video'] = '';
      }
      if ($getUserDetails['privateAccount'] == 0) {
        $finalData['privateAccount'] = false;
      } else {
        $finalData['privateAccount'] = true;
      }
      if ($getUserDetails['likeVideo'] == 0) {
        $finalData['likeVideo'] = false;
      } else {
        $finalData['likeVideo'] = true;
      }
      $list =  $this->db->get_where('userProfileInformation', array('userId' => $this->input->post('userId')))->row_array();

      // $list = $this->db->select("userProfileInformation.*,users.country,users.gender")
      // ->from("userProfileInformation")
      // ->join("users","users.id = userProfileInformation.userId","left")
      // ->where("userProfileInformation.userId",$this->input->post('userId'))
      // ->get()
      // ->row_array();


      if (!empty($list)) {
        $finalData['followers'] = $list['followers'];
        $finalData['likes'] = $list['likes'];
        $finalData['videoCount'] = $list['videoCount'];
        // $finalData['country'] = $list['country'];
        // $finalData['gender'] = $list['gender'];
      } else {
        $finalData['followers'] = "0";
        $finalData['likes'] = "0";
        $finalData['videoCount'] = "0";
        // $finalData['country'] = "";
        // $finalData['gender'] = "";
      }

      $checkLiveBlock = $this->db->get_where('banLiveUser', array('userIdLive' => $this->input->post('loginId'), 'userIdViewer' => $this->input->post('userId')))->row_array();
      if (!empty($checkLiveBlock)) {
        $finalData['liveBanUserStatus'] = true;
      } else {
        $finalData['liveBanUserStatus'] = false;
      }
      $countFollwers = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'status' => '1'))->num_rows();
      if (!empty($countFollwers)) {
        $finalData['following'] = (string)$countFollwers;
      } else {
        $finalData['following'] = "0";
      }
      $userId = $this->input->post('userId');
      $selectFollowProvider = $this->db->query("SELECT a.*,b.*,users.id as uId,users.name as uname,users.image as userImage,users.country from userFollow as a LEFT JOIN userFollow as b on b.userId=a.followingUserId and b.followingUserId=$userId left join users on users.id=a.followingUserId where a.userId=$userId and a.status='1' HAVING a.followingUserId = b.userId and b.status='1'")->num_rows();
      if (!empty($selectFollowProvider)) {
        $finalData['friendCount'] = (string)$selectFollowProvider;
        $finalData['country'] = $selectFollowProvider['country'];
      } else {
        $finalData['friendCount'] = "0";
      }


      $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('loginId'), 'followingUserId' => $this->input->post('userId')))->row_array();
      if (!empty($checkFollow)) {
        $finalData['followStatus'] = $checkFollow['status'];
      } else {
        $finalData['followStatus'] = '0';
      }
      $message['success'] = '1';
      $message['message'] = 'Details found successfully';
      $message['details'] = $finalData;
    }
    echo json_encode($message);
  }

  public function accountType()
  {
    $getUserDetails =  $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    if ($getUserDetails['privateAccount'] == 0) {
      $data['privateAccount'] = 1;
      $status = true;
    } else {
      $data['privateAccount'] = 0;
      $status = false;
    }
    $update = $this->Common_Model->update('users', $data, 'id', $this->input->post('userId'));
    $message['status'] = $status;
    echo json_encode($message);
  }

  public function likeVideoShow()
  {
    $getUserDetails =  $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    if ($getUserDetails['likeVideo'] == 0) {
      $data['likeVideo'] = 1;
      $status = true;
    } else {
      $data['likeVideo'] = 0;
      $status = false;
    }
    $update = $this->Common_Model->update('users', $data, 'id', $this->input->post('userId'));
    $message['status'] = $status;
    echo json_encode($message);
  }

  public function userCommentVideo()
  {
    if ($this->input->post()) {
      $data['userId'] = $this->input->post('userId');
      $data['videoId'] = $this->input->post('videoId');
      $data['comment'] = $this->input->post('comment');
      $data['created'] = date('Y-m-d H:i:s');
      $update = $this->db->insert('videoComments', $data);
      if ($update) {
        $id = $this->db->insert_id();


        $loginUserDetails = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
        $UserDetails = $this->db->get_where('users', array('id' => $this->input->post('ownerId')))->row_array();
        $mess = $loginUserDetails['username'] . " commented on your video";
        $regId = $UserDetails['reg_id'];
        if ($this->input->post('userId') != $this->input->post('ownerId')) {
          if ($loginUserDetails['commentNotification'] == '1') {
            $this->notification($regId, $mess, 'comment', $this->input->post('userId'), $this->input->post('ownerId'));
          }
          $notiMess['loginId'] = $this->input->post('userId');
          $notiMess['userId'] = $this->input->post('ownerId');
          $notiMess['videoId'] = $this->input->post('videoId');
          $notiMess['commentId'] = $id;
          $notiMess['message'] = $mess;
          $notiMess['type'] = 'comment';
          $notiMess['notiDate'] = date('Y-m-d');
          $notiMess['created'] = date('Y-m-d H:i:s');
          $this->db->insert('userNotification', $notiMess);
        }



        $checkCommentCount = $this->db->get_where('userVideos', array('id' => $this->input->post('videoId')))->row_array();

        $upComment['commentCount'] = $checkCommentCount['commentCount'] + 1;
        $this->Common_Model->update('userVideos', $upComment, 'id', $this->input->post('videoId'));


        $details = $this->db->query("select videoComments.*,users.username,users.image as userImage from videoComments left join users on users.id = videoComments.userId where videoComments.id=$id")->result_array();
        foreach ($details as $detail) {
          if (empty($detail['userImage'])) {
            $detail['userImage'] =  base_url() . 'uploads/no_image_available.png';
          }
          $detail['likeStatus'] = false;
          $detail['likeCount'] = "0";
          $detail['subComment'] = [];
          $detail['created'] = 'just now';
          $dd[] = $detail;
        }
        $comCount = $upComment['commentCount'];
        $message['success'] = '1';
        $message['message'] = 'Comment added successfully';
        $message['commentCount'] = (string)$comCount;
        $message['details'] = $dd;
      } else {
        $message['success'] = '0';
        $message['message'] = 'Please try after sometime';
      }
    } else {
      $message['message'] = 'Please enter parameters';
    }
    echo json_encode($message);
  }

  public function likeAndDislikeComments()
  {
    $check_like =  $this->db->get_where('videoCommentsLikeOrUnlike', array('commentId' => $this->input->post('commentId'), 'userId' => $this->input->post('userId')))->row_array();
    if (!empty($check_like)) {
      if ($check_like['status'] == '0') {
        $status = '1';
      } else {
        $status = '0';
      }
      $data = array(
        'userId' => $this->input->post('userId'),
        'commentId' => $this->input->post('commentId'),
        'status' => $status,
        'updated' => date('Y-m-d H:i:s')
      );
      $update = $this->Common_Model->update('videoCommentsLikeOrUnlike', $data, 'id', $check_like['id']);
    } else {
      $status = '1';
      $data = array(
        'userId' => $this->input->post('userId'),
        'commentId' => $this->input->post('commentId'),
        'status' => $status,
        'created' => date('Y-m-d H:i:s')
      );
      $insert = $this->db->insert('videoCommentsLikeOrUnlike', $data);
    }
    $likeCount = $this->db->get_where('videoCommentsLikeOrUnlike', array('commentId' => $this->input->post('commentId'), 'status' => '1'))->num_rows();
    $successmessage = array(
      'success' => '1',
      'likeStatus' => $status,
      'likeCount' => (string)$likeCount
    );
    echo json_encode($successmessage);
  }

  public function getVideoComments()
  {
    $getVideoIds = $this->Common_Model->getCommentsVideos($this->input->post('userId'), $this->input->post('videoId'));
    if (!empty($getVideoIds)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      foreach ($getVideoIds as $lists) {
        if (empty($lists['userImage'])) {
          $lists['userImage'] = base_url() . 'uploads/no_image_available.png';
        }
        $lists['created'] = $this->getTime($lists['created']);
        $likeCount = $this->db->get_where('videoCommentsLikeOrUnlike', array('commentId' => $lists['id'], 'status' => '1'))->num_rows();
        $likeStatus = $this->db->get_where('videoCommentsLikeOrUnlike', array('commentId' => $lists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
        if (!empty($likeCount)) {
          $lists['likeCount'] = (string)$likeCount;
        } else {
          $lists['likeCount'] = '0';
        }
        if (!empty($likeStatus)) {
          $lists['likeStatus'] = true;
        } else {
          $lists['likeStatus'] = false;
        }
        $getSubComment =  $this->Common_Model->getSubComment($lists['id']);
        if (!empty($getSubComment)) {
          foreach ($getSubComment as $getSubComments) {
            if (empty($getSubComments['userImage'])) {
              $getSubComments['userImage'] = base_url() . 'uploads/no_image_available.png';
            }
            $getSubComments['created'] = $this->getTime($getSubComments['created']);
            $lists['subComment'][] = $getSubComments;
          }
        } else {
          $lists['subComment'] = [];
        }

        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'no details found';
    }
    echo json_encode($message);
  }

  public function getLikeVideo()
  {
    $getVideoIds = $this->Common_Model->getLikeVideoIds($this->input->post('userId'));
    if (!empty($getVideoIds)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      foreach ($getVideoIds as $lists) {
        // if(!empty($lists['hashTag'])){
        //  $lists['hashtagTitle'] = $this->hashTagName($lists['hashTag']);
        // }
        // else{
        //  $lists['hashtagTitle'] = '';
        // }
        if (!empty($lists['hashtag'])) {
          $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
          $finalTagIds = explode(',', $lists['hashtag']);
          foreach ($finalTagIds as $finalTagId) {
            $hashArray = $this->db->get_where('hashtag', array('id' => $finalTagId))->row_array();
            if (!empty($hashArray)) {
              $lists['hastagLists'][] = $hashArray;
            }
          }
        } else {
          $lists['hashtagTitle'] = '';
          $lists['hastagLists'] = [];
        }
        if (!empty($lists['name'])) {
          $lists['username'] = $lists['name'];
        } else {
          $lists['username'] = $lists['username'];
        }
        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
        if (!empty($likeStatus)) {
          $lists['likeStatus'] = true;
        } else {
          $lists['likeStatus'] = false;
        }
        $likeCount = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'], 'status' => '1'))->num_rows();
        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
        if (!empty($likeCount)) {
          $lists['likeCount'] = (string)$likeCount;
        } else {
          $lists['likeCount'] = '0';
        }
        if (!empty($likeStatus)) {
          $lists['likeStatus'] = true;
        } else {
          $lists['likeStatus'] = false;
        }

        $commentCoutList = $this->db->get_where('videoComments', array('videoId' => $lists['id']))->num_rows();
        if (!empty($commentCoutList)) {
          $lists['commentCount'] = (string)$commentCoutList;
        } else {
          $lists['commentCount'] = '';
        }
        $checkFollow = $this->db->get_where('videoComments', array('videoId' => $lists['id']))->num_rows();
        if (!empty($checkFollow)) {
          $lists['followStatus'] = '1';
        } else {
          $lists['followStatus'] = '0';
        }

        //$lists['viewCount'] = '5';
        $lists['shareCount'] = '';
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'no details found';
    }
    echo json_encode($message);
  }

  public function myVideoList()
  {
    $videoList =  $this->Common_Model->myVideoList($this->input->post('userId'));
    if (!empty($videoList)) {
      foreach ($videoList as $lists) {

        $message['success'] = '1';
        $message['message'] = 'List found successfully';
        if (!empty($lists['name'])) {
          $lists['username'] = $lists['name'];
        } else {
          $lists['username'] = $lists['username'];
        }
        if (empty($lists['image'])) {
          $lists['image'] = base_url() . 'uploads/no_image_available.png';
        }
        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'], 'userId' => $this->input->post('loginId'), 'status' => '1'))->row_array();
        if (!empty($likeStatus)) {
          $lists['likeStatus'] = true;
        } else {
          $lists['likeStatus'] = false;
        }
        $checkFollow = $this->db->get_where('videoComments', array('videoId' => $lists['id']))->num_rows();
        if (!empty($checkFollow)) {
          $lists['followStatus'] = '1';
        } else {
          $lists['followStatus'] = '0';
        }
        // if(!empty($lists['hashtag'])){
        //  $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
        // }
        // else{
        //  $lists['hashtagTitle'] = '';
        // }

        if (!empty($lists['hashtag'])) {
          $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
          $finalTagIds = explode(',', $lists['hashtag']);
          foreach ($finalTagIds as $finalTagId) {
            $hashArray = $this->db->get_where('hashtag', array('id' => $finalTagId))->row_array();
            if (!empty($hashArray)) {
              $lists['hastagLists'][] = $hashArray;
            }
          }
        } else {
          $lists['hashtagTitle'] = '';
          $lists['hastagLists'] = [];
        }
        if (!empty($lists['hastagLists'])) {
          $lists['hastagLists'] = $lists['hastagLists'];
        } else {
          $lists['hastagLists'] = [];
        }
        if ($lists['soundTitle'] == '') {
          $lists['soundTitle'] = '';
          $lists['soundId'] = '';
        }
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'no details found';
    }
    echo json_encode($message);
  }

  //  public function likeAndDislikeVideo(){
  //    $data['userId'] = $this->input->post('userId');
  //    $data['ownerId'] = $this->input->post('ownerId');
  //    $data['videoId'] = $this->input->post('videoId');
  //    $data['status'] = '1';
  //    $videoId = $this->input->post('videoId');

  //    $ckeckCount = $this->db->get_where("userVideos",['id' => $this->input->post('videoId')])->row_array();

  //    $getlike = $ckeckCount['likeCount'];

  //    if($getlike <= '0'){
  //    $insert = $this->db->insert('videoLikeOrUnlike', $data);
  //    if ($insert){

  //      $this->db->set('likeCount', 'likeCount +1', false)->where('id', $this->input->post('videoId'))->update("userVideos");
  //      $this->db->set('likes', 'likes +1', false)->where('userId', $this->input->post('userId'))->update("userProfileInformation");
  //    //   $this->db->set('likeCount', 'likeCount +1', false)->where('id', $this->input->post('ownerId'))->update("userVideos");

  //      $Counts = $this->db->get_where("userVideos",['id' => $videoId])->row_array();

  //      $message['success'] = '1';
  //      $message['message'] = 'Video like succesfully';
  //      $message['details'] = $Counts;

  //    }
  //    }
  //    else{

  //    $get = $this->db->get_where('videoLikeOrUnlike', ['userId' => $this->input->post('userId'), 'ownerId' => $this->input->post('ownerId')])->row_array();

  //    if (!empty($get)) {

  //    $delete = $this->db->delete('videoLikeOrUnlike', ['userId' => $this->input->post('userId'), 'ownerId' => $this->input->post('ownerId')]);
  //    if ($delete) {

  //      $update['status'] = '0';
  //      $this->db->update("videoLikeOrUnlike",$update,['userId' => $this->input->post('userId'), 'videoId' => $this->input->post('videoId')]);

  //      $this->db->set('likeCount', 'likeCount -1', false)->where('id', $this->input->post('videoId'))->update("userVideos");
  //      $this->db->set('likes', 'likes -1', false)->where('userId', $this->input->post('userId'))->update("userProfileInformation");

  //      $getCounts = $this->db->get_where("userVideos",['id' => $videoId])->row_array();



  //      $message['success'] = '2';
  //      $message['message'] = 'Video dislike successfully';
  //      $message['details'] = $getCounts;

  //    }
  //    } else {

  //    $insert = $this->db->insert('videoLikeOrUnlike', $data);
  //    if ($insert) {

  //      $this->db->set('likeCount', 'likeCount +1', false)->where('id', $this->input->post('videoId'))->update("userVideos");
  //      $this->db->set('likes', 'likes +1', false)->where('userId', $this->input->post('userId'))->update("userProfileInformation");

  //      $Counts = $this->db->get_where("userVideos",['id' => $videoId])->row_array();

  //      $message['success'] = '1';
  //      $message['message'] = 'Video like succesfully';
  //      $message['details'] = $Counts;

  //    }
  //    }
  //  }
  //    echo json_encode($message);
  //  }

  public function hastTagIds($hastage, $userId)
  {
    $hasTag = $hastage;
    $exp = explode(',', $hasTag);
    foreach ($exp as $exps) {
      $checkHashTag = $this->db->get_where('hashtag', array('hashtag' => $exps))->row_array();
      if (!empty($checkHashTag)) {
        $updateCount['videoCount'] = $checkHashTag['videoCount'] + 1;
        $this->Common_Model->update('hashtag', $updateCount, 'id', $checkHashTag['id']);
        $hastIds[] = $checkHashTag['id'];
      } else {
        $addHash['hashtag'] = $exps;
        $addHash['userId'] = $userId;
        $addHash['created'] = date('Y-m-d H:i:s');
        $addHash['videoCount'] = 1;
        $insertHash = $this->db->insert('hashtag', $addHash);
        $hastIds[] = $this->db->insert_id();
      }
    }
    $finalHashTag = implode(',', $hastIds);
    return $finalHashTag;
  }

  public function uploadVideos()
  {
    require APPPATH . '/libraries/vendor/autoload.php';

    if (!empty($this->input->post('soundId'))) {
      $sound =  $this->input->post('soundId');
    } else {
      //$addSound['thumbnail'] = $this->input->post('thumbnail');
      $addSound['title'] = $this->input->post('soundTitle');
      $addSound['userId'] = $this->input->post('userId');
      $addSound['type'] = 'Original Sound';
      $addSound['created'] = date('Y-m-d H:i:s');
      if (!empty($_FILES['soundFile']['name'])) {
        $name1 = time() . '_' . $_FILES["soundFile"]["name"];
        $name = str_replace(' ', '_', $name1);
        $tmp_name = $_FILES['soundFile']['tmp_name'];
        $path = 'uploads/sounds/' . $name;
        move_uploaded_file($tmp_name, $path);
        $addSound['sound'] = $path;
      }
      $this->db->insert('sounds', $addSound);
      $sound = $this->db->insert_id();
    }
    $data['userId'] = $this->input->post('userId');
    if (!empty($this->input->post('hashTag'))) {
      $data['hashTag'] = $this->hastTagIds($this->input->post('hashTag'), $this->input->post('userId'));
    } else {
      $data['hashTag'] = '';
    }
    $data['allowDownloads']  = $this->input->post('allowDownloads');
    $data['description'] = $this->input->post('description');
    $data['allowComment'] = $this->input->post('allowComment');
    $data['allowDuetReact'] = $this->input->post('allowDuetReact');

    $data['soundId']  = 0;
    $data['viewVideo']  = $this->input->post('viewVideo');
    $data['created'] = date('Y-m-d H:i:s');


    //*******************//
    $s3 = new Aws\S3\S3Client([
      'version' => 'latest',
      'region'  => 'ap-south-1',
      'credentials' => [
        'key'    => 'AKIA4CGHBXURVRVF66S7',
        'secret' => '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'
      ]
    ]);
    $bucket = 'cienmavideos';

    $upload = $s3->upload($bucket, $_FILES['videoPath']['name'], fopen($_FILES['videoPath']['tmp_name'], 'rb'), 'public-read');
    $upload1 = $s3->upload($bucket, $_FILES['thumbnail']['name'], fopen($_FILES['thumbnail']['tmp_name'], 'rb'), 'public-read');
    $url = $upload->get('ObjectURL');
    if (!empty($url)) {
      $data['videoPath'] = $url;
    } else {
      $data['videoPath'] = '';
    }
    $url1 = $upload1->get('ObjectURL');
    if (!empty($url1)) {
      $data['thumbnail'] = $url1;
    } else {
      $data['thumbnail'] = '';
    }

    //**********************//



    //if(!empty($_FILES['videoPath']['name'])){
    //$name1= time().'_'.$_FILES["videoPath"]["name"];
    //$name= str_replace(' ', '_', $name1);
    //$tmp_name = $_FILES['videoPath']['tmp_name'];
    //$path = 'uploads/users/'.$name;
    //move_uploaded_file($tmp_name,$path);
    //$data['videoPath'] = $path;
    //}
    $insert = $this->db->insert('userVideos', $data);
    if (!empty($insert)) {
      $vIDs = $this->db->insert_id();
      $checkData = $this->db->get_where('userProfileInformation', array('userId' => $this->input->post('userId')))->row_array();
      if (empty($checkData)) {
        $userProfile['userId'] = $this->input->post('userId');
        $userProfile['videoCount'] = 1;
        $this->db->insert('userProfileInformation', $userProfile);
      } else {
        $userProfile['videoCount'] = 1 + $checkData['videoCount'];
        $update = $this->Common_Model->update('userProfileInformation', $userProfile, 'id', $checkData['id']);
      }


      //$this->videoNotification($vIDs,$this->input->post('userId'));


      $videoId = $vIDs;
      $userId = $this->input->post('userId');
      $lists = $this->db->get_where('userFollow', array('followingUserId' => $userId))->result_array();
      if (!empty($lists)) {
        foreach ($lists as $list) {
          $loginUserDetails = $this->db->get_where('users', array('id' => $userId))->row_array();
          $getUserId = $this->db->get_where('users', array('id' => $list['userId']))->row_array();
          $regId = $getUserId['reg_id'];
          $mess = $loginUserDetails['username'] . ' uploaded new video';
          if ($loginUserDetails['videoNotification'] == '1') {
            $this->notification($regId, $mess, 'video', $list['userId'], $userId);
          }
          $notiMess['loginId'] = $userId;
          $notiMess['userId'] = $list['userId'];
          $notiMess['videoId'] = $videoId;
          $notiMess['message'] = $mess;
          $notiMess['type'] = 'video';
          $notiMess['notiDate'] = date('Y-m-d');
          $notiMess['created'] = date('Y-m-d H:i:s');
          $this->db->insert('userNotification', $notiMess);
        }
      }




      $checkSoundData = $this->db->get_where('sounds', array('id' => $sound))->row_array();
      $updateSound['soundCount'] = 1 + $checkSoundData['soundCount'];
      $update = $this->Common_Model->update('sounds', $updateSound, 'id', $sound);




      $message['success'] = '1';
      $message['message'] = 'Video Upload Successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }


  //   public function getVideo(){
  //      $startLimit = $this->input->post('startLimit');
  //         $endLimit = 5;
  //    $userId = $this->input->post('userId');
  //    $countNotification = $this->db->get_where('userNotification',array('userId' => $this->input->post('userId'),'status' => 0))->num_rows();


  //    if(!empty($countNotification)){
  //      $message['notificationCount'] = (string)$countNotification;
  //    }
  //    else{
  //      $message['notificationCount'] = '0';
  //    }

  //    if($this->input->post('videoType') == 'following'){
  //      // $list =  $this->db->query("SELECT sounds.title as soundTitle,sounds.id as soundId,users.username,users.name,users.followerCount as followers,users.image,userVideos.id, userVideos.userId,userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDownloads, userVideos.allowDuetReact,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join userFollow on userFollow.followingUserId = userVideos.userId left join users on users.id = userVideos.userId left join sounds on sounds.id = userVideos.soundId where userFollow.userId = $userId  and userFollow.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  ) order by RAND() LIMIT $startLimit , 5")->result_array();
  //       $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,userVideos.id, userVideos.userId,userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDownloads, userVideos.allowDuetReact,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join userFollow on userFollow.followingUserId = userVideos.userId left join users on users.id = userVideos.userId where userFollow.userId = $userId  and userFollow.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  ) order by RAND() LIMIT $startLimit , 100")->result_array();

  //    }
  //    else{
  //    // $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,sounds.title as soundTitle,sounds.id as soundId, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join sounds on sounds.id = userVideos.soundId left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ORDER BY RAND() LIMIT $startLimit , 5")->result_array();

  //        $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ORDER BY RAND() LIMIT $startLimit , 100")->result_array();

  //     }

  //    if(!empty($list)){
  //           $count = count($list);
  //           if($count < 9){
  //           $this->db->delete('viewVideo',array('userId' => $this->input->post('userId')));
  //       }
  //      $message['success'] = '1';
  //      $message['message'] = 'List Found Successfully';
  //      foreach($list as $lists){

  //         $viewVideoInsert['userId'] = $this->input->post('userId');
  //         $viewVideoInsert['videoId'] = $lists['id'];
  //         $this->db->insert('viewVideo',$viewVideoInsert);
  //         $updateVideoCount['viewCount'] = $videoLists['viewCount'] + 1;
  //         $this->Common_Model->update('userVideos',$updateVideoCount,'id',$lists['id']);

  //         if(!empty($lists['name'])){
  //           $lists['username'] = $lists['name'];
  //         }
  //         else{
  //           $lists['username'] = $lists['username'];
  //         }
  //        if(!empty($lists['downloadPath'])){
  //          $lists['downloadPath'] = $lists['downloadPath'];
  //        }
  //        else{
  //          $lists['downloadPath'] =  '';
  //        }

  //        if(empty($lists['image'])){
  //          $lists['image'] = base_url().'uploads/no_image_available.png';
  //        }
  //        if(!empty($lists['hashtag'])){
  //          $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
  //          $finalTagIds = explode(',',$lists['hashtag']);
  //          foreach($finalTagIds as $finalTagId){
  //            $hashArray = $this->db->get_where('hashtag',array('id' => $finalTagId))->row_array();
  //            if(!empty($hashArray)){
  //              $lists['hastagLists'][] = $hashArray;
  //            }
  //          }
  //        }
  //        else{
  //          $lists['hashtagTitle'] = '';
  //          $lists['hastagLists'] = [];
  //        }
  //        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'],'userId'=> $this->input->post('userId'),'status'=> '1'))->row_array();
  //        if(!empty($likeStatus)){
  //          $lists['likeStatus'] = true;
  //        }
  //        else{
  //          $lists['likeStatus'] = false;
  //        }


  //         $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'),'followingUserId' =>$lists['userId'],'status' => '1'))->row_array();
  //         if(!empty($checkFollow)){
  //           $lists['followStatus'] = '1';
  //         }
  //         else{
  //           $lists['followStatus'] = '0';
  //         }

  //        $message['details'][] = $lists;
  //      }
  //    }
  //    else{
  //       $this->db->delete('viewVideo',array('userId' => $this->input->post('userId')));
  //       if($this->input->post('videoType') == 'following'){
  //         $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,userVideos.id, userVideos.userId,userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDownloads, userVideos.allowDuetReact,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join userFollow on userFollow.followingUserId = userVideos.userId left join users on users.id = userVideos.userId  where userFollow.userId = $userId  and userFollow.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  ) order by RAND() LIMIT $startLimit , 100")->result_array();
  //       }
  //       else{
  //         $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ORDER BY RAND() LIMIT $startLimit , 100")->result_array();
  //       }

  //       if(!empty($list)){

  //        $message['success'] = '1';
  //        $message['message'] = 'List Found Successfully';
  //        foreach($list as $lists){

  //           $viewVideoInsert['userId'] = $this->input->post('userId');
  //           $viewVideoInsert['videoId'] = $videoLists['id'];
  //           $this->db->insert('viewVideo',$viewVideoInsert);
  //           $updateVideoCount['viewCount'] = $videoLists['viewCount'] + 1;
  //           $this->Common_Model->update('userVideos',$updateVideoCount,'id',$videoLists['id']);

  //           if(!empty($lists['name'])){
  //             $lists['username'] = $lists['name'];
  //           }
  //           else{
  //             $lists['username'] = $lists['username'];
  //           }
  //          if(!empty($lists['downloadPath'])){
  //            $lists['downloadPath'] = $lists['downloadPath'];
  //          }
  //          else{
  //            $lists['downloadPath'] =  '';
  //          }

  //          if(empty($lists['image'])){
  //            $lists['image'] = base_url().'uploads/no_image_available.png';
  //          }
  //          if(!empty($lists['hashtag'])){
  //            $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
  //            $finalTagIds = explode(',',$lists['hashtag']);
  //            foreach($finalTagIds as $finalTagId){
  //              $hashArray = $this->db->get_where('hashtag',array('id' => $finalTagId))->row_array();
  //              if(!empty($hashArray)){
  //                $lists['hastagLists'][] = $hashArray;
  //              }
  //            }
  //          }
  //          else{
  //            $lists['hashtagTitle'] = '';
  //            $lists['hastagLists'] = [];
  //          }
  //          $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'],'userId'=> $this->input->post('userId'),'status'=> '1'))->row_array();
  //          if(!empty($likeStatus)){
  //            $lists['likeStatus'] = true;
  //          }
  //          else{
  //            $lists['likeStatus'] = false;
  //          }


  //           $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'),'followingUserId' =>$lists['userId'],'status' => '1'))->row_array();
  //           if(!empty($checkFollow)){
  //             $lists['followStatus'] = '1';
  //           }
  //           else{
  //             $lists['followStatus'] = '0';
  //           }

  //          $message['details'][] = $lists;
  //        }
  //      }
  //       else{
  //           $message['success'] = '0';
  //           $message['message'] = 'NO List Found';
  //       }
  //    }
  //    echo json_encode($message);
  //  }

  //  public function getVideo(){
  //      $startLimit = $this->input->post('startLimit');
  //         $endLimit = 5;
  //    $userId = $this->input->post('userId');
  //    $countNotification = $this->db->get_where('userNotification',array('userId' => $this->input->post('userId'),'status' => 0))->num_rows();

  //    if(!empty($countNotification)){
  //      $message['notificationCount'] = (string)$countNotification;
  //    }
  //    else{
  //      $message['notificationCount'] = '0';
  //    }

  //    if($this->input->post('videoType') == 'following'){
  //           $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,userVideos.id, userVideos.userId,userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDownloads, userVideos.allowDuetReact,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join userFollow on userFollow.followingUserId = userVideos.userId left join users on users.id = userVideos.userId where userFollow.userId = $userId  and userFollow.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  ) order by RAND() LIMIT $startLimit , 100")->result_array();

  //    }
  //    else{
  //         $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ORDER BY RAND() LIMIT $startLimit , 100")->result_array();
  //       }

  //  if(!empty($list)){
  //     //   $count = count($list);

  //     //   if($count < 9){
  //     //     $this->db->delete('viewVideo',array('userId' => $this->input->post('userId')));
  //     //   }

  //    $message['success'] = '1';
  //    $message['message'] = 'List Found Successfully';
  //    foreach($list as $lists){

  //         $viewVideoInsert['userId'] = $this->input->post('userId');
  //         $viewVideoInsert['videoId'] = $lists['id'];
  //         $this->db->insert('viewVideo',$viewVideoInsert);
  //         $updateVideoCount['viewCount'] = $videoLists['viewCount'] + 1;
  //         $this->Common_Model->update('userVideos',$updateVideoCount,'id',$lists['id']);

  //         if(!empty($lists['name'])){
  //           $lists['username'] = $lists['name'];
  //         }
  //         else{
  //           $lists['username'] = $lists['username'];
  //         }
  //        if(!empty($lists['downloadPath'])){
  //          $lists['downloadPath'] = $lists['downloadPath'];
  //        }
  //        else{
  //          $lists['downloadPath'] =  '';
  //        }

  //        if(empty($lists['image'])){
  //          $lists['image'] = base_url().'uploads/no_image_available.png';
  //        }
  //        if(!empty($lists['hashtag'])){
  //          $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
  //          $finalTagIds = explode(',',$lists['hashtag']);
  //          foreach($finalTagIds as $finalTagId){
  //            $hashArray = $this->db->get_where('hashtag',array('id' => $finalTagId))->row_array();
  //            if(!empty($hashArray)){
  //              $lists['hastagLists'][] = $hashArray;
  //            }
  //          }
  //        }
  //        else{
  //          $lists['hashtagTitle'] = '';
  //          $lists['hastagLists'] = [];
  //        }
  //        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'],'userId'=> $this->input->post('userId'),'status'=> '1'))->row_array();
  //        if(!empty($likeStatus)){
  //          $lists['likeStatus'] = true;
  //        }
  //        else{
  //          $lists['likeStatus'] = false;
  //        }


  //         $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'),'followingUserId' =>$lists['userId'],'status' => '1'))->row_array();
  //         if(!empty($checkFollow)){
  //           $lists['followStatus'] = '1';
  //         }
  //         else{
  //           $lists['followStatus'] = '0';
  //         }

  //        $message['details'][] = $lists;
  //      }
  //    }
  // //     else{
  // //       $this->db->delete('viewVideo',array('userId' => $this->input->post('userId')));
  // //       if($this->input->post('videoType') == 'following'){
  // //         $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,userVideos.id, userVideos.userId,userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDownloads, userVideos.allowDuetReact,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join userFollow on userFollow.followingUserId = userVideos.userId left join users on users.id = userVideos.userId  where userFollow.userId = $userId  and userFollow.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  ) order by RAND() LIMIT $startLimit , 100")->result_array();
  // //       }
  // //       else{
  // //         $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ORDER BY RAND() LIMIT $startLimit , 100")->result_array();
  // //       }

  // //       if(!empty($list)){

  // //         $message['success'] = '1';
  // //         $message['message'] = 'List Found Successfully';
  // //         foreach($list as $lists){

  // //           $viewVideoInsert['userId'] = $this->input->post('userId');
  // //           $viewVideoInsert['videoId'] = $videoLists['id'];
  // //           $this->db->insert('viewVideo',$viewVideoInsert);
  // //           $updateVideoCount['viewCount'] = $videoLists['viewCount'] + 1;
  // //           $this->Common_Model->update('userVideos',$updateVideoCount,'id',$videoLists['id']);

  // //           if(!empty($lists['name'])){
  // //             $lists['username'] = $lists['name'];
  // //           }
  // //           else{
  // //             $lists['username'] = $lists['username'];
  // //           }
  // //           if(!empty($lists['downloadPath'])){
  // //             $lists['downloadPath'] = $lists['downloadPath'];
  // //           }
  // //           else{
  // //             $lists['downloadPath'] =  '';
  // //           }

  // //           if(empty($lists['image'])){
  // //             $lists['image'] = base_url().'uploads/no_image_available.png';
  // //           }
  // //           if(!empty($lists['hashtag'])){
  // //             $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
  // //             $finalTagIds = explode(',',$lists['hashtag']);
  // //             foreach($finalTagIds as $finalTagId){
  // //               $hashArray = $this->db->get_where('hashtag',array('id' => $finalTagId))->row_array();
  // //               if(!empty($hashArray)){
  // //                 $lists['hastagLists'][] = $hashArray;
  // //               }
  // //             }
  // //           }
  // //           else{
  // //             $lists['hashtagTitle'] = '';
  // //             $lists['hastagLists'] = [];
  // //           }
  // //           $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'],'userId'=> $this->input->post('userId'),'status'=> '1'))->row_array();
  // //           if(!empty($likeStatus)){
  // //             $lists['likeStatus'] = true;
  // //           }
  // //           else{
  // //             $lists['likeStatus'] = false;
  // //           }


  // //           $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'),'followingUserId' =>$lists['userId'],'status' => '1'))->row_array();
  // //           if(!empty($checkFollow)){
  // //             $lists['followStatus'] = '1';
  // //           }
  // //           else{
  // //             $lists['followStatus'] = '0';
  // //           }

  // //           $message['details'][] = $lists;
  // //         }
  // //       }
  // //       else{
  // //            $message['success'] = '0';
  // //            $message['message'] = 'NO List Found';
  // //       }
  // //     }
  //    echo json_encode($message);
  //  }

  public function getVideo()
  {
    $startLimit = $this->input->post('startLimit');
    $endLimit = 10;
    $userId = $this->input->post('userId');
    $countNotification = $this->db->get_where('userNotification', array('userId' => $this->input->post('userId'), 'status' => 0))->num_rows();

    if (!empty($countNotification)) {
      $message['notificationCount'] = (string)$countNotification;
    } else {
      $message['notificationCount'] = '0';
    }

    if ($this->input->post('videoType') == 'following') {
      // $list =  $this->db->query("SELECT sounds.title as soundTitle,sounds.id as soundId,users.username,users.name,users.followerCount as followers,users.image,userVideos.id, userVideos.userId,userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDownloads, userVideos.allowDuetReact,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join userFollow on userFollow.followingUserId = userVideos.userId left join users on users.id = userVideos.userId left join sounds on sounds.id = userVideos.soundId where userFollow.userId = $userId  and userFollow.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  ) order by RAND() LIMIT $startLimit , 5")->result_array();
      $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,userVideos.id, userVideos.userId,userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDownloads, userVideos.allowDuetReact,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join userFollow on userFollow.followingUserId = userVideos.userId left join users on users.id = userVideos.userId where userFollow.userId = $userId  and userFollow.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  ) order by RAND() LIMIT $startLimit , 100")->result_array();
    } else {
      // $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,sounds.title as soundTitle,sounds.id as soundId, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join sounds on sounds.id = userVideos.soundId left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ORDER BY RAND() LIMIT $startLimit , 5")->result_array();

      $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ORDER BY RAND() LIMIT $startLimit , 100")->result_array();
    }

    if (!empty($list)) {
      $count = count($list);
      if ($count < 9) {
        $this->db->delete('viewVideo', array('userId' => $this->input->post('userId')));
      }

      $message['success'] = '1';
      $message['message'] = 'List Found Successfully';
      foreach ($list as $lists) {

        $viewVideoInsert['userId'] = $this->input->post('userId');
        $viewVideoInsert['videoId'] = $lists['id'];




        $this->db->insert('viewVideo', $viewVideoInsert);
        $updateVideoCount['viewCount'] = $videoLists['viewCount'] + 1;
        $this->Common_Model->update('userVideos', $updateVideoCount, 'id', $lists['id']);

        if (!empty($lists['name'])) {
          $lists['username'] = $lists['name'];
        } else {
          $lists['username'] = $lists['username'];
        }
        if (!empty($lists['downloadPath'])) {
          $lists['downloadPath'] = $lists['downloadPath'];
        } else {
          $lists['downloadPath'] =  '';
        }

        if (empty($lists['image'])) {
          $lists['image'] = base_url() . 'uploads/no_image_available.png';
        }
        if (!empty($lists['hashtag'])) {
          $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
          $finalTagIds = explode(',', $lists['hashtag']);
          foreach ($finalTagIds as $finalTagId) {
            $hashArray = $this->db->get_where('hashtag', array('id' => $finalTagId))->row_array();
            if (!empty($hashArray)) {
              $lists['hastagLists'][] = $hashArray;
            }
          }
        } else {
          $lists['hashtagTitle'] = '';
          $lists['hastagLists'] = [];
        }
        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
        if (!empty($likeStatus)) {
          $lists['likeStatus'] = true;
        } else {
          $lists['likeStatus'] = false;
        }


        $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
        if (!empty($checkFollow)) {
          $lists['followStatus'] = '1';
        } else {
          $lists['followStatus'] = '0';
        }

        $message['details'][] = $lists;
      }
    } else {
      $this->db->delete('viewVideo', array('userId' => $this->input->post('userId')));
      if ($this->input->post('videoType') == 'following') {
        $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,userVideos.id, userVideos.userId,userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDownloads, userVideos.allowDuetReact,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join userFollow on userFollow.followingUserId = userVideos.userId left join users on users.id = userVideos.userId  where userFollow.userId = $userId  and userFollow.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  ) order by RAND() LIMIT $startLimit , 100")->result_array();
      } else {
        $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ORDER BY RAND() LIMIT $startLimit , 100")->result_array();
      }

      if (!empty($list)) {

        $message['success'] = '1';
        $message['message'] = 'List Found Successfully';
        foreach ($list as $lists) {

          $viewVideoInsert['userId'] = $this->input->post('userId');
          $viewVideoInsert['videoId'] = $lists['id'];

          $this->db->insert('viewVideo', $viewVideoInsert);

          $updateVideoCount['viewCount'] = $videoLists['viewCount'] + 1;
          $this->Common_Model->update('userVideos', $updateVideoCount, 'id', $videoLists['id']);

          if (!empty($lists['name'])) {
            $lists['username'] = $lists['name'];
          } else {
            $lists['username'] = $lists['username'];
          }
          if (!empty($lists['downloadPath'])) {
            $lists['downloadPath'] = $lists['downloadPath'];
          } else {
            $lists['downloadPath'] =  '';
          }

          if (empty($lists['image'])) {
            $lists['image'] = base_url() . 'uploads/no_image_available.png';
          }
          if (!empty($lists['hashtag'])) {
            $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
            $finalTagIds = explode(',', $lists['hashtag']);
            foreach ($finalTagIds as $finalTagId) {
              $hashArray = $this->db->get_where('hashtag', array('id' => $finalTagId))->row_array();
              if (!empty($hashArray)) {
                $lists['hastagLists'][] = $hashArray;
              }
            }
          } else {
            $lists['hashtagTitle'] = '';
            $lists['hastagLists'] = [];
          }
          $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
          if (!empty($likeStatus)) {
            $lists['likeStatus'] = true;
          } else {
            $lists['likeStatus'] = false;
          }


          $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
          if (!empty($checkFollow)) {
            $lists['followStatus'] = '1';
          } else {
            $lists['followStatus'] = '0';
          }

          $message['details'][] = $lists;
        }
      } else {
        $message['success'] = '0';
        $message['message'] = 'NO List Found';
      }
    }
    echo json_encode($message);
  }

  public function getUserUploadVedios()
  {

    $getDetails = $this->db->get_where("userVideos", ['userId' => $this->input->post("userId")])->result_array();

    if (!!$getDetails) {

      echo json_encode([

        "message" => 'details found',
        "success" => '1',
        "details" => $getDetails,
      ]);

      exit;
    } else {
      echo json_encode([
        "message" => 'details not found!',
        "success" => '0',
      ]);
      exit;
    }
  }

  public function getLiveUsers()
  {


    if (!$this->input->post("status")) {

      echo json_encode([

        "message" => "status cannot be null",
        "success" => "0",
      ]);
      exit;
    }

    if ($this->input->post("status") == '1') {

      $details = $this->db->query("SELECT userLive.userId,TIMEDIFF(TIME(userLive.archivedDate),TIME(userLive.created))/60 as duration,userLiveRequest.* From userLive join userLiveRequest on userLiveRequest.userId = userLive.userId order by duration desc")->result_array();

      if (!!$details) {
        echo json_encode([

          "message" => "details found",
          "success" => "1",
          "details" => $details,
        ]);
        exit;
      }
    }

    if ($this->input->post("status") == '2') {

      $details = $this->db->query("SELECT u.id,u.userId,u.created,userLiveRequest.request,userLiveRequest.agencyId,userLiveRequest.paymentType,userLiveRequest.paymentMethod,userLiveRequest.email,userLiveRequest.name,userLiveRequest.phone,userLiveRequest.country,userLiveRequest.address From userLive u join userLiveRequest on userLiveRequest.userId = u.userId WHERE u.created BETWEEN date_sub(now(),INTERVAL 1 WEEK) and now()")->result_array();

      if (!!$details) {
        echo json_encode([

          "message" => "details found",
          "success" => "1",
          "details" => $details,
        ]);
        exit;
      }
    }
  }



  public function getVideoOld()
  {
    $startLimit = $this->input->post('startLimit');
    $endLimit = 5;
    $userId = $this->input->post('userId');
    $countNotification = $this->db->get_where('userNotification', array('userId' => $this->input->post('userId'), 'status' => 0))->num_rows();
    if (!empty($countNotification)) {
      $message['notificationCount'] = (string)$countNotification;
    } else {
      $message['notificationCount'] = '0';
    }
    $list =  $this->db->query("SELECT users.username,users.followerCount as followers,users.image,sounds.title as soundTitle,sounds.id as soundId, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath, userVideos.allowComment, userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount FROM `userVideos` left join sounds on sounds.id = userVideos.soundId left join users on users.id = userVideos.userId where userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ORDER BY userVideos.viewCount desc,userVideos.likeCount desc,userVideos.commentCount LIMIT $startLimit , 5")->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List Found Successfully';
      foreach ($list as $lists) {
        if (empty($lists['image'])) {
          $lists['image'] = base_url() . 'uploads/no_image_available.png';
        }
        if (!empty($lists['hashtag'])) {
          $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
        } else {
          $lists['hashtagTitle'] = '';
        }
        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
        if (!empty($likeStatus)) {
          $lists['likeStatus'] = true;
        } else {
          $lists['likeStatus'] = false;
        }

        $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
        if (!empty($checkFollow)) {
          $lists['followStatus'] = '1';
        } else {
          $lists['followStatus'] = '0';
        }

        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'NO List Found';
    }
    echo json_encode($message);
  }

  public function checkPhoneAndEmail()
  {
    $checkRegId = $this->db->get_where('blockDeviceId', array('deviceId' => $this->input->post('deviceId')))->row_array();
    if (empty($checkRegId)) {
      $type = $this->input->post('type');
      $emailPhone = $this->input->post('emailPhone');

      $checkData = $this->db->get_where('users', array($type => $this->input->post('emailPhone')))->row_array();
      if (empty($checkData)) {
        // $otp = rand(1000,9999);
        $otp = '0000';
        // if($type == 'phone'){
        //   $curl = curl_init();
        //   $phone = $emailPhone;
        //   $message12 = "Hi, Your Verification Code is: ".$otp;
        //   $a = $phone;
        //   require APPPATH.'/libraries/twilio-php-master/Twilio/autoload.php';
        //   $sid    = "AC28cbb8b04a32be13f3f97e165452c1a7";
        //   $token  = "5091b9d944422d906bcbd4c7c268e1a7";
        //   $twilio = new Client($sid, $token);
        //   $message23 = $twilio->messages
        //     ->create($a, // to
        //        array(
        //          "from" => "+16182055887",
        //          "body" =>  $message12,
        //       )
        //   );
        // }
        $message['success'] = '1';
        $message['message'] = 'Otp Send Successfully';
        $message['otp'] = (string)$otp;
      } else {
        $message['success'] = '0';
        $message['message'] = $type . ' is alerdy exist';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'Your Account has been declined. Please contact support@LiveBazaar.com';
    }
    echo json_encode($message);
  }

  public function updadtePhoneAndEmail()
  {
    $type = $this->input->post('type');
    $data[$type] = $this->input->post('emailPhone');
    $update = $this->Common_Model->update('users', $data, 'id', $this->input->post('userId'));
    if (!empty($update)) {
      $userDetails = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
      if (empty($userDetails['image'])) {
        $userDetails['image'] =  base_url() . 'uploads/no_image_available.png';
      }
      $message['success'] = '1';
      $message['message'] = $type . ' Update successfully';
      $message['details'] = $userDetails;
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function updateUserInformation()
  {
    if (!empty($this->input->post('name'))) {
      $data['name'] = $this->input->post('name');
      $error = '';
    }
    if (!empty($this->input->post('email'))) {
      $data['email'] = $this->input->post('email');
      $error = '';
    }
    if (!empty($this->input->post('username'))) {
      $usernameGet = $this->input->post('username');
      $checkU = substr($usernameGet, 0, 1);
      $p = ($checkU == '@') ? array('username' => $this->input->post('username')) : array('username' => '@' . $this->input->post('username'));
      $checkUserName = $this->db->get_where('users', $p)->row_array();
      if (empty($checkUserName)) {
        $data['username'] = ($checkU == '@') ? $this->input->post('username') : '@' . $this->input->post('username');
        $data['usernameChangeStatus'] = 1;
      } else {
        $error = 'error';
      }
    }
    if (!empty($this->input->post('bio'))) {
      $data['email'] = $this->input->post('email');
      $error = '';
    }

    if (!empty($this->input->post('dob'))) {
      $data['dob'] = $this->input->post('dob');
      $error = '';
    }
    if (!empty($this->input->post('locationName'))) {
      $data['locationName'] = $this->input->post('locationName');
      $error = '';
    }
    if (!empty($this->input->post('latitude'))) {
      $data['latitude'] = $this->input->post('latitude');
      $error = '';
    }
    if (!empty($this->input->post('longitude'))) {
      $data['longitude'] = $this->input->post('longitude');
      $error = '';
    }
    if (!empty($this->input->post('gender'))) {
      $data['gender'] = $this->input->post('gender');
      $error = '';
    }

    if (empty($error)) {
      $update = $this->Common_Model->update('users', $data, 'id', $this->input->post('userId'));
      if (!empty($update)) {
        $userDetails = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
        if (empty($userDetails['image'])) {
          $userDetails['image'] = base_url() . 'uploads/no_image_available.png';
        }
        $message['success'] = '1';
        $message['message'] = 'Information update successfully';
        $message['details'] = $userDetails;
      } else {
        $message['success'] = '0';
        $message['message'] = 'please try after some time';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'Username is already exist';
    }
    echo json_encode($message);
  }

  public function imageVideo()
  {
    require APPPATH . '/libraries/vendor/autoload.php';
    $s3 = new Aws\S3\S3Client([
      'version' => 'latest',
      'region'  => 'ap-south-1',
      'credentials' => [
        'key'    => 'AKIA3L2TB5JIUEWHXL3L',
        'secret' => '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'
      ]
    ]);
    $bucket = 'zebovideos';

    if (!empty($_FILES['image']['name'])) {
      $upload = $s3->upload($bucket, $_FILES['image']['name'], fopen($_FILES['image']['tmp_name'], 'rb'), 'public-read');
      $url = $upload->get('ObjectURL');
      if (!empty($url)) {
        $details['image'] = $url;
      } else {
        $details['image'] = '';
      }
    }
    /*if(!empty($_FILES['video']['name'])){
         $upload = $s3->upload($bucket, $_FILES['video']['name'], fopen($_FILES['video']['tmp_name'], 'rb'), 'public-read');
         $url = $upload->get('ObjectURL');
         if(!empty($url)){
             $details['video'] = $url;
         }
         else{
             $details['video'] = '';
         }
   }*/

    if (!empty($_FILES['video']['name'])) {
      $uploadMain = $s3->upload($bucket, $_FILES['video']['name'], fopen($_FILES['video']['tmp_name'], 'rb'), 'public-read');
      $url123 = $uploadMain->get('ObjectURL');
      if (!empty($url123)) {
        $details['video'] = $url123;
      } else {
        $details['video'] = '';
      }

      // $name1= time().'_'.$_FILES["video"]["name"];
      // $name= str_replace(' ', '_', $name1);
      // $tmp_name = $_FILES['video']['tmp_name'];
      // $path = 'uploads/users/'.$name;
      // move_uploaded_file($tmp_name,$path);
      // $details['video'] = base_url().$path;
    }

    $update = $this->Common_Model->update('users', $details, 'id', $this->input->post('userId'));
    if (!empty($update)) {
      $userDetails = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
      if (empty($userDetails['image'])) {
        $userDetails['image'] =  base_url() . 'uploads/no_image_available.png';
      }
      $message['success'] = '1';
      $message['message'] = 'Information update successfully';
      $message['details'] = $userDetails;
    } else {
      $message['success'] = '0';
      $message['message'] = 'please try after some time';
    }
    echo json_encode($message);
  }

  public function imageVideoDelete()
  {
    $type = $this->input->post('type');
    $data[$type] = '';
    $update = $this->Common_Model->update('users', $data, 'id', $this->input->post('userId'));
    if (!empty($update)) {
      $userDetails = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
      if (empty($userDetails['image'])) {
        $userDetails['image'] =  base_url() . 'uploads/no_image_available.png';
      }
      $message['success'] = '1';
      $message['message'] = 'Information update successfully';
      $message['details'] = $userDetails;
    } else {
      $message['success'] = '0';
      $message['message'] = 'please try after some time';
    }
    echo json_encode($message);
  }

  // public function socialLogin(){
  //   $checkSocialId = $this->db->get_where('users',array('social_id' => $this->input->post('social_id')))->row_array();
  //     if(!empty($this->input->post('email'))){
  //     $checkEmailId = $this->db->get_where('users',array('email' => $this->input->post('email')))->row_array();
  //       }
  //     else{
  //         $checkEmailId = '';
  //       }
  //   if(!empty($checkSocialId)){
  //     if($checkSocialId['status'] == 'Approved'){
  //       $checkRegId = $this->db->get_where('blockDeviceId',array('deviceId' => $checkSocialId['deviceId']))->row_array();
  //       if(empty($checkRegId)){
  //         $datas = array('onlineStatus'=>1,'reg_id' => $this->input->post('reg_id'),'device_type' => $this->input->post('device_type'),'deviceId' => $this->input->post('deviceId'));
  //         $update = $this->Common_Model->update('users',$datas,'id',$checkSocialId['id']);
  //         if(!empty($update)){
  //           $userDetails = $this->db->get_where('users',array('id' => $checkSocialId['id']))->row_array();
  //           if(empty($userDetails['image'])){
  //             $userDetails['image'] =  base_url().'uploads/no_image_available.png';
  //           }
  //           if(!empty($userDetails['video'])){
  //             $userDetails['video'] = base_url().$userDetails['video'];
  //           }
  //           else{
  //             $userDetails['video'] = '';
  //           }
  //           $message['success'] = '1';
  //           $message['message'] = 'user login successfully';
  //           $message['details'] = $userDetails;
  //         }
  //       }
  //       else{
  //         $message['success'] = '0';
  //         $message['message'] = 'Your Account has been declined. Please contact support@LiveBazaar.com';
  //       }
  //     }
  //     else{
  //       $message = array(
  //         'success'=>'0',
  //         'message' => 'Your Account has been declined. Please contact support@LiveBazaar.com',
  //       );
  //     }
  //   }
  //   elseif(!empty($checkEmailId)){
  //     if($checkEmailId['status'] == 'Approved'){
  //       $checkRegId = $this->db->get_where('blockDeviceId',array('deviceId' => $checkEmailId['deviceId']))->row_array();
  //       if(empty($checkRegId)){
  //         $datas1 = array('onlineStatus'=>1,'reg_id' => $this->input->post('reg_id'),'device_type' => $this->input->post('device_type'),'social_id' => $this->input->post('social_id'),'deviceId' => $this->input->post('deviceId'));
  //         $update1 = $this->Common_Model->update('users',$datas1,'id',$checkEmailId['id']);
  //         if(!empty($update1)){
  //           $userDetails1 = $this->db->get_where('users',array('id' => $checkEmailId['id']))->row_array();
  //           if(empty($userDetails1['image'])){
  //             $userDetails1['image'] =  base_url().'uploads/no_image_available.png';
  //           }
  //           $message['success'] = '1';
  //           $message['message'] = 'user login successfully';
  //           $message['details'] = $userDetails1;
  //         }
  //       }
  //       else{
  //         $message['success'] = '0';
  //         $message['message'] = 'Your Account has been declined. Please contact support@LiveBazaar.com';
  //       }
  //     }
  //     else{
  //       $message = array(
  //         'success'=>'0',
  //         'message' => 'Your Account has been declined. Please contact support@LiveBazaar.com',
  //       );
  //     }
  //   }
  //   else{
  //     $checkRegId = $this->db->get_where('blockDeviceId',array('deviceId' => $this->input->post('deviceId')))->row_array();
  //     if(empty($checkRegId)){
  //       $datass['username'] = '@'.rand(100000000,999999999);
  //       $datass['name'] = $this->input->post('name');;
  //       $datass['social_id'] = $this->input->post('social_id');
  //       $datass['email'] = $this->input->post('email');
  //       $datass['phone'] = $this->input->post('phone');
  //       $datass['reg_id'] = $this->input->post('reg_id');
  //       $datass['deviceId'] = $this->input->post('deviceId');
  //       $datass['image'] = $this->input->post('image');
  //       $datass['expCoin'] = '0';
  //       $datass['leval'] = '0';
  //       $datass['wallet'] = '0';
  //       $datass['coin'] = '0';
  //       $datass['incomeDollar'] = '0';
  //       $datass['purchasedCoin'] = '0';
  //       $datass['device_type'] = $this->input->post('device_type');
  //       $datass['login_type'] = 'normal';
  //       $datass['created'] = date('Y-m-d H:i:s');
  //       $insert = $this->db->insert('users',$datass);
  //       if(!empty($insert)){
  //         $insert_id = $this->db->insert_id();
  //         $userDetails = $this->db->get_where('users', array('id' => $insert_id))->row_array();
  //
  //         $blockData['userId'] = $insert_id;
  //         $blockData['blockUserId'] = $insert_id;
  //         $blockData['created'] = date('Y-m-d H:i:s');
  //         $this->db->insert('blockUser',$blockData);
  //
  //         $infoUserRegister['userId'] = $insert_id;
  //         $this->db->insert('userProfileInformation',$infoUserRegister);
  //
  //
  //         if(empty($userDetails['image'])){
  //           $userDetails['image'] =  base_url().'uploads/no_image_available.png';
  //         }
  //         $message = array('success' => '1', 'message' => 'User login successfully', 'details' => $userDetails);
  //       }else{
  //         $message = array('success' => '0', 'message' => 'Please Try after some time');
  //       }
  //     }
  //     else{
  //       $message['success'] = '0';
  //       $message['message'] = 'Your Account has been declined. Please contact support@zebolive.com';
  //     }
  //   }
  //   echo json_encode($message);
  // }


  public function logout()
  {
    if ($this->input->post()) {
      $userId = $this->input->post('userId');
      $data['reg_id'] = "";
      $data['onlineStatus'] = "0";

      $this->db->update('users', $data, array('id' => $userId));

      $message['success'] = "1";
      $message['message'] = "User logout successfully";
    } else {
      $message = array(
        'message' => 'please enter parameters',
      );
    }
    echo json_encode($message);
  }

  public function register()
  {
    $checkRegId = $this->db->get_where('blockDeviceId', array('deviceId' => $this->input->post('deviceId')))->row_array();
    if (empty($checkRegId)) {
      $type = $this->input->post('type');
      $data[$type] = $this->input->post('emailPhone');
      $data['password'] = md5($this->input->post('password'));
      $data['dob'] = $this->input->post('dob');
      $data['name'] = $this->input->post('name');
      $data['deviceId'] = $this->input->post('deviceId');
      $data['country'] = $this->input->post('country');
      $data['email'] = $this->input->post('email');
      $data['username'] = '@5000000' . rand(10, 99);
      $data['reg_id'] = $this->input->post('reg_id');
      $data['expCoin'] = '0';
      $data['leval'] = '0';
      $data['coin'] = '0';
      $data['purchasedCoin'] = '0';
      $data['wallet'] = '0';
      $data['incomeDollar'] = '0';
      $data['device_type'] = $this->input->post('device_type');
      $data['login_type'] = 'normal';
      $data['onlineStatus'] = 1;
      $data['status'] = 'Approved';
      $data['created'] = date('Y-m-d H:i:s');
      $insert = $this->db->insert('users', $data);
      if (!empty($insert)) {
        $insert_id = $this->db->insert_id();
        $userDetails = $this->db->get_where('users', array('id' => $insert_id))->row_array();
        $blockData['userId'] = $insert_id;
        $blockData['blockUserId'] = $insert_id;
        $blockData['created'] = date('Y-m-d H:i:s');
        $this->db->insert('blockUser', $blockData);
        $infoUserRegister['userId'] = $insert_id;
        //$infoUserRegister['created'] = date('Y-m-d H:i:s');
        $this->db->insert('userProfileInformation', $infoUserRegister);

        $message = array('success' => '1', 'message' => 'User registered successfully', 'details' => $userDetails);
      } else {
        $message = array('success' => '0', 'message' => 'Please Try after some time');
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'Your Account has been declined. Please contact support@LiveBazaar.com';
    }
    echo json_encode($message);
  }

  //  public function loginPhone(){
  //
  //    $phone = $this->input->post('phone');
  //
  //    if($phone != null){
  //
  //    $checkData = $this->db->get_where('users',array('phone' => $this->input->post('phone')))->row_array();
  //
  //
  //        $otp = rand(100000,999999);
  //        $datas['loginOtp'] = $otp;
  //        $update = $this->db->update('users',$datas,'phone',$this->input->post('phone'));
  //        $message['success'] = '1';
  //        $message['message'] = 'Otp Send Successfully';
  //        $message['otp'] = (string)$otp;
  //      }
  //      else{
  //        $message['success'] = '0';
  //        $message['message'] = 'Please your parameter';
  //      }
  // //
  //    echo json_encode($message);
  //  }

  public function loginPhone()
  {
    $this->db->delete('verifyOTP', array('phone' => $this->input->post('phone')));
    $data['phone'] = $this->input->post('phone');
    $otp = rand(100000, 999999);
    $data['loginOtp'] = $otp;
    $insert = $this->db->insert('verifyOTP', $data);
    if (!empty($insert)) {
      $message['success'] = '1';
      $message['message'] = 'Otp Send Successfully';
      $message['otp'] = (string)$otp;
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function otpTestingMall()
  {


    $curl = curl_init();

    // $phone = "+917988674160";
    // $otp =  "123456";
    // $message12 = "Hi jukesh, OTP Testing Twilio account ".$otp;
    //
    //
    //  $a = $phone;
    //require dirname(dirname(dirname(_FILE_))).'/libraries/twilio-php-master/Twilio/autoload.php';
    //require APPPATH.'/libraries/twilio/twilio-php-master/Twilio/autoload.php';
    require APPPATH . '/libraries/twilio-php-master/Twilio/autoload.php';
    $sid    = "AC972125188c97a8fedc14f78ea7c243bb";
    $token  = "c80895f7377a7ba7968d61a33eaa24d7";
    // $twilio = new Client($sid, $token);
    $client = new Twilio\Rest\Client($sid, $token);
    $message = $client->messages->create(
      '+917626819687', // Text this number
      [
        'from' => '+19377447188', // From a valid Twilio number
        'body' => 'Hello from Twilio!'
      ]
    );

    print_r($message);
    die;
  }



  // public function loginRegisterUser(){
  //
  //     $phone = $this->input->post('phone');
  //     $otp = $this->input->post('otp');
  //
  //     if($phone != null && $otp != null){
  //
  //         $checkOtp = $this->db->get_where('users',['phone' => $phone])->row_array();
  //
  //         if(!!$checkOtp){
  //
  //            $checkData = $this->db->get_where('users',['loginOtp' => $otp,'phone' => $phone])->row_array();
  //
  //            $getId = $checkData['id'];
  //
  //             if(!!$checkData){
  //
  //                 $getStatus = $this->db->get_where('userLiveRequest',['userId' => $getId])->row_array();
  //
  //                 if(!!$getStatus){
  //                     $checkData['host_status'] = $getStatus['host_status'];
  //                 }
  //                 else{
  //                     $checkData['host_status'] = '0';
  //                 }
  //
  //                    $message['success'] = '1';
  //                    $message['message'] = 'User login successully';
  //                    $message['details'] = $checkData;
  //            }
  //            else{
  //                $message['success'] = '0';
  //                  $message['message'] = 'Invalid OTP, Please enter valid OTP';
  //            }
  //        }
  //         else{
  //             $data['deviceId'] = $this->input->post('deviceId') ?? "";
  //             $data['phone'] = $this->input->post('phone');
  //             $data['reg_id'] = $this->input->post('reg_id') ?? "";
  //             $data['country'] = $this->input->post("country");
  //             $data['expCoin'] = '0';
  //             $data['leval'] = '0';
  //             $data['coin'] = '0';
  //             $data['purchasedCoin'] = '0';
  //             $data['username'] = '7GE5'.random_string('alpha', 4).rand(1,9);
  //             $data['wallet'] = '0';
  //             $data['incomeDollar'] = '0';
  //             $data['device_type'] = $this->input->post('device_type') ?? "";
  //             $data['login_type'] = 'normal';
  //             $data['onlineStatus'] = 1;
  //             $data['status'] = 'Approved';
  //             $data['created'] = date('Y-m-d H:i:s');
  //             $upload = $this->db->insert("users",$data);
  //             $insert_id = $this->db->insert_id();
  //
  //             if($upload == true){
  //                 $grtDetails = $this->db->get_where("users",['id' => $insert_id])->row_array();
  //
  //                 $message['success'] = '1';
  //                 $message['message'] = 'User register successfully';
  //                 $message['details'] = $grtDetails;
  //             }
  //             else{
  //                 $message['success'] = '0';
  //                 $message['message'] = 'Something went wrong!';
  //             }
  //         }
  //     }
  //     else{
  //          $message['success'] = '0';
  //          $message['message'] = 'please enter valid params!';
  //    }
  //
  //     echo json_encode($message);
  // }

  public function loginRegisterUser()
  {
    $checkOTP = $this->db->get_where('verifyOTP', array('phone' => $this->input->post('phone'), 'loginOtp' => $this->input->post('otp')))->row_array();
    if (!empty($checkOTP)) {
      $checkUser = $this->db->get_where('users', array('phone' => $this->input->post('phone')))->row_array();

      $getId = $checkUser['id'];
      if (!empty($checkUser)) {

        $getStatus = $this->db->get_where('userLiveRequest', ['userId' => $getId])->row_array();

        if (!!$getStatus) {
          $checkUser['host_status'] = $getStatus['host_status'];
        } else {
          $checkUser['host_status'] = '0';
        }
        $message['success'] = '1';
        $message['message'] = 'User login successully';
        $message['details'] = $checkUser;
      } else {
        $data['deviceId'] = $this->input->post('deviceId') ?? "";
        $data['phone'] = $this->input->post('phone');
        $data['reg_id'] = $this->input->post('reg_id') ?? "";
        $data['country'] = $this->input->post("country") ?? "";
        $data['expCoin'] = '0';
        $data['leval'] = '0';
        $data['coin'] = '0';
        $data['purchasedCoin'] = '0';
        $data['username'] = '7GE5' . random_string('alpha', 4) . rand(1, 9);
        $data['wallet'] = '0';
        $data['incomeDollar'] = '0';
        $data['device_type'] = $this->input->post('device_type') ?? "";
        $data['login_type'] = 'normal';
        $data['onlineStatus'] = 1;
        $data['status'] = 'Approved';
        $data['created'] = date('Y-m-d H:i:s');
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






  public function login()
  {
    $emailPhone = $this->input->post('emailPhone');
    $otp = md5($this->input->post('password'));
    $data = $this->db->query("select * from users where  phone='$emailPhone' and password = '$otp'")->row_array();
    if (!empty($data)) {
      $checkRegId = $this->db->get_where('blockDeviceId', array('deviceId' => $data['deviceId']))->row_array();
      if (empty($checkRegId)) {
        if ($data['status'] == 'Approved') {
          $datas = array('onlineStatus' => 1, 'reg_id' => $this->input->post('reg_id'), 'device_type' => $this->input->post('device_type'), 'deviceId' => $this->input->post('deviceId'));
          $update = $this->Common_Model->update('users', $datas, 'id', $data['id']);
          $userDetails = $this->db->get_where('users', array('id' => $data['id']))->row_array();
          if (empty($userDetails['image'])) {
            $userDetails['image'] =  base_url() . 'uploads/no_image_available.png';
          }
          $message = array(
            'success' => '1',
            'message' => 'user login successfully',
            'details' => $userDetails
          );
        } else {
          $message = array(
            'success' => '0',
            'message' => 'Your Account has been declined. Please contact support@LiveBazaar.com',
          );
        }
      } else {
        $message = array(
          'success' => '0',
          'message' => 'Your Account has been declined. Please contact support@LiveBazaar.com',
        );
      }
    } else {
      $message = array(
        'success' => '0',
        'message' => 'Please enter valid Details!',
      );
    }
    echo json_encode($message);
  }

  public function accessToken()
  {
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.instamojo.com/oauth2/token/",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id=u5zMQS0PrSIhWm56tXtx8YcukqSUZped4UVPRkxK&client_secret=fnzNs8GZYMr0qxZQCWLF8ohAVrYu56WCBe4UwZ6fw5nYzNeEGcf8Wlj0f2dsvIeYJTQZRWchxPEVW3YPoqdXBCkEEro5y8RWJ0XxyCYUyFSxlo6vcAoNU0q0ULx7vI4N",
      CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: application/x-www-form-urlencoded"
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      echo "cURL Error #:" . $err;
    } else {
      echo $response;
    }
  }

  public function hashCreate()
  {
    $response = $this->input->post();
    $data['reg_id'] = json_encode($response);
    // $this->Common_model->insert_data($data,'userDetails');
    $this->Common_Model->register('allPaymentDetails', $data);
    // It is very important to calculate the hash using the returned value and compare it against the hash that was sent while payment request, to make sure the response is legitimate /
    $salt = "64b46227472dc27a4ad162a0265481a95ad9494a"; // put your salt provided by traknpay here
    if (isset($salt) && !empty($salt)) {
      $response['calculated_hash'] = $this->hashCalculate($salt, $response);
      $response['valid_hash'] = ($response['hash'] == $response['calculated_hash']) ? 'Yes' : 'No';
    } else {
      $response['valid_hash'] = 'Set your salt in return_page.php to do a hash check on receiving response from Traknpay';
    }
  }

  public function hashCalculate($salt, $input)
  {
    //  Remove hash key if it is present
    unset($input['hash']);
    /*Sort the array before hashing*/
    ksort($input);

    /*first value of hash data will be salt*/
    $hash_data = $salt;

    /*Create a | (pipe) separated string of all the $input values which are available in $hash_columns*/
    foreach ($input as $key => $value) {
      if (strlen($value) > 0) {
        $hash_data .= '|' . $value;
      }
    }

    $hash = null;
    if (strlen($hash_data) > 0) {
      $hash = strtoupper(hash("sha512", $hash_data));
    }

    return $hash;
  }


  public function getDistance($latitude1, $longitude1, $latitude2, $longitude2)
  {
    $earth_radius = 6371;
    $dLat = deg2rad($latitude2 - $latitude1);
    $dLon = deg2rad($longitude2 - $longitude1);
    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * asin(sqrt($a));
    $d = $earth_radius * $c;
    return $d;
  }


  public function checkDestance()
  {
    $distance = $this->getDistance(30.7402543, 76.7738928, $this->input->post('lat'), $this->input->post('long'));
    if ($distance < 15) {
      $message['success'] = '1';
      $message['message'] = 'it is okay';
    } else {
      $details['latitude'] = $this->input->post('lat');
      $details['longitude'] = $this->input->post('long');
      $details['address'] = $this->input->post('address');
      $details['userId'] = $this->input->post('userId');;
      $details['created'] = date('Y-m-d H:i:s');
      $data = $this->Common_Model->register('userOutstationDetails', $details);
      if ($data) {
        $message['success'] = '0';
        $message['message'] = 'Sorry, we are not currently providing service in this area yet. Please, select between Chandigarh, Mohali and Panchkula.';
      }
    }
    echo json_encode($message);
  }

  public function checkUserEmail()
  {
    if (filter_var($this->input->post('email'), FILTER_VALIDATE_EMAIL)) {
      $data = $this->db->get_where('userDetails', array('email' => $this->input->post('email')))->row_array();
      if (!empty($data)) {
        $message['success'] = "0";
        $message['message'] = "Email is alerdy exists";
      } else {
        $message['success'] = "1";
        $message['message'] = "Email does not exists";
      }
    } else {
      $message['success'] = "0";
      $message['message'] = "Please Enter valid Email";;
    }
    echo json_encode($message);
  }

  public function checkUserPhone()
  {
    $data = $this->db->get_where('userDetails', array('phone' => $this->input->post('phone')))->row_array();
    if (!empty($data)) {
      $message['success'] = "0";
      $message['message'] = "Email is already exists";
    } else {
      $otp = rand(9999, 1000);
      $message['success'] = "1";
      $message['message'] = "Phone does not exists";
      $message['otp'] = (string)$otp;
    }
    echo json_encode($message);
  }

  public function userLogin()
  {
    if ($this->input->post()) {
      $email = $this->input->post('email');
      //$data = $this->User_model->userLogin('userDetails',$email,md5($this->input->post('password')));
      $password = md5($this->input->post('password'));
      $data = $this->db->query("select * from userDetails where (email='$email' or phone='$email') and password = '$password'")->row_array();
      //$data = $this->db->get_where('userDetails',array('phone' =>$email,'password' =>  md5($this->input->post('password'))))->row_array();
      if (!empty($data)) {
        if ($data['phoneVerifyStatus'] == '0') {
          $message = array(
            'success' => '2',
            'message' => 'Please Verify Your Phone Number',
            'details' => $data
          );
        } else {
          $datas = array('onlineStatus' => '1', 'reg_id' => $this->input->post('reg_id'), 'device_type' => $this->input->post('device_type'));
          $update = $this->Common_Model->update('userDetails', $datas, 'id', $data['id']);
          $userDetails = $this->db->get_where('userDetails', array('id' => $data['id']))->row_array();
          $message = array(
            'success' => '1',
            'message' => 'user login successfully',
            'details' => $userDetails
          );
        }
      } else {
        $message = array(
          'success' => '0',
          'message' => 'Please enter valid login credentials!',
        );
      }
    } else {
      $message = array(
        'message' => 'please enter parameters',
      );
    }
    echo json_encode($message);
  }

  public function matchVerificationToken()
  {
    if ($this->input->post()) {
      $id = $this->input->post('id');
      $token = $this->input->post('token');
      $data = $this->User_model->match_Verification_token($id, $token, 'userDetails');
      if (!empty($data)) {
        $datas = array('phoneVerifyStatus' => '1');
        $update = $this->Common_Model->update('userDetails', $datas, 'id', $id);
        $message = array(
          'success' => '1',
          'message' => 'verification token match successfully',
        );
        echo json_encode($message);
      } else {
        $message = array(
          'message' => "sorry your verification token does not match!",
          'success' => '0'
        );
        echo json_encode($message);
      }
    } else {
      $message = array(
        'message' => 'please enter parameters',
      );
      echo json_encode($message);
    }
  }

  public function userRegister()
  {
    if ($this->input->post()) {
      $phone = $this->input->post('phone');
      $email = $this->input->post('email');
      $data = $this->db->get_where('userDetails', array('email' => $email))->row_array();
      if (!empty($phone)) {
        $dataa = $this->db->get_where('userDetails', array('phone' => $phone))->row_array();
      }
      if (!empty($data)) {
        $message = array('success' => '0', 'message' => 'Email already exists');
      } elseif (!empty($dataa)) {
        $message = array('success' => '0', 'message' => 'Phone number already exists');
      } else {
        $details['name'] = $this->input->post('name');
        $details['email'] = $this->input->post('email');
        $details['phone'] = $this->input->post('phone');
        if (empty($this->input->post('phone'))) {
          $details['phoneVerifyStatus'] = '1';
        }
        $details['otp'] = mt_rand(1000, 9999);
        $details['password'] = md5($this->input->post('password'));
        $details['device_type'] = $this->input->post('device_type');
        $details['reg_id'] = $this->input->post('reg_id');
        $details['login_type'] = $this->input->post('login_type');;
        $details['created'] = date('Y-m-d H:i:s');
        $data = $this->Common_Model->register('userDetails', $details);
        if ($data) {
          $insert_id = $this->db->insert_id();
          $userDetails = $this->db->get_where('userDetails', array('id' => $insert_id))->row_array();
          $message = array('success' => '1', 'message' => 'User registered successfully', 'details' => $userDetails);
        } else {
          $message = array('success' => '0', 'message' => 'Please Try after some time');
        }
      }
    } else {
      $message = array('message' => 'Please enter parameters');
    }
    echo json_encode($message);
  }

  public function userLogin2()
  {
    if ($this->input->post()) {
      $email = $this->input->post('email');
      $data = $this->User_model->userLogin('userDetails', $email, md5($this->input->post('password')));
      if (!empty($data)) {
        $datas = array('reg_id' => $this->input->post('reg_id'), 'device_type' => $this->input->post('device_type'));
        $update = $this->Common_Model->update('userDetails', $datas, 'id', $data['id']);
        $userDeatils = $this->db->get_where('userDetails', array('id' => $data['id']))->row_array();
        if ($update) {
          $message = array(
            'success' => '1',
            'message' => 'User login successfully',
            'details' => $userDeatils
          );
        } else {
          $message = array(
            'success' => '0',
            'message' => 'Please Try After Some Time',
          );
        }
      } else {
        $message = array(
          'success' => '0',
          'message' => 'Please enter valid login credentials!',
        );
      }
    } else {
      $message = array(
        'message' => 'Please enter parameters',
      );
    }
    echo json_encode($message);
  }

  public function resendVerificationToken()
  {
    if ($this->input->post()) {
      $id = $this->input->post('id');
      $otp = mt_rand(1000, 9999);
      $user = $this->db->get_where('userDetails', array('id' => $id))->row_array();
      if (!empty($user)) {
        $datas = array('otp' => $otp);
        $update = $this->Common_Model->update('userDetails', $datas, 'id', $id);

        $mess = "Hi " . $user['name'] . " your otp here " . $otp;
        $number = $user['phone'];
        $sendMessage = file_get_contents("https://www.fast2sms.com/dev/bulk?authorization=OCpJriqzRs9yoexQYvINak8SfhcW7PjtAgdL0BEMXFHmZD4GunnMfLtmDHBu4EAG6Y7idvNFqKbWjSJe&sender_id=Omnino&message=" . urlencode(".$mess.") . "&language=english&route=p&numbers=" . urlencode('' . $number . ''));


        $message = array(
          'success' => '1',
          'message' => 'Otp send to your phone',
          'otp' => (string)$datas['otp'],
        );
      } else {
        $message = array(
          'success' => '0',
          'message' => "Error",
        );
      }
    } else {
      $message = array(
        'message' => 'Please enter parameters',
      );
    }
    echo json_encode($message);
  }

  public function userCheckAppleId()
  {
    if ($this->input->post()) {
      $check_social_id = $this->Common_Model->get_data_by_id('userDetails', 'social_id', $this->input->post('social_id'));
      if (!empty($check_social_id)) {
        $message = array('success' => '1', 'message' => 'User login successfully', 'details' => $check_social_id);
      } else {
        $message = array('success' => '0', 'message' => 'Please create your account');
      }
    } else {
      $message = array(
        'message' => 'Please enter parameters', // Automatically generated by the model
      );
    }

    echo json_encode($message);
  }

  public function registerPhone()
  {
    if ($this->input->post()) {
      $userId = $this->input->post('userId');
      $check_user = $this->Common_Model->get_data_by_id('userDetails', 'id', $userId);
      if (!empty($check_user['phone'])) {
        $message = array(
          'success' => '1',
          'message' => 'Phone number is registered' // Automatically generated by the model
        );
      } else {
        $check_social_id = $this->Common_Model->get_data_by_id('userDetails', 'phone', $this->input->post('phone'));
        if (empty($check_social_id)) {
          $data = array(
            'phone' => $this->input->post('phone')
          );
          $insert = $this->db->update('userDetails', $data, array('id' => $userId));
          if ($insert) {
            $userDetails = $this->db->get_where('userDetails', array('id' => $userId))->row_array();
            $message = array(
              'success' => '1',
              'message' => 'Profile Update Successfully', // Automatically generated by the model
              'details' => $userDetails
            );
          } else {
            $message = array(
              'success' => '0',
              'message' => 'Try after sometime' // Automatically generated by the model
            );
          }
        } else {
          $message = array(
            'success' => '0',
            'message' => 'Phone number already exist' // Automatically generated by the model
          );
        }
      }
    } else {
      $message = array(
        'message' => 'Please enter parameters' // Automatically generated by the model
      );
    }
    echo json_encode($message);
  }

  public function UserAppleLogin()
  {
    if ($this->input->post()) {
      $check_social_id = $this->db->get_where('userDetails', array('social_id' => $this->input->post('social_id')))->row_array();
      //      if(!empty($this->input->post('email'))){
      //            $check_email = $this->db->get_where('userDetails',array('email'=>$this->input->post('email')))->row_array();
      //      }

      if (!empty($check_social_id)) {
        $message = array('success' => '1', 'message' => 'User login successfully', 'details' => $check_social_id);
      } else {
        $data['name'] = $this->input->post('username');
        $data['email'] = $this->input->post('email');
        // $data['phone'] = $this->input->post('phone');
        $data['social_id'] = $this->input->post('social_id');
        $data['device_type'] = "ios";
        $data['reg_id'] = $this->input->post('reg_id');
        // $data['image'] =$this->input->post('image');
        // $data['login_type'] =$this->input->post('loginType');
        $data['created'] = date('y-m-d h:i:s');
        $details = $this->Common_Model->register('userDetails', $data);
        if ($details) {
          $insert_id = $this->db->insert_id();
          $datass['userId'] = $insert_id;
          $datass['productEmails'] = "1";
          $datass['marketingEmails'] = "1";
          $insert = $this->db->insert('userSettings', $datass);
          $userDetails = $this->db->get_where('userDetails', array('id' => $insert_id))->row_array();
          $message = array('success' => '1', 'message' => 'User register successfully', 'details' => $userDetails);
        }
      }
    } else {
      $message = array(
        'message' => 'Please enter parameters',
      );
    }
    echo json_encode($message);
  }


  public function userCheckSocialId()
  {
    if ($this->input->post()) {
      $check_social_id = $this->Common_Model->get_data_by_id('userDetails', 'social_id', $this->input->post('social_id'));
      if (!empty($check_social_id)) {
        if ($check_social_id['dob'] == '' || $check_social_id['dob'] == null) {
          $check_social_id['dob'] = "";
        }
        $datas = array('onlineStatus' => '1', 'reg_id' => $this->input->post('reg_id'), 'device_type' => $this->input->post('device_type'));
        $update = $this->Common_Model->update('userDetails', $datas, 'id', $check_social_id['id']);
        $message = array('success' => '1', 'message' => 'User login successfully', 'details' => $check_social_id);
      } else {
        $message = array('success' => '0', 'message' => 'Please create your account');
      }
    } else {
      $message = array(
        'message' => 'Please enter parameters', // Automatically generated by the model
      );
    }

    echo json_encode($message);
  }

  public function UserSocialLogin()
  {
    if ($this->input->post()) {
      $check_social_id = $this->db->get_where('userDetails', array('social_id' => $this->input->post('social_id')))->row_array();
      if (!empty($check_social_id)) {
        if ($check_social_id['dob'] == '' || $check_social_id['dob'] == null) {
          $check_social_id['dob'] = "";
        }
        $message = array('success' => '1', 'message' => 'User login successfully', 'details' => $check_social_id);
      } else {
        $data['name'] = $this->input->post('username');
        $data['email'] = $this->input->post('email');
        $data['phone'] = $this->input->post('phone');
        $data['social_id'] = $this->input->post('social_id');
        $data['device_type'] = $this->input->post('device_type');
        $data['reg_id'] = $this->input->post('reg_id');
        $data['image'] = $this->input->post('image');
        $data['login_type'] = $this->input->post('loginType');
        $data['expCoin'] = '0';
        $data['leval'] = '0';
        $data['coin'] = '0';
        $data['purchasedCoin'] = '0';
        $data['wallet'] = '0';
        $data['incomeDollar'] = '0';
        $data['created'] = date('y-m-d h:i:s');
        $details = $this->Common_Model->register('userDetails', $data);
        if ($details) {
          $insert_id = $this->db->insert_id();
          $datass['userId'] = $insert_id;
          $datass['productEmails'] = "1";
          $datass['marketingEmails'] = "1";
          $insert = $this->db->insert('userSettings', $datass);
          $userDetails = $this->db->get_where('userDetails', array('id' => $insert_id))->row_array();
          $message = array('success' => '1', 'message' => 'User register successfully', 'details' => $userDetails);
        }
      }
    } else {
      $message = array(
        'message' => 'Please enter parameters',
      );
    }
    echo json_encode($message);
  }

  public function getProductList()
  {
    $data = $this->db->get_where('productList', array('status' => '1'))->result_array();
    if (!empty($data)) {
      $dd = $this->db->get_where('pages', array('id' => 5, 'status' => '1'))->row_array();
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      if (!empty($dd))
        $message['homepageText']  = $dd['description'];
      else
        $message['homepageText']  = "";
      $message['details'] = $data;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function getProductListNew()
  {
    $data = $this->db->query('select * from productList where description <> "" ')->result_array();
    if (!empty($data)) {
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $data;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function getFaqList()
  {
    $data = $this->db->get('faq')->result_array();
    if (!empty($data)) {
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $data;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function getPartnerList()
  {
    $data = $this->db->get('partnerList')->result_array();
    if (!empty($data)) {
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $data;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function aboutUs()
  {
    $data['datas'] = $this->db->get_where('pages', array('id' => 1))->row_array();
    $this->load->view('template/about_us', $data);
  }

  public function terms()
  {
    $data['datas'] = $this->db->get_where('pages', array('id' => 3))->row_array();
    $this->load->view('template/terms', $data);
  }

  public function privacyAndPolicy()
  {
    $data['datas'] = $this->db->get_where('pages', array('id' => 4))->row_array();
    $this->load->view('template/privacy_and_policy', $data);
  }

  public function editUserProfile()
  {
    if ($this->input->post()) {
      $userId = $this->input->post('userId');
      $data = array(
        'name' => $this->input->post('name'),
        'email' => $this->input->post('email'),
        'phone' => $this->input->post('phone'),
        'address' => $this->input->post('address'),
        'dob' => $this->input->post('dob'),
        'country' => $this->input->post('country')
      );


      $insert = $this->db->update('users', $data, array('id' => $userId));
      if ($insert) {
        $userDetails = $this->db->get_where('users', array('id' => $userId))->row_array();
        $message = array(
          'success' => '1',
          'message' => 'Profile Update Successfully', // Automatically generated by the model
          'details' => $userDetails
        );
      } else {
        $message = array(
          'success' => '0',
          'message' => 'Try after sometime' // Automatically generated by the model
        );
      }
    } else {
      $message = array(
        'message' => 'Please enter parameters' // Automatically generated by the model
      );
    }
    echo json_encode($message);
  }

  public function userPlaceOrder()
  {
    if ($this->input->post()) {
      if ($this->input->post('paymentMethod') == '1') {
        $details['paymentMethod'] = '1';
        $details['userId'] = $this->input->post('userId');
        $details['bookingId'] = mt_rand(100000, 999999);
        $details['productId'] = $this->input->post('productId');
        $details['quantity'] = $this->input->post('quantity');
        $details['latitude'] = $this->input->post('latitude');
        $details['longitude'] = $this->input->post('longitude');
        $details['location'] = $this->input->post('location');
        $details['date'] = $this->input->post('date');
        if (empty($this->input->post('time'))) {
          $details['time'] = date('23:59:00');
        } else {
          $details['time'] = $this->input->post('time');
        }
        $details['pricePerLitre'] = $this->input->post('pricePerLitre');
        $details['totalPrice'] = $this->input->post('totalPrice');
        $details['created'] = date('Y-m-d H:i:s');
        $data = $this->Common_Model->register('userBookingOrder', $details);
        if ($data) {
          $insertId = $this->db->insert_id();
          $det['bookingId'] = $insertId;
          $datadd = $this->Common_Model->register('notificationBooking', $det);
          $message['success'] = '1';
          $message['paymentMethod'] = 'Cash';
          $message['message'] = 'Order booked successfully';
        }
      } else {
        $details['paymentMethod'] = '2';
        $details['userId'] = $this->input->post('userId');
        $details['bookingId'] = mt_rand(100000, 999999);
        $details['productId'] = $this->input->post('productId');
        $details['quantity'] = $this->input->post('quantity');
        $details['latitude'] = $this->input->post('latitude');
        $details['longitude'] = $this->input->post('longitude');
        $details['location'] = $this->input->post('location');
        $details['transactionId'] = $this->input->post('transactionId');
        $details['date'] = $this->input->post('date');
        if (empty($this->input->post('time'))) {
          $details['time'] = date('23:59:00');
        } else {
          $details['time'] = $this->input->post('time');
        }
        $details['pricePerLitre'] = $this->input->post('pricePerLitre');
        $details['totalPrice'] = $this->input->post('totalPrice');
        $details['created'] = date('Y-m-d H:i:s');
        $data = $this->Common_Model->register('userBookingOrder', $details);
        if ($data) {
          $insertId = $this->db->insert_id();
          $det['bookingId'] = $insertId;
          $datadd = $this->Common_Model->register('notificationBooking', $det);
          $message['success'] = '1';
          $message['paymentMethod'] = 'TranknPay';
          $message['message'] = 'Order booked successfully';
        }
      }
      if ($data) {
        $user = $this->db->get_where('userDetails', array('id' => $this->input->post('userId')))->row_array();
        $pro = $this->db->get_where('productList', array('id' => $this->input->post('productId')))->row_array();
        $mess = "";
        if (!empty($user['name'])) {
          $mess .= $user['name'] . ", ";
        }
        if (!empty($user['email'])) {
          $mess .= $user['email'] . " ";
        }
        if (!empty($user['phone'])) {
          $mess .= $user['phone'];
        }
        $mess .= " booked order for " . $this->input->post('quantity') . " litre of " . $pro['title'] . ", " . "address is " . $this->input->post('location');
        $number = '9815493702,9817664164';
        $sendMessage = file_get_contents("https://www.fast2sms.com/dev/bulk?authorization=OCpJriqzRs9yoexQYvINak8SfhcW7PjtAgdL0BEMXFHmZD4GunnMfLtmDHBu4EAG6Y7idvNFqKbWjSJe&sender_id=Omnino&message=" . urlencode(".$mess.") . "&language=english&route=p&numbers=" . urlencode('' . $number . ''));
      }
    } else {
      $message = array('success' => '0', 'message' => 'Please Enter Parameters.');
    }
    echo json_encode($message);
  }

  public function testingStripe()
  {
    $userId = 1;
    $price = (800 * 100); // converted into pence
    $get_price = $price;
    require_once dirname(dirname(dirname(__FILE__))) . '/libraries/stripe/init.php';
    \stripe\Stripe::setApiKey("sk_test_yAyO9JQMC8joBqVRb8JnY515008JvsDaqx"); //Replace with your Secret Key
    try {
      $payment_success = "success";
      $token = $this->input->post('token');

      $customer = \stripe\Customer::create(
        array(
          "source" => $token,
          "description" => $userId
        )
      );
      $charge = \stripe\Charge::create(
        array(
          "amount" => $get_price, // amount in pence, again
          "currency" => "USD",
          "customer" => $customer->id
        )
      );
    } catch (\stripe\Error\ApiConnection $e) {
      $payment_success = $e->getMessage();
    } catch (\stripe\Error\InvalidRequest $e) {
      echo "Network problem, perhaps try again";
    } catch (\stripe\Error\Api $e) {
      echo "there is an error in your card validity try in few minutes";
    } catch (\stripe\Error\Card $e) {
      echo "servers are down kindly try again in few minutes";
    }
    if ($payment_success == "success") {
      print_r($customer->id);
    }
  }

  public function getCurrentOrderList()
  {
    $date = date('Y-m-d');
    $time = date('H:i:s');
    $providerId = $this->input->post('userId');
    $jobs = $this->db->query("select userBookingOrder.*,productList.title,productList.image from userBookingOrder left join productList on productList.id = userBookingOrder.productId where ((userBookingOrder.date ='$date' and userBookingOrder.time >='$time') or (userBookingOrder.date >'$date')) and userBookingOrder.orderStatus not in ('5') and userBookingOrder.orderStatus not in ('2') and userBookingOrder.userId='$providerId' order by userBookingOrder.id desc")->result_array();

    if (!empty($jobs)) {
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $jobs;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function getPastOrderList()
  {
    $date = date('Y-m-d');
    $time = date('H:i:s');
    $providerId = $this->input->post('userId');
    $jobs = $this->db->query("select userBookingOrder.*,productList.title,productList.image from userBookingOrder left join productList on productList.id = userBookingOrder.productId where ((userBookingOrder.date ='$date' and userBookingOrder.time <'$time') or (userBookingOrder.date <='$date')) and userBookingOrder.orderStatus='5' and userBookingOrder.userId='$providerId' order by userBookingOrder.id desc")->result_array();
    if (!empty($jobs)) {
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $jobs;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function userReorderProduct()
  {
    $data = $this->db->get_where('userBookingOrder', array('id' => $this->input->post('orderId')))->row_array();
    if (!empty($data)) {
      if ($this->input->post('paymentMethod') == '1') {
        $details['paymentMethod'] = '1';
        $details['userId'] = $this->input->post('userId');
        $details['productId'] = $data['productId'];
        $details['bookingId'] = mt_rand(100000, 999999);
        $details['quantity'] = $data['quantity'];
        //$details['location'] = $data['location'];
        $details['latitude'] = $this->input->post('latitude');
        $details['longitude'] = $this->input->post('longitude');
        $details['location'] = $this->input->post('location');
        $details['date'] = $this->input->post('date');
        if (empty($this->input->post('time'))) {
          $details['time'] = date('23:59:00');
        } else {
          $details['time'] = $this->input->post('time');
        }
        $details['pricePerLitre'] = $data['pricePerLitre'];
        $details['totalPrice'] = $data['totalPrice'];
        $details['created'] = date('Y-m-d H:i:s');
        $data1 = $this->Common_Model->register('userBookingOrder', $details);
        if ($data1) {
          $message['success'] = '1';
          $message['paymentMethod'] = 'Cash';
          $message['message'] = 'Order booked successfully';
        }
      } else {
        $details['paymentMethod'] = '2';
        $details['userId'] = $this->input->post('userId');
        $details['bookingId'] = mt_rand(100000, 999999);
        $details['productId'] = $data['productId'];
        $details['quantity'] =  $data['quantity'];
        $details['latitude'] = $this->input->post('latitude');
        $details['longitude'] = $this->input->post('longitude');
        $details['location'] = $this->input->post('location');
        $details['transactionId'] = $this->input->post('transactionId');
        $details['date'] = $this->input->post('date');
        if (empty($this->input->post('time'))) {
          $details['time'] = date('23:59:00');
        } else {
          $details['time'] = $this->input->post('time');
        }
        $details['pricePerLitre'] = $this->input->post('pricePerLitre');
        $details['totalPrice'] = $this->input->post('totalPrice');
        $details['created'] = date('Y-m-d H:i:s');
        $data1 = $this->Common_Model->register('userBookingOrder', $details);
        if ($data1) {
          $insertId = $this->db->insert_id();
          $det['bookingId'] = $insertId;
          $datadd = $this->Common_Model->register('notificationBooking', $det);
          $message['success'] = '1';
          $message['paymentMethod'] = 'TranknPay';
          $message['message'] = 'Order booked successfully';
        }
      }
      if ($data1) {
        $user = $this->db->get_where('userDetails', array('id' => $this->input->post('userId')))->row_array();
        $pro = $this->db->get_where('productList', array('id' => $data['productId']))->row_array();
        $mess = "";
        if (!empty($user['name'])) {
          $mess .= $user['name'];
        }
        if (!empty($user['email'])) {
          $mess .= "," . $user['email'];
        }
        if (!empty($user['phone'])) {
          $mess .= "," . $user['phone'];
        }
        $mess .= " booked order for " . $data['quantity'] . " litre of " . $pro['title'] . ", " . "address is " . $this->input->post('location');;
        $number = '9815493702,9817664164';
        $sendMessage = file_get_contents("https://www.fast2sms.com/dev/bulk?authorization=OCpJriqzRs9yoexQYvINak8SfhcW7PjtAgdL0BEMXFHmZD4GunnMfLtmDHBu4EAG6Y7idvNFqKbWjSJe&sender_id=Omnino&message=" . urlencode(".$mess.") . "&language=english&route=p&numbers=" . urlencode('' . $number . ''));
      }
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function getTrackOrderList()
  {
    $orderId = $this->input->post('bookingOrderId');
    $jobs = $this->db->query("select userBookingOrder.*,productList.title,productList.image from userBookingOrder left join productList on productList.id = userBookingOrder.productId where userBookingOrder.id='$orderId'")->row_array();
    if (!empty($jobs)) {
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $jobs;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function getLatestOrderList()
  {
    $date = date('Y-m-d');
    $time = date('H:i:s');
    $providerId = $this->input->post('userId');
    $jobs = $this->db->query("select userBookingOrder.*,productList.title,productList.image from userBookingOrder left join productList on productList.id = userBookingOrder.productId where ((userBookingOrder.date ='$date' and userBookingOrder.time >='$time') or (userBookingOrder.date >'$date')) and userBookingOrder.orderStatus not in ('5','2') and userBookingOrder.userId='$providerId' order by date,time asc")->row_array();
    if (!empty($jobs)) {
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $jobs;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function forgotPassword()
  {
    if ($this->input->post()) {
      $email = $this->input->post('email');
      $check_email = $this->db->get_where('userDetails', array('phone' => $email))->row_array();
      if (empty($check_email)) {
        $message = array(
          'message' => 'This phone number is not registered!',
          'success' => '0'
        );
      } else {
        $length = 8;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $password = '';
        for ($i = 0; $i < $length; $i++) {
          $password .= $characters[rand(0, $charactersLength - 1)];
        }
        $data = array(
          'password' => md5($password),
          'fStatus' => '1'
        );
        $update = $this->db->update('userDetails', $data, array('phone' => $email));
        if ($update) {
          //        $this->load->library('email');
          // $config['mailtype'] = 'html';
          // $this->email->initialize($config);
          // $this->email->from('info@petrowagon.com');
          // $this->email->to($check_email['email']);
          // $this->email->subject("PetroWagon - Regarding Forget password.");
          // $message = "Hi ".$check_email['name']." your new password here ".$password;
          // $this->email->message($message);
          // $send = $this->email->send();
          $mess = "Hi " . $check_email['name'] . " your password is here " . $password;
          $number = $check_email['phone'];
          $sendMessage = file_get_contents("https://www.fast2sms.com/dev/bulk?authorization=OCpJriqzRs9yoexQYvINak8SfhcW7PjtAgdL0BEMXFHmZD4GunnMfLtmDHBu4EAG6Y7idvNFqKbWjSJe&sender_id=Omnino&message=" . urlencode(".$mess.") . "&language=english&route=p&numbers=" . urlencode('' . $number . ''));
          $message = array(
            'message' => 'Your password send to your phone',
            'success' => '1'
          );
        } else {
          $message = array(
            'message' => 'some error occured',
          );
        }
      }
    } else {
      $message = array(
        'message' => 'please enter parameters',
      );
    }
    echo json_encode($message);
  }

  public function changePassword()
  {
    if ($this->input->post()) {
      $id = $this->input->post('userId');
      $old_password = md5($this->input->post('old_password'));
      $new_password = md5($this->input->post('new_password'));
      $check_password = $this->Common_Model->check_password($old_password, $id);
      if (empty($check_password)) {
        $message = array(
          'success' => "0",
          'message' => "Old Password Doesn't Match"
        );
        echo json_encode($message);
      } else {
        $data = array(
          'password' => $new_password,
        );
        $update_password = $this->db->update('users', $data, array('id' => $id));
        if ($update_password) {
          $message = array(
            'success' => "1",
            'message' => "Password Changed Successfully"
          );
          echo json_encode($message);
        } else {
          $message = array(
            'success' => "0",
            'message' => "Please try again"
          );
          echo json_encode($message);
        }
      }
    }
  }

  public function notificationTesting()
  {
    if ($this->input->post()) {
      $bookingId = $this->input->post('bookingId');
      $order = $this->db->get_where('userBookingOrder', array('id' => $bookingId))->row_array();
      if (!empty($order)) {
        $datas1['orderStatus'] = $this->input->post('status');
        $up = $this->Common_Model->update('userBookingOrder', $datas1, 'id', $bookingId);
        if ($up) {
          $datas = $this->db->get_where('userBookingOrder', array('id' => $bookingId))->row_array();
          $reg_id = $this->db->get_where('userDetails', array('id' => $order['userId'], 'notificationStatus' => '1'))->row_array();
          $regIDs = $reg_id['reg_id'];
          if ($datas['orderStatus'] == '1') {
            $message = $reg_id['name'] . " " . "Your Order Accepted";
            $type = 'accept';
          } elseif ($datas['orderStatus'] == '2') {
            $message = $reg_id['name'] . " " . "Your Order Is Rejected";
            $type = 'reject';
          } elseif ($datas['orderStatus'] == '3') {
            $message = $reg_id['name'] . " " . "Your Order In Progress";
            $type = 'progress';
          } elseif ($datas['orderStatus'] == '4') {
            $message = $reg_id['name'] . " " . "Your Order Is On The Way";
            $type = 'onTheWay';
          } elseif ($datas['orderStatus'] == '5') {
            $message = $reg_id['name'] . " " . "Your Order Is Delivered Successfully";
            $type = 'delivered';
          } else {
            $message = $reg_id['name'] . " " . "Your Order Accepted";
            $type = 'accept';
          }
          $registrationIds =  array($regIDs);
          define('API_ACCESS_KEY', 'AAAAwGOedEY:APA91bFPToCnwZiEY9WDk1AglCOgEncjvRaCXILX1iHkyplckUf_ZG8a6hlwl6bdFe6XMxpOUJtp4wv2H6EPi70gKhXmhv9kMzS_K_7Ktr1x_oFPpy0NQaUq42-yaKWoRndNmMShg555');
          $msg = array(
            'message'  => $message,
            'title'    => 'PetroWagon',
            'subtitle' => 'Response',
            'vibrate'  => 1,
            'sound'    => 1,
            'largeIcon'  => 'large_icon',
            'smallIcon'  => 'small_icon',
            'type'      => $type
          );
          $fields = array(
            'registration_ids'   => $registrationIds,
            'data'     => $msg
          );
          $headers = array(
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
          );
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
          $result = curl_exec($ch);
          //print_r($result);
          //die;
          curl_close($ch);
          $message1['success'] = "1";
          $message1['message'] = "Status updated successfully";
          //$message['details'] = $jobs;
        } else {
          $message1 = array(
            'success' => "0",
            'message' => "Try after some time"
          );
        }
      }
    } else {
      $message1 = array(
        'success' => "0",
        'message' => "Please enter parameters"
      );
    }
    echo json_encode($message1);
  }

  public function pushNotificationsOnOff()
  {
    if ($this->input->post()) {
      $deta = $this->db->get_where('userDetails', array('id' => $this->input->post('userId')))->row_array();
      if (!empty($deta)) {
        $id = $this->input->post('userId');
        $datas = array(
          'notificationStatus' => $this->input->post('status')
        );
        $update = $this->Common_Model->update('userDetails', $datas, 'id', $id);
        if ($update) {
          $deta1 = $this->db->get_where('userDetails', array('id' => $this->input->post('userId')))->row_array();
          if ($deta1['notificationStatus'] == '0') {
            $message = array(
              'success' => '1',
              'message' => 'Push notifications are off now'
            );
          } else {
            $message = array(
              'success' => '1',
              'message' => 'Push notifications are on now'
            );
          }
        }
      } else {
        $message = array(
          'success' => '0',
          'message' => 'No details found'
        );
      }
    } else {
      $message = array(
        'message' => 'please enter parameters',
      );
    }
    echo json_encode($message);
  }

  public function getTransactionList()
  {
    $providerId = $this->input->post('userId');
    $jobs = $this->db->query("select userBookingOrder.*,productList.title,productList.image from userBookingOrder left join productList on productList.id = userBookingOrder.productId where userBookingOrder.orderStatus in ('5') and userBookingOrder.userId='$providerId' order by userBookingOrder.id desc")->result_array();
    if (!empty($jobs)) {
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $jobs;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function cancelOrder()
  {
    if ($this->input->post()) {
      $details['orderStatus'] = '2';
      $details['cancelType'] = '1';
      $id = $this->input->post('orderId');
      $update = $this->Common_Model->update('userBookingOrder', $details, 'id', $id);
      if ($update) {
        $message['success'] = "1";
        $message['message'] = "Your order cancelled successfully";
      } else {
        $message['success'] = "0";
        $message['message'] = "Try after sometime";
      }
    } else {
      $message['message'] = "Please enter parameters";
    }
    echo json_encode($message);
  }

  public function userChangeAlert()
  {
    $checkData = $this->db->get_where('userSettings', array('userId' => $this->input->post('userId')))->row_array();
    if (empty($checkData)) {
      $data['userId'] = $this->input->post('userId');
      $data['productEmails'] = $this->input->post('productEmails');
      $data['marketingEmails'] = $this->input->post('marketingEmails');
      $insert = $this->db->insert('userSettings', $data);
      if ($insert) {
        $message['success'] = '1';
        $message['message'] = 'Permission Assign Successfully';
      }
    } else {
      $data['userId'] = $this->input->post('userId');
      $data['productEmails'] = $this->input->post('productEmails');
      $data['marketingEmails'] = $this->input->post('marketingEmails');
      $update = $this->Common_Model->update('userSettings', $data, 'id', $checkData['id']);
      if ($update) {
        $message['success'] = '1';
        $message['message'] = 'Permission Update Successfully';
      }
    }
    echo json_encode($message);
  }

  public function getUserSettings()
  {
    if ($this->input->post()) {
      $deta = $this->db->get_where('userSettings', array('userId' => $this->input->post('userId')))->row_array();
      if (!empty($deta)) {
        $deta1 = $this->db->get_where('userDetails', array('id' => $this->input->post('userId')))->row_array();
        //print_r($deta1);die;
        $deta['nstatus'] = $deta1['notificationStatus'];
        $message['success'] = '1';
        $message['message'] = 'details found successfully';
        $message['details'] = $deta;
      } else {
        $message = array(
          'success' => '0',
          'message' => 'No details found'
        );
      }
    } else {
      $message = array(
        'message' => 'please enter parameters',
      );
    }
    echo json_encode($message);
  }

  public function getCharges()
  {
    $jobs = $this->db->get('charges')->result_array();
    if (!empty($jobs)) {
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $jobs;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function getDeliveryTime()
  {
    $jobs = $this->db->get('deliveryTime')->result_array();
    if (!empty($jobs)) {
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $jobs;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function getMinimumValues()
  {
    $jobs = $this->db->get('minimumValue')->result_array();
    if (!empty($jobs)) {
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $jobs;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function checkFast2Sms()
  {
    $otp = 3390;
    $message = "hello your otp is $otp";
    $number = '7087772970';
    // $url = 'https://www.fast2sms.com/dev/bulk?authorization=OCpJriqzRs9yoexQYvINak8SfhcW7PjtAgdL0BEMXFHmZD4GunnMfLtmDHBu4EAG6Y7idvNFqKbWjSJe&sender_id=Omnino&message=".urlencode("'.$message.'")."&language=english&route=p&numbers=".urlencode('.$number.')';
    // echo $url;
    $sendMessage = file_get_contents("https://www.fast2sms.com/dev/bulk?authorization=OCpJriqzRs9yoexQYvINak8SfhcW7PjtAgdL0BEMXFHmZD4GunnMfLtmDHBu4EAG6Y7idvNFqKbWjSJe&sender_id=Omnino&message=" . urlencode(".$message.") . "&language=english&route=p&numbers=" . urlencode('' . $number . ''));

    print_r($sendMessage);
    //die;
  }

  public function getOrderRejectList()
  {
    $userId = $this->input->post('userId');
    $jobs = $this->db->query("select userBookingOrder.*,productList.title,productList.image from userBookingOrder left join productList on productList.id = userBookingOrder.productId where userBookingOrder.orderStatus='2' and userId='$userId'")->result_array();
    if (!empty($jobs)) {
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $jobs;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function chkServer($host, $port)
  {
    $hostip = @gethostbyname($host);
    if ($hostip == $host) {
      echo "Server is down or does not exist";
    } else {
      if (!$x = @fsockopen($hostip, $port, $errno, $errstr, 5)) {
        echo "Port $port is closed.";
      } else {
        echo "Port $port is open.";
        if ($x) {
          @fclose($x);
        }
      }
    }
  }

  public function ser()
  {
    $x = $this->chkServer('gateway.sandbox.push.apple.com', 2195);
    print_r($x);
    //chkServer('gateway.push.apple.com',2195);
  }

  public function pushIosNotification()
  {
    // (Android)API access key from Google API's Console.
    static $API_ACCESS_KEY = 'AIzaSyDG3fYAj1uW7VB-wejaMJyJXiO5JagAsYI';
    // (iOS) Private key's passphrase.
    static $passphrase = 'joashp';
    // (Windows Phone 8) The name of our push channel.
    static $channelName = "joashp";
    // Change the above three vriables as per your app.
    // Sends Push notification for iOS users
    $text = "hello boy";
    $msg_payload = array(
      'mtitle' => 'Hello PetroWagon',
      'mdesc' => $text,
    );
    $data = $msg_payload;
    $registrationIds = '6683de0c388ebf6efd039ab14cd7a6342fc181db38dca646e157ef61c96fdb86';
    $deviceToken = $registrationIds;

    $ctx = stream_context_create();
    // ck.pem is your certificate file
    // $t = $this->load->view('pemFile/pushcert.pem');
    $t = APPPATH . 'third_party/CertPetroWgn.pem';
    stream_context_set_option($ctx, 'ssl', 'local_cert', $t);
    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

    // Open a connection to the APNS server
    $fp = stream_socket_client(
      'ssl://gateway.sandbox.push.apple.com:2195',
      $err,
      $errstr,
      60,
      STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT,
      $ctx
    );

    if (!$fp)
      exit("Failed to connect: $err $errstr" . PHP_EOL);

    // Create the payload body
    $body['aps'] = array(
      'alert' => array(
        'title' => "fdgh",
        'body' => "fghd",
      ),
      'sound' => 'default'

    );
    // Encode the payload as JSON
    $payload = json_encode($body);

    // Build the binary notification
    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
    // Send it to the server
    $result = fwrite($fp, $msg, strlen($msg));
    print_r($result);
    // Close the connection to the server
    fclose($fp);
    if (!$result)
      return "0";
    else
      return "1";
  }

  public function checkVersion()
  {
    $data['versionStatus'] = '3';
    $message['success'] = '1';
    $message['message'] = 'App is in Maintenance';
    $message['details'] = $data;
    echo json_encode($message);
  }

  public function checkVersionIos()
  {
    $data['versionStatus'] = '1.0.3';
    $message['success'] = '1';
    $message['message'] = 'App is in Maintenance';
    $message['details'] = $data;
    echo json_encode($message);
  }

  public function forgotPassword1()
  {
    if ($this->input->post()) {
      $email = $this->input->post('email');
      $check_email = $this->db->query("select * from userDetails where email='$email' or phone='$email'")->row_array();
      if (empty($check_email)) {
        if ($this->input->post('status') == 1) {
          $message = array(
            'message' => 'This phone number is not registered!',
            'success' => '0'
          );
        } else {
          $message = array(
            'message' => 'This email is not registered!',
            'success' => '0'
          );
        }
      } else {
        $length = 8;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $password = '';
        for ($i = 0; $i < $length; $i++) {
          $password .= $characters[rand(0, $charactersLength - 1)];
        }
        $data = array(
          'password' => md5($password),
          'fStatus' => '1'
        );
        $update = $this->db->update('userDetails', $data, array('phone' => $email));
        if ($update) {
          //
          if ($this->input->post('status') == 1) {
            $mess = "Hi " . $check_email['name'] . " your password is here " . $password;
            $number = $check_email['phone'];
            $sendMessage = file_get_contents("https://www.fast2sms.com/dev/bulk?authorization=OCpJriqzRs9yoexQYvINak8SfhcW7PjtAgdL0BEMXFHmZD4GunnMfLtmDHBu4EAG6Y7idvNFqKbWjSJe&sender_id=Omnino&message=" . urlencode(".$mess.") . "&language=english&route=p&numbers=" . urlencode('' . $number . ''));
            $message = array(
              'message' => 'Your password send to your phone',
              'success' => '1'
            );
          } else {
            $this->load->library('email');
            $config['mailtype'] = 'html';
            $this->email->initialize($config);
            $this->email->from('info@petrowagon.com');
            $this->email->to($check_email['email']);
            $this->email->subject("PetroWagon - Regarding Forget password.");
            $message = "Hi " . $check_email['name'] . " your new password here " . $password;
            $this->email->message($message);
            $send = $this->email->send();
            if ($send) {
              $message = array(
                'message' => 'Your password send to your email',
                'success' => '1'
              );
            }
          }
        } else {
          $message = array(
            'message' => 'some error occured',
          );
        }
      }
    } else {
      $message = array(
        'message' => 'please enter parameters',
      );
    }
    echo json_encode($message);
  }

  public function getCountryCode()
  {
    $data = $this->db->get('country')->result_array();
    $i = 0;
    while ($i < count($data)) {
      $img = base_url() . "assets/country/flags-medium/" . strtolower($data[$i]['code']) . ".png";
      $data[$i]['image'] = $img;
      $i++;
    }
    if (!empty($data)) {
      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $data;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }




  public function enquiry()
  {
    if ($this->input->post()) {
      $data['username'] = $this->input->post('username');
      $data['email'] = $this->input->post('email');
      $data['dob'] = $this->input->post('dob');
      $data['mobile'] = $this->input->post('mobile');
      $data['address'] = $this->input->post('address');
      $data['city'] = $this->input->post('city');
      $data['time'] = $this->input->post('time');
      $test = $this->db->insert('enquiryDetails', $data);
      if ($test) {
        $message['success'] = "1";
        $message['message'] = "data submitted successfully";
        $message['data'] = $data;
      }
    } else {
      $message = array(
        'message' => 'please enter parameters',
      );
    }
    echo json_encode($message);
  }

  public function getSearchUsersList()
  {
    if (!empty($this->input->post('search'))) {
      $search = strtolower($this->input->post('search'));
      $userId  = $this->input->post('userId');
      $data = $this->db->query("select users.id,users.name,users.username,users.image from users where id NOT IN (select blockUserId from blockUser where userId = $userId ) and (username like '@$search%' || name like '$search%') ")->result_array();
      //echo $this->db->last_query();die;
    } else {
      $data = $this->db->query("select users.id,users.name,users.username,users.image from users where id NOT IN (select blockUserId from blockUser where userId = $userId ) ")->result_array();
    }
    if (!empty($data)) {
      foreach ($data as $list) {
        $checkStataus = $this->db->get_where('userFollow', array('userId' => $list['id'], 'followingUserId' => $this->input->post('userId'), 'status' => '1'))->num_rows();
        //print_r($checkStataus);die;
        // echo $this->db->last_query();
        $checkStataus1 = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $list['id'], 'status' => '1'))->num_rows();

        if (!empty($checkStataus) && !empty($checkStataus1)) {
          $list['status'] = '3';
        } elseif (!empty($checkStataus) && empty($checkStataus1)) {
          $list['status'] = '2';
        } elseif (empty($checkStataus) && !empty($checkStataus1)) {
          $list['status'] = '1';
        } else {
          $list['status'] = '0';
        }
        if (empty($list['image'])) {
          $list['image'] =  base_url() . 'uploads/no_image_available.png';
          //$list['frame'] =  base_url().'uplassets/gifts/epgifts/frame1.svga';
        }
        $dd[] = $list;
        // $dd['frame'] = base_url().'uplassets/gifts/epgifts/frame1.svga';

      }


      //$dd[] = $frame;


      $message['success'] = "1";
      $message['message'] = "Details found successfully";
      $message['details'] = $dd;
    } else {
      $message['success'] = "0";
      $message['message'] = "Details not found";
    }
    echo json_encode($message);
  }

  public function getSoundsList()
  {
    if (!empty($this->input->post('search'))) {
      $search = strtolower($this->input->post('search'));

      $checkData = $this->db->query("select id,length,author,title,soundImg,concat('" . base_url() . "',sound) as sound from sounds where lower(title) like '%$search%'")->result_array();
      //echo $this->db->last_query();die;
    } else {
      $checkData = $this->db->select("id,length,author,title,soundImg,concat('" . base_url() . "',sound) as sound")->get('sounds')->result_array();
    }
    if (!empty($checkData)) {
      foreach ($checkData as $deta) {
        $deta['favStatus'] = empty($this->db->get_where("favouriteSoundList", array('userId' => $this->input->post('userId'), 'soundId' => $deta['id'], 'status' => '1'))->row_array()) ? '0' : '1';
        if (!empty($deta['soundImg'])) {
          $deta['soundImg'] = base_url() . $deta['soundImg'];
        } else {
          $deta['soundImg'] = base_url() . 'uploads/logo/logo3.png';
        }
        $dd[] = $deta;
      }
      $message['success'] = '1';
      $message['message'] = 'Details found successfully';
      $message['details'] = $dd;
    } else {
      $message['success'] = '0';
      $message['message'] = 'Details not found';
    }
    echo json_encode($message);
  }

  public function getUserSoundFavoriteList()
  {
    // $checkData = $this->db->select("favouriteSoundList.*,sounds.title,concat('".base_url()."',sounds.sound) as sound")->join("sounds","sounds.id=favouriteSoundList.soundId","left")->get_where("favouriteSoundList",array('userId'=>$this->input->post('userId'),'status'=>'1'))->result_array();
    // $path = base_url();
    $userId = $this->input->post('userId');
    $query = $this->db->query("SELECT favouriteSoundList.*,soundImg,sounds.title,sounds.length,sounds.author,concat('" . base_url() . "',sounds.sound) as sound FROM `favouriteSoundList` left join sounds on sounds.id = favouriteSoundList.soundId WHERE favouriteSoundList.userId = $userId and favouriteSoundList.status = '1'");
    $checkData = $query->result_array();
    if (!empty($checkData)) {
      $message['success'] = '1';
      $message['message'] = 'Details found successfully';
      foreach ($checkData as $list) {
        if (!empty($list['soundImg'])) {
          $list['soundImg'] = base_url() . $list['soundImg'];
        } else {
          $list['soundImg'] = base_url() . 'uploads/logo/logo3.png';
        }
        $message['details'][] = $list;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'Details not found';
    }
    echo json_encode($message);
  }

  public function userBlock()
  {
    $checkBlock = $this->db->get_where('blockUser', array('userId' => $this->input->post('userId'), 'blockUserId' => $this->input->post('blockUserId')))->row_array();
    if (!empty($checkBlock)) {
      $this->db->delete('blockUser', array('id' => $checkBlock['id']));
      $message['success'] = '1';
      $message['message'] = 'user unblock successfully';
    } else {
      $data['userId'] = $this->input->post('userId');
      $data['blockUserId'] = $this->input->post('blockUserId');
      $data['created'] = date('Y-m-d H:i:s');
      $insert = $this->db->insert('blockUser', $data);
      if (!empty($insert)) {
        $this->db->delete('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $this->input->post('blockUserId')));
        $this->db->delete('userFollow', array('userId' => $this->input->post('blockUserId'), 'followingUserId' => $this->input->post('userId')));
        $message['success'] = '1';
        $message['message'] = 'user block successfully';
      } else {
        $message['success'] = '0';
        $message['message'] = 'Please try after some time';
      }
    }
    echo json_encode($message);
  }

  public function commentReply()
  {
    $data['userId'] = $this->input->post('userId');
    $data['videoId'] = $this->input->post('videoId');
    $data['commentId'] = $this->input->post('commentId');
    $data['comment'] = $this->input->post('comment');
    $data['created'] = date('Y-m-d H:i:s');
    $insert = $this->db->insert('videoSubComment', $data);
    if (!empty($insert)) {
      $lastId = $this->db->insert_id();
      $checkCommentCount = $this->db->get_where('userVideos', array('id' => $this->input->post('videoId')))->row_array();
      $upComment['commentCount'] = $checkCommentCount['commentCount'] + 1;
      $this->Common_Model->update('userVideos', $upComment, 'id', $this->input->post('videoId'));
      $userDetails = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
      if (empty($userDetails['userImage'])) {
        $data1['userImage'] =  base_url() . 'uploads/no_image_available.png';
      }





      $lists = $this->Common_Model->getMainCommentsVideos($this->input->post('userId'), $this->input->post('videoId'), $this->input->post('commentId'));
      if (empty($lists['userImage'])) {
        $lists['userImage'] = base_url() . 'uploads/no_image_available.png';
      }
      $lists['created'] = $this->getTime($lists['created']);
      $likeCount = $this->db->get_where('videoCommentsLikeOrUnlike', array('commentId' => $lists['id'], 'status' => '1'))->num_rows();
      $likeStatus = $this->db->get_where('videoCommentsLikeOrUnlike', array('commentId' => $lists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
      if (!empty($likeCount)) {
        $lists['likeCount'] = (string)$likeCount;
      } else {
        $lists['likeCount'] = '0';
      }
      if (!empty($likeStatus)) {
        $lists['likeStatus'] = true;
      } else {
        $lists['likeStatus'] = false;
      }
      $getSubComment =  $this->Common_Model->getSubComment($lists['id']);
      if (!empty($getSubComment)) {
        foreach ($getSubComment as $getSubComments) {
          if (empty($getSubComments['userImage'])) {
            $getSubComments['userImage'] = base_url() . 'uploads/no_image_available.png';
          }
          $getSubComments['created'] = $this->getTime($getSubComments['created']);
          $lists['subComment'][] = $getSubComments;
        }
      } else {
        $lists['subComment'] = [];
      }




      $comCount = $upComment['commentCount'];
      $message['success'] = '1';
      $message['message'] = 'comment send successfully';
      $message['commentCount'] = (string)$comCount;
      $message['details'][] = $lists;
    } else {
      $message['success'] = '0';
      $message['message'] = 'please try after some time';
    }
    echo json_encode($message);
  }

  public function userSearchApi($search, $userId)
  {
    $userLists =  $this->Common_Model->userSearch($search, $userId);
    if (!empty($userLists)) {
      foreach ($userLists as $userList) {
        if (empty($userList['image'])) {
          $userList['image'] = base_url() . 'uploads/no_image_available.png';
        }
        $checkStataus = $this->db->get_where('userFollow', array('userId' => $userList['id'], 'followingUserId' => $this->input->post('userId'), 'status' => '1'))->num_rows();
        $checkStataus1 = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $userList['id'], 'status' => '1'))->num_rows();
        if (!empty($checkStataus) && !empty($checkStataus1)) {
          $userList['followStatus'] = '3';
        } elseif (!empty($checkStataus) && empty($checkStataus1)) {
          $userList['followStatus'] = '2';
        } elseif (empty($checkStataus) && !empty($checkStataus1)) {
          $userList['followStatus'] = '1';
        } else {
          $userList['followStatus'] = '0';
        }
        $finalUserList[] = $userList;
      }
    } else {
      $finalUserList = [];
    }
    return $finalUserList;
  }

  public function videoSearchApi($search, $userId)
  {
    $userLists =  $this->Common_Model->videoSearch($search, $userId);
    if (!empty($userLists)) {
      foreach ($userLists as $userList) {

        if (!empty($userList['name'])) {
          $userList['username'] = $userList['name'];
        } else {
          $userList['username'] = $userList['username'];
        }


        // if(!empty($userList['hashTag'])){
        //  $userList['hashtagTitle'] = $this->hashTagName($userList['hashTag']);
        // }
        // else{
        //  $userList['hashtagTitle'] = '';
        // }

        if (!empty($userList['hashtag'])) {
          $userList['hashtagTitle'] = $this->hashTagName($userList['hashtag']);
          $finalTagIds = explode(',', $userList['hashtag']);
          foreach ($finalTagIds as $finalTagId) {
            $hashArray = $this->db->get_where('hashtag', array('id' => $finalTagId))->row_array();
            if (!empty($hashArray)) {
              $userList['hastagLists'][] = $hashArray;
            }
          }
        } else {
          $userList['hashtagTitle'] = '';
          $userList['hastagLists'] = [];
        }


        if (empty($userList['image'])) {
          $userList['image'] = base_url() . 'uploads/no_image_available.png';
        }
        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $userList['id'], 'userId' => $userId, 'status' => '1'))->row_array();
        if (!empty($likeStatus)) {
          $userList['likeStatus'] = true;
        } else {
          $userList['likeStatus'] = false;
        }

        $checkFollow = $this->db->get_where('userFollow', array('userId' => $userId, 'followingUserId' => $userList['userId'], 'status' => '1'))->row_array();
        if (!empty($checkFollow)) {
          $userList['followStatus'] = '1';
        } else {
          $userList['followStatus'] = '0';
        }

        $finalVideoList[] = $userList;
      }
    } else {
      $finalVideoList = [];
    }
    return $finalVideoList;
  }

  public function userHashTagApi($search, $userId)
  {
    $getHashtag =  $this->Common_Model->gethashTag($search);
    if (!empty($getHashtag)) {
      $finalHashTag = $getHashtag;
    } else {
      $finalHashTag = [];
    }
    return $finalHashTag;
  }

  public function userCategoryApi($search, $userId)
  {
    $getHashtag =  $this->Common_Model->getCategory($search);
    if (!empty($getHashtag)) {
      $finalHashTag = $getHashtag;
    } else {
      $finalHashTag = [];
    }
    return $finalHashTag;
  }



  public function userSoundApi($search, $userId)
  {
    $getSoundList =  $this->Common_Model->getSoundListApi($search);
    if (!empty($getSoundList)) {
      foreach ($getSoundList as $getSoundLists) {
        $favorites = $this->db->get_where('favouriteSoundList', array('userId' => $userId, 'soundId' => $getSoundLists['id'], 'status' => '1'))->row_array();
        if (!empty($favorites['status'])) {
          $getSoundLists['favoritesStatus'] = $favorites['status'];
        } else {
          $getSoundLists['favoritesStatus'] = '0';
        }
        $finalSoundList[] = $getSoundLists;
      }
    } else {
      $finalSoundList = [];
    }
    return $finalSoundList;
  }

  public function search()
  {
    $type = $this->input->post('type');
    $search = $this->input->post('search');
    $userId = $this->input->post('userId');
    if ($type == '1') {
      $finalUserList = $this->userSearchApi($search, $userId);
      $finalVideoList = [];
      $finalHashTag = [];
      $finalSoundList = [];
      $finalCategoryList = [];
    } elseif ($type == '2') {
      $finalUserList = [];
      $finalVideoList = $this->videoSearchApi($search, $userId);
      $finalHashTag = [];
      $finalSoundList = [];
      $finalCategoryList = [];
    } elseif ($type == '3') {
      $finalUserList = [];
      $finalVideoList = [];
      $finalHashTag = $this->userHashTagApi($search, $userId);
      $finalSoundList = [];
      $finalCategoryList = [];
    } elseif ($type == '4') {
      $finalUserList = [];
      $finalVideoList = [];
      $finalHashTag = [];
      $finalSoundList = $this->userSoundApi($search, $userId);
      $finalCategoryList = [];
    } elseif ($type == '5') {
      $finalUserList = [];
      $finalVideoList = [];
      $finalHashTag = [];
      $finalSoundList = [];
      $finalCategoryList = $this->userCategoryApi($search, $userId);
    } else {
      $finalUserList = $this->userSearchApi($search, $userId);
      $finalVideoList = $this->videoSearchApi($search, $userId);
      $finalHashTag = $this->userHashTagApi($search, $userId);
      $finalSoundList = $this->userSoundApi($search, $userId);
      $finalCategoryList = $this->userCategoryApi($search, $userId);
    }
    if (!empty($finalUserList) || !empty($finalVideoList) || !empty($finalHashTag) || !empty($finalSoundList) || !empty($finalCategoryList)) {
      $list['peopleList'] = $finalUserList;
      $list['videoList'] = $finalVideoList;
      $list['hasTagList'] = $finalHashTag;
      $list['soundList'] = $finalSoundList;
      $list['categoryList'] = $finalCategoryList;
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      $message['details'] = $list;
    } else {
      $message['success'] = '0';
      $message['message'] = 'List not found';
    }
    echo json_encode($message);
  }

  public function blockList()
  {
    $userLists =  $this->Common_Model->blockListUser($this->input->post('userId'));
    if (!empty($userLists)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      foreach ($userLists as $userList) {
        if (empty($userList['image'])) {
          $userList['image'] = base_url() . 'uploads/no_image_available.png';
        }
        $message['details'][] = $userList;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'List not found';
    }
    echo json_encode($message);
  }

  public function showFollowingUserStatus()
  {
    $checkStatus = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    if ($checkStatus['followingUser'] == 1) {
      $upStatus['followingUser'] = 0;
      $status = true;
    } else {
      $upStatus['followingUser'] = 1;
      $status = false;
    }
    $update = $this->Common_Model->update('users', $upStatus, 'id', $this->input->post('userId'));
    if (!empty($update)) {
      $message['success'] = '1';
      $message['message'] = 'follow Status update successfully';
      $message['status'] = $status;
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function showProfilePhotoStatus()
  {
    $checkStatus = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    if ($checkStatus['profilePhotoStatus'] == 1) {
      $upStatus['profilePhotoStatus'] = 0;
      $status = false;
    } else {
      $upStatus['profilePhotoStatus'] = 1;
      $status = true;
    }
    $update = $this->Common_Model->update('users', $upStatus, 'id', $this->input->post('userId'));
    if (!empty($update)) {
      $message['success'] = '1';
      $message['message'] = 'Profile Photo Status update successfully';
      $message['status'] = $status;
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function hashtag()
  {
    $getHashtag =  $this->Common_Model->gethashTag($this->input->post('search'));
    if (!empty($getHashtag)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      $message['details'] = $getHashtag;
    } else {
      $message['success'] = '0';
      $message['message'] = 'No details found';
    }
    echo json_encode($message);
  }


  public function soundVideos()
  {
    $getSound = $this->db->get_where('sounds', array('id' => $this->input->post('soundId')))->row_array();
    $getVideo = $this->db->order_by('id', 'asc')->get_where('userVideos', array('soundId' => $this->input->post('soundId')))->row_array();
    if (!empty($getVideo)) {
      $favorites = $this->db->get_where('favouriteSoundList', array('userId' => $this->input->post('userId'), 'soundId' => $this->input->post('soundId'), 'status' => '1'))->row_array();
      if (!empty($getSound['userId'])) {
        $userDetails  = $this->db->get_where('users', array('id' => $getSound['userId']))->row_array();
        if (!empty($userDetails['name'])) {
          $userInfo['username'] = $userDetails['name'];
        } else {
          $userInfo['username'] = $userDetails['username'];
        }
        $userInfo['name'] = $userDetails['name'];
        $userInfo['followers'] = $userDetails['followerCount'];
        $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $userDetails['id'], 'status' => '1'))->row_array();
        if (!empty($checkFollow)) {
          $userInfo['followStatus'] = '1';
        } else {
          $userInfo['followStatus'] = '0';
        }
        if (empty($userDetails['image'])) {
          $userInfo['image'] = base_url() . 'uploads/no_image_available.png';
        } else {
          $userInfo['image'] = $userDetails['image'];
        }
      } else {
        $userInfo['username'] = '';
        $userInfo['name'] = '';
        $userInfo['image'] = '';
        $userInfo['followers'] = '';
        $userInfo['followStatus'] = '0';
      }
      $userInfo['soundTitle'] = $getSound['title'];
      $userInfo['soundPath'] = base_url() . $getSound['sound'];
      $userInfo['videoCount'] = $getSound['soundCount'];
      $userInfo['description'] = $getVideo['description'];
      $userInfo['allowComment'] = $getVideo['allowComment'];
      $userInfo['allowDuetReact'] = $getVideo['allowDuetReact'];
      $userInfo['allowDownloads'] = $getVideo['allowDownloads'];
      $userInfo['viewVideo'] = $getVideo['viewVideo'];
      $userInfo['soundId'] = $getVideo['soundId'];
      $userInfo['commentCount'] = $getVideo['commentCount'];
      $userInfo['viewCount'] = $getVideo['viewCount'];
      $userInfo['likeCount'] = $getVideo['likeCount'];
      $userInfo['id'] = $getVideo['id'];
      $userInfo['userId'] = $getVideo['userId'];
      $userInfo['videoPath'] = $getVideo['videoPath'];
      $userInfo['downloadPath'] = $getVideo['downloadPath'];
      $userInfo['thumbnail'] = $getVideo['thumbnail'];
      if (!empty($favorites['status'])) {
        $userInfo['favoritesStatus'] = $favorites['status'];
      } else {
        $userInfo['favoritesStatus'] = '0';
      }

      $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $getVideo['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
      if (!empty($likeStatus)) {
        $userInfo['likeStatus'] = true;
      } else {
        $userInfo['likeStatus'] = false;
      }
      $userInfo['hashTag'] = $getVideo['hashTag'];
      // if(!empty($getVideo['hashTag'])){
      //  $userInfo['hashtagTitle'] = $this->hashTagName($getVideo['hashTag']);
      // }
      // else{
      //  $userInfo['hashtagTitle'] = '';
      // }

      if (!empty($getVideo['hashtag'])) {
        $userInfo['hashtagTitle'] = $this->hashTagName($getVideo['hashtag']);
        $finalTagIds = explode(',', $getVideo['hashtag']);
        foreach ($finalTagIds as $finalTagId) {
          $hashArray = $this->db->get_where('hashtag', array('id' => $finalTagId))->row_array();
          if (!empty($hashArray)) {
            $userInfo['hastagLists'][] = $hashArray;
          }
        }
      } else {
        $userInfo['hashtagTitle'] = '';
        $userInfo['hastagLists'] = [];
      }



      $videoId = $getVideo['id'];
      $soundId = $this->input->post('soundId');
      $userId = $this->input->post('userId');

      $list =  $this->db->query("SELECT sounds.title as soundTitle,sounds.id as soundId,sounds.soundCount as videoCount,sounds.sound as soundPath,sounds.type as soundType, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath, userVideos.allowComment, userVideos.allowDownloads, userVideos.viewVideo, userVideos.viewCount, userVideos.likeCount, userVideos.commentCount,userVideos.viewCount,userVideos.allowDuetReact,userVideos.downloadPath,userVideos.thumbnail FROM `userVideos` left join sounds on sounds.id = userVideos.soundId where userVideos.id !=  $videoId and userVideos.soundId = $soundId and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  ) ORDER BY userVideos.id ASC LIMIT  0 , 10")->result_array();
      if (!empty($list)) {
        foreach ($list as $lists) {
          $userDetails = $this->db->get_where('users', array('id' => $lists['userId']))->row_array();
          if (!empty($userDetails['name'])) {
            $lists['username'] = $userDetails['name'];
          } else {
            $lists['username'] = $userDetails['username'];
          }
          $lists['name'] = $userDetails['name'];
          $lists['followers'] = $userDetails['followerCount'];
          if (empty($userDetails['image'])) {
            $lists['image'] = base_url() . 'uploads/no_image_available.png';
          } else {
            $lists['image'] = $userDetails['image'];
          }

          // $lists['hashtag'] = $lists['hashtag'];
          // if(!empty($lists['hashtag'])){
          //  $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
          // }
          // else{
          //  $lists['hashtagTitle'] = '';
          // }


          if (!empty($lists['hashtag'])) {
            $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
            $finalTagIds = explode(',', $lists['hashtag']);
            foreach ($finalTagIds as $finalTagId) {
              $hashArray = $this->db->get_where('hashtag', array('id' => $finalTagId))->row_array();
              if (!empty($hashArray)) {
                $lists['hastagLists'][] = $hashArray;
              }
            }
          } else {
            $lists['hashtagTitle'] = '';
            $lists['hastagLists'] = [];
          }

          if (!empty($favorites['status'])) {
            $lists['favoritesStatus'] = $favorites['status'];
          } else {
            $lists['favoritesStatus'] = '0';
          }


          $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
          if (!empty($likeStatus)) {
            $lists['likeStatus'] = true;
          } else {
            $lists['likeStatus'] = false;
          }
          $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
          if (!empty($checkFollow)) {
            $lists['followStatus'] = '1';
          } else {
            $lists['followStatus'] = '0';
          }
          $checkFollow = $this->db->get_where('videoComments', array('videoId' => $lists['id']))->num_rows();
          if (!empty($checkFollow)) {
            $lists['followStatus'] = '1';
          } else {
            $lists['followStatus'] = '0';
          }

          $lists['soundPath'] = base_url() . $lists['soundPath'];
          $finalSoundList[] = $lists;
        }
      } else {
        $finalSoundList = [];
      }
      $finalUserINfo[] = $userInfo;
      $finalListSound = array_merge($finalUserINfo, $finalSoundList);

      $array['soundInfo'] = $userInfo;
      $array['soundVideo'] = $finalListSound;
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      $message['details'] = $array;
    } else {
      $message['success'] = '0';
      $message['message'] = 'No list found';
    }
    echo json_encode($message);
  }




  public function hashTagVideos()
  {
    $getHashTag = $this->db->get_where('hashtag', array('id' => $this->input->post('hashtagId')))->row_array();
    $getVideo = $this->Common_Model->getHashTagVideos($this->input->post('hashtagId'));
    if (!empty($getVideo)) {
      $favorites = $this->db->get_where('favouriteHashTagList', array('userId' => $this->input->post('userId'), 'hashtagId' => $this->input->post('hashtagId'), 'status' => '1'))->row_array();
      $getSound = $this->db->get_where('sounds', array('id' => $getVideo['soundId']))->row_array();
      if (!empty($getHashTag['userId'])) {
        $userDetails = $this->db->get_where('users', array('id' => $getHashTag['userId']))->row_array();
        if (!empty($userDetails['name'])) {
          $userInfo['username'] = $userDetails['name'];
        } else {
          $userInfo['username'] = $userDetails['username'];
        }
        $userInfo['name'] = $userDetails['name'];
        $userInfo['followers'] = $userDetails['followerCount'];
        $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $userDetails['id'], 'status' => '1'))->row_array();
        if (!empty($checkFollow)) {
          $userInfo['followStatus'] = '1';
        } else {
          $userInfo['followStatus'] = '0';
        }
        if (empty($userDetails['image'])) {
          $userInfo['image'] = base_url() . 'uploads/no_image_available.png';
        } else {
          $userInfo['image'] = $userDetails['image'];
        }
      } else {
        $userInfo['username'] = '';
        $userInfo['name'] = '';
        $userInfo['image'] = '';
        $userInfo['followers'] = '';
        $userInfo['followStatus'] = '0';
      }
      $userInfo['soundTitle'] = $getSound['title'];
      $userInfo['soundPath'] = base_url() . $getSound['sound'];
      $userInfo['videoCount'] = $getSound['soundCount'];
      $userInfo['description'] = $getVideo['description'];
      $userInfo['allowComment'] = $getVideo['allowComment'];
      $userInfo['allowDuetReact'] = $getVideo['allowDuetReact'];
      $userInfo['allowDownloads'] = $getVideo['allowDownloads'];
      $userInfo['viewVideo'] = $getVideo['viewVideo'];
      $userInfo['soundId'] = $getVideo['soundId'];
      $userInfo['commentCount'] = $getVideo['commentCount'];
      $userInfo['viewCount'] = $getVideo['viewCount'];
      $userInfo['likeCount'] = $getVideo['likeCount'];
      $userInfo['id'] = $getVideo['id'];
      $userInfo['userId'] = $getVideo['userId'];
      $userInfo['videoPath'] = $getVideo['videoPath'];
      $userInfo['downloadPath'] = $getVideo['downloadPath'];
      $userInfo['thumbnail'] = $getVideo['thumbnail'];
      if (!empty($favorites['status'])) {
        $userInfo['favoritesStatus'] = $favorites['status'];
      } else {
        $userInfo['favoritesStatus'] = '0';
      }

      $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $getVideo['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
      if (!empty($likeStatus)) {
        $userInfo['likeStatus'] = true;
      } else {
        $userInfo['likeStatus'] = false;
      }
      $userInfo['hashTag'] = $getHashTag['id'];
      // if(!empty($getVideo['hashTag'])){
      //  $userInfo['hashtagTitle'] = $getHashTag['hashtag'];
      // }
      // else{
      //  $userInfo['hashtagTitle'] = '';
      // }


      if (!empty($userInfo['hashTag'])) {

        $userInfo['hashtagTitle'] = $this->hashTagName($userInfo['hashTag']);
        $finalTagIds = explode(',', $userInfo['hashTag']);
        foreach ($finalTagIds as $finalTagId) {
          $hashArray = $this->db->get_where('hashtag', array('id' => $finalTagId))->row_array();
          if (!empty($hashArray)) {
            $userInfo['hastagLists'][] = $hashArray;
          }
        }
      } else {
        $userInfo['hashtagTitle'] = '';
        $userInfo['hastagLists'] = [];
      }




      $videoId = $getVideo['id'];
      $hashtagId = $this->input->post('hashtagId');
      $userId = $this->input->post('userId');

      $list =  $this->db->query("SELECT sounds.title as soundTitle,sounds.id as soundId,sounds.soundCount as videoCount,sounds.sound as soundPath, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath, userVideos.allowComment, userVideos.allowDownloads,userVideos.viewVideo,userVideos.viewCount,userVideos.likeCount, userVideos.commentCount,userVideos.viewCount,userVideos.allowDuetReact,userVideos.downloadPath,userVideos.thumbnail FROM `userVideos` left join sounds on sounds.id = userVideos.soundId where userVideos.id !=  $videoId and userVideos.hashTag Like '%$hashtagId%' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  ) ORDER BY userVideos.id ASC LIMIT  0 , 10")->result_array();
      if (!empty($list)) {
        foreach ($list as $lists) {
          $userDetails = $this->db->get_where('users', array('id' => $lists['userId']))->row_array();
          $lists['name'] = $userDetails['name'];
          if (!empty($userDetails['name'])) {
            $lists['username'] = $userDetails['name'];
          } else {
            $lists['username'] = $userDetails['username'];
          }
          $lists['followers'] = $userDetails['followerCount'];
          if (empty($userDetails['image'])) {
            $lists['image'] = base_url() . 'uploads/no_image_available.png';
          } else {
            $lists['image'] = $userDetails['image'];
          }

          $lists['hashtag'] = $lists['hashtag'];
          // if(!empty($lists['hashtag'])){
          //  $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
          // }
          // else{
          //  $lists['hashtagTitle'] = '';
          // }


          if (!empty($lists['hashtag'])) {
            $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
            $finalTagIds = explode(',', $lists['hashtag']);
            foreach ($finalTagIds as $finalTagId) {
              $hashArray = $this->db->get_where('hashtag', array('id' => $finalTagId))->row_array();
              if (!empty($hashArray)) {
                $lists['hastagLists'][] = $hashArray;
              }
            }
          } else {
            $lists['hashtagTitle'] = '';
            $lists['hastagLists'] = [];
          }





          if (!empty($favorites['status'])) {
            $lists['favoritesStatus'] = $favorites['status'];
          } else {
            $lists['favoritesStatus'] = '0';
          }
          $checkFollow = $this->db->get_where('videoComments', array('videoId' => $lists['id']))->num_rows();
          if (!empty($checkFollow)) {
            $lists['followStatus'] = '1';
          } else {
            $lists['followStatus'] = '0';
          }

          $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
          if (!empty($likeStatus)) {
            $lists['likeStatus'] = true;
          } else {
            $lists['likeStatus'] = false;
          }

          $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
          if (!empty($checkFollow)) {
            $lists['followStatus'] = '1';
          } else {
            $lists['followStatus'] = '0';
          }

          $lists['soundPath'] = base_url() . $lists['soundPath'];
          $finalSoundList[] = $lists;
        }
      } else {
        $finalSoundList = [];
      }
      $finalUserINfo[] = $userInfo;
      $finalListSound = array_merge($finalUserINfo, $finalSoundList);

      $array['hashtagInfo'] = $userInfo;
      $array['hashtagVideo'] = $finalListSound;
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      $message['details'] = $array;
    } else {
      $message['success'] = '0';
      $message['message'] = 'No list found';
    }
    echo json_encode($message);
  }


  public function userNotificationSetting()
  {
    $list = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    $data['likeNotifaction'] =  $list['likeNotifaction'];
    $data['commentNotification'] =  $list['commentNotification'];
    $data['followersNotification'] =  $list['followersNotification'];
    $data['messageNotification'] =  $list['messageNotification'];
    $data['videoNotification'] =  $list['videoNotification'];
    $message['success'] = '1';
    $message['message'] = 'List found Successfully';
    $message['details'] = $data;
    echo json_encode($message);
  }

  public function updateNotificationSetting()
  {
    $data['likeNotifaction'] =  $this->input->post('likeNotifaction');
    $data['commentNotification'] =  $this->input->post('commentNotification');
    $data['followersNotification'] =  $this->input->post('followersNotification');
    $data['messageNotification'] =  $this->input->post('messageNotification');
    $data['videoNotification'] =  $this->input->post('videoNotification');
    $update = $this->Common_Model->update('users', $data, 'id', $this->input->post('userId'));
    if ($update) {
      $message['success'] = '1';
      $message['message'] = 'Notification Status updated successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function hashTagName($ids)
  {
    $exp = explode(',', $ids);
    foreach ($exp as $exps) {
      $hashTitile = $this->db->get_where('hashtag', array('id' => $exps))->row_array();
      $hashTati[] = $hashTitile['hashtag'];
    }
    return $finalTitle = implode(',', $hashTati);
  }

  public function contactList()
  {
    $myArr = $this->input->post('phoneNumber');
    //$myArr = '[{"phone_no":"01765509238"},{"phone_no":"7087772970"},{"phone_no":"9898989898"}]';
    $list = [];
    //$myArr = '{"7087772970","78987987","9876543"}';
    $contacts1 = str_replace('"', '', $myArr);
    $contacts = explode(',', $contacts1);
    foreach ($contacts as $contact) {
      $checkPhone = $this->db->get_where('users', array('phone' => $contact, 'id !=' => $this->input->post('userId')))->row_array();
      if (!empty($checkPhone)) {
        $list[] = $checkPhone;
      }
    }
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      foreach ($list as $lists) {
        $finalData['userId'] = $lists['id'];
        $finalData['username'] = $lists['username'];
        $finalData['name'] = $lists['name'];
        $finalData['phone'] = $lists['phone'];
        if (empty($lists['image'])) {
          $finalData['image'] = base_url() . 'uploads/no_image_available.png';
        }
        $checkStataus = $this->db->get_where('userFollow', array('userId' => $lists['id'], 'followingUserId' => $this->input->post('userId'), 'status' => '1'))->num_rows();
        $checkStataus1 = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['id'], 'status' => '1'))->num_rows();
        if (!empty($checkStataus) && !empty($checkStataus1)) {
          $finalData['followStatus'] = '3';
        } elseif (!empty($checkStataus) && empty($checkStataus1)) {
          $finalData['followStatus'] = '2';
        } elseif (empty($checkStataus) && !empty($checkStataus1)) {
          $finalData['followStatus'] = '1';
        } else {
          $finalData['followStatus'] = '0';
        }
        $message['details'][] = $finalData;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No List found';
    }
    echo json_encode($message);
  }

  public function addFavoriteHahtag()
  {
    if ($this->input->post()) {
      $deta = $this->db->get_where("favouriteHashTagList", array('hashtagId' => $this->input->post('hashtagId'), 'userId' => $this->input->post('userId')))->row_array();
      $data['userId'] = $this->input->post('userId');
      $data['hashtagId'] = $this->input->post('hashtagId');
      if (empty($deta)) {
        $data['status'] = '1';
        $data['created'] = date("Y-m-d H:i:s");
        $in = $this->db->insert("favouriteHashTagList", $data);
      } else {
        $data['status'] = ($deta['status'] == '0') ? '1' : '0';
        $data['updated'] = date("Y-m-d H:i:s");
        $in = $this->db->update("favouriteHashTagList", $data, array('hashtagId' => $this->input->post('hashtagId'), 'userId' => $this->input->post('userId')));
      }
      if ($in) {
        $message['success'] = '1';
        $message['message'] = 'Added to favorites';
      } else {
        $message['success'] = '0';
        $message['message'] = 'Please try again';
      }
    } else {
      $message['message'] = 'Please enter parameters';
    }
    echo json_encode($message);
  }


  public function getFavoriteHahtag()
  {
    $userId = $this->input->post('userId');
    $query = $this->db->query("SELECT favouriteHashTagList.*,hashtag.hashtag,hashtag.videoCount FROM `favouriteHashTagList` left join hashtag on hashtag.id = favouriteHashTagList.hashtagId WHERE favouriteHashTagList.userId = $userId and favouriteHashTagList.status = '1'");
    $checkData = $query->result_array();
    if (!empty($checkData)) {
      $message['success'] = '1';
      $message['message'] = 'Details found successfully';
      foreach ($checkData as $checkDatas) {
        $hasId = $checkDatas['hashtagId'];
        $countHash = $this->db->query("SELECT * FROM `userVideos` where hashTag like '%$hasId%'");
        $countHashT = $countHash->num_rows();
        if (!empty($countHashT)) {
          $checkDatas['videoCount'] = (string)$countHashT;
        } else {
          $checkDatas['videoCount'] = '0';
        }

        $message['details'][] = $checkDatas;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'Details not found';
    }
    echo json_encode($message);
  }

  public function report()
  {
    $list = $this->db->get_where('report')->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      $message['details'] = $list;
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function videoReportList()
  {
    $list = $this->db->get_where('videoReport')->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      $message['details'] = $list;
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function reportUserVideo()
  {
    $data['userId'] = $this->input->post('userId');
    $data['reportUserId'] = $this->input->post('reportUserId');
    $data['reportVideoId'] = $this->input->post('reportVideoId');
    $data['created'] = date('Y-m-d H:i:s');
    $data['report'] = $this->input->post('report');
    $this->db->insert('userReportVideo', $data);
    $message['success'] = '1';
    $message['message'] = 'user reporting successfully';
    echo json_encode($message);
  }


  public function reportUser()
  {
    $data['userId'] = $this->input->post('userId');
    $data['reportUserId'] = $this->input->post('reportUserId');
    $data['created'] = date('Y-m-d H:i:s');
    $data['report'] = $this->input->post('report');
    $this->db->insert('reportUser', $data);
    $message['success'] = '1';
    $message['message'] = 'user reporting successfully';
    echo json_encode($message);
  }

  public function muteNotification()
  {
    $checkData = $this->db->get_where('muteUserNotification', array('userId' => $this->input->post('userId'), 'muteId' => $this->input->post('muteId')))->row_array();
    if (!empty($checkData)) {
      if ($checkData['status'] == '1') {
        $data['status'] = '0';
        $mess = 'unmute notifiation successfully';
      } else {
        $data['status'] = '1';
        $mess = 'mute notifiation successfully';
      }
      $data['created'] = date('Y-m-d H:i:s');
      $update = $this->Common_Model->update('muteUserNotification', $data, 'id', $checkData['id']);
    } else {
      $data['status'] = '1';
      $data['userId'] = $this->input->post('userId');
      $data['muteId'] = $this->input->post('muteId');
      $data['created'] = date('Y-m-d H:i:s');
      $insert = $this->db->insert('muteUserNotification', $data);
      $mess = 'mute notifiation successfully';
    }
    $message['success'] = '1';
    $message['message'] = $mess;
    $message['status'] = (string)$data['status'];
    echo json_encode($message);
  }


  public function viewVideo()
  {
    $data['userId'] = $this->input->post('userId');
    $data['videoId'] = $this->input->post('videoId');
    $this->db->insert('viewVideo', $data);
    $getCount = $this->db->get_where('userVideos', array('id' => $this->input->post('videoId')))->row_array();
    $data1['viewCount'] = $getCount['viewCount'] +  1;
    $update = $this->Common_Model->update('userVideos', $data1, 'id', $this->input->post('videoId'));
    $message['success'] = '1';
    $message['message'] = 'Count update successfully';
    echo json_encode($message);
  }

  public function problemReport()
  {
    $list = $this->db->get_where('problemReport')->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      $message['details'] = $list;
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function userProblemReport()
  {
    $data['userId'] = $this->input->post('userId');
    $data['report'] = $this->input->post('report');
    $data['created'] = date('Y-m-d H:i:s');
    $this->db->insert('problemReportUser', $data);
    $message['success'] = '1';
    $message['message'] = 'user reporting successfully';
    echo json_encode($message);
  }


  public function Messagenotification($regId, $message, $type, $loginId, $userId, $mainMessage, $messageType, $messCreated, $messageTime, $messageImage, $messageMainId)
  {
    $checkMuteNotifiaton = $this->db->get_where('muteUserNotification', array('userId' => $userId, 'muteId' => $loginId, 'status' => '1'))->row_array();
    if (empty($checkMuteNotifiaton)) {
      $registrationIds =  array($regId);
      define('API_ACCESS_KEY', 'AAAA0WgbM-c:APA91bGsdT5rx9u_QmaG9cjyapHiAY6NrzMw_WbMXgEuQi0f7ZbZ6-GCQZUDU6PvHY52KBBYwqC_Dxc9a22la0ad84p1tblRcK2evrYM2ONKU-Oa0xuL9d9jgkMrTynDadMO2OL1VIJz');
      $msg = array(
        'message'   => $message,
        'title'   => 'LiveBazaar',
        'type'    => $type,
        'subtitle'  => $type,
        'loginId' => $loginId,
        'userId' => $userId,
        'image' => $messageImage,
        'id' => $messageMainId,
        'sender_id' => $loginId,
        'reciver_id' => $userId,
        'mainMessage' => $mainMessage,
        'messageType' => $messageType,
        'created' => $messCreated,
        'time' => $messageTime,
        'vibrate' => 1,
        'sound'   => 1,
        'largeIcon' => 'large_icon',
        'smallIcon' => 'small_icon',
      );


      $fields = array(
        'registration_ids'  => $registrationIds,
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
    }
  }

  public function sendMessage()
  {
    require APPPATH . '/libraries/vendor/autoload.php';
    $sender_id = $this->input->post('sender_id');
    $reciver_id = $this->input->post('reciver_id');
    $message123 =  $this->input->post('message');
    $get_sender_data = $this->Common_Model->get_inbox_same_data($sender_id, $reciver_id);

    $loginUserDetails = $this->db->get_where('users', array('id' => $sender_id))->row_array();
    $getUserId = $this->db->get_where('users', array('id' => $reciver_id))->row_array();
    $regId = $getUserId['reg_id'];
    $mess = $loginUserDetails['username'] . ' send you a message';

    $notiMess['loginId'] = $sender_id;
    $notiMess['userId'] = $reciver_id;
    $notiMess['message'] = $mess;
    $notiMess['type'] = 'message';
    $notiMess['notiDate'] = date('Y-m-d');
    $notiMess['created'] = date('Y-m-d H:i:s');
    $this->db->insert('userNotification', $notiMess);


    if (empty($get_sender_data)) {
      $data['sender_id '] = $sender_id;
      $data['reciver_id'] = $reciver_id;
      $data['messageType'] = $this->input->post('messageType');
      if ($this->input->post('messageType') == '1') {
        $data['message'] = $this->input->post('message');
        $sendResponse['image'] = '';
      } else {
        $s3 = new Aws\S3\S3Client([
          'version' => 'latest',
          'region'  => 'us-east-2',
          'credentials' => [
            'key'    => 'AKIAZXL7ADPSCH2K4B6D',
            'secret' => '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'
          ]
        ]);
        $bucket = 'instahit';
        $upload = $s3->upload($bucket, $_FILES['image']['name'], fopen($_FILES['image']['tmp_name'], 'rb'), 'public-read');
        $url = $upload->get('ObjectURL');
        if (!empty($url)) {
          $data['image'] = $url;
        } else {
          $data['image'] = '';
        }
        $sendResponse['image'] = $url;
      }
      $data['created'] = date('y-m-d H:i:s');
      $insert = $this->db->insert('inbox', $data);
      if (!empty($insert)) {
        $insert_conversation = $this->db->insert('conversation', $data);
        $lastId = $this->db->insert_id();
        $sendResponse['id'] = $lastId;
        $sendResponse['sender_id'] = $sender_id;
        $sendResponse['reciver_id'] = $reciver_id;
        $sendResponse['message'] = $message123;
        $sendResponse['messageType'] = $this->input->post('messageType');
        $sendResponse['created'] = $data['created'];
        $sendResponse['time'] = 'just now';
        $message['success'] = '1';
        $message['message'] = 'Message send succssfully';
        $message['details'][] = $sendResponse;

        $mainMessage = $message123;
        $messageType = $this->input->post('messageType');
        $messCreated = $data['created'];
        $messageTime = 'just now';
        $messageImage = $sendResponse['image'];
        $messageMainId = $lastId;
        $this->Messagenotification($regId, $mess, 'message', $sender_id, $reciver_id, $mainMessage, $messageType, $messCreated, $messageTime, $messageImage, $messageMainId);
      }
    } else {
      $data['sender_id '] = $sender_id;
      $data['reciver_id'] = $reciver_id;
      $data['messageType'] = $this->input->post('messageType');
      $data['deleteChat'] = '';
      if ($this->input->post('messageType') == '1') {
        $data['message'] = $this->input->post('message');
        $sendResponse['image'] = '';
      } else {
        $s3 = new Aws\S3\S3Client([
          'version' => 'latest',
          'region'  => 'us-east-2',
          'credentials' => [
            'key'    => 'AKIAZXL7ADPSCH2K4B6D',
            'secret' => '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'
          ]
        ]);
        $bucket = 'instahit';
        $upload = $s3->upload($bucket, $_FILES['image']['name'], fopen($_FILES['image']['tmp_name'], 'rb'), 'public-read');
        $url = $upload->get('ObjectURL');
        if (!empty($url)) {
          $data['image'] = $url;
        } else {
          $data['image'] = '';
        }
        $sendResponse['image'] = $url;
      }
      $data['created'] = date('y-m-d H:i:s');
      $update = $this->Common_Model->update('inbox', $data, 'id', $get_sender_data['id']);
      if (!empty($update)) {



        $data['created'] = date('y-m-d H:i:s');
        $insert_conversation = $this->db->insert('conversation', $data);
        $lastId = $this->db->insert_id();
        $sendResponse['id'] = $lastId;
        $sendResponse['sender_id'] = $sender_id;
        $sendResponse['reciver_id'] = $reciver_id;
        $sendResponse['message'] = $message123;
        $sendResponse['messageType'] = $this->input->post('messageType');
        $sendResponse['created'] = $data['created'];
        $sendResponse['time'] = 'just now';
        $message['success'] = '1';
        $message['message'] = 'Message send succssfully';
        $message['details'][] = $sendResponse;

        $mainMessage = $message123;
        $messageType = $this->input->post('messageType');
        $messCreated = $data['created'];
        $messageTime = 'just now';
        $messageImage = $sendResponse['image'];
        $messageMainId = $lastId;
        $this->Messagenotification($regId, $mess, 'message', $sender_id, $reciver_id, $mainMessage, $messageType, $messCreated, $messageTime, $messageImage, $messageMainId);
      }
    }
    echo json_encode($message);
  }


  public function conversationMessage()
  {
    $sender_id = $this->input->post('sender_id');
    $reciver_id = $this->input->post('reciver_id');

    $changeStatus['readStatus'] = 1;
    $this->db->update('conversation', $changeStatus, array('reciver_id' => $sender_id, 'sender_id' => $reciver_id));

    $datas = $this->Common_Model->get_conversation_data($sender_id, $reciver_id);
    if (!empty($datas)) {
      $user = $this->db->get_where('users', array('id' => $reciver_id))->row_array();
      if (empty($user['image'])) {
        $message['image'] = base_url() . 'uploads/no_image_available.png';
      }
      $message['username'] = $user['username'];

      foreach ($datas as $data) {
        $HiddenProducts = explode(',', $data['deleteChat']);
        if (!in_array($sender_id, $HiddenProducts)) {
          $data['time'] = $this->getTime($data['created']);
          $conver[] = $data;
        }
      }
      if (!empty($conver)) {
        $message['success'] = '1';
        $message['message'] = 'conversation message get succssfully';
        $message['details'] = $conver;
      } else {
        $message['success'] = '0';
        $message['message'] = 'no message data found';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'no message data found';
    }
    echo json_encode($message);
  }


  public function deleteChat()
  {
    $userId = $this->input->post('userId');
    $inboxId = $this->input->post('inboxId');
    //$senderId = $this->input->post('senderId');
    // $receiverId = $this->input->post('receiverId');
    $getInboxList = $this->db->get_where('inbox', array('id' => $inboxId))->row_array();
    if ($getInboxList['sender_id'] == $userId) {
      $senderId = $userId;
      $receiverId = $getInboxList['reciver_id'];
    } else {
      $senderId = $getInboxList['reciver_id'];
      $receiverId = $getInboxList['sender_id'];
    }

    $list =  $this->db->query("SELECT * FROM `inbox` where sender_id = $senderId and reciver_id = $receiverId || sender_id = $receiverId and reciver_id = $senderId ")->row_array();
    if (!empty($list['deleteChat'])) {
      $data['deleteChat'] = $senderId . ',' . $receiverId;
    } else {
      $data['deleteChat'] = $senderId;
    }
    $update = $this->Common_Model->update('inbox', $data, 'id', $list['id']);

    $getConver = $this->Common_Model->get_conversation_data($senderId, $receiverId);
    foreach ($getConver as $getConve) {
      if (!empty($getConve['deleteChat'] && $getConve['deleteChat'] != $senderId)) {
        $up['deleteChat'] = $senderId . ',' . $receiverId;
      } else {
        $up['deleteChat'] = $senderId;
      }
      $this->Common_Model->update('conversation', $up, 'id', $getConve['id']);
    }
    $message['success'] = '1';
    $message['message'] = 'chat delete successfully';
    echo json_encode($message);
  }

  public function singleMessageDelete()
  {
    $userId = $this->input->post('userId');
    $conversationId = $this->input->post('conversationId');
    $getConverList = $this->db->get_where('conversation', array('id' => $conversationId))->row_array();
    if ($getConverList['sender_id'] == $userId) {
      $senderId = $userId;
      $receiverId = $getConverList['reciver_id'];
    } else {
      $senderId = $getConverList['reciver_id'];
      $receiverId = $getConverList['sender_id'];
    }
    if (!empty($getConverList['deleteChat'] && $getConverList['deleteChat'] != $senderId)) {
      $up['deleteChat'] = $senderId . ',' . $receiverId;
    } else {
      $up['deleteChat'] = $senderId;
    }
    $this->Common_Model->update('conversation', $up, 'id', $getConverList['id']);
    $message['success'] = '1';
    $message['message'] = 'Message delete successfully';
    echo json_encode($message);
  }

  public function inbox()
  {
    $sender_id = $this->input->post('sender_id');
    $datas = $this->Common_Model->get_inbox_data($sender_id);
    if (!empty($datas)) {
      foreach ($datas as $data) {
        $HiddenProducts = explode(',', $data['deleteChat']);
        if (!in_array($sender_id, $HiddenProducts)) {
          $data['time'] = $this->getTime($data['created']);
          if ($data['sender_id'] == $sender_id) {
            $mainId = $data['reciver_id'];
            $user = $this->Common_Model->get_data_by_id('users', 'id', $data['reciver_id']);
          } else {
            $mainId = $data['sender_id'];
            $user = $this->Common_Model->get_data_by_id('users', 'id', $data['sender_id']);
          }
          if (empty($user['image'])) {
            $data['image'] = base_url() . 'uploads/no_image_available.png';
          } else {
            $data['image'] = $user['image'];
          }
          $countMessage = $this->db->get_where('conversation', array('reciver_id' => $this->input->post('sender_id'), 'sender_id' => $mainId, 'readStatus' => 0))->num_rows();
          if (!empty($countMessage)) {
            $data['messageCount'] = $countMessage;
          } else {
            $data['messageCount'] = '0';
          }

          $data['username'] = $user['username'];
          $data['name'] = $user['name'];
          $inboxDetails[] = $data;
        }
      }

      if (!empty($inboxDetails)) {
        $message['success'] = '1';
        $message['message'] = 'inbox message get succssfully';
        $message['details'] = $inboxDetails;
      } else {
        $message['success'] = '0';
        $message['message'] = 'no message data found';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'no message data found';
    }
    echo json_encode($message);
  }


  public function categoryVideos()
  {
    $categoryDetails = $this->db->get_where('category', array('id' => $this->input->post('categoryId')))->row_array();
    $categoryDetails['image'] = base_url() . $categoryDetails['image'];
    $categoryId = $this->input->post('categoryId');
    $userId = $this->input->post('userId');

    $list =  $this->db->query("SELECT category.title as categoryTitle, sounds.title as soundTitle,sounds.id as soundId,sounds.soundCount as videoCount,sounds.sound as soundPath,sounds.type as soundType, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath, userVideos.allowComment, userVideos.allowDownloads,userVideos.viewVideo,userVideos.viewCount,userVideos.likeCount, userVideos.commentCount,userVideos.viewCount,userVideos.allowDuetReact FROM `userVideos` left join sounds on sounds.id = userVideos.soundId left join category on category.id = userVideos.categoryId where userVideos.categoryId = $categoryId and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  ) ORDER BY userVideos.id ASC LIMIT  0 , 10")->result_array();
    $categoryDetails['videoCount'] = (string)count($list);
    if (!empty($list)) {
      foreach ($list as $lists) {
        $userDetails = $this->db->get_where('users', array('id' => $lists['userId']))->row_array();
        $lists['name'] = $userDetails['name'];
        $lists['username'] = $userDetails['username'];
        if (!empty($userDetails['image'])) {
          $lists['image'] = $userDetails['image'];
        } else {
          $lists['image'] = base_url() . 'uploads/no_image_available.png';
        }
        $lists['hashtag'] = $lists['hashtag'];
        if (!empty($lists['hashtag'])) {
          $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
        } else {
          $lists['hashtagTitle'] = '';
        }

        if (!empty($favorites['status'])) {
          $lists['favoritesStatus'] = $favorites['status'];
        } else {
          $lists['favoritesStatus'] = '0';
        }


        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
        if (!empty($likeStatus)) {
          $lists['likeStatus'] = true;
        } else {
          $lists['likeStatus'] = false;
        }

        $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
        if (!empty($checkFollow)) {
          $lists['followStatus'] = '1';
        } else {
          $lists['followStatus'] = '0';
        }

        $countFollowers = $this->db->get_where('userFollow', array('followingUserId' => $lists['userId'], 'status' => '1'))->num_rows();
        if (!empty($countFollowers)) {
          $lists['follwersCount'] = (string)$countFollowers;
        } else {
          $lists['follwersCount'] = '0';
        }

        $lists['videoPath'] = $lists['videoPath'];
        $lists['soundPath'] = base_url() . $lists['soundPath'];
        $finalSoundList[] = $lists;
      }
    } else {
      $finalSoundList = [];
    }

    $array['categoryInfo'] = $categoryDetails;
    $array['categoryVideo'] = $finalSoundList;

    $message['success'] = '1';
    $message['message'] = 'list found successfully';
    $message['details'] = $array;

    echo json_encode($message);
  }

  public function createChannel()
  {
    require APPPATH . '/libraries/vendor/autoload.php';
    $checkChanelName = $this->db->get_where('userChannel', array('title' => $this->input->post('title')))->row_array();
    if (!empty($checkChanelName)) {
      $message['success'] = '0';
      $message['message'] = 'Channel Name is already exist';
    } else {
      $data['userId'] = $this->input->post('userId');
      $data['title'] = $this->input->post('title');
      $data['description'] = $this->input->post('description');
      $data['coin'] = $this->input->post('coin');
      $s3 = new Aws\S3\S3Client([
        'version' => 'latest',
        'region'  => 'us-east-2',
        'credentials' => [
          'key'    => 'AKIAZXL7ADPSCH2K4B6D',
          'secret' => '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'
        ]
      ]);
      $bucket = 'instahit';
      $upload = $s3->upload($bucket, $_FILES['image']['name'], fopen($_FILES['image']['tmp_name'], 'rb'), 'public-read');
      $url = $upload->get('ObjectURL');
      if (!empty($url)) {
        $data['image'] = $url;
      } else {
        $data['image'] = '';
      }
      $insert = $this->db->insert('userChannel', $data);
      if (!empty($insert)) {
        $message['success'] = '1';
        $message['message'] = 'Channel Create Successfully';
      } else {
        $message['success'] = '0';
        $message['message'] = 'Please try after some tym';
      }
    }
    echo json_encode($message);
  }


  public function getUserChannel()
  {
    $list = $this->db->get_where('userChannel', array('userId' => $this->input->post('userId')))->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List Found successfully';
      $message['details'] = $list;
    } else {
      $message['success'] = '0';
      $message['message'] = 'no list found';
    }
    echo json_encode($message);
  }

  public function getChannel()
  {
    $list = $this->db->order_by('videoCount', 'desc')->get_where('userChannel')->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List Found Successfully';
      $message['details'] = $list;
    } else {
      $message['success'] = '0';
      $message['message'] = 'No list found';
    }
    echo json_encode($message);
  }

  public function getChannelVideo()
  {
    $channelId = $this->input->post('channelId');
    $channelInfo = $this->db->get_where('userChannel', array('id' => $this->input->post('channelId')))->row_array();
    $userId = $channelInfo['userId'];
    $userDetails = $this->db->get_where('users', array('id' => $userId))->row_array();
    $channelInfo['username'] = $userDetails['username'];
    $channelInfo['followers'] = $userDetails['followerCount'];
    $channelInfo['name'] = $userDetails['name'];
    if (empty($userDetails['image'])) {
      $channelInfo['image'] = base_url() . 'uploads/no_image_available.png';
    } else {
      $channelInfo['image'] = $userDetails['image'];
    }
    $channelInfo['viewStatus'] = '1';

    $message['success'] = '1';
    $message['message'] = 'list found succssfully';
    $message['channelInfo'] = $channelInfo;

    $list =  $this->db->query("SELECT users.username,users.followerCount as followers,users.image,sounds.title as soundTitle,sounds.id as soundId, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath, userVideos.allowComment, userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount FROM `userVideos` left join sounds on sounds.id = userVideos.soundId left join users on users.id = userVideos.userId where userVideos.channelId = $channelId and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ORDER BY userVideos.viewCount desc,userVideos.likeCount desc,userVideos.commentCount ")->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List Found Successfully';
      foreach ($list as $lists) {
        if (empty($lists['image'])) {
          $lists['image'] = base_url() . 'uploads/no_image_available.png';
        }
        if (!empty($lists['hashtag'])) {
          $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
        } else {
          $lists['hashtagTitle'] = '';
        }
        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
        if (!empty($likeStatus)) {
          $lists['likeStatus'] = true;
        } else {
          $lists['likeStatus'] = false;
        }

        $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
        if (!empty($checkFollow)) {
          $lists['followStatus'] = '1';
        } else {
          $lists['followStatus'] = '0';
        }

        $message['details'][] = $lists;
      }
    } else {
      $message['details'] = [];
    }
    echo json_encode($message);
  }


  public function home()
  {
    $startLimit = $this->input->post('startLimit');
    $userId = $this->input->post('userId');
    $endLimit = 10;
    if ($this->input->post('type') == 'video') {
      $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,sounds.title as soundTitle,sounds.id as soundId, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath, userVideos.allowComment, userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join sounds on sounds.id = userVideos.soundId left join users on users.id = userVideos.userId where users.hotlist = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' )  ORDER BY rand() LIMIT $startLimit , 100")->result_array();
    } elseif ($this->input->post('type') == 'following') {
      $follwerList = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'status' => '1'))->result_array();
      if (!empty($follwerList)) {
        foreach ($follwerList as $follwerLists) {
          $idList[] = $follwerLists['followingUserId'];
        }
        $fIds = implode(',', $idList);
        $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,sounds.title as soundTitle,sounds.id as soundId, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath, userVideos.allowComment, userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join sounds on sounds.id = userVideos.soundId left join users on users.id = userVideos.userId where userVideos.userId  IN ($fIds ) rand() LIMIT $startLimit , 100")->result_array();
      } else {
        $list = '';
      }
    } else {
      $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,sounds.title as soundTitle,sounds.id as soundId, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath, userVideos.allowComment, userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join sounds on sounds.id = userVideos.soundId left join users on users.id = userVideos.userId where users.hotlist = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) rand() LIMIT $startLimit , 100")->result_array();
    }
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List Found Successfully';
      foreach ($list as $lists) {
        if (empty($lists['image'])) {
          $lists['image'] = base_url() . 'uploads/no_image_available.png';
        }

        if (!empty($lists['hashtag'])) {
          $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
          $finalTagIds = explode(',', $lists['hashtag']);
          foreach ($finalTagIds as $finalTagId) {
            $hashArray = $this->db->get_where('hashtag', array('id' => $finalTagId))->row_array();
            if (!empty($hashArray)) {
              $lists['hastagLists'][] = $hashArray;
            }
          }
        } else {
          $lists['hashtagTitle'] = '';
          $lists['hastagLists'] = [];
        }

        // if(!empty($lists['hashtag'])){
        //  $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
        // }
        // else{
        //  $lists['hashtagTitle'] = '';
        // }

        if (!empty($lists['name'])) {
          $lists['username'] = $lists['name'];
        } else {
          $lists['username'] = $lists['username'];
        }


        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
        if (!empty($likeStatus)) {
          $lists['likeStatus'] = true;
        } else {
          $lists['likeStatus'] = false;
        }

        $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
        if (!empty($checkFollow)) {
          $lists['followStatus'] = '1';
        } else {
          $lists['followStatus'] = '0';
        }

        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No details found';
    }
    echo json_encode($message);
  }

  public function agoraToken()
  {
    $checkUser = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    // $chechRequest = $this->db->get_where('userLiveRequest',array('userId' => $this->input->post('userId')))->row_array();

    // $getStatus = $chechRequest['host_status'];
    if (empty($checkUser)) {
      $message['success'] = '0';
      $message['message'] = 'please logout and login again';
    }
    // elseif(empty($chechRequest)){
    //     $message['success'] = '0';
    //     $message['message'] = 'please send Host request';
    // }
    else {

      //   if($checkUser['liveStatus'] == '1'){
      //   if($chechRequest['host_status'] == '2'){
      require APPPATH . '/libraries/agora/RtcTokenBuilder.php';
      require APPPATH . '/libraries/agora/RtmTokenBuilder.php';

      // $appID = "0ebf0179ad5f47ef93f32cf7f6851e1b";
      // $appCertificate = "0405943eabe04260acb48aedb6102605";
      $appID = "4cfbc2092e4a4ca485ce99c58e1c054a";
      $appCertificate = "1dc5e0fb75df49b483ef9cabdf7aedd8";
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
        $data['count'] = $this->input->post('count');
        $data['token'] = $token;
        $data['rtmToken'] = $tokenb;
        $data['createdDate'] = date('Y-m-d');
        $data['createdTime'] = date('H:i:s');
        $data['status'] = 'live';
        $insert = $this->db->insert('userLive', $data);
        $ids = $this->db->insert_id();
        if (!empty($insert)) {
          $checkFollow = $this->db->get_where('userFollow', array('followingUserId' => $this->input->post('userId'), 'status' => '1'))->num_rows();
          if (!empty($checkFollow)) {
            $outPut['followerCount'] = (string)$checkFollow;
          } else {
            $outPut['followerCount'] = '0';
          }

          $userId = $this->input->post('userId');

          $userDetails = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
          $todyDD = date('Y-m-d');
          $checkStarStatus = $this->db->get_where('userStar', array('userId' => $this->input->post('userId'), 'created' => $todyDD))->row_array();
          if (!empty($checkStarStatus)) {
            $starStatus = $checkStarStatus['star'];
            $starStatusstarCount = $checkStarStatus['starCount'];
            $starListStatus =  $this->db->query("SELECT * FROM starList WHERE starCount BETWEEN 1 AND $starStatusstarCount order by id desc limit 1")->row_array();
            if (!empty($starListStatus['box'])) {
              $starBOX = $starListStatus['box'];
            } else {
              $starBOX = 0;
            }
          } else {
            $starStatus = '0';
            $starBOX = 0;
          }
          $todyDD = date('Y-m-d');
          $mainUserId = $this->input->post('userId');
          $checkStarStatus12 = $this->db->query("SELECT * FROM `starBoxResult` where userId = $mainUserId and box = $starBOX and date(created) = '$todyDD'")->num_rows();
          if (!empty($checkStarStatus12)) {
            $outPut['checkBoxStatus'] = '0';
          } else {
            $outPut['checkBoxStatus'] = '1';
          }


          $outPut['frameImage'] = "";

          $created = $userDetails['created'];
          $dateCreated = date_create($created);
          $addedDate = date_add($dateCreated, date_interval_create_from_date_string("7 days"));
          $addedDateArray = ((array) $addedDate);
          $finalDate = date_create($addedDateArray['date']);
          $dateSevenDay = date_format($finalDate, "Y-m-d");

          // print_r($userDetails);exit;
          if ($userDetails['setFrameId'] == '1') {

            if (date('Y-m-d') > $dateSevenDay) {
              $this->db->set('setFrameId', '0')->where('id', $userDetails['id'])->update('users');
            } else {
              $outPut['frameImage'] = base_url() . 'assets/gifts/epgifts/frame1.svga';
            }
          } else if ($outPut['setFrameId'] == '0') {
          } else {

            $getFrame = $this->db->get_where('Ep_admin_add_frames', ['id' => $userDetails['setFrameId']])->row_array();

            $outPut['frameImage'] = base_url() . $getFrame['frame_img'];
          }


          $outPut['name'] = $userDetails['name'];
          $outPut['hostImage'] = $userDetails['image'];
          $outPut['coin'] = $userDetails['coin'];
          $outPut['userLeval'] = $userDetails['leval'];
          $outPut['starCount'] = $starStatus;
          $outPut['count'] = $data['count'];
          $outPut['toke'] = $token;
          $outPut['box'] = (string)$starBOX;
          $outPut['channelName'] = $this->input->post('channelName');
          $outPut['rtmToken'] = $tokenb;
          $outPut['mainId'] = (string)$ids;
          $message['success'] = '1';
          $message['message'] = 'Token Generate Successfully';
          $message['details'] = $outPut;
        } else {
          $message['success'] = '0';
          $message['message'] = 'Please try after some time';
        }
      } else {
        $message['success'] = '0';
        $message['message'] = 'Please Try after some time';
      }
    }
    $checkFollow = $this->db->get_where('userFollow', array('followingUserId' => $this->input->post('userId'), 'status' => '1'))->result_array();
    if (!empty($checkFollow)) {

      foreach ($checkFollow as $followers) {

        $get = $this->db->get_where('users', ['id' => $followers['userId']])->row_array();

        $title = $checkUser['name'];
        $mess = $checkUser['username'] . " is Live.";
        $type = 'live';
        $imgpath = $checkUser['image'];

        pushNotification($get['reg_id'], $mess, $title, $type, $imgpath);
      }
    }
    echo json_encode($message);
  }


  public function liveNotification($regId, $message, $type, $loginId, $userId, $liveuserimage, $liveUsername, $channelName, $latitude, $longitude, $token, $tokenb)
  {
    $checkMuteNotifiaton = $this->db->get_where('muteUserNotification', array('userId' => $userId, 'muteId' => $loginId, 'status' => '1'))->row_array();
    if (empty($checkMuteNotifiaton)) {
      $registrationIds =  array($regId);
      define('API_ACCESS_KEY', 'AAAA0WgbM-c:APA91bGsdT5rx9u_QmaG9cjyapHiAY6NrzMw_WbMXgEuQi0f7ZbZ6-GCQZUDU6PvHY52KBBYwqC_Dxc9a22la0ad84p1tblRcK2evrYM2ONKU-Oa0xuL9d9jgkMrTynDadMO2OL1VIJz');
      $msg = array(
        'message'  => $message,
        'title'    => 'LiveBazaar',
        'type'   => $type,
        'subtitle' => $type,
        'loginId' => $loginId,
        'userId' => $userId,
        'username' => $liveUsername,
        'liveuserimage' => (string)$liveuserimage,
        'channelName' => $channelName,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'token' => $token,
        'rtmToken' => $tokenb,
        'vibrate'  => 1,
        'sound'    => 1,
        'largeIcon'  => 'large_icon',
        'smallIcon'  => 'small_icon',
      );
      $fields = array(
        'registration_ids'   => $registrationIds,
        'data'     => $msg
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
    }
  }

  public function getLiveUserList()
  {
    if (!empty($this->input->post('latitude'))) {
      $latitude = $this->input->post('latitude');
      $longitude = $this->input->post('longitude');
      $loginIdMain = $this->input->post('userId');
      $list =  $this->db->query("SELECT  users.*,userLive.* FROM (SELECT *, (((acos(sin(($latitude*pi()/180)) * sin((`latitude`*pi()/180))+cos(($latitude*pi()/180)) * cos((`latitude`*pi()/180)) * cos((($longitude- `longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance FROM `userLive`)userLive left join users on users.id = userLive.userId WHERE userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and userLive.status = 'live' and userLive.userId != $loginIdMain and distance <= 62.1371 ")->result_array();
    } else {
      $loginIdMain = $this->input->post('userId');
      if ($this->input->post('type') == 1) {
        $list =  $this->db->query("select users.*,userLive.*,yy_user_room_tools.frameId,Ep_admin_add_frames.id  frame_imgId,concat('" . base_url() . "', frame_img) as frame_img from userLive left join users on users.id = userLive.userId left join yy_user_room_tools on yy_user_room_tools.userId = userLive.userId left join Ep_admin_add_frames on Ep_admin_add_frames.id = yy_user_room_tools.frameId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and userLive.status = 'live' and userLive.userId != $loginIdMain ORDER BY userLive.created desc")->result_array();
      } else {
        $loginIdMain = $this->input->post('userId');
        $follwerList = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'status' => '1'))->result_array();
        if (!empty($follwerList)) {
          foreach ($follwerList as $follwerLists) {
            $idList[] = $follwerLists['followingUserId'];
          }
          $fIds = implode(',', $idList);
          $list =  $this->db->query("select users.*,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and userLive.userId  IN ($fIds ) and userLive.status = 'live' ORDER BY userLive.id desc")->result_array();
        }
      }
    }
    $useriNfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';


      $ids = array_column($list, 'username');
      $ids = array_unique($ids);
      $list = array_filter($list, function ($key, $value) use ($ids) {
        return in_array($value, array_keys($ids));
      }, ARRAY_FILTER_USE_BOTH);

      //$input = array_map("unserialize", array_unique(array_map("serialize", $list)));

      // print_r($list);
      // die;
      foreach ($list as $lists) {
        $todyDD = date('Y-m-d');

        $checkStarStatus = $this->db->get_where('userStar', array('userId' => $lists['userId'], 'created' => $todyDD))->row_array();
        if (!empty($checkStarStatus)) {
          $starStatus = $checkStarStatus['star'];
          $starStatusstarCount = $checkStarStatus['starCount'];
          if ($starStatus != 0) {
            $checkBoxCount = $this->db->get_where('starList', array('star' => $starStatus))->row_array();
            $starBOX = $checkBoxCount['box'];
          } else {
            $starBOX = 0;
          }
        } else {
          $starStatus = '0';
          $starBOX = 0;
        }
        $todyDD = date('Y-m-d');
        $mainUserId = $this->input->post('userId');
        $mainLiveId = $lists['id'];
        $checkStarStatus12 = $this->db->query("SELECT * FROM `starBoxResult` where userId = $mainUserId and liveId= $mainLiveId and box = $starBOX and date(created) = '$todyDD'")->num_rows();
        if (!empty($checkStarStatus12)) {
          $lists['checkBoxStatus'] = '0';
        } else {
          $lists['checkBoxStatus'] = '1';
        }

        $lists['box'] = (string)$starBOX;


        $lists['purchasedCoin'] = $lists['coin'];
        $lists['userLeval'] = $lists['leval'];
        $lists['startCount'] = $starStatus;
        $lists['cameraFrame'] = base_url() . 'assets/gifts/epgifts/cAMERA_.svg';
        if (empty($lists['image'])) {
          //   $lists['image'] = base_url().'uploads/liveDummy.png';
          $lists['image'] = base_url() . 'uploads/logo (5).png';
        } else {
          $lists['image'] = $lists['image'];
        }
        $lists['created'] = $this->sohitTime($lists['created']);

        $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
        if (!empty($checkFollow)) {
          $lists['followStatus'] = '1';
        } else {
          $lists['followStatus'] = '0';
        }

        $lists['frameImage'] = "";

        $created = $lists['created'];
        $dateCreated = date_create($created);
        $addedDate = date_add($dateCreated, date_interval_create_from_date_string("7 days"));
        $addedDateArray = ((array) $addedDate);
        $finalDate = date_create($addedDateArray['date']);
        $dateSevenDay = date_format($finalDate, "Y-m-d");

        // print_r($LIST);exit;
        if ($lists['setFrameId'] == '1') {

          if (date('Y-m-d') > $dateSevenDay) {
            $this->db->set('setFrameId', '0')->where('id', $lists['id'])->update('users');
          } else {
            $lists['frameImage'] = base_url() . 'assets/gifts/epgifts/frame1.svga';
          }
        } else if ($lists['setFrameId'] == '0') {
        } else {

          $getFrame = $this->db->get_where('Ep_admin_add_frames', ['id' => $lists['setFrameId']])->row_array();

          $lists['frameImage'] = $getFrame['frame_img'];
        }

        if ($lists['setGrageId'] == '1') {

          if (date('Y-m-d') > $dateSevenDay) {
            $this->db->set('setGrageId', '0')->where('id', $lists['userId'])->update('users');
          } else {
            $lists['garageImage'] = base_url() . 'assets/gifts/garage/blackCar.svga';
          }
        } else if ($lists['setGrageId'] == '0') {
        } else {

          $garage = $this->db->get_where('garageData', ['id' => $lists['setGrageId']])->row_array();


          $lists['garageImage'] = $garage['mainImage'];
          // print_r($value);exit;
        }

        $getEffects = $this->db->get('entryEffectsData')->result_array();

        foreach ($getEffects as $effects) {
          if ($lists['my_level'] >= $effects['levelFrom'] && $lists['my_level'] <= $effects['levelTo']) {
            $lists['entry'] = $effects['image'];
          }
        }



        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No List found';
    }
    echo json_encode($message);
  }
  //
  // public function getuserListss(){
  //
  //   $getDetails = $this->db->select("users.id,users.username,users.name,users.coin,users.leval,users.image,users.followerCount")
  //   ->from()
  //   ->where()
  //   ->get()
  //   ->result_array();
  //
  // }



  public function archivedLive()
  {
    $data['status'] = 'archived';
    $data['archivedDate'] = date('Y-m-d');
    $rTime = date('H:i:s');

    $getCreatedTime = $this->db->select('createdTime')
      ->from('userLive')
      ->where('id', $this->input->post('id'))
      ->get()->row_array();
    $cTime = $getCreatedTime['createdTime'];

    // $arcDate = date('Y-m-d H:i:s');

    $archieved = strtotime($rTime);
    $created = strtotime($cTime);
    $minutes = round(abs($archieved - $created) / 60, 2);

    $data['totaltimePerLive'] = $minutes;
    $data['archivedTime'] = $rTime;

    // print_r($data);exit;

    $update = $this->db->set($data)->where('id', $this->input->post('id'))->update('userLive');

    if ($update) {

      $message['success'] = '1';
      $message['message'] = 'Live Streming Archived Successfully';
      echo json_encode($message);
      exit;
    } else {
      $message['success'] = '0';
      $message['message'] = 'Live Streming Not Archived';
      echo json_encode($message);
    }
  }


  public function RTMTokenGenrate()
  {
    require APPPATH . '/libraries/agora/RtmTokenBuilder.php';
    // $appID = "0ebf0179ad5f47ef93f32cf7f6851e1b";
    // $appCertificate = "0405943eabe04260acb48aedb6102605";
    $appID = "501b7a11731a4f8483c87f9779948f14";
    $appCertificate = "7c4fbb74a0904636a128425368909837";
    $user = $this->input->post('channelName');
    $role = RtmTokenBuilder::RoleRtmUser;
    $expireTimeInSeconds = 3600;
    $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
    $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

    $token = RtmTokenBuilder::buildToken($appID, $appCertificate, $user, $role, $privilegeExpiredTs);
    $message['success'] = '1';
    $message['message'] = 'Token generate Successfully';
    $message['token'] = $token;

    echo json_encode($message);
  }

  public function freindList()
  {
    $userId = $this->input->post('userId');
    $lists = $this->db->query("SELECT a.*,b.*,users.id as uId,users.name as uname,users.username,users.image as userImage,users.email,users.phone,users.followerCount from userFollow as a LEFT JOIN userFollow as b on b.userId=a.followingUserId and b.followingUserId=$userId left join users on users.id=a.followingUserId where a.userId=$userId and a.status='1' HAVING a.followingUserId = b.userId and b.status='1'")->result_array();
    if (!empty($lists)) {
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      foreach ($lists as $list) {
        if (empty($list['userImage'])) {
          $list['userImage'] = base_url() . 'uploads/no_image_available.png';
        }
        $message['details'][] = $list;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No List found';
    }
    echo json_encode($message);
  }


  public function uploadPanAndAadhar()
  {
    require APPPATH . '/libraries/vendor/autoload.php';
    $data['userId'] = $this->input->post('userId');
    $data['panAadharNumber'] = $this->input->post('panAadharNumber');
    $data['type'] = $this->input->post('type');
    $s3 = new Aws\S3\S3Client([
      'version' => 'latest',
      'region'  => 'us-east-2',
      'credentials' => [
        'key'    => 'AKIASKU6EJBLLBL5FSOL',
        'secret' => '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'
      ]
    ]);
    $bucket = 'cancremedia';

    $upload = $s3->upload($bucket, $_FILES['image']['name'], fopen($videoMainPath, 'rb'), 'public-read');
    $url = $upload->get('ObjectURL');
    if (!empty($url)) {
      $data['image'] = 'http://d2ufnc3urw5h1h.cloudfront.net/' . $_FILES['image']['name'];
    } else {
      $data['image'] = '';
    }
    $insert = $this->db->insert('userPanAndAadharCard', $data);
    if (!empty($insert)) {
      $message['success'] = '1';
      $message['message'] = 'Infomation upload Successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function getVerifyStatus()
  {
    // 1 accept
    // 2 pending
    // 3 reject
    $checkStatus = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    $message['success'] = '1';
    $message['message'] = 'infomation get Successfully';
    $message['status'] = '0';
    echo json_encode($message);
  }


  public function registerInfo()
  {
    $agencyId = $this->db->get_where('agencyDetails', array('agencyCode' => $this->input->post('agencyCode')))->row_array();
    if (!empty($agencyId)) {
      $data['userId'] = $this->input->post('userId');
      $data['username'] = $this->input->post('username');
      $data['name'] = $this->input->post('name');
      $data['email'] = $this->input->post('email');
      $data['contactNumber'] = $this->input->post('contactNumber');
      // $data['accountNumber'] = $this->input->post('accountNumber');
      // $data['ifsc'] = $this->input->post('ifsc');
      $data['agencyCode'] = $agencyId['id'];
      $data['aadharNumber'] = $this->input->post('aadharNumber');
      $data['created'] = date('Y-m-d H:i:s');
      if (!empty($_FILES['addharCard']['name'])) {
        $name1 = time() . '_' . $_FILES["addharCard"]["name"];
        $name = str_replace(' ', '_', $name1);
        $tmp_name = $_FILES['addharCard']['tmp_name'];
        $path = 'uploads/sounds/' . $name;
        move_uploaded_file($tmp_name, $path);
        $data['addharCard'] = $path;
      }
      if (!empty($_FILES['panCard']['name'])) {
        $name1 = time() . '_' . $_FILES["panCard"]["name"];
        $name = str_replace(' ', '_', $name1);
        $tmp_name = $_FILES['panCard']['tmp_name'];
        $path = 'uploads/sounds/' . $name;
        move_uploaded_file($tmp_name, $path);
        $data['panCard'] = $path;
      }
      if (!empty($_FILES['selfie']['name'])) {
        $name1 = time() . '_' . $_FILES["selfie"]["name"];
        $name = str_replace(' ', '_', $name1);
        $tmp_name = $_FILES['selfie']['tmp_name'];
        $path = 'uploads/sounds/' . $name;
        move_uploaded_file($tmp_name, $path);
        $data['selfie'] = $path;
      }
      $insert = $this->db->insert('registerUserInfo', $data);
      if (!empty($insert)) {
        $message['success'] = '1';
        $message['message'] = 'Infomation upload Successfully';
      } else {
        $message['success'] = '0';
        $message['message'] = 'Please try after some time';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please enter valid Agency Code';
    }
    echo json_encode($message);
  }


  public function levelList()
  {
    $list = $this->db->get_where('leval')->result_array();
    if (!empty($list)) {
      $userInfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
      $message['success'] = '1';
      $message['message'] = 'List found Successfully';
      $message['expCount'] = $userInfo['expCoin'];
      foreach ($list as $lists) {
        if ($lists['leval'] == $userInfo['leval']) {
          $lists['status'] = true;
          $lists['color'] = '#A52A2A';
        } else {
          $lists['status'] = false;
          $lists['color'] = $lists['color'];
        }
        if ($lists['leval'] > $userInfo['leval']) {
          $pendingCoin = $lists['expCount'] - $userInfo['expCoin'];
          $lists['description'] = "You need " . $pendingCoin . " more exp to move to Level " . $lists['leval'];
        } else {
          $lists['description'] = 'Successfully completed.';
        }
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No details found';
    }
    echo json_encode($message);
  }

  public function luckyWheelUserCoins()
  {
    $userInfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    if (!empty($userInfo)) {
      if (!empty($userInfo['purchasedCoin'])) {
        $out = $userInfo['purchasedCoin'];
      } else {
        $out = '0';
      }
      if (!empty($userInfo['coin'])) {
        $outs = $userInfo['coin'];
      } else {
        $outs = '0';
      }
      $message['success'] = '1';
      $message['message'] = 'Coins fetched successfully';
      $message['purchasedCoins'] = $out;
      $message['receivedCoins'] = $outs;
    } else {
      $message['success'] = '0';
      $message['message'] = 'No list found';
    }
    echo json_encode($message);
  }

  public function liveSoundList()
  {
    $list = $this->db->get_where('agoraSound')->result_array();
    $message['success'] = '1';
    $message['message'] = 'List found Successfully';
    foreach ($list as $lists) {
      $lists['soundPath'] = base_url() . $lists['soundPath'];
      $message['details'][] = $lists;
    }
    echo json_encode($message);
  }

  public function banLive()
  {
    $checkBlock = $this->db->get_where('banLiveUser', array('userIdLive' => $this->input->post('userIdLive'), 'userIdViewer' => $this->input->post('userIdViewer')))->row_array();
    if (!empty($checkBlock)) {
      $this->db->delete('banLiveUser', array('id' => $checkBlock['id']));
      $message['success'] = '1';
      $message['status'] = false;
      $message['message'] = 'user unbanned successfully';
    } else {
      $data['userIdLive'] = $this->input->post('userIdLive');
      $data['userIdViewer'] = $this->input->post('userIdViewer');
      $data['created'] = date('Y-m-d H:i:s');
      $insert = $this->db->insert('banLiveUser', $data);
      if (!empty($insert)) {
        $message['success'] = '1';
        $message['status'] = true;
        $message['message'] = 'user ban successfully';
      } else {
        $message['success'] = '0';
        $message['message'] = 'Please try after some time';
      }
    }
    echo json_encode($message);
  }

  public function banLiveUserList()
  {
    $userId = $this->input->post('userId');
    $list = $this->db->query("select users.username,users.name,users.email,users.image,banLiveUser.* from banLiveUser left join users on users.id = banLiveUser.userIdViewer where banLiveUser.userIdLive = $userId")->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List Found Successfully';
      foreach ($list as $lists) {
        if (empty($lists['image'])) {
          $lists['image'] = base_url() . 'uploads/no_image_available.png';
        }
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No List found';
    }
    echo json_encode($message);
  }



  public function liveAndVideoList()
  {
    $userId = $this->input->post('userId');
    $startLimit = $this->input->post('startLimit');



    $videoList =  $this->db->query("SELECT users.username,users.name,users.followerCount,users.image, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.thumbnail, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and (users.hotlist = '1'|| userVideos.status = '1') and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and  userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ")->result_array();

    // print_r($videoList);
    // die;
    if (!empty($videoList)) {

      $getpro = $this->db->select("products.*")
        ->from("products")
        ->join("userVideos", "userVideos.userId = products.userId", "left")
        ->where("products.userId", $userId)
        ->group_by("id")
        ->get()
        ->result_array();
      //$count = count($videoList);
      //       if($count < 9){
      //        $this->db->delete('viewVideo',array('userId' => $this->input->post('userId')));
      //      }
      $message['success'] = '1';
      $message['message'] = 'details found Successfully';
      foreach ($videoList as $videoLists) {
        $viewVideoInsert['userId'] = $this->input->post('userId');
        $viewVideoInsert['videoId'] = $videoLists['id'];
        // $this->db->insert('viewVideo',$viewVideoInsert);
        $updateVideoCount['viewCount'] = $videoLists['viewCount'] + 1;
        // $this->Common_Model->update('userVideos',$updateVideoCount,'id',$videoLists['id']);

        $videoLists['viewCount'] = '177';
        $videoLists['viewVideo'] = '170';
        $videoLists['soundTitle'] = $videoLists['username'];
        if (!empty($videoLists['name'])) {
          $videoLists['username'] = $videoLists['name'];
        } else {
          $videoLists['username'] = $videoLists['username'];
        }
        if (!empty($videoLists['downloadPath'])) {
          $videoLists['downloadPath'] = $videoLists['downloadPath'];
        } else {
          $videoLists['downloadPath'] =  '';
        }

        if (empty($videoLists['image'])) {
          $videoLists['image'] = base_url() . 'uploads/no_image_available.png';
        }
        if (!empty($videoLists['hashtag'])) {
          $videoLists['hashtagTitle'] = $this->hashTagName($videoLists['hashtag']);
          $finalTagIds = explode(',', $videoLists['hashtag']);
          foreach ($finalTagIds as $finalTagId) {
            $hashArray = $this->db->get_where('hashtag', array('id' => $finalTagId))->row_array();
            if (!empty($hashArray)) {
              $videoLists['hastagLists'][] = $hashArray;
            }
          }
        } else {
          $videoLists['hashtagTitle'] = '';
          $videoLists['hastagLists'] = [];
        }
        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $videoLists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
        if (!empty($likeStatus)) {
          $videoLists['likeStatus'] = true;
        } else {
          $videoLists['likeStatus'] = false;
        }
        $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $videoLists['userId'], 'status' => '1'))->row_array();
        if (!empty($checkFollow)) {
          $videoLists['followStatus'] = '1';
        } else {
          $videoLists['followStatus'] = '0';
        }
        $videoLists['dataType'] = 'video';


        $videoLists['product'] = $getpro;

        $message['details'][] = $videoLists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No List found';
    }
    // else{
    //    $this->db->delete('viewVideo',array('userId' => $this->input->post('userId')));

    //     $videoList =  $this->db->query("SELECT users.username,users.name,users.followerCount,users.image, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.thumbnail, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and (users.hotlist = '1'|| userVideos.status = '1') and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and  userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' )  ORDER BY RAND() LIMIT $startLimit , 10")->result_array();

    //     if(!empty($videoList)){
    //       $message['success'] = '1';
    //       $message['message'] = 'details found Successfully';
    //       foreach($videoList as $videoLists){
    //         $viewVideoInsert['userId'] = $this->input->post('userId');
    //        $viewVideoInsert['videoId'] = $videoLists['id'];
    //        $this->db->insert('viewVideo',$viewVideoInsert);
    //         $updateVideoCount['viewCount'] = $videoLists['viewCount'] + 1;
    //         $this->Common_Model->update('userVideos',$updateVideoCount,'id',$videoLists['id']);

    //         $videoLists['viewCount'] = '177';
    //         $videoLists['viewVideo'] = '170';
    //         $videoLists['soundTitle'] = $videoLists['username'];
    //         if(!empty($videoLists['name'])){
    //           $videoLists['username'] = $videoLists['name'];
    //         }
    //         else{
    //           $videoLists['username'] = $videoLists['username'];
    //         }
    //         if(!empty($videoLists['downloadPath'])){
    //           $videoLists['downloadPath'] = $videoLists['downloadPath'];
    //         }
    //         else{
    //           $videoLists['downloadPath'] =  '';
    //         }

    //         if(empty($videoLists['image'])){
    //           $videoLists['image'] = base_url().'uploads/no_image_available.png';
    //         }
    //         if(!empty($videoLists['hashtag'])){
    //           $videoLists['hashtagTitle'] = $this->hashTagName($videoLists['hashtag']);
    //           $finalTagIds = explode(',',$videoLists['hashtag']);
    //           foreach($finalTagIds as $finalTagId){
    //             $hashArray = $this->db->get_where('hashtag',array('id' => $finalTagId))->row_array();
    //             if(!empty($hashArray)){
    //               $videoLists['hastagLists'][] = $hashArray;
    //             }
    //           }
    //         }
    //         else{
    //           $videoLists['hashtagTitle'] = '';
    //           $videoLists['hastagLists'] = [];
    //         }
    //         $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $videoLists['id'],'userId'=> $this->input->post('userId'),'status'=> '1'))->row_array();
    //         if(!empty($likeStatus)){
    //           $videoLists['likeStatus'] = true;
    //         }
    //         else{
    //           $videoLists['likeStatus'] = false;
    //         }
    //         $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'),'followingUserId' =>$videoLists['userId'],'status' => '1'))->row_array();
    //          if(!empty($checkFollow)){
    //           $videoLists['followStatus'] = '1';
    //          }
    //          else{
    //           $videoLists['followStatus'] = '0';
    //          }
    //         $videoLists['dataType'] = 'video';

    //         $message['details'][] = $videoLists;
    //       }
    //     }
    //     else{
    //       $message['success'] = '0';
    //       $message['message'] = 'No List found';
    //     }
    // }
    echo json_encode($message);
  }

  public function liveVedioList()
  {

    $userId = $this->input->post("userId");

    $getDetails = $this->db->select("userVideos.*,users.username,users.name,users.followerCount,users.image")
      ->from("userVideos")
      ->join("users", "users.id = userVideos.userId")
      ->where("userVideos.userId", $userId)
      ->where("userVideos.status", "1")
      ->get()
      ->result_array();

    if (!!$getDetails) {

      foreach ($getDetails as $key => $value) {

        $id = $getDetails[$key]['userId'];

        $hastags = $this->db->get_where("hashtag", ['userId' => $id])->result_array();

        $getDetails[$key]['hastaglist'] = $hastags;
      }

      foreach ($hastags as $key1 => $value1) {
        $ids = $hastags[$key1]['userId'];

        $getpro = $this->db->select("products.*")
          ->from("products")
          ->where("products.userId", $ids)
          ->get()
          ->result_array();

        $getDetails[$key1]['products'] = $getpro;
      }

      $message['success'] = '1';
      $message['message'] = 'vedios found';
      $message['details'] = $getDetails;
    } else {
      $message['message'] = '0';
      $message['message'] = 'vedios not found!';
    }
    echo json_encode($message);
  }


  public function liveAndVideoListOLD()
  {
    $loginIdMain = $this->input->post('userId');
    $userId = $this->input->post('userId');
    $startLimit = $this->input->post('startLimit');
    $list =  $this->db->query("select users.username,users.name,users.coin,users.leval,users.image,users.followerCount,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and userLive.status = 'live' and userLive.userId != $loginIdMain ORDER BY userLive.id desc LIMIT $startLimit , 20")->result_array();
    $useriNfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();


    if (!empty($list)) {

      $ids = array_column($list, 'username');
      $ids = array_unique($ids);
      $list = array_filter($list, function ($key, $value) use ($ids) {
        return in_array($value, array_keys($ids));
      }, ARRAY_FILTER_USE_BOTH);


      foreach ($list as $lists) {
        if (!empty($lists['name'])) {
          $lists['username'] = $lists['name'];
        } else {
          $lists['username'] = $lists['username'];
        }


        $todyDD = date('Y-m-d');
        $checkStarStatus = $this->db->get_where('userStar', array('userId' => $this->input->post('userId'), 'created' => $todyDD))->row_array();
        if (!empty($checkStarStatus)) {
          $starStatus = $checkStarStatus['star'];
        } else {
          $starStatus = '0';
        }
        $lists['purchasedCoin'] = $lists['coin'];
        $lists['userLeval'] = $lists['leval'];
        $lists['startCount'] = $starStatus;
        if (empty($lists['image'])) {
          $lists['image'] = base_url() . 'uploads/no_image_available.png';
        } else {
          $lists['image'] = $lists['image'];
        }
        $lists['created'] = $this->sohitTime($lists['created']);

        $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
        if (!empty($checkFollow)) {
          $lists['followStatus'] = '1';
        } else {
          $lists['followStatus'] = '0';
        }
        $lists['dataType'] = 'live';
        $liveuserList[] = $lists;
      }
    } else {
      $liveuserList = [];
    }

    $videoList =  $this->db->query("SELECT users.username,users.name,users.followerCount,users.image,sounds.title as soundTitle,sounds.id as soundId, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.thumbnail, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join sounds on sounds.id = userVideos.soundId left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and users.hotlist = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' )  ORDER BY RAND() LIMIT $startLimit , 20")->result_array();
    if (!empty($videoList)) {
      foreach ($videoList as $videoLists) {
        if (!empty($videoLists['name'])) {
          $videoLists['username'] = $videoLists['name'];
        } else {
          $videoLists['username'] = $videoLists['username'];
        }
        if (!empty($videoLists['downloadPath'])) {
          $videoLists['downloadPath'] = $videoLists['downloadPath'];
        } else {
          $videoLists['downloadPath'] =  '';
        }

        if (empty($videoLists['image'])) {
          $videoLists['image'] = base_url() . 'uploads/no_image_available.png';
        }
        if (!empty($videoLists['hashtag'])) {
          $videoLists['hashtagTitle'] = $this->hashTagName($videoLists['hashtag']);
          $finalTagIds = explode(',', $videoLists['hashtag']);
          foreach ($finalTagIds as $finalTagId) {
            $hashArray = $this->db->get_where('hashtag', array('id' => $finalTagId))->row_array();
            if (!empty($hashArray)) {
              $videoLists['hastagLists'][] = $hashArray;
            }
          }
        } else {
          $videoLists['hashtagTitle'] = '';
          $videoLists['hastagLists'] = [];
        }
        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $videoLists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
        if (!empty($likeStatus)) {
          $videoLists['likeStatus'] = true;
        } else {
          $videoLists['likeStatus'] = false;
        }
        $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $videoLists['userId'], 'status' => '1'))->row_array();
        if (!empty($checkFollow)) {
          $videoLists['followStatus'] = '1';
        } else {
          $videoLists['followStatus'] = '0';
        }
        $videoLists['dataType'] = 'video';

        $videoListFinal[] = $videoLists;
      }
    } else {
      $videoListFinal = [];
    }

    $finalArray = array_merge($liveuserList, $videoListFinal);
    $newArr = $this->msort($finalArray, array('followerCount'));
    if (!empty($videoListFinal)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      $message['details'] = $videoListFinal;
    }
    // if(!empty($newArr)){
    //   $message['success'] = '1';
    //   $message['message'] = 'List found successfully';
    //   $message['details'] = $newArr;
    // }
    else {
      $message['success'] = '0';
      $message['message'] = 'No List found';
    }
    echo json_encode($message);
    //print_r($videoListFinal);


  }

  public function msort($array, $key, $sort_flags = SORT_REGULAR)
  {
    if (is_array($array) && count($array) > 0) {
      if (!empty($key)) {
        $mapping = array();
        foreach ($array as $k => $v) {
          $sort_key = '';
          if (!is_array($key)) {
            $sort_key = $v[$key];
          } else {
            // @TODO This should be fixed, now it will be sorted as string
            foreach ($key as $key_key) {
              $sort_key .= $v[$key_key];
            }
            $sort_flags = SORT_STRING;
          }
          $mapping[$k] = $sort_key;
        }
        asort($mapping, $sort_flags);
        $sorted = array();
        foreach ($mapping as $k => $v) {
          $sorted[] = $array[$k];
        }
        return $sorted;
      }
    }
    return $array;
  }

  public function shareVideo()
  {
    $startLimit = $this->input->post('startLimit');
    $endLimit = 5;
    $userId = $this->input->post('userId');
    if (!empty($this->input->post('videoId'))) {
      $videoId = $this->input->post('videoId');
      $videoListApi =  $this->db->query("SELECT sounds.title as soundTitle,sounds.id as soundId,users.username,users.name,users.followerCount as followers,users.image,userVideos.id, userVideos.userId,userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDownloads, userVideos.allowDuetReact,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join userFollow on userFollow.followingUserId = userVideos.userId left join users on users.id = userVideos.userId left join sounds on sounds.id = userVideos.soundId where userVideos.id = $videoId group by userVideos.id")->row_array();
      if (!empty($videoListApi['name'])) {
        $videoListApi['username'] = $videoListApi['name'];
      } else {
        $videoListApi['username'] = $videoListApi['username'];
      }
      if (!empty($videoListApi['downloadPath'])) {
        $videoListApi['downloadPath'] = $videoListApi['downloadPath'];
      } else {
        $videoListApi['downloadPath'] =  '';
      }

      if (empty($videoListApi['image'])) {
        $videoListApi['image'] = base_url() . 'uploads/no_image_available.png';
      }
      if (!empty($videoListApi['hashtag'])) {
        $videoListApi['hashtagTitle'] = $this->hashTagName($videoListApi['hashtag']);
        $finalTagIdss = explode(',', $videoListApi['hashtag']);
        foreach ($finalTagIdss as $finalTagIds) {
          $hashArrays = $this->db->get_where('hashtag', array('id' => $finalTagIds))->row_array();
          if (!empty($hashArrays)) {
            $videoListApi['hastagLists'][] = $hashArrays;
          }
        }
      } else {
        $videoListApi['hashtagTitle'] = '';
        $videoListApi['hastagLists'] = [];
      }
      $likeStatus1 = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $videoListApi['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
      if (!empty($likeStatus1)) {
        $videoListApi['likeStatus'] = true;
      } else {
        $videoListApi['likeStatus'] = false;
      }


      $checkFollow1 = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $videoListApi['userId'], 'status' => '1'))->row_array();
      if (!empty($checkFollow1)) {
        $videoListApi['followStatus'] = '1';
      } else {
        $videoListApi['followStatus'] = '0';
      }
    }
    //else{
    // $list =  $this->db->query("SELECT users.username,users.followerCount as followers,users.image,sounds.title as soundTitle,sounds.id as soundId, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join sounds on sounds.id = userVideos.soundId left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ORDER BY userVideos.viewCount desc,userVideos.likeCount desc,userVideos.commentCount LIMIT $startLimit , 5")->result_array();
    $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,sounds.title as soundTitle,sounds.id as soundId, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join sounds on sounds.id = userVideos.soundId left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) ORDER BY RAND() LIMIT $startLimit , 5")->result_array();
    //}
    if (!empty($list)) {
      foreach ($list as $lists) {
        if (!empty($lists['name'])) {
          $lists['username'] = $lists['name'];
        } else {
          $lists['username'] = $lists['username'];
        }
        if (!empty($lists['downloadPath'])) {
          $lists['downloadPath'] = $lists['downloadPath'];
        } else {
          $lists['downloadPath'] =  '';
        }

        if (empty($lists['image'])) {
          $lists['image'] = base_url() . 'uploads/no_image_available.png';
        }
        if (!empty($lists['hashtag'])) {
          $lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
          $finalTagIds = explode(',', $lists['hashtag']);
          foreach ($finalTagIds as $finalTagId) {
            $hashArray = $this->db->get_where('hashtag', array('id' => $finalTagId))->row_array();
            if (!empty($hashArray)) {
              $lists['hastagLists'][] = $hashArray;
            }
          }
        } else {
          $lists['hashtagTitle'] = '';
          $lists['hastagLists'] = [];
        }
        $likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'], 'userId' => $this->input->post('userId'), 'status' => '1'))->row_array();
        if (!empty($likeStatus)) {
          $lists['likeStatus'] = true;
        } else {
          $lists['likeStatus'] = false;
        }


        $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
        if (!empty($checkFollow)) {
          $lists['followStatus'] = '1';
        } else {
          $lists['followStatus'] = '0';
        }

        $otherVideo[] = $lists;
      }

      if (!empty($videoListApi)) {
        $finalUserINfo[] = $videoListApi;
        $finalListSound = array_merge($finalUserINfo, $otherVideo);
      } else {
        $finalListSound = $otherVideo;
      }
      $message['success'] = '1';
      $message['message'] = 'List found SuccessFully';
      $message['details'] = $finalListSound;
    } else {
      $message['success'] = '0';
      $message['message'] = 'NO List Found';
    }
    echo json_encode($message);
  }

  public function beansExchange()
  {
    $list = $this->db->get_where('beansExchange')->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found SuccessFully';
      $message['details'] = $list;
    } else {
      $message['success'] = '0';
      $message['message'] = 'No list found';
    }
    echo json_encode($message);
  }

  public function convertBeansToDiamond()
  {
    $checkData = $this->db->get_where('beansExchange', array('id' => $this->input->post('beansId')))->row_array();
    $userInfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    $upCoin['coin'] = $userInfo['coin'] - $checkData['beans'];
    $upCoin['purchasedCoin'] = $userInfo['purchasedCoin'] + $checkData['diamond'];
    $upCoin['wallet'] = $userInfo['wallet'] + $checkData['diamond'];
    $update = $this->Common_Model->update('users', $upCoin, 'id', $this->input->post('userId'));
    if (!empty($update)) {
      $data['userId'] = $this->input->post('userId');
      $data['beansExchangeId'] = $this->input->post('beansId');
      $data['beans'] = $checkData['beans'];
      $data['wallet'] = $checkData['diamond'];
      $data['created'] = date('Y-m-d H:i:s');
      $insert = $this->db->insert('userWalletHistory', $data);
      if (!empty($insert)) {
        $message['success'] = '1';
        $message['message'] = 'Diamond addedd Successfully';
      } else {
        $message['success'] = '0';
        $message['message'] = 'please try after some time';
      }
      echo json_encode($message);
    }
  }

  public function convertBeansToDollar()
  {
    $checkData = $this->db->get_where('beansExchangeToDollar', array('id' => 1))->row_array();
    $getBeansCount = $this->input->post('coin') / $checkData['beans'];
    $getDollar = $getBeansCount * $checkData['dollar'];

    $userInfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    $upCoin['coin'] = $userInfo['coin'] - $this->input->post('coin');
    $upCoin['incomeDollar'] = $userInfo['incomeDollar'] + $getDollar;
    $update = $this->Common_Model->update('users', $upCoin, 'id', $this->input->post('userId'));
    if (!empty($update)) {
      $data['userId'] = $this->input->post('userId');
      $data['realDollar'] = $getDollar;
      $data['dollarPrice'] = $checkData['dollar'];
      $data['beansCount'] = $checkData['beans'];
      $data['pendingCoin'] = $userInfo['coin'];
      $data['exchangeCoin'] = $this->input->post('coin');
      $data['realCoin'] = $upCoin['coin'];
      $data['created'] = date('Y-m-d H:i:s');
      $insert = $this->db->insert('userDollarHistory', $data);
      if (!empty($insert)) {
        $message['success'] = '1';
        $message['message'] = 'Beans convert to dollar Successfully';
      } else {
        $message['success'] = '0';
        $message['message'] = 'please try after some time';
      }
      echo json_encode($message);
    }
  }

  public function getSingleLiveUserList()
  {
    $id = $this->input->get('streamId');
    $lists =  $this->db->query("select users.username,users.name,users.coin,users.image,users.followerCount,userLive.* from userLive left join users on users.id = userLive.userId where   userLive.id = $id")->row_array();
    if (!empty($lists)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      if (empty($lists['image'])) {
        $lists['image'] = base_url() . 'uploads/no_image_available.png';
      } else {
        $lists['image'] = $lists['image'];
      }

      $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
      if (!empty($checkFollow)) {
        $lists['followStatus'] = '1';
      } else {
        $lists['followStatus'] = '0';
      }

      $lists['created'] = $this->sohitTime($lists['created']);
      $message['details'] = $lists;
    } else {
      $message['success'] = '0';
      $message['message'] = 'No List found';
    }
    echo json_encode($message);
  }




  public function dollarHistory()
  {
    $userInfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    $history = $this->db->get_where('userDollarHistory', array('userId' => $this->input->post('userId')))->result_array();
    $dollar = (string)round($userInfo['incomeDollar'], 2);

    if (!empty($history)) {
      $message['success'] = '1';
      $message['message'] = 'list found SuccessFully';
      $message['dollar'] = (string)round($userInfo['incomeDollar'], 2);
      $message['details'] = $history;
    } else {
      $message['dollar'] = '0';
      $message['success'] = '0';
      $message['message'] = 'no list found';
    }
    echo json_encode($message);
  }

  public function addBankAccount()
  {
    $data['userId'] = $this->input->post('userId');
    $data['ifscCode'] = $this->input->post('ifscCode');
    $data['accountNumber'] = $this->input->post('accountNumber');
    $data['accountHolderName'] = $this->input->post('accountHolderName');
    $data['created'] = date('Y-m-d H:i:s');
    $insert = $this->db->insert('userBankAccount', $data);
    if (!empty($insert)) {
      $message['success'] = '1';
      $message['message'] = 'Bank Account addedd SuccessFully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }


  public function getUserBankAccount()
  {
    $list = $this->db->get_where('userBankAccount', array('userId' => $this->input->post('userId')))->result_array();
    if (!empty($list)) {
      $message['success'] = '1';
      $message['message'] = 'List found succssfully';
      $message['details'] = $list;
    } else {
      $message['success'] = '0';
      $message['message'] = 'No list found';
    }
    echo json_encode($message);
  }

  public function deleteBankAccount()
  {
    $delete = $this->db->delete('userBankAccount', array('id' => $this->input->post('bankId')));
    if (!empty($delete)) {
      $message['success'] = '1';
      $message['message'] = 'Bank account delete successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function withdrawalPayment()
  {
    $userInfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    $data['userId'] = $this->input->post('userId');
    $data['bankId'] = $this->input->post('bankId');
    $data['ammount'] = $this->input->post('ammount');
    $data['pendingAmmount'] = $userInfo['incomeDollar'] -  $this->input->post('ammount');
    $data['transactionId'] = rand(100000, 99999);
    $data['created'] = date('Y-m-d H:i:s');
    $insert = $this->db->insert('withdrawalPaymentHistory', $data);
    if (!empty($insert)) {
      $upInfo['incomeDollar'] = $data['pendingAmmount'];
      $update = $this->Common_Model->update('users', $upInfo, 'id', $this->input->post('userId'));
      $message['success'] = '1';
      $message['message'] = 'Payment withdraw Successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function liveRequest()
  {
    $checkRequest = $this->db->get_where('userLiveRequest', array('userId' => $this->input->post('userId')))->row_array();
    if (empty($checkRequest)) {
      $data['userId'] = $this->input->post('userId');
      $data['request'] = $this->input->post('request');
      $data['created'] = date('Y-m-d H:i:s');
      $insert = $this->db->insert('userLiveRequest', $data);
      if (!empty($insert)) {
        $message['success'] = '1';
        $message['message'] = 'Request send SuccessFully';
      } else {
        $message['success'] = '0';
        $message['message'] = 'Please try after some time';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'You have already sent request, Please wait for the response';
    }
    echo json_encode($message);
  }

  public function dummyAPp()
  {
    $data['frist'] = 'We Advocate healthy and postive broadcasts. Live content involving  violence, vulgarity, alcohol and smoking are stricitly prohibited and in case or violation your account will be suspended.';
    $data['sec'] = 'Join the fun and grab.';
    $data['third'] = 'Welocme to EpLive';
    $message['success'] = '1';
    $message['message'] = 'List found successfully';
    $message['details'][] = $data;
    echo json_encode($message);
  }


  public function hashtagBanner()
  {
    $list = $this->db->get_where('hashtagBanner', array('status' => 'Approved'))->result_array();
    if (!empty($list)) {
      $messgae['success'] = '1';
      $messgae['message'] = 'list found Successfully';
      foreach ($list as $lists) {
        if (!empty($lists['image'])) {
          $lists['image'] = base_url() . $lists['image'];
        } else {
          $lists['image'] = '';
        }
        $messgae['details'][] = $lists;
      }
    } else {
      $messgae['success'] = '0';
      $messgae['message'] = 'No details found';
    }
    echo json_encode($messgae);
  }

  public function pkHostMembers()
  {
    $ristIn['pkHostId'] = $this->input->post('pkHostId');
    $ristIn['giftUserId'] = $this->input->post('mainHost');
    $ristIn['coin'] = '0';
    $insert = $this->db->insert('pkHostLiveGift', $ristIn);

    $ristInw['pkHostId'] = $this->input->post('pkHostId');
    $ristInw['giftUserId'] = $this->input->post('secondHost');
    $ristInw['coin'] = '0';
    $insert = $this->db->insert('pkHostLiveGift', $ristInw);
    $message['success'] = '1';
    $message['message'] = 'Information added successfully';
    echo json_encode($message);
  }


  public function giftHistoryCalculation()
  {
    $pkHostid = $this->input->post('pkSessionId');
    $list =  $this->db->query("SELECT users.username,users.image,users.image,pkHostLiveGift.* FROM `pkHostLiveGift` left join users on users.id = pkHostLiveGift.giftUserId where pkHostLiveGift.pkHostId = '$pkHostid' order by pkHostLiveGift.coin desc")->result_array();
    if (!empty($list)) {
      $first = $list[0]['coin'];
      $sec = $list[1]['coin'];
      if ($first == $sec) {
        $winLoseStatus = 'Tie';
      } else {
        $winLoseStatus = '';
      }
      $message['success'] = '1';
      $message['message'] = 'list found Successfully';
      foreach ($list as $lists) {
        if (!empty($winLoseStatus)) {
          $lists['resultStatus'] = 'Tie';
        } else {
          if ($lists['coin'] == $first) {
            $lists['resultStatus'] = 'Win';
          } else {
            $lists['resultStatus'] = 'Lose';
          }
        }
        $userId = $lists['giftUserId'];
        $url = base_url();
        $history =  $this->db->query("select users.username,users.image,users.phone,users.name,livegift.title as giftTitle,livegift.primeAccount as giftCoin,concat('$url',livegift.image) as giftImage   , userGiftHistory.userId,userGiftHistory.created from userGiftHistory left JOIN users on users.id = userGiftHistory.userId left join livegift on livegift.id = userGiftHistory.giftId where userGiftHistory.giftUserId = $userId and userGiftHistory.pkHostId = '$pkHostid'")->result_array();
        if (!empty($history)) {
          $lists['history'] = $history;
        } else {
          $lists['history'] = [];
        }
        $message['details'][] = $lists;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'no list found';
    }
    echo json_encode($message);
  }



  public function myLiveFriends()
  {
    $finalOutput = [];
    $userId = $this->input->post('userId');
    $frindList =  $this->db->query("SELECT a.*,b.* from userFollow as a LEFT JOIN userFollow as b on b.userId=a.followingUserId and b.followingUserId=$userId where a.userId=$userId and a.status='1' HAVING a.followingUserId = b.userId and b.status='1'")->result_array();
    if (!empty($frindList)) {
      foreach ($frindList as $frindLists) {
        $friendUseriD[] = $frindLists['userId'];
      }

      foreach ($friendUseriD as $friendIds) {
        $checkLive = $this->db->get_where('userLive', array('status' => 'live', 'userId' => $friendIds, 'hostType' => 2))->row_array();
        if (!empty($checkLive)) {
          $outPut['id'] = $checkLive['id'];
          $outPut['hostType'] = $checkLive['hostType'];
          $outPut['channelName'] = $checkLive['channelName'];
          $outPut['token'] = $checkLive['token'];
          $outPut['latitude'] = $checkLive['latitude'];
          $outPut['longitude'] = $checkLive['longitude'];
          $outPut['rtmToken'] = $checkLive['rtmToken'];
          $outPut['status'] = $checkLive['status'];
          $outPut['created'] = $checkLive['created'];
          $userInfo = $this->db->get_where('users', array('id' => $friendIds))->row_array();
          $outPut['userId'] = $userInfo['id'];
          $outPut['username'] = $userInfo['username'];
          $outPut['name'] = $userInfo['name'];
          $outPut['email'] = $userInfo['email'];
          $outPut['phone'] = $userInfo['phone'];
          if (!empty($userInfo['coin'])) {
            $outPut['coin'] = $userInfo['coin'];
          } else {
            $outPut['coin'] = '0';
          }
          if (!empty($userInfo['purchasedCoin'])) {
            $outPut['purchasedCoin'] = $userInfo['purchasedCoin'];
          } else {
            $outPut['purchasedCoin'] = '0';
          }
          if (empty($userInfo['image'])) {
            $outPut['image'] = base_url() . 'uploads/no_image_available.png';
          } else {
            $outPut['image'] = $userInfo['image'];
          }
          $countFollowers = $this->db->get_where('userFollow', array('followingUserId' => $friendIds))->num_rows();
          if (!empty($countFollowers)) {
            $outPut['noOfFollowers'] = (string)$countFollowers;
          } else {
            $outPut['noOfFollowers'] = '0';
          }
          $finalOutput[] = $outPut;
        }
      }
      if (!empty($finalOutput)) {
        $message['success'] = '1';
        $message['message'] = 'List found successfully';
        $message['details'] = $finalOutput;
      } else {
        $message['success'] = '0';
        $message['message'] = 'No List found';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No List found';
    }
    echo json_encode($message);
  }



  public function orderIdGenerate()
  {
    $api_key = 'rzp_live_2RIKadYieK4eVR';
    $api_secret = 'FYAf3lVCBoXv1aSp85DJaLYk';
    // $api_key = 'rzp_test_rVeycL8ovVMX2J';
    // $api_secret = 'h3k0JVJWvXJEIs1lEipN8IEU';
    $api = new Api($api_key, $api_secret);
    $amount = $this->input->post('amount') * 100;
    $receipt = date('YmdHis');
    $order  = $api->order->create(array('receipt' => $receipt, 'amount' => $amount, 'currency' => 'INR')); // Creates order
    $orderId = $order['id']; // Get the created Order ID
    $message['success'] = '1';
    $message['message'] = 'Order id generate successfully';
    $message['orderId'] = $orderId;
    echo json_encode($message);
  }



  public function notificationGTesting()
  {
    $registrationIds =  array('c_davzsM0z4:APA91bGxEMZzaLJ0XBMaaxUjQWJoU7v0_sGLtiX4CbjAUp8-H9Chk2RVQY0K3cwL_vts4Q0SmAONxg5vAgrLrOqVqLybY-CnX5ZpGHRVDHa-CYy5_hmOi7bo_YJJ4z46CuOr3Un5_CEL');
    define('API_ACCESS_KEY', 'AAAA0WgbM-c:APA91bGsdT5rx9u_QmaG9cjyapHiAY6NrzMw_WbMXgEuQi0f7ZbZ6-GCQZUDU6PvHY52KBBYwqC_Dxc9a22la0ad84p1tblRcK2evrYM2ONKU-Oa0xuL9d9jgkMrTynDadMO2OL1VIJz');
    $msg = array(
      'message'  => "sdfsdf",
      'title'    => 'LiveBazaar',
      'type'   => "video",
      'subtitle' => "video",
      'loginId' => 1,
      'userId' => 2,
      'vibrate'  => 1,
      'sound'    => 1,
      'largeIcon'  => 'large_icon',
      'smallIcon'  => 'small_icon',
    );


    $fields = array(
      'registration_ids'   => $registrationIds,
      'data'     => $msg
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
    print_r($response);
    die;
  }


  public function publicBulletMessage()
  {
    $userInfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    $error = '';
    if ($this->input->post('type') == 'public') {
      if ($userInfo['purchasedCoin'] >= 1000) {
        $error = 'no';
      } else {
        $error = 'yes';
      }
      $deductCoin = '1000';
    } else {
      if ($userInfo['purchasedCoin'] >= 1) {
        $error = 'no';
      } else {
        $error = 'yes';
      }
      $deductCoin = '1';
    }
    if ($error == 'yes') {
      $message['success'] = '0';
      $message['message'] = 'Please add Coin your wallet';
    } else {
      $userInfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
      $updateCoin['purchasedCoin'] = $userInfo['purchasedCoin'] - $deductCoin;
      $update = $this->Common_Model->update('users', $updateCoin, 'id', $this->input->post('userId'));

      if (!empty($update)) {
        $message['success'] = '1';
        $message['message'] = 'Coin deduct successfully';
      } else {
        $message['success'] = '0';
        $message['message'] = 'Please try after some time';
      }
    }
    echo json_encode($message);
  }


  public function boxOpenAPi()
  {
    $liveId = $this->input->post('liveId');
    $checkPriceCoin = $list =  $this->db->query("SELECT sum(coin) as totalReciveCoin FROM `starBoxResult` where liveId = $liveId")->row_array();
    if ($checkPriceCoin['totalReciveCoin'] < 500) {
      $randomCoin = rand(1, 40);

      $data['userId'] = $this->input->post('userId');
      $data['liveId'] = $this->input->post('liveId');
      $data['box'] = $this->input->post('box');
      $data['coin'] = $randomCoin;
      $data['created'] = date('Y-m-d H:i:s');
      $this->db->insert('starBoxResult', $data);

      $userInfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
      $updateCoin['coin'] = $userInfo['coin'] + $randomCoin;
      $update = $this->Common_Model->update('users', $updateCoin, 'id', $this->input->post('userId'));
      $message['success'] = '1';
      $message['message'] = 'Gift Collected SuccessFully';
      $message['winCoin'] = (string)$randomCoin;
    } else {
      $message['success'] = '1';
      $message['message'] = 'No Gift Available';
      $message['winCoin'] = '0';
    }
    echo json_encode($message);
  }

  public function getLiveSessionDetails()
  {
    if ($this->input->post()) {
      $dateTime = $this->input->post('dateTime');
      $userId = $this->input->post('userId');
      $details = $this->db->query("select userGiftHistory.coins, u.id,u.userId,u.archivedDate,u.created,u.channelName,TIMEDIFF(TIME(u.archivedDate),TIME(u.created))/60 as duration from userLive u left join userGiftHistory on userGiftHistory.liveId = u.id where u.archivedDate != '' and DATE_FORMAT(u.created ,'%Y-%m') = '$dateTime' and u.userId = $userId")->result_array();
      if (!empty($details)) {
        $numRows = $this->db->query("SELECT DISTINCT day(created) AS days FROM `userLive` WHERE archivedDate != '' and userId = $userId and created LIKE '$dateTime%'")->num_rows();

        foreach ($details as $details1) {

          if ($details1['coin'] == null) {
            $details1['coin'] = '0';
          } else {
            $details1['coin'] =  $details1['coin'];
          }
          $final[] = $details1;
        }
        // $final['days'] = $numRows;
        $message['success'] = '1';
        $message['days'] = $numRows;
        $message['message'] = 'Details found successfully';
        $message['details'] = $final;
      } else {
        $message['success'] = '0';
        $message['message'] = 'Details not found';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please enter parameters';
    }
    echo json_encode($message);
  }


  public function verifactionForm()
  {
    $getStatus = $this->db->get_where('registerUserInfo', array('userId' => $this->input->post('userId')))->row_array();
    if (!empty($getStatus)) {
      $outPut['status'] = $getStatus['status'];
      if ($getStatus['status'] == '1') {
        $outPut['reason'] = 'Approved';
      } elseif ($getStatus['status'] == '2') {
        $outPut['reason'] = $getStatus['reason'];
      } else {
        $outPut['reason'] = 'Pending';
      }
      $message['success'] = '1';
      $message['message'] = 'details found SuccessFully';
      $message['details'] = $outPut;
    } else {
      $message['success'] = '0';
      $message['message'] = 'No details found';
    }

    echo json_encode($message);
  }


  public function lllll()
  {
    echo $todayDateTime = date('Y-m-d H:i:s');
    die;
  }



  public function singleLiveDetails()
  {

    $loginIdMain = $this->input->post('userId');
    $liveId = $this->input->post('liveId');
    $lists =  $this->db->query("select users.username,users.name,users.coin,users.leval,users.image,users.followerCount,userLive.* from userLive left join users on users.id = userLive.userId where userLive.id = $liveId ")->row_array();
    // echo $this->db->last_query();
    // print_r($lists);
    // die;
    $useriNfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    if (!empty($lists)) {
      $todyDD = date('Y-m-d');

      $checkStarStatus = $this->db->get_where('userStar', array('userId' => $lists['userId'], 'created' => $todyDD))->row_array();
      if (!empty($checkStarStatus)) {
        $starStatus = $checkStarStatus['star'];
        $starStatusstarCount = $checkStarStatus['starCount'];
        if ($starStatus != 0) {
          $checkBoxCount = $this->db->get_where('starList', array('star' => $starStatus))->row_array();
          $starBOX = $checkBoxCount['box'];
        } else {
          $starBOX = 0;
        }
      } else {
        $starStatus = '0';
        $starBOX = 0;
      }

      $mainUserId = $this->input->post('userId');
      $mainLiveId = $lists['id'];
      $checkStarStatus12 = $this->db->query("SELECT * FROM `starBoxResult` where userId = $mainUserId and liveId= $mainLiveId and box = $starBOX and date(created) = '$todyDD'")->num_rows();
      if (!empty($checkStarStatus12)) {
        $lists['checkBoxStatus'] = '0';
      } else {
        $lists['checkBoxStatus'] = '1';
      }

      $lists['box'] = (string)$starBOX;



      $todyDD = date('Y-m-d');
      $checkStarStatus = $this->db->get_where('userStar', array('userId' => $this->input->post('userId'), 'created' => $todyDD))->row_array();
      if (!empty($checkStarStatus)) {
        $starStatus = $checkStarStatus['star'];
      } else {
        $starStatus = '0';
      }
      $lists['purchasedCoin'] = $lists['coin'];
      $lists['userLeval'] = $lists['leval'];
      $lists['startCount'] = $starStatus;
      if (empty($lists['image'])) {
        $lists['image'] = base_url() . 'uploads/no_image_available.png';
      } else {
        $lists['image'] = $lists['image'];
      }
      $lists['created'] = $this->sohitTime($lists['created']);

      $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
      if (!empty($checkFollow)) {
        $lists['followStatus'] = '1';
      } else {
        $lists['followStatus'] = '0';
      }


      if ($this->input->post('type') == '1') {
        //   echo "yes";

        $followUserInfo = $this->db->get_where('users', array('id' => $this->input->post('otherId')))->row_array();
        //echo $this->db->last_query();
        $registrationIds =  array($followUserInfo['reg_id']);

        // print_r($registrationIds);
        define('API_ACCESS_KEY', 'AAAA0WgbM-c:APA91bGsdT5rx9u_QmaG9cjyapHiAY6NrzMw_WbMXgEuQi0f7ZbZ6-GCQZUDU6PvHY52KBBYwqC_Dxc9a22la0ad84p1tblRcK2evrYM2ONKU-Oa0xuL9d9jgkMrTynDadMO2OL1VIJz');
        if (!empty($lists['name'])) {
          $notyMessage  = $lists['name'] . ' is Live Now..';
        } else {
          $notyMessage  = $lists['username'] . ' is Live Now..';
        }
        $msg = array(
          'message'  => $notyMessage,
          'title'    => 'LiveBazaar',
          'type'   => 'liveUser',
          'subtitle' => 'liveUser',
          'username' => $lists['username'],
          'name' => $lists['name'],
          'coin' => $lists['coin'],
          'leval' => $lists['leval'],
          'image' => $lists['image'],
          'followerCount' => $lists['followerCount'],
          'id' => $lists['id'],
          'liveId' => $lists['id'],
          'hostType' => $lists['hostType'],
          'userId' => $lists['username'],
          'channelName' => $lists['channelName'],
          'token' => $lists['token'],
          'latitude' => $lists['latitude'],
          'longitude' => $lists['longitude'],
          'rtmToken' => $lists['rtmToken'],
          'status' => $lists['status'],
          'archivedDate' => $lists['archivedDate'],
          'checkBoxStatus' => $lists['checkBoxStatus'],
          'box' => $lists['box'],
          'created' => 'just now',
          'purchasedCoin' => $lists['purchasedCoin'],
          'userLeval' => $lists['userLeval'],
          'startCount' => $lists['startCount'],
          'followStatus' => $lists['followStatus'],
          'vibrate'  => 1,
          'sound'    => 1,
          'largeIcon'  => 'large_icon',
          'smallIcon'  => 'small_icon',
        );

        // print_r($msg);
        // die;
        $fields = array(
          'registration_ids'   => $registrationIds,
          'data'     => $msg
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

        // print_r($response);
        // die;


        $err = curl_error($curl);
        curl_close($curl);
      } else {





        $lists12 = $this->db->get_where('userFollow', array('followingUserId' => $this->input->post('userId'), 'status' => '1'))->result_array();
        if (!empty($lists12)) {
          foreach ($lists12 as $list123) {

            $followUserInfo = $this->db->get_where('users', array('id' => $list123['userId']))->row_array();
            $registrationIds =  array($followUserInfo['reg_id']);
            define('API_ACCESS_KEY', 'AAAA0WgbM-c:APA91bGsdT5rx9u_QmaG9cjyapHiAY6NrzMw_WbMXgEuQi0f7ZbZ6-GCQZUDU6PvHY52KBBYwqC_Dxc9a22la0ad84p1tblRcK2evrYM2ONKU-Oa0xuL9d9jgkMrTynDadMO2OL1VIJz');
            if (!empty($lists['name'])) {
              $notyMessage  = $lists['name'] . ' is Live Now..';
            } else {
              $notyMessage  = $lists['username'] . ' is Live Now..';
            }

            $msg = array(
              'message'  => $notyMessage,
              'title'    => 'LiveBazaar',
              'type'   => 'liveUser',
              'subtitle' => 'liveUser',
              'username' => $lists['username'],
              'name' => $lists['name'],
              'coin' => $lists['coin'],
              'leval' => $lists['leval'],
              'image' => $lists['image'],
              'followerCount' => $lists['followerCount'],
              'id' => $lists['id'],
              'liveId' => $lists['id'],
              'hostType' => $lists['hostType'],
              'userId' => $lists['username'],
              'channelName' => $lists['channelName'],
              'token' => $lists['token'],
              'latitude' => $lists['latitude'],
              'longitude' => $lists['longitude'],
              'rtmToken' => $lists['rtmToken'],
              'status' => $lists['status'],
              'archivedDate' => $lists['archivedDate'],
              'checkBoxStatus' => $lists['checkBoxStatus'],
              'box' => $lists['box'],
              'created' => 'just now',
              'purchasedCoin' => $lists['purchasedCoin'],
              'userLeval' => $lists['userLeval'],
              'startCount' => $lists['startCount'],
              'followStatus' => $lists['followStatus'],
              'vibrate'  => 1,
              'sound'    => 1,
              'largeIcon'  => 'large_icon',
              'smallIcon'  => 'small_icon',
            );
            $fields = array(
              'registration_ids'   => $registrationIds,
              'data'     => $msg
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

            //die;

            $err = curl_error($curl);
            curl_close($curl);
          }
        }
      }
    } else {
    }

    $message1['success'] = '1';
    $message1['message'] = 'Notification send Successfully';
    echo json_encode($message1);
  }

  public function post()
  {
    $data['userId'] = $this->input->post('userId');
    $data['description'] = $this->input->post('description');
    $data['type'] = $this->input->post('type');
    $data['postDate'] = date('Y-m-d');
    $data['postTime'] = date('H:i:s');
    $data['created'] = date('Y-m-d H:i:s');
    if (!empty($_FILES["image"]["name"])) {
      $name1 = time() . '_' . $_FILES["image"]["name"];
      $name = str_replace(' ', '_', $name1);
      $liciense_tmp_name = $_FILES["image"]["tmp_name"];
      $error = $_FILES["image"]["error"];
      $liciense_path = 'uploads/users/' . $name;
      move_uploaded_file($liciense_tmp_name, $liciense_path);
      $data['image'] = $liciense_path;
    }
    $insert = $this->db->insert('post', $data);
    if (!empty($insert)) {
      $message['success'] = '1';
      $message['message'] = 'Post uploaded successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function singleUserPost()
  {
    $userDetails = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
    $data['name'] = $userDetails['name'];
    $data['username'] = $userDetails['username'];
    if (!empty($userDetails['image'])) {
      if (filter_var($userDetails['image'], FILTER_VALIDATE_URL)) {
        $data['image'] = $userDetails['image'];
      } else {
        $data['image'] = base_url() . $userDetails['image'];
      }
    } else {
      $data['image'] = '';
    }
    $followers = $this->db->get_where('userFollow', array('followingUserId' => $this->input->post('userId'), 'status' => '1'))->num_rows();
    if (!empty($followers)) {
      $data['followers'] = (string)$followers;
    } else {
      $data['followers'] = '0';
    }
    $following = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'status' => '1'))->num_rows();
    if (!empty($following)) {
      $data['following'] = (string)$following;
    } else {
      $data['following'] = '0';
    }

    $followerStatus = $this->db->get_where('userFollow', array('userId' => $this->input->post('loginId'), 'followingUserId' => $this->input->post('userId')))->row_array();
    if (!empty($followerStatus)) {
      $data['followerStatus'] = $followerStatus['status'];
    } else {
      $data['followerStatus'] = '0';
    }

    $lists = $this->db->get_where('post', array('userId' => $this->input->post('userId')))->result_array();
    if (!empty($lists)) {
      $data['postCount'] = (string)count($lists);
      foreach ($lists as $list) {
        if (!empty($list['image'])) {
          $list['image'] = base_url() . $list['image'];
        } else {
          $list['image'] = '';
        }
        $list['time'] = $this->getTime($list['created']);
        $likeStatus = $this->db->get_where('postLike', array('userId' => $this->input->post('loginId'), 'postId' => $list['id']))->row_array();
        if (!empty($likeStatus)) {
          $list['likeStatus'] = $likeStatus['status'];
        } else {
          $list['likeStatus'] = '0';
        }
        $data['post'][] = $list;
      }
    } else {
      $data['postCount'] = '0';
      $data['post'] = [];
    }
    $message['success'] = '1';
    $message['message'] = 'List Found Successfully';
    $message['details'] = $data;
    echo json_encode($message);
  }

  public function postLikeUnlike()
  {
    $table = 'postLike';
    $postTable = $this->db->get_where('post', array('id' => $this->input->post('postId')))->row_array();
    $check_like =  $this->db->get_where($table, array('postId' => $this->input->post('postId'), 'userId' => $this->input->post('userId')))->row_array();
    if (!empty($check_like)) {
      if ($check_like['status'] == '0') {
        $status = '1';
      } else {
        $status = '0';
      }
      $data = array(
        'userId' => $this->input->post('userId'),
        'postId' => $this->input->post('postId'),
        'status' => $status,
        'updated' => date('y-m-d h:i:s')
      );
      $update = $this->Common_Model->update($table, $data, 'id', $check_like['id']);
    } else {
      $status = '1';
      $data = array(
        'userId' => $this->input->post('userId'),
        'postId' => $this->input->post('postId'),
        'status' => $status,
        'created' => date('y-m-d h:i:s')
      );
      $insert = $this->Common_Model->register($table, $data);
      $insert_id = $this->db->insert_id();
    }
    if (empty($check_like)) {
      $upd['likeCount'] = $postTable['likeCount'] + 1;
      $update = $this->Common_Model->update('post', $upd, 'id', $this->input->post('postId'));
      $message123 = 'post like successfully';
    } else {
      if ($status == '0') {
        $upd['likeCount'] = $postTable['likeCount'] - 1;
        $update = $this->Common_Model->update('post', $upd, 'id', $this->input->post('postId'));
        $message123 = 'post unlike successfully';
      } else {
        $upd['likeCount'] = $postTable['likeCount'] + 1;
        $update = $this->Common_Model->update('post', $upd, 'id', $this->input->post('postId'));
        $message123 = 'post like successfully';
      }
    }
    $post['like_count'] = $this->db->get_where($table, array('postId' => $this->input->post('postId'), 'status' => '1'))->num_rows();
    $successmessage = array(
      'success' => '1',
      'message' => $message123,
      'like_status' => $status,
      'like_count' => (string)$post['like_count']
    );

    echo json_encode($successmessage);
  }

  public function deletePost()
  {
    $delete = $this->db->delete('post', array('id' => $this->input->post('postId')));
    if ($delete) {
      $this->db->delete('postLike', array('postId' => $this->input->post('postId')));
      $message['success'] = '1';
      $message['message'] = 'Post Delete Successfully';
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please try after some time';
    }
    echo json_encode($message);
  }

  public function getPost()
  {
    $lists = $this->db->query("select users.name,users.username,users.image as userImage,post.* FROM post LEFT JOIN users on users.id = post.userId order by post.id DESC")->result_array();
    if (!empty($lists)) {
      $message['success'] = '1';
      $message['message'] = 'List found successfully';
      foreach ($lists as $list) {
        if (!empty($list['image'])) {
          $list['image'] = base_url() . $list['image'];
        } else {
          $list['image'] = '';
        }
        if (!empty($list['userImage'])) {
          if (filter_var($list['userImage'], FILTER_VALIDATE_URL)) {
            $list['userImage'] = $list['userImage'];
          } else {
            $list['userImage'] = base_url() . $list['userImage'];
          }
        } else {
          $list['userImage'] = '';
        }
        $likeStatus = $this->db->get_where('postLike', array('userId' => $this->input->post('userId'), 'postId' => $list['id'], 'status' => "1"))->num_rows();
        if (!empty($likeStatus)) {
          $list['likeStatus'] = '1';
        } else {
          $list['likeStatus'] = '0';
        }
        $list['time'] = $this->getTime($list['created']);
        $message['details'][] = $list;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'No list found';
    }
    echo json_encode($message);
  }

  // ============ LiveBazaar Apis Start ==============

  //  public function liveShippingAddress()
  //  {

  //      if (!$this->input->post("userId") || !$this->input->post("type")) {
  //      echo json_encode([
  //        'success' => '0',
  //        'message' => 'Please enter valid param!'
  //      ]);
  //      exit;
  //      }
  //    $userId = $this->db->get_where('liveShippingAddress', array('userId' => $this->input->post('userId'),'type' => $this->input->post('type')))->row_array();
  //    if (!empty($userId)) {
  //      $data['fullname'] = $this->input->post("fullname");
  //      $data['address'] = $this->input->post("address");
  //      $data['address2'] = $this->input->post("address2");
  //      $data['city'] = $this->input->post("city");
  //      $data['state'] = $this->input->post("state");
  //      $data['postal_code'] = $this->input->post("postal_code");
  //      $data['country'] = $this->input->post("country");
  //      $data['userId'] = $this->input->post("userId");
  //      $data['phone'] = $this->input->post("phone");
  //      $data['updated'] = date('Y-m-d H:i:s');
  //      $type = $this->input->post("type");

  //      $update = $this->db->update('liveShippingAddress', $data,  ['userId' => $this->input->post('userId'),'type' => $type]);

  //      if ($update == true) {

  //        $message = [
  //          'success' => '1',
  //          'message' => 'address updated Done'

  //        ];
  //      } else {
  //        $message = [
  //          'success' => '0',
  //          'message' => 'Something went wrong'
  //        ];
  //      }
  //    } else {
  //      $data['fullname'] = $this->input->post("fullname");
  //      $data['address'] = $this->input->post("address");
  //      $data['address2'] = $this->input->post("address2");
  //      $data['city'] = $this->input->post("city");
  //      $data['state'] = $this->input->post("state");
  //      $data['postal_code'] = $this->input->post("postal_code");
  //      $data['country'] = $this->input->post("country");
  //      $data['userId'] = $this->input->post("userId");
  //      $data['phone'] = $this->input->post("phone");
  //      $data['type'] = $this->input->post("type");
  //      $data['created'] = date('Y-m-d H:i:s');

  //      $insert = $this->db->insert('liveShippingAddress', $data);
  //      if ($insert == true) {

  //        $message = [
  //          'success' => '1',
  //          'message' => 'address addded succesfully'

  //        ];
  //      } else {
  //        $message = [
  //          'success' => '0',
  //          'message' => 'Please try After Some Time'
  //        ];
  //      }
  //    }


  //    echo json_encode($message);
  // }

  public function liveShippingAddress()
  {

    if ($this->input->post()) {

      $data['fullname'] = $this->input->post("fullname");
      $data['address'] = $this->input->post("address");
      $data['address2'] = $this->input->post("address2");
      $data['city'] = $this->input->post("city");
      $data['state'] = $this->input->post("state");
      $data['postal_code'] = $this->input->post("postal_code");
      $data['country'] = $this->input->post("country");
      $data['userId'] = $this->input->post("userId");
      $data['phone'] = $this->input->post("phone");
      $data['created'] = date('Y-m-d H:i:s');
      $data['type'] = $this->input->post("type");

      $upload = $this->db->insert("liveShippingAddress", $data);

      if ($upload == true) {
        $message['success'] = '1';
        $message['message'] = 'address addded succesfully';
      } else {
        $message['success'] = '0';
        $message['message'] = 'Something went wrong!';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please enters valid params!';
    }
    echo json_encode($message);
  }

  public function getUserProductAddress()
  {

    $userId = $this->input->post("userId");
    $type = $this->input->post("type");

    $getAddress = $this->db->select("liveShippingAddress.*")
      ->from("liveShippingAddress")
      ->where("liveShippingAddress.userId", $userId)
      ->where("liveShippingAddress.type", $type)
      ->get()
      ->result_array();

    if (!!$getAddress) {
      $message['success'] = '1';
      $message['message'] = 'Details get successfully';
      $message['details'] = $getAddress;
    } else {
      $message['success'] = '0';
      $message['message'] = 'Details not found!';
    }
    echo json_encode($message);
  }


  public function deleteUserProductAddress()
  {
    $id = $this->db->get_where('liveShippingAddress', array('id' => $this->input->post('id')))->row_array();

    if (!empty($id)) {
      $id = $this->input->post('id');
      $del = $this->db->query("DELETE liveShippingAddress FROM liveShippingAddress WHERE liveShippingAddress.id = '$id'");
      if ($del) {
        $message['success'] = '1';
        $message['message'] = 'Address deleted';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'Something went wrong!';
    }

    echo json_encode($message);
  }

  public function editUserProductAddress()
  {

    if (!$this->input->post("id") || !$this->input->post("type")) {

      echo json_encode([
        'success' => '0',
        'message' => 'Please enter valid param!'
      ]);
      exit;
    }

    $userId = $this->db->get_where('liveShippingAddress', array('id' => $this->input->post('id'), 'type' => $this->input->post('type')))->row_array();

    if (!!$userId) {
      if (!empty($this->input->post('fullname'))) {
        $data['fullname'] = $this->input->post('fullname');
      }
      if (!empty($this->input->post('address'))) {
        $data['address'] = $this->input->post('address');
      }
      if (!empty($this->input->post('address2'))) {
        $data['address2'] = $this->input->post('address2');
      }
      if (!empty($this->input->post('city'))) {
        $data['city'] = $this->input->post('city');
      }
      if (!empty($this->input->post('state'))) {
        $data['state'] = $this->input->post('state');
      }
      if (!empty($this->input->post('postal_code'))) {
        $data['postal_code'] = $this->input->post('postal_code');
      }
      if (!empty($this->input->post('country'))) {
        $data['country'] = $this->input->post('country');
      }
      if (!empty($this->input->post('phone'))) {
        $data['phone'] = $this->input->post('phone');
      }
      $type = $this->input->post("type");
      $data['updated'] = date('Y-m-d H:i:s');

      $update = $this->db->update('liveShippingAddress', $data,  ['id' => $this->input->post('id'), 'type' => $type]);

      if ($update == true) {
        $message['success'] = '1';
        $message['message'] = 'address edit succesfully';
      } else {
        $message['success'] = '0';
        $message['message'] = 'Something went wrong!';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'Something went wrong!';
    }

    echo json_encode($message);
  }


  public function uploadProduct()
  {
    $userId = $this->input->post("userId");
    $checkUserId = $this->db->get_where("users", array('id' => $userId))->row_array();
    if (!empty($checkUserId)) {


      if (!empty($_FILES["product_image"]["name"])) {
        $name1 = time() . '_' . $_FILES["product_image"]["name"];
        $name = str_replace(' ', '_', $name1);
        $liciense_tmp_name = $_FILES["product_image"]["tmp_name"];
        $error = $_FILES["product_image"]["error"];
        $liciense_path = 'uploads/products/' . $name;
        move_uploaded_file($liciense_tmp_name, $liciense_path);
        $data['product_image'] = $liciense_path;
      }

      $data = [
        'userId' => $userId,
        "product_Name" => $this->input->post("product_Name"),
        "product_description" => $this->input->post("product_description"),
        "product_qunt" =>  $quantity + $this->input->post("product_qunt"),
        "product_price" =>  $this->input->post("product_price"),
        'product_image' => base_url($liciense_path)
      ];

      $insert = $this->db->insert('products', $data);
      $lastId = $this->db->insert_id();
      if (!empty($insert)) {
        $getProduct = $this->db->get_where("products", array('id' => $lastId))->row_array();
        if (!!$getProduct) {
          $message['status'] = '1';
          $message['message'] = 'product uploaded';
          $message['details'] = $getProduct;
        }
      } else {
        $message['status'] = '0';
        $message['message'] = 'product uploading process failed';
      }
    } else {
      $message['status'] = '0';
      $message['message'] = 'user does not exit';
    }

    echo json_encode($message);
  }

  public function getuploadProduct()
  {
    $userId = $this->input->post("userId");
    $checkUserId = $this->db->get_where("users", array('id' => $userId))->row_array();
    if (!empty($checkUserId)) {
      $getProduct = $this->db->get_where("products", array('userId' => $userId))->result_array();

      if (!empty($getProduct)) {
        $message['status'] = '1';
        $message['message'] = 'product details found successfully';
        $message['details'] = $getProduct;
      } else {
        $message['status'] = '0';
        $message['message'] = 'no record found';
      }
    } else {
      $message['status'] = '0';
      $message['message'] = 'user does not exit';
    }

    echo json_encode($message);
  }

  public function deleteProduct()
  {
    $productId = $this->input->post('productId');
    $delete = $this->db->delete('products', array('id' => $productId));
    if (!!$delete) {
      $message['status'] = '1';
      $message['message'] = 'product delete successfully ';
    } else {
      $message['status'] = '0';
      $message['message'] = 'product delete failed ';
    }
    echo json_encode($message);
  }

  public function VideosLike()
  {
    $data['userId'] = $this->input->post('userId');
    $data['ownerId'] = $this->input->post('ownerId');
    $data['videoId'] = $this->input->post('videoId');
    $data['status'] = '1';
    $videoId = $this->input->post('videoId');

    $ckeckCount = $this->db->get_where("userVideos", ['id' => $this->input->post('videoId')])->row_array();

    $getlike = $ckeckCount['likeCount'];

    if ($getlike <= '0') {
      $insert = $this->db->insert('videoLikeOrUnlike', $data);
      if ($insert) {

        $this->db->set('likeCount', 'likeCount +1', false)->where('id', $this->input->post('videoId'))->update("userVideos");
        $this->db->set('likes', 'likes +1', false)->where('userId', $this->input->post('userId'))->update("userProfileInformation");
        //   $this->db->set('likeCount', 'likeCount +1', false)->where('id', $this->input->post('ownerId'))->update("userVideos");

        $Counts = $this->db->get_where("userVideos", ['id' => $videoId])->row_array();

        $message['success'] = '1';
        $message['message'] = 'Video like succesfully';
        $message['details'] = $Counts;
      }
    } else {

      $get = $this->db->get_where('videoLikeOrUnlike', ['userId' => $this->input->post('userId'), 'ownerId' => $this->input->post('ownerId')])->row_array();

      if (!empty($get)) {

        $delete = $this->db->delete('videoLikeOrUnlike', ['userId' => $this->input->post('userId'), 'ownerId' => $this->input->post('ownerId')]);
        if ($delete) {

          $update['status'] = '0';
          $this->db->update("videoLikeOrUnlike", $update, ['userId' => $this->input->post('userId'), 'videoId' => $this->input->post('videoId')]);

          $this->db->set('likeCount', 'likeCount -1', false)->where('id', $this->input->post('videoId'))->update("userVideos");
          $this->db->set('likes', 'likes -1', false)->where('userId', $this->input->post('userId'))->update("userProfileInformation");

          $getCounts = $this->db->get_where("userVideos", ['id' => $videoId])->row_array();



          $message['success'] = '2';
          $message['message'] = 'Video dislike successfully';
          $message['details'] = $getCounts;
        }
      } else {

        $insert = $this->db->insert('videoLikeOrUnlike', $data);
        if ($insert) {

          $this->db->set('likeCount', 'likeCount +1', false)->where('id', $this->input->post('videoId'))->update("userVideos");
          $this->db->set('likes', 'likes +1', false)->where('userId', $this->input->post('userId'))->update("userProfileInformation");

          $Counts = $this->db->get_where("userVideos", ['id' => $videoId])->row_array();

          $message['success'] = '1';
          $message['message'] = 'Video like succesfully';
          $message['details'] = $Counts;
        }
      }
    }
    echo json_encode($message);
  }

  //============ NC Apis =============

  public function updateUserProfile()
  {
    if ($this->input->post()) {
      $userId = $this->input->post('id');
      $checkId = $this->db->get_where("users", ['id' => $userId])->row_array();
      if (!!$checkId) {

        if (!empty($this->input->post('name'))) {
          $data['name'] = $this->input->post('name');
        }
        // if(!empty($this->input->post('username'))){
        //   $data['username'] = $this->input->post('username');
        // }
        if (!empty($this->input->post('firstname'))) {
          $data['firstname'] = $this->input->post('firstname');
        }
        if (!empty($this->input->post('lastname'))) {
          $data['lastname'] = $this->input->post('lastname');
        }
        if (!empty($this->input->post('gender'))) {
          $data['gender'] = $this->input->post('gender');
        }
        if (!empty($this->input->post('dob'))) {
          $data['dob'] = $this->input->post('dob');
        }
        if (!empty($this->input->post('country'))) {
          $data['country'] = $this->input->post('country');
        }
        if (!empty($this->input->post('bio'))) {
          $data['bio'] = $this->input->post('bio');
        }

        $data['updated'] = date("Y-m-d H:i:s");

        if (!empty($_FILES["image"]["name"])) {
          // $name1= time().'_'.$_FILES["image"]["name"];
          // $name= str_replace(' ', '_', $name1);
          // $liciense_tmp_name=$_FILES["image"]["tmp_name"];
          // $error=$_FILES["image"]["error"];
          // $liciense_path='uploads/user/'.$name;
          // move_uploaded_file($liciense_tmp_name,$liciense_path);
          $data['image'] = $this->uploadVideo($_FILES["image"]);
        }

        $update = $this->db->update('users', $data, array('id' => $userId));

        if (!empty($update)) {

          $details = $this->db->get_where('users', array('id' => $userId))->row_array();
          if (!empty($details['image'])) {
            $details['image'] = $details['image'];
          }

          $getFollowers = $this->db->get_where('userFollow', ['followingUserId' => $userId])->num_rows();
          if ($getFollowers > '0') {
            $details['FollowersCount'] = $getFollowers;
          } else {
            $details['FollowersCount'] = '0';
          }

          $getFollowing = $this->db->get_where('userFollow', ['userId' => $userId])->num_rows();
          if ($getFollowing > '0') {
            $details['FollowingCount'] = $getFollowing;
          } else {
            $details['FollowingCount'] = '0';
          }
          $message = array(
            'success' => '1',
            'message' => 'Profile updated succssfully!',
            'details' => $details
          );
        }
      } else {
        $message = array(
          'success' => '0',
          'message' => 'Please enter valid id!',
        );
      }
    } else {
      $message = array(
        'success' => '0',
        'message' => 'Please enter valid parameters!',
      );
    }
    echo json_encode($message);
  }

  public function userBackgroundImage()
  {

    if (!empty($_FILES["background_image"]["name"])) {
      // $name1= time().'_'.$_FILES["image"]["name"];
      // $name= str_replace(' ', '_', $name1);
      // $liciense_tmp_name=$_FILES["image"]["tmp_name"];
      // $error=$_FILES["image"]["error"];
      // $liciense_path='uploads/user/'.$name;
      // move_uploaded_file($liciense_tmp_name,$liciense_path);
      $data['background_image'] = $this->uploadVideo($_FILES["background_image"]);
    }

    $update = $this->db->update("users", $data, ['id' => $this->input->post("userId")]);

    if ($update == true) {
      echo json_encode([
        "message" => "background image updated",
        "success" => "1",
      ]);
      exit;
    }
  }

  public function getBackgroundImage()
  {

    $get = $this->db->get_where("users", ['id' => $this->input->post("userId")])->row_array();

    if (!!$get) {
      echo json_encode([
        "message" => "background image found",
        "success" => "1",
        "details" => $get,
      ]);
      exit;
    } else {
      echo json_encode([
        "message" => "background image not found!",
        "success" => "0",
      ]);
      exit;
    }
  }

  public function uploadMedia(){
    print_r($this->uploadVideo($_FILES['name']));
  }

  protected function uploadVideo($file)
  {
    require APPPATH . '/libraries/vendor/autoload.php';

    try {

      $client = \Aws\S3\S3Client::factory([
        'version' => 'latest',
        'region'  => 'us-west-2',
        'credentials' => [
          'key'    => "AKIAYNWA4DQWMMK4TIME",
          'secret' => "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",
        ]
      ]); //exit;

      $return = $client->putObject([
        'Bucket'     => 'healthvideos12',
        'Key'        => time() . $file["name"], // we can define custom name //
        'SourceFile' => $file["tmp_name"],    // like /var/www/vhosts/mysite/file.csv
        'ACL'        => 'public-read',
      ]);

      $aws_result = new \Aws\Result();
      return $return->get("ObjectURL");
    } catch (Exception $e) {
      // Catch an S3 specific exception.
      echo json_encode([
        "success"  =>  "0",
        "message"  =>  $e->getMessage(),
      ]);
    }
  }

  public function up()
  {
    $path = $this->uploadVideo($_FILES['filename']);

    echo $path;
  }

  // public function uploadToAws($file){
  //
  //
  //   require APPPATH.'/libraries/vendor/autoload.php';
  //
  //   try {
  //
  //       $client = \Aws\S3\S3Client::factory([
  //         'version' => 'latest',
  //         'region'  => 'us-east-2',
  //         'credentials' => [
  //             'key'    => "AKIATGH6JSV6CTY3HDEH",
  //             'secret' => "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",
  //         ]
  //     ]);//exit;
  //
  //       $return = $client->putObject([
  //           'Bucket'     =>'eplivevideo',
  //           'Key'        => time().$file["name"], // we can define custom name //
  //           'SourceFile' => $file["tmp_name"],    // like /var/www/vhosts/mysite/file.csv
  //           'ACL'        => 'public-read',
  //       ]);
  //
  //       $aws_result = new \Aws\Result();
  //       return $return->get("ObjectURL");
  //   } catch (Exception $e) {
  //       // Catch an S3 specific exception.
  //       echo json_encode([
  //         "success"	=>	"0",
  //         "message"	=>	$e->getMessage(),
  //       ]);
  //   }
  //
  // }

  public function logoutUser()
  {

    if ($this->input->post('userId') != null) {
      $data['reg_id'] = '';

      $updateReg = $this->db->update("users", $data, ['id' => $this->input->post('userId')]);

      if ($updateReg == true) {
        $message = [
          'success' => '1',
          'message' => 'Logout Done'
        ];
      } else {
        $message = [
          'success' => '0',
          'message' => 'Please Try After Some Time'
        ];
      }
    } else {
      $message = [
        'success' => '0',
        'message' => 'Please Enter Valid Parameters'
      ];
    }
    echo json_encode($message);
  }

  public function host()
  {

    if ($this->input->post()) {

      $checkNum = $this->db->get_where("users", ['phone' => $this->input->post('phone')])->row_array();

      if (!!$checkNum) {

        echo json_encode([
          "message"   =>  "Phone already exist",
          "success"   =>  "0",
        ]);
        exit;
      }

      $email = $this->db->where("email", $this->input->post("users"))->get("users")->row_array();

      if (!!$email) {

        echo json_encode([
          "message"   =>  "email already exist",
          "success"   =>  "0",
        ]);
        exit;
      }

      $data['name'] = $this->input->post('name');
      $data['phone'] = $this->input->post('phone');
      $data['country'] = $this->input->post('country');
      $data['address'] = $this->input->post('address');
      $data['email'] = $this->input->post('email');
      $data['nationalId'] = $this->input->post('nationalId');
      $data['agencyId'] = $this->input->post('agencyId');
      $data['paymentType'] = $this->input->post('paymentType');
      $data['hotlist']  = '1';
      $data['join_agencyId'] = $this->input->post('join_agencyId');
      $data['created'] = date("Y-m-d H:i:s");
      if (!empty($_FILES["image"]["name"])) {
        $name1 = time() . '_' . $_FILES["image"]["name"];
        $name = str_replace(' ', '_', $name1);
        $liciense_tmp_name = $_FILES["image"]["tmp_name"];
        $error = $_FILES["image"]["error"];
        $liciense_path = 'uploads/products/' . $name;
        move_uploaded_file($liciense_tmp_name, $liciense_path);
        $data['image'] = $liciense_path;
      }

      $upload = $this->db->insert("users", $data);

      if ($upload == True) {
        $message = [
          'success' => '1',
          'message' => 'records add successfully'
        ];
      } else {
        $message = [
          'success' => '0',
          'message' => 'Please Enter Valid Parameters'
        ];
      }
    } else {
      $message = [
        'success' => '0',
        'message' => 'Please Enter Valid Parameters'
      ];
    }
    echo json_encode($message);
  }



  public function hostApi()
  {
    $checkHost = $this->db->get_where("userLiveRequest", ['userId' => $this->input->post('userId')])->row_array();
    $checkEmail = $this->db->get_where("userLiveRequest", ['email' => $this->input->post('email')])->row_array();
    $checkPh = $this->db->get_where("userLiveRequest", ['phone' => $this->input->post('phone')])->row_array();
    if (!!$checkHost) {

      $message['success'] = '0';
      $message['message'] = 'You have already sent request, Please wait for the response';
    } elseif (!!$checkEmail) {

      $message['success'] = '0';
      $message['message'] = 'Email alreadt exist!';
    } elseif (!!$checkPh) {

      $message['success'] = '0';
      $message['message'] = 'Ph already exist!';
    } else {
      if ($this->input->post()) {
        $data['name'] = $this->input->post('name');
        $data['phone'] = $this->input->post('phone');
        $data['country'] = $this->input->post('country');
        $data['address'] = $this->input->post('address');
        $data['email'] = $this->input->post('email');
        $data['national_no'] = $this->input->post('national_no');
        $data['agencyId'] = $this->input->post('agencyId');
        $data['userId'] = $this->input->post('userId');
        $data['paymentType'] = $this->input->post('paymentType');
        $data['host_status']  = '1';
        $data['paymentMethod'] = $this->input->post('paymentMethod');
        $data['created'] = date("Y-m-d H:i:s");
        if (!empty($_FILES["nationalId"]["name"])) {
          $name1 = time() . '_' . $_FILES["nationalId"]["name"];
          $name = str_replace(' ', '_', $name1);
          $liciense_tmp_name = $_FILES["nationalId"]["tmp_name"];
          $error = $_FILES["image"]["error"];
          $liciense_path = 'uploads/products/' . $name;
          move_uploaded_file($liciense_tmp_name, $liciense_path);
          $data['nationalId'] = $liciense_path;
        }

        $upload = $this->db->insert("userLiveRequest", $data);

        $getId = $this->db->insert_id();

        if ($upload == True) {

          $getDetails = $this->db->select("userLiveRequest.host_status")
            ->from("userLiveRequest")
            ->where("userLiveRequest.id", $getId)
            ->get()
            ->row_array();

          $getStatus = $getDetails['host_status'];

          $datas['host_status'] = $getStatus;

          $update = $this->db->update("users", $datas, ['id' => $this->input->post('userId')]);


          $message = [
            'success' => '1',
            'message' => 'Host request added successfully',
            'status' => $getDetails['host_status'],
          ];
        } else {
          $message = [
            'success' => '0',
            'message' => 'Please Enter Valid Parameters'
          ];
        }
      } else {
        $message = [
          'success' => '0',
          'message' => 'Please Enter Valid Parameters'
        ];
      }
    }
    echo json_encode($message);
  }

  public function getHostRequest()
  {


    $get = $this->db->select("userLiveRequest.host_status")
      ->from("userLiveRequest")
      ->where("userLiveRequest.userId", $this->input->post("userId"))
      ->get()
      ->row_array();

    if (!!$get) :
      $message['success'] = '1';
      $message['message'] = 'records found successfully';
      $message['status'] = $get['host_status'];
    else :
      $message['success'] = '0';
      $message['message'] = 'records not found';

    endif;

    echo json_encode($message);
  }

  public function getAllUserUploadedVideos()
  {


    $get = $this->db->select("userVideos.*,users.*")
      ->from("userVideos")
      ->join("users", "users.id = userVideos.userId")
      ->get()
      ->result_array();

    if (!!$get) :
      $message['success'] = '1';
      $message['message'] = 'records found successfully';
      $message['details'] = $get;
    else :
      $message['success'] = '0';
      $message['message'] = 'records not found';

    endif;

    echo json_encode($message);
  }
  public function getLiveMultiLive()
  {
    $get = $this->db->get_where("userLive", ['hostType' => '3'])->result_array();

    foreach ($get as $row) {
      $id = $row['userId'];
      $getUser = $this->db->get_where("users", array('id' => $id))->row_array();

      if (!empty($getUser['image'])) {
        $row['image'] = base_url() . $getUser['image'];
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

  public function sendGift()
  {
    $data['senderId'] = $this->input->post('senderId');
    $data['receiverId'] = $this->input->post('receiverId');
    $data['diamond'] = $this->input->post('diamond');
    $data['giftId'] = $this->input->post('giftId');
    $data['liveId'] = $this->input->post('liveId');
    $data['created'] = date('Y-m-d H:i:s');
    $insert = $this->db->insert("received_gift_coin", $data);

    if ($insert) {

      $diamond = $this->input->post('diamond');

      $getUserCoin = $this->db->get_where("users", array('id' => $this->input->post('senderId')))->row_array();

      if ($getUserCoin['purchasedCoin'] >= $diamond) {
        $userCoin = $getUserCoin['purchasedCoin'];
        $u_coin['purchasedCoin'] = $userCoin - $diamond;
        $update_userCoin = $this->db->update("users", $u_coin, array('id' => $this->input->post('senderId')));

        $gethostCoin = $this->db->get_where('users', array('id' => $this->input->post('receiverId')))->row_array();
        $hostCoin = $gethostCoin['coin'];
        $h_coin['coin'] = $hostCoin + $diamond;
        $update_host_coin = $this->db->update('users', $h_coin, array('id' => $this->input->post('receiverId')));

        $message['success'] = '1';
        $message['message'] = 'gift send successfully';
      } else {
        $message['success'] = '0';
        $message['message'] = 'insufficient wallet balance';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'gift not send';
    }
    echo json_encode($message);
  }

  public function endLiveHost()
  {
    $userId = $this->db->get_where('h_liveMultiLiveToken', ['userId' => $this->input->post("userId"), 'currentEndlive' => $this->input->post("currentEndlive")])->row_array();
    if (!empty($userId)) {
      $Id = $this->input->post('userId');
      $data['status'] = '2';
      $currentEndlive = $this->input->post('currentEndlive');
      $data['updated_at'] = date('Y-m-d H:i:s');
      $ins = $this->db->update('h_liveMultiLiveToken', $data, ['userId' => $Id, 'currentEndlive' => $currentEndlive]);
      if ($ins) {

        $getDetails = $this->db->select("h_liveMultiLiveToken.*,received_gift_coin.senderId,received_gift_coin.receiverId,SUM(diamond) AS coin,received_gift_coin.giftId,TIMEDIFF(TIME(updated_at),TIME(created_at))/60 as duration")
          ->from("h_liveMultiLiveToken")
          ->join("received_gift_coin", "received_gift_coin.receiverId = h_liveMultiLiveToken.userId", "left join")
          ->where("h_liveMultiLiveToken.currentEndlive", $currentEndlive)
          ->get()
          ->row_array();

        $message['success'] = '1';
        $message['message'] = 'Host endLive';
        $message['details'] = $getDetails;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'something went wrong!';
    }

    echo json_encode($message);
  }



  public function getEndLiveHostHistory()
  {

    $userId = $this->db->get_where('h_liveMultiLiveToken', array('userId' => $this->input->post('userId')))->row_array();

    if (!!$userId) {

      $getHostEndLive = $this->db->get_where('h_liveMultiLiveToken', ['userId' => $this->input->post('userId'), 'status' => '2'])->result_array();

      if (!!$getHostEndLive) {
        $message['success'] = '1';
        $message['message'] = 'Host endLive';
        $message['details'] = $getHostEndLive;
      } else {
        $message['success'] = '0';
        $message['message'] = 'something went wrong!';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'host not found!';
    }
    echo json_encode($message);
  }



  public function getPrimeGift()
  {
    $get = $this->db->get_where("gift", array('status' => 'Approved'))->result_array();
    foreach ($get as $row) {
      $row['image'] = base_url() . $row['image'];
      $finalData[] = $row;
    }
    if (!empty($finalData)) {

      $message['success'] = "1";
      $message['message'] = "List found successfully";
      $message['details'] = $finalData;
    } else {
      $message['success'] = '0';
      $message['message'] = 'List not found';
    }
    echo json_encode($message);
  }

  // ================ getPkuserLive =============

  function getPkLiveList()
  {

    $get = $this->db->select("userLive.*,users.name,users.image")
      ->from("userLive")
      ->join("users", "users.id = userLive.userId", "left")
      ->where("userLive.hostType", '2')
      ->where("userLive.status", 'live')
      ->where("userLive.userId != ", $this->input->post("userId"))
      // ->group_start()
      // ->or_where("userLive.userId", $this->input->post("userId"))
      // ->group_end()
      ->get()
      ->result_array();

    if (!!$get) {

      foreach ($get as $gets) {

        $gets['image'] = base_url() . $gets['image'];

        $getImage[] = $gets;
      }
      echo json_encode([
        "success" =>  "1",
        "message" =>  "Record found successfully",
        "dimaond" =>  $getImage,
      ]);
      exit;
    }

    echo json_encode([
      "success" =>  "0",
      "message" =>  "No record found",
    ]);
    exit;
  }

  public function endlive()
  {
    $get = $this->db->where("id", $this->input->post("liveId"))->get("live_histrory")->row_array();

    if (!$get) {
      echo json_encode([
        "success" =>  "0",
        "message" =>  "No record found",
      ]);
      exit;
    }

    if ($get["live_status"] == 0) {
      echo json_encode([
        "success" =>  "0",
        "message" =>  "Live already ended",
      ]);
      exit;
    }
    if ($this->db->set([
      "live_status" =>  "0",
      "endLive" =>  $this->input->post("endLive"),
      "updated" =>  date("Y-m-d H:i:s"),
    ])
      ->where("id", $this->input->post("liveId"))->update("live_histrory")
    ) {
      echo json_encode([
        "success" =>  "1",
        "message" =>  "Live successfully ended",
      ]);
      exit;
    }

    echo json_encode([
      "success" =>  "0",
      "message" =>  "Failed to end live",
    ]);
    exit;
  }



  public function liveMultiLiveToken()
  {
    require APPPATH . '/libraries/agora/RtcTokenBuilder.php';
    if ($this->input->post()) {
      $getData = $this->db->from('userLive')
        ->where('userId', $this->input->post('userId'))
        ->get()
        ->row_array();

      if (empty($getData)) {
        $appID = "63b4e226157447a28fc4e25bfc04f526";
        $appCertificate = "9b2b7c52926f4eabbd4705ce4bfcd1c4";
        $channelName = $this->input->post('channelName');
        $uid = '';
        $uidStr = '';
        $role = RtcTokenBuilder::RoleAttendee;
        $expireTimeInSeconds = 10800;
        $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
        $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);

        $datas = [
          'token' => $token,
          'hostType' => $this->input->post('hostType'),
          'userId' => $this->input->post('userId'),
          'status' => $this->input->post('status'),
          'channelName' => $this->input->post('channelName'),
          'updated' => date('Y-m-d H:i:s')
        ];
        // $userDetails = $this->db->select('image , name')
        //                          ->from('users')
        //                          ->where('id', $this->input->post('userId'))
        //                          ->get()->row_array();

        // $userId = $this->input->post('userId');
        // $followersCount = $this->db->query("SELECT COUNT(followedTo) AS COUNT FROM followers WHERE followedTo = $userId")->row_array();
        // if (!empty($userDetails['image'])) {
        //   $data['image'] = base_url() . $userDetails['image'];
        // } else {
        //   $data['image'] = '';
        // }
        // $data['name'] = $userDetails['name'];
        // if (empty($followersCount['count'])) {
        //   $data['count'] = '0';
        // } else {
        //   $data['count'] = $followersCount['count'];
        // }
        $insert = $this->db->insert('userLive', $datas);
        $getId = $this->db->insert_id();
        if ($insert) {

          $getUserDetails = $this->db->get_where("userLive", ['id' => $getId])->row_array();

          $userDetails = $this->db->select('image , name')
            ->from('users')
            ->where('id', $this->input->post('userId'))
            ->get()->row_array();

          if (!empty($userDetails['image'])) {
            $getUserDetails['image'] = $userDetails['image'];
          } else {
            $getUserDetails['image'] = '';
          }
          $getUserDetails['name'] = $userDetails['name'];

          echo json_encode([
            'success' => '1',
            'message' => 'Token Generated',
            'token' => $getUserDetails
          ]);
          exit;
        } else {
          echo json_encode([
            'success' => '0',
            'message' => 'Please Try After Some Time'
          ]);
          exit;
        }
      } else {
        $appID = "63b4e226157447a28fc4e25bfc04f526";
        $appCertificate = "9b2b7c52926f4eabbd4705ce4bfcd1c4";
        $channelName = $this->input->post('channelName');
        $uid = '';
        $uidStr = '';
        $role = RtcTokenBuilder::RoleAttendee;
        $expireTimeInSeconds = 10800;
        $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
        $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
        $data = [
          'token' => $token,
          'userId' => $this->input->post('userId'),
          'hostType' => $this->input->post('hostType'),
          'status' => $this->input->post('status'),
          'channelName' => $this->input->post('channelName'),
          'updated' => date('Y-m-d H:i:s')
        ];
        $datas = [
          'token' => $token,
          'hostType' => $this->input->post('hostType'),
          'userId' => $this->input->post('userId'),
          'status' => $this->input->post('hostType'),
          'channelName' => $this->input->post('channelName'),
          'updated' => date('Y-m-d H:i:s')
        ];
        $userDetails = $this->db->select('image , name')
          ->from('users')
          ->where('id', $this->input->post('userId'))
          ->get()->row_array();
        // $userId = $this->input->post('userId');
        // $followersCount = $this->db->query("SELECT COUNT(followedTo) AS COUNT FROM followers WHERE followedTo = $userId")->row_array();
        if (!empty($userDetails['image'])) {
          $data['image'] = base_url() . $userDetails['image'];
        } else {
          $data['image'] = '';
        }
        $data['name'] = $userDetails['name'];
        /*if (empty($followersCount['count'])) {
          $data['count'] = '0';
        } else {
          $data['count'] = $followersCount['count'];
        }*/
        $update = $this->db->where('userId', $this->input->post('userId'))
          ->update('userLive', $datas);

        if ($update) {

          $get = $this->db->get_where("userLive", ['userId' => $this->input->post('userId')])->row_array();

          $getId = $get['id'];

          $data[id] = $getId;
          echo json_encode([
            'success' => '1',
            'message' => 'Token Generated',
            'token' => $data
          ]);
          exit;
        } else {
          echo json_encode([
            'success' => '0',
            'message' => 'Please Try After Some Time'
          ]);
          exit;
        }
      }
    } else {
      echo json_encode([
        'success' => '0',
        'message' => 'Please Enter Valid Parameters'
      ]);
      exit;
    }
  }

  // public function searchUsers()
  // {
  //
  //   try {
  //
  //     $records = $this->db->from("users");
  //
  //     if (!!$this->input->post("search"))
  //       $records = $records->like("name", $this->input->post("search"));
  //
  //
  //     $records = $records->get()->result_array();
  //
  //
  //
  //     if (!!$records) {
  //       foreach($records as $key =>  $list){
  //         $checkFollowing = $this->db->get_where('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' => $list['id']])->row_array();
  //         if(!!$checkFollowing['status'] == 1){
  //           $list['following'] = TRUE;
  //         }else{
  //           $list['following'] = FALSE;
  //         }
  //
  //         $check[$key] = $list;
  //       }
  //       echo json_encode([
  //         "success"  =>  "1",
  //         "message"  =>  "Record found successfully",
  //         "details"  =>  $check,
  //       ]);
  //       exit;
  //     }else{
  //       $getAll = $this->db->get('users')->result_array();
  //       foreach($getAll as $key =>  $list){
  //         $checkFollowing = $this->db->get_where('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' => $list['id']])->row_array();
  //         if(!!$checkFollowing['status'] == 1){
  //           $list['following'] = TRUE;
  //         }else{
  //           $list['following'] = FALSE;
  //         }
  //
  //         $check[$key] = $list;
  //       }
  //       echo json_encode([
  //         "success"  =>  "1",
  //         "message"  =>  "all record",
  //         "details"  =>  $check,
  //       ]);
  //       exit;
  //     }
  //
  //     echo json_encode([
  //       "success"  =>  "0",
  //       "message"  =>  "Record not found",
  //     ]);
  //     exit;
  //   } catch (Exception $ex) {
  //     echo json_encode([
  //       "success"  =>  "0",
  //       "message"  =>  $ex->getMessage(),
  //     ]);
  //     exit;
  //   }
  // }


  public function searchUsers()
  {

    try {

      $records = $this->db->from("users");

      if (!!$this->input->post("search"))

        $records = $this->db->query("SELECT * FROM users where name LIKE '" . $this->input->post('search') . "%' OR id LIKE '" . $this->input->post('search') . "%'")->result_array();

      if (!!$records) {


        foreach ($records as $key => $LIST) {
          // print_r($LIST['id']);

          if ($LIST['id'] == $this->input->post('userId')) {
          } else {

            $getId = $LIST['id'];

            $getStatus = $this->db->get_where("userFollow", ['userId' => $this->input->post('userId'), 'followingUserId' => $getId])->row_array();

            if (!!$getStatus) {

              $LIST['following'] = 1;
            } else {

              $LIST['following'] = 2;
            }

            if ($LIST['following'] == 2) {

              $checkRequest = $this->db->get_where('userFollowRequest', ['userId' => $getId, 'AcceptUserId' => $this->input->post('userId')])->row_array();
              // print_r($checkRequest);exit;

              if (empty($checkRequest)) {
                $LIST['following'] = 2;
              } else {
                if ($checkRequest['type'] == '0') {
                  $LIST['following'] = 3;
                } else if ($checkRequest['type'] == '2') {
                  $LIST['following'] = 4;
                }
              }
            }

            $LIST['frameImage'] = "";

            $created = $LIST['created'];
            $dateCreated = date_create($created);
            $addedDate = date_add($dateCreated, date_interval_create_from_date_string("7 days"));
            $addedDateArray = ((array) $addedDate);
            $finalDate = date_create($addedDateArray['date']);
            $dateSevenDay = date_format($finalDate, "Y-m-d");

            // print_r($LIST);exit;
            if ($LIST['setFrameId'] == '1') {

              if (date('Y-m-d') > $dateSevenDay) {
                $this->db->set('setFrameId', '0')->where('id', $LIST['id'])->update('users');
              } else {
                $LIST['frameImage'] = base_url() . 'assets/gifts/epgifts/frame1.svga';
              }
            } else if ($LIST['setFrameId'] == '0') {
            } else {

              $getFrame = $this->db->get_where('Ep_admin_add_frames', ['id' => $LIST['setFrameId']])->row_array();

              $LIST['frameImage'] = base_url() . $getFrame['frame_img'];
            }


            $check[] = $LIST;
            // $check['frame'] = base_url().'assets/gifts/epgifts/frame1.svga';
            // print_r($check['frame']);exit;

          }
        }
        echo json_encode([
          "success"  =>  "1",
          "message"  =>  "Record found successfully",
          "details"  =>  $check,
        ]);
        exit;
      }

      echo json_encode([
        "success"  =>  "0",
        "message"  =>  "Result not found",
      ]);
      exit;
    } catch (Exception $ex) {
      echo json_encode([
        "success"  =>  "0",
        "message"  =>  $ex->getMessage(),
      ]);
      exit;
    }
  }

  public function test()
  {
    $records = $this->db->from("users");

    if (!!$this->input->post("search")) {
      $records = $records->like("name", $this->input->post("search"))->get()->row_array();
      if (!!$records) {
        $checkId = $records['id'];
        $checkFollowing = $this->db->get_where('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' =>  $checkId])->row_array();
        if (!!$checkFollowing) {
          echo json_encode([
            "success"  =>  "1",
            "message"  =>  "record",
            "details"  =>  $checkFollowing,
          ]);
        }
      }
    }
  }
  public function getFollowingVideos()
  {


    $getVideos = $this->db->select("userFollow.*,userVideos.*")
      ->from("userFollow")
      ->join("userVideos", "userVideos.userId = userFollow.followingUserId")
      ->where("userFollow.userId", $this->input->post("userId"))
      ->get()
      ->result_array();

    if (!!$getVideos) {

      $message['success'] = '1';
      $message['message'] = 'details found';
      $message['details'] = $getVideos;
    } else {
      $message['success'] = '0';
      $message['message'] = 'details not found!';
    }

    echo json_encode($message);
  }

  public function getArchivedLiveUser()
  {

    $getUser = $this->db->get_where("userLive", ['userId' => $this->input->post("userId"), 'status' => 'archived'])->result_array();

    if (!!$getUser) {

      echo json_encode([
        "message" => "details found",
        "success" => "1",
        "details" => $getUser,
      ]);
      exit;
    } else {
      echo json_encode([
        "message" => "details not found",
        "success" => "0",
      ]);
      exit;
    }
  }


  public function applyAgency()
  {

    if ($this->input->post()) {

      $data['userId'] = $this->input->post("userId");
      $data['username'] = $this->input->post('username');
      $data['email'] = $this->input->post('email');
      $data['special_approval_name'] = $this->input->post("special_approval_name");
      $data['phone'] = $this->input->post('phone');
      $data['webiteLink'] = 'http://18.188.32.245/app/NC_Project/index.php/AgencyPanel';
      $data['deposit_amount'] = $this->input->post("deposit_amount");
      $data['bank_name'] = $this->input->post("bank_name");
      $data['account_num'] = $this->input->post("account_num");
      $data['IFCS_code'] = $this->input->post('IFCS_code');
      $data['password'] = md5($this->input->post("password"));
      $data['viewPassword'] = $this->input->post("password");
      $data['agencyCode'] = '12' . rand(100, 999);
      $data['payment_method'] = $this->input->post("payment_method");
      if (!empty($_FILES['aadharCardFront']['name'])) {
        $name1 = time() . '_' . $_FILES["aadharCardFront"]["name"];
        $name = str_replace(' ', '_', $name1);
        $liciense_tmp_name = $_FILES["aadharCardFront"]["tmp_name"];
        $error = $_FILES["aadharCardFront"]["error"];
        $liciense_path = 'uploads/products/' . $name;
        move_uploaded_file($liciense_tmp_name, $liciense_path);
        $data['aadharCardFront'] = $liciense_path;
      }
      if (!empty($_FILES['panCardFrontPhoto']['name'])) {
        $name1 = time() . '_' . $_FILES["panCardFrontPhoto"]["name"];
        $name = str_replace(' ', '_', $name1);
        $liciense_tmp_name = $_FILES["panCardFrontPhoto"]["tmp_name"];
        $error = $_FILES["panCardFrontPhoto"]["error"];
        $liciense_path = 'uploads/products/' . $name;
        move_uploaded_file($liciense_tmp_name, $liciense_path);
        $data['panCardFrontPhoto'] = $liciense_path;
      }
      if (!empty($_FILES['aadharCardBack']['name'])) {
        $name1 = time() . '_' . $_FILES["aadharCardBack"]["name"];
        $name = str_replace(' ', '_', $name1);
        $liciense_tmp_name = $_FILES["aadharCardBack"]["tmp_name"];
        $error = $_FILES["aadharCardBack"]["error"];
        $liciense_path = 'uploads/products/' . $name;
        move_uploaded_file($liciense_tmp_name, $liciense_path);
        $data['aadharCardBack'] = $liciense_path;
      }
      if (!empty($_FILES['govt_photoId_proof']['name'])) {
        $name1 = time() . '_' . $_FILES["govt_photoId_proof"]["name"];
        $name = str_replace(' ', '_', $name1);
        $liciense_tmp_name = $_FILES["govt_photoId_proof"]["tmp_name"];
        $error = $_FILES["govt_photoId_proof"]["error"];
        $liciense_path = 'uploads/products/' . $name;
        move_uploaded_file($liciense_tmp_name, $liciense_path);
        $data['govt_photoId_proof'] = $liciense_path;
      }
      if (!empty($_FILES['image']['name'])) {
        $name1 = time() . '_' . $_FILES["image"]["name"];
        $name = str_replace(' ', '_', $name1);
        $liciense_tmp_name = $_FILES["image"]["tmp_name"];
        $error = $_FILES["image"]["error"];
        $liciense_path = 'uploads/products/' . $name;
        move_uploaded_file($liciense_tmp_name, $liciense_path);
        $data['image'] = $liciense_path;
      }

      $upload = $this->db->insert("agencyDetails", $data);

      $getId = $this->db->insert_id();

      if ($upload == true) {

        $getdetails = $this->db->get_where("agencyDetails", ['id' => $getId])->row_array();

        $message['success'] = '1';
        $message['message'] = 'agency added successfully';
        $message['status'] = $getdetails['status'];
        $message['details'] = $getdetails;
      } else {
        $message['success'] = '0';
        $message['message'] = 'something went wrong';
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'Please enter valid params!';
    }
    echo json_encode($message);
  }

  public function getUserLevels()
  {

    $getLevels = $this->db->get("user_levels")->result_array();

    if (!!$getLevels) {

      foreach ($getLevels as $gets) {

        $gets['image'] = base_url() . $gets['image'];

        $final[] = $gets;
      }

      $message['success'] = '1';
      $message['message'] = 'details found';
      $message['details'] = $final;
    } else {
      $message['success'] = '0';
      $message['message'] = 'details not found!';
    }

    echo json_encode($message);
  }

  // public function getBanner()
  // {
  //
  //   $data = $this->db->order_by('id', 'desc')->select('nursingBanner.id , nursingBanner.banner')->get('nursingBanner')->result_array();
  //   if ($data) {
  //     foreach ($data as $details) {
  //       if (!empty($details['banner'])) {
  //         $details['banner'] = base_url().$details['banner'];
  //       }
  //       $final[] = $details;
  //     }
  //     $message['success'] = '1';
  //     $message['message'] = 'List found successully';
  //     $message['details'] = $final;
  //   } else {
  //     $message['success'] = '0';
  //     $message['message'] = 'List not found';
  //   }
  //   echo json_encode($message);
  // }

  public function getBanner()
  {

    $data = $this->db->get("nursingBanner")->result_array();

    if (!!$data) {
      foreach ($data as $details) {
        if (!empty($details['banner'])) {
          $details['banner'] = $details['banner'];
        }
        $final[] = $details;
      }

      $message['success'] = '1';
      $message['message'] = 'List found successully';
      $message['details'] = $final;
    } else {
      $message['success'] = '0';
      $message['message'] = 'List not found';
    }

    echo json_encode($message);
  }

  public function getAgencyStatus()
  {

    $username = $this->input->post("username");

    $getDetails = $this->db->select("agencyDetails.*")
      ->from("agencyDetails")
      ->where("agencyDetails.username", $username)
      ->get()
      ->row_array();

    $checkStatus = $getDetails['status'];

    if ($checkStatus == '1') {
      $message['success'] = '1';
      $message['message'] = 'List found successully';
      $message['status'] = '1';
      $message['panelLink'] = $getDetails['webiteLink'];
      $message['agencyCode'] = $getDetails['agencyCode'];
      $message['password'] = $getDetails['viewPassword'];
    } elseif ($checkStatus == '0') {
      $message['success'] = '1';
      $message['message'] = 'List not found!';
      $message['status'] = '0';
    } else {
      $message['success'] = '0';
      $message['message'] = 'List not found';
    }

    echo json_encode($message);
  }

  public function userPoster()
  {

    if ($this->input->post()) {


      $data['userId'] = $this->input->post("userId");
      $data['status'] = '0';

      if (!empty($_FILES['image']['name'])) {
        $name1 = time() . '_' . $_FILES["image"]["name"];
        $name = str_replace(' ', '_', $name1);
        $liciense_tmp_name = $_FILES["image"]["tmp_name"];
        $error = $_FILES["image"]["error"];
        $liciense_path = 'uploads/products/' . $name;
        move_uploaded_file($liciense_tmp_name, $liciense_path);
        $data['image'] = $liciense_path;
      }

      $upload = $this->db->insert("userPoster", $data);

      $Id = $this->db->insert_id();

      if ($upload == true) {

        $getDetails = $this->db->get_where("userPoster", ['id' => $Id])->row_array();

        if (!!$getDetails) {
          $getDetails['image'] = base_url() . $getDetails['image'];
        }
        $message['success'] = '1';
        $message['message'] = 'posters Added';
        $message['details'] = $getDetails;
      }
    } else {
      $message['success'] = '0';
      $message['message'] = 'please enter valid parameters!';
    }
    echo json_encode($message);
  }

  public function approvePosters()
  {

    $getPosters = $this->db->get_where("userPoster", ['userId' => $this->input->post('userId'), 'status' => '1'])->result_array();

    if (!!$getPosters) {

      foreach ($getPosters as $get) {

        $get['image'] = base_url() . $get['image'];

        $final[] = $get;
      }

      $message['success'] = '1';
      $message['message'] = 'posters found';
      $message['details'] = $get;
    } else {
      $message['success'] = '0';
      $message['message'] = 'posters not found!';
    }

    echo json_encode($message);
  }


  // ============= EpLive Apis =============


  public function getFrames()
  {

    if (!$this->input->post('userId')) {
      echo json_encode([
        'status' => 0,
        'message' => 'userId required'
      ]);
      exit;
    }



    $get = $this->db->get("Ep_admin_add_frames")->result_array();

    if (!!$get) {

      foreach ($get as $gets) {

        $checkPurchased = $this->db->get_where('userPurchaseFram', ['userId' => $this->input->post('userId'), 'framId' => $gets['id']])->row_array();



        if (empty($checkPurchased)) {

          $gets['purachaseStatus'] = false;
        } else {


          $gets['purachaseStatus'] = true;


          if (date('Y-m-d') > $checkPurchased['expired']) {

            $gets['purachaseStatus'] = false;
          }
        }

        if ($gets['id'] == 1) {

          $gets['purachaseStatus'] = true;
        }

        $final[] = $gets;
      }
      // exit;
      $message['status'] = 1;
      $message['message'] = 'Frames found';
      $message['details'] = $final;
    } else {
      $message['status'] = 0;
      $message['message'] = 'Frames not found!';
    }

    echo json_encode($message);
  }

  public function getGarage()
  {

    $get = $this->db->get("garageData")->result_array();

    if (!!$get) {

      $final = [];

      foreach ($get as $gets) {
        // print_r($gets);

        $getPurchasedGarage = $this->db->get_where('userPurchaseGarage', ['garageId' => $gets['id'], 'userId' => $this->input->post('userId')])->row_array();

        $gets['purchased'] = false;

        if (!empty($getPurchasedGarage)) {
          if ($getPurchasedGarage['validTo'] >= date('Y-m-d')) {
            $gets['purchased'] = true;
          }
        }

        $final[] = $gets;
      }

      $message['success'] = '1';
      $message['message'] = 'garage data found';
      $message['details'] = $final;
    } else {
      $message['success'] = '0';
      $message['message'] = 'garage data not found!';
    }

    echo json_encode($message);
  }

  public function userPurchaseGarage()
  {

    if ($this->input->post()) {

      $chckUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if (empty($chckUser)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);
        exit;
      }

      $chckGarage = $this->db->get_where('garageData', ['id' => $this->input->post('garageId')])->row_array();

      if (empty($chckGarage)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid garageId',
        ]);
        exit;
      }

      $chckPurchasedGarage = $this->db->get_where('userPurchaseGarage', ['garageId' => $this->input->post('garageId'), 'userId' => $this->input->post('userId')])->row_array();

      if (!empty($chckPurchasedGarage)) {
        if ($chckPurchasedGarage['validTo'] >= date('Y-m-d')) {
          echo json_encode([
            'status' => 0,
            'message' => 'garage alredy purchased'
          ]);
          exit;
        }
      }

      $garagePrice = $chckGarage['price'];
      $userBalance = $chckUser['purchasedCoin'];

      if ($userBalance < $garagePrice) {
        echo json_encode([
          'status' => 0,
          'message' => 'insufficient balance'
        ]);
        exit;
      }


      $gdata['userId'] = $this->input->post('userId');
      $gdata['garageId'] = $this->input->post('garageId');
      $gdata['validFrom'] = date('Y-m-d');
      $gdata['validTo'] = date('Y-m-d', strtotime("+ " . $chckGarage['valid'] . " day"));

      $u['purchasedCoin'] = $userBalance - $garagePrice;
      //       print_r($u);
      // exit;
      if ($this->db->insert('userPurchaseGarage', $gdata) && $this->db->set($u)->where('id', $this->input->post('userId'))->update('users')) {
        echo json_encode([
          'status' => 1,
          'message' => 'garage purchased',
        ]);
        exit;
      } else {
        echo json_encode([
          'status' => 0,
          'message' => 'tech error'
        ]);
        exit;
      }
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'Please enter valid parameters'
      ]);
      exit;
    }
  }

  public function getUserGarage()
  {
    if ($this->input->post()) {

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if (empty($checkUser)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);
        exit;
      }

      $getGarage = $this->db->get_where('userPurchaseGarage', ['userId' => $this->input->post('userId')])->result_array();

      if (empty($getGarage)) {
        echo json_encode([
          'status' => 0,
          'message' => 'no data found'
        ]);
        exit;
      }

      $final = [];
      foreach ($getGarage as $garage) {
        if ($garage['validTo'] < date("Y-m-d")) {

          $this->db->delete('userPurchaseGarage', ['id' => $garage['id']]);
        } else {

          $details = $this->db->get_where('garageData', ['id' => $garage['garageId']])->row_array();

          $final[] = $details;
        }
      }

      echo json_encode([
        'status' => 1,
        'message' => 'list found',
        'details' => $final
      ]);
      exit;
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid parameters'
      ]);
      exit;
    }
  }

  public function purchaseFrames()
  {
    try {

      $getUser = $this->db->get_where("users", ["id" => $this->input->post("userId")])->row_array();

      $getGift = $this->db->get_where("Ep_admin_add_frames", ["id" => $this->input->post("frameId")])->row_array();
      $checkPurchased = $this->db->get_where("yy_user_room_tools", ['userId' => $this->input->post("userId"), 'frameId' => $this->input->post("frameId")])->row_array();

      if (!!$checkPurchased) {
        echo json_encode([
          "success"  =>  "0",
          "message"  =>  "Frame already purchased",
        ]);
        exit;
      }

      if (!$getUser) {
        echo json_encode([
          "success"  =>  "0",
          "message"  =>  "User not found",
        ]);
        exit;
      }

      if (!$getGift) {
        echo json_encode([
          "success"  =>  "0",
          "message"  =>  "Frame not found",
        ]);
        exit;
      }

      if ($getUser["purchasedCoin"] >= $getGift["price"]) {
        $this->db->trans_start();
        // $data['type'] = '1';
        // $this->db->update("Ep_admin_add_frames",$data,['id' => $this->input->post("frameId")]);
        $this->db->insert("yy_user_room_tools", [
          "amount" =>  $getGift["price"],
          "userId" =>  $getUser["id"],
          "frameId" =>  $getGift["id"],
          "type" =>  $getGift["type"],
          "expiryDate"  =>  date("Y-m-d", strtotime(date("Y-m-d") . " + " . $getGift["viewDays"] . " day")),
          "purchaseDate"  =>  date("Y-m-d"),
          "created"  =>  date("Y-m-d H:i:s"),
          "updated"  =>  date("Y-m-d H:i:s")
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
          echo json_encode([
            "success"  =>  "0",
            "message"  =>  "Failed to purchase gift",
          ]);
          exit;
        }

        echo json_encode([
          "success"  =>  "1",
          "message"  =>  "Frame purchased successfully",
        ]);
        exit;
      }

      echo json_encode([
        "success"  =>  "0",
        "message"  =>  "Low balance",
      ]);
      exit;
    } catch (Exception $e) {
      echo json_encode([
        "success"  =>  "0",
        "message"  =>  $e->getMessage(),
      ]);
      exit;
    }
  }


  public function getPurchaseFrames()
  {


    $gif = $this->db->select("yy_user_room_tools.id as purchaseFrameId,yy_user_room_tools.userId,yy_user_room_tools.frameId,yy_user_room_tools.activated,yy_user_room_tools.created,users.username,users.name,Ep_admin_add_frames.frame_img")
      ->from("yy_user_room_tools")
      ->join("users", "users.id = yy_user_room_tools.userId", "left")
      ->join("Ep_admin_add_frames", "yy_user_room_tools.frameId = Ep_admin_add_frames.id", "left")
      ->where('yy_user_room_tools.userId', $this->input->post("userId"))
      ->get()
      ->result_array();

    if (!!$gif) {

      foreach ($gif as $gifs) {
        $gifs['frame_img'] = base_url() . $gifs['frame_img'];
        $final[] = $gifs;
      }
      $message['success'] = '1';
      $message['message'] = 'frame found';
      $message['details'] = $final;
    } else {
      $message['success'] = '0';
      $message['message'] = 'frame not found!';
    }
    echo json_encode($message);
  }

  function activateFrame()
  {

    $check = $this->db->get_where('yy_user_room_tools', ['userId' => $this->input->post("userId"), 'frameId' => $this->input->post("frameId")])->row_array();

    if (!!$check) {

      $checkActive = $this->db->get_where('yy_user_room_tools', ['activated' => '1'])->row_array();

      $get = $checkActive['activated'];

      $data['activated'] = '0';

      $delete = $this->db->update('yy_user_room_tools', $data, ['activated' => $get]);

      $data['activated'] = '1';
      $data['updated'] = date('Y-m-d H:i:s');

      $active = $this->db->update('yy_user_room_tools', $data, ['userId' => $this->input->post("userId"), 'frameId' => $this->input->post("frameId")]);

      if ($active == true) {
        echo json_encode([
          "success"  =>  "1",
          "message"  =>  "frame activated",
        ]);
        exit;
      } else {
        echo json_encode([
          "success"  =>  "0",
          "message"  =>  "error occured!",
        ]);
        exit;
      }
    } else {
      echo json_encode([
        "success"  =>  "0",
        "message"  =>  "something went wrong!",
      ]);
      exit;
    }
  }

  public function getActivatedFrames()
  {

    $get = $this->db->select("yy_user_room_tools.*,Ep_admin_add_frames.frame_img")
      ->from("yy_user_room_tools")
      ->join("Ep_admin_add_frames", "yy_user_room_tools.frameId = Ep_admin_add_frames.id", "left")
      ->where("yy_user_room_tools.userId", $this->input->post("userId"))
      ->where("yy_user_room_tools.activated", '1')
      ->get()
      ->result_array();

    if (!!$get) {

      foreach ($get as $gifs) {
        $gifs['frame_img'] = base_url() . $gifs['frame_img'];
        $final[] = $gifs;
      }
      echo json_encode([
        "success"  =>  "1",
        "message"  =>  "activated frame found",
        "details" => $final,
      ]);
      exit;
    } else {
      echo json_encode([
        "success"  =>  "0",
        "message"  =>  "frame not found",
      ]);
      exit;
    }
  }

  public function getLuckyId()
  {

    $get = $this->db->get("Ep_luckyId")->result_array();

    if (!!$get) {

      foreach ($get as $gets) {

        $gets['image'] = base_url() . $gets['image'];

        $final[] = $gets;
      }
      $message['success'] = '1';
      $message['message'] = 'LuckyId found';
      $message['details'] = $final;
    } else {
      $message['success'] = '0';
      $message['message'] = 'LuckyId not found!';
    }

    echo json_encode($message);
  }

  public function getUserBeans()
  {

    $get = $this->db->select("users.id,users.beans")
      ->from("users")
      ->where("users.id", $this->input->post("userId"))
      ->get()
      ->row_array();

    if (!!$get) {
      $message['success'] = '1';
      $message['message'] = 'details found';
      $message['details'] = $get;
    } else {
      $message['success'] = '0';
      $message['message'] = 'details not found!';
    }

    echo json_encode($message);
  }

  public function overAllGiftAmount()
  {

    $overAll = $this->db->query("SELECT giftUserId FROM userGiftHistory GROUP BY giftUserId")->result_array();

    if (!empty($overAll)) {

      foreach ($overAll as $details) {

        $id = $details['giftUserId'];

        $get = $this->db->query("SELECT SUM(coins) as Total_amount,users.id,users.username,users.name,users.image FROM userGiftHistory
				   left join users on users.id = giftUserId where giftUserId = $id ORDER BY Total_amount ASC")->row_array();

        $final[] = $get;
      }

      $message = [
        'success' => 'true',
        'message' => 'details found successfully',
        // 'amount' => ($daily["Total_amount"] == 0) ? "Nill" : $daily["Total_amount"],
        'amount' => $final,
      ];
    } else {
      $message = [
        'success' => 'false',
        'message' => 'Nill'
      ];
    }

    echo json_encode($message);
  }

  public function montlyGiftAmount()
  {
    if ($this->input->post()) {

      $type = $this->input->post('type');

      if ($type == 'monthy') {
        $date = date("Y-m-d");

        $monthy = $this->db->query("SELECT giftUserId FROM userGiftHistory where created_at >= DATE(NOW()) - INTERVAL 1 MONTH GROUP BY giftUserId")->result_array();

        // print_r($monthy);
        // die;

        if (!empty($monthy)) {

          foreach ($monthy as $details) {

            $id = $details['giftUserId'];

            $get = $this->db->query("SELECT SUM(coins) as Total_amount,users.id,users.username,users.name,users.image FROM userGiftHistory
				   join users on users.id = userGiftHistory.giftUserId where userGiftHistory.giftUserId = $id AND created_at >= DATE(NOW()) - INTERVAL 1 MONTH ORDER BY Total_amount ASC")->row_array();

            $final[] = $get;
          }

          $message = [
            'success' => 'true',
            'message' => 'details found successfully',
            // 'amount' => ($daily["Total_amount"] == 0) ? "Nill" : $daily["Total_amount"],
            'amount' => $final,
          ];
        } else {
          $message = [
            'success' => 'false',
            'message' => 'Nill'
          ];
        }
      } else {
        $message = array(
          'success' => 'false',
          'message' => 'Please enter type parameter!'
        );
      }
    } else {
      $message = array(
        'success' => 'false',
        'message' => 'Please enter type parameter!'
      );
    }


    echo json_encode($message);
  }

  public function dailyGiftAmount()
  {
    if ($this->input->post()) {

      $type = $this->input->post('type');

      if ($type == 'daily') {
        $date = date("Y-m-d");

        $daily = $this->db->query("SELECT giftUserId FROM userGiftHistory WHERE DATE(created_at) = DATE(NOW()) GROUP BY giftUserId")->result_array();

        // print_r($daily);
        // die;

        if (!empty($daily)) {

          foreach ($daily as $details) {

            $id = $details['giftUserId'];

            // 	$get = $this->db->query("SELECT SUM(coins) as Total_amount,users.id,users.username,users.name,users.image FROM userGiftHistory
            //    join users on users.id = userGiftHistory.userId WHERE userGiftHistory.userId = $id AND DATE(created_at) = DATE(NOW()) order by id desc")->row_array();

            $get = $this->db->select("SUM(coins) as Total_amount,users.id,users.username,users.name,users.image")
              ->from("userGiftHistory")
              ->join("users", "users.id = userGiftHistory.giftUserId")
              ->where("userGiftHistory.giftUserId", $id)
              ->where("DATE(created_at) = DATE(NOW())")
              ->order_by("Total_amount", "DESC")
              ->get()
              ->row_array();


            $final[] = $get;
          }

          $message = [
            'success' => 'true',
            'message' => 'details found successfully',
            // 'amount' => ($daily["Total_amount"] == 0) ? "Nill" : $daily["Total_amount"],
            'amount' => $final,
          ];
        } else {
          $message = [
            'success' => 'false',
            'message' => 'Nill'
          ];
        }
      } else {
        $message = array(
          'success' => 'false',
          'message' => 'Please enter type parameter!'
        );
      }
    } else {
      $message = array(
        'success' => 'false',
        'message' => 'Please enter type parameter!'
      );
    }


    echo json_encode($message);
  }

  public function weeklyGiftAmount()
  {
    if ($this->input->post()) {

      $type = $this->input->post('type');

      if ($type == 'weekly') {
        $date = date("Y-m-d");

        $weekly = $this->db->query("SELECT giftUserId FROM userGiftHistory WHERE created_at >= DATE(NOW()) - INTERVAL 7 DAY GROUP BY giftUserId")->result_array();

        // print_r($weekly);
        // die;

        if (!empty($weekly)) {

          foreach ($weekly as $details) {

            $id = $details['giftUserId'];

            $get = $this->db->query("SELECT SUM(coins) as Total_amount,users.id,users.username,users.name,users.image FROM userGiftHistory
				   join users on users.id = userGiftHistory.giftUserId WHERE userGiftHistory.giftUserId = $id AND created_at >= DATE(NOW()) - INTERVAL 7 DAY ORDER BY Total_amount ASC")->row_array();

            $final[] = $get;
          }

          $message = [
            'success' => 'true',
            'message' => 'details found successfully',
            // 'amount' => ($daily["Total_amount"] == 0) ? "Nill" : $daily["Total_amount"],
            'amount' => $final,
          ];
        } else {
          $message = [
            'success' => 'false',
            'message' => 'Nill'
          ];
        }
      } else {
        $message = array(
          'success' => 'false',
          'message' => 'Please enter type parameter!'
        );
      }
    } else {
      $message = array(
        'success' => 'false',
        'message' => 'Please enter type parameter!'
      );
    }


    echo json_encode($message);
  }

  public function socialLoginEp()
  {
    if ($this->input->post()) {
      $checkSocialId = $this->db->get_where('users', ['social_id' => $this->input->post('socialId')])->row_array();
      if (!empty($checkSocialId)) {
        $data['social_id'] = $this->input->post('socialId');
        $data['reg_id'] = $this->input->post('reg_id');
        $data['deviceId'] = $this->input->post('device_id');
        $data['latitude'] = $this->input->post('latitude');
        $data['longitude'] = $this->input->post('longitude');
        $data['name'] = $this->input->post('name');
        $data['email'] = $this->input->post('email');
        $data['registerType'] = 'Social';
        $update = $this->db->update('users', $data, ['social_id' => $this->input->post('socialId')]);
        if ($update) {
          $detail = $this->db->get_where('users', ['social_id' => $this->input->post('socialId')])->row_array();
          $message = [
            'success' => '1',
            'message' => 'Login Done',
            'details' => $detail
          ];
        } else {
          $message = [
            'success' => '0',
            'message' => 'Please Try After Some Time'
          ];
        }
      } else {
        $data['social_id'] = $this->input->post('socialId');
        $data['reg_id'] = $this->input->post('reg_id');
        $data['deviceId'] = $this->input->post('device_id');
        $data['latitude'] = $this->input->post('latitude');
        $data['longitude'] = $this->input->post('longitude');
        $data['name'] = $this->input->post('name');
        $data['username'] = substr($this->input->post('name'), 0, 4) . date('his');
        $data['password'] = md5(substr($this->input->post('password'), 0, 4) . date('his'));
        $hash = md5($data['password']);
        $data['email'] = $this->input->post('email');
        $data['registerType'] = 'normal';
        $insert = $this->db->insert('users', $data);
        $lastId = $this->db->insert_id();
        if ($insert) {
          $detail = $this->db->get_where('users', ['id' => $lastId])->row_array();

          if (!empty($detail['password'])) {
            $detail['password'] = $hash;
          } else {
            $detail['password'] =  '';
          }

          $detail['followerCount'] = "0";
          $detail['followingCount'] = "0";

          $message = [
            'success' => '1',
            'message' => 'Registration Done',
            'details' => $detail
          ];
        } else {
          $message = [
            'success' => '0',
            'message' => 'Please Try After Some Time'
          ];
        }
      }
    } else {
      $message = [
        'success' => '0',
        'message' => 'Please Enter Valid Parameters'
      ];
    }
    echo json_encode($message);
  }

  public function liveUser()
  {

    if ($this->input->post()) {

      $data['userId'] = $this->input->post('userId');
      $data['roomName'] = $this->input->post('roomName');
      $data['token'] = $this->input->post('token');
      $data['type'] = $this->input->post('type');
      $data['liveStatus'] = $this->input->post('liveStatus');
      $data['roomId'] = $this->input->post('roomId');
      $data['updated'] = date('Y-m-d H:i:s');

      //check if user already exist
      $checkUser = $this->db->get_where('liveUser', array('userId' => $this->input->post('userId')))->row_array();
      if ($checkUser) {

        $update = $this->db->set($data)
          ->where('userId', $this->input->post('userId'))
          ->update('liveUser');

        if ($update) {
          if ($this->input->post('liveStatus') == '0') {
            echo json_encode([

              'status' => '1',
              'mesasge' => 'User updated, status not live.'

            ]);
          } else {
            echo json_encode([

              'status' => '1',
              'mesasge' => 'User updated, status live.'

            ]);
          }
        }

        // if user not exist
      } else {

        $data['created'] = date('Y-m-d H:i:s');
        $insert = $this->db->insert('liveUser', $data);
        $insertId = $this->db->insert_id();
        if (($insertId)) {

          if ($this->input->post('liveStatus') == '0') {
            echo json_encode([

              'status' => '1',
              'mesasge' => 'User added, status not live.'

            ]);
          } else {
            echo json_encode([

              'status' => '1',
              'mesasge' => 'User added, status live.'

            ]);
          }
        }
      }
    } else {
      echo json_encode([
        'status' => '0',
        'message' => 'enter valid data.'
      ]);
    }
  }

  public function changeLiveStatus()
  {

    if ($this->input->post()) {
      $checkUser = $this->db->get_where('liveUser', array('userId' => $this->input->post('userId')))->row_array();
      if ($checkUser) {

        $update = $this->db->set(["liveStatus" => $this->input->post('liveStatus')])
          ->where('userId', $this->input->post('userId'))
          ->update('liveUser');
        if ($update) {
          if ($this->input->post('liveStatus') == '0') {
            echo json_encode([
              'status' => '1',
              'message' => 'user status set NOT LIVE'
            ]);
          } else {
            echo json_encode([
              'status' => '1',
              'message' => 'user status set LIVE'
            ]);
          }
        }
      } else {
        echo json_encode([
          'status' => '0',
          'message' => 'user not exist with this userId.'
        ]);
      }
    } else {
      echo json_encode([
        'status' => '0',
        'message' => 'enter valid data.'
      ]);
    }
  }

  public function getUsersFromLiveUser()
  {
    if ($this->input->post()) {
      $userId = $this->input->post('userId');
      $country = $this->input->post('country');
      if (!empty($country)) {
        $whereid = "users.id <> $userId";

        $getone = $this->db->select(['liveUser.id', 'liveUser.userId', 'liveUser.roomName', 'liveUser.token', 'liveUser.type', 'liveUser.liveStatus', 'liveUser.roomId', 'liveUser.updated', 'username', 'image', 'country'])
          ->from('users')
          ->join("liveUser", "liveUser.userId = users.id")
          ->where($whereid)
          ->where('country', $country)
          ->get()->result_array();

        if ($getone) {
          echo json_encode([
            'status' => '1',
            'message' => 'list found',
            'details' => $getone
          ]);
        } else {
          echo json_encode([
            'status' => '0',
            'message' => 'list not found.'
          ]);
        }
      } else {
        $get = $this->db->query("SELECT liveUser.id, liveUser.userId, liveUser.roomName, liveUser.token, liveUser.type, liveUser.liveStatus, liveUser.roomId, liveUser.updated, users.username, users.image, users.country FROM liveUser JOIN users ON users.id = liveUser.userId WHERE userId <> $userId")->result_array();
        if ($get) {
          echo json_encode([
            'status' => '1',
            'message' => 'list found',
            'details' => $get
          ]);
        } else {
          echo json_encode([
            'status' => '0',
            'message' => 'list not found.'
          ]);
        }
      }
    } else {
      echo json_encode([
        'status' => '0',
        'message' => 'enter valid data.'
      ]);
    }
  }

  public function socialLogin()
  {
    if ($this->input->post()) {

      // $checkEmail = $this->db->get_where('users', ['email' => $this->input->post('email')])->row_array();
      // if(!!$checkEmail){
      //   echo json_encode([
      //     'status' => 0,
      //     'message' => 'Email already exists'
      //   ]);exit;
      // }

      $checkSocialId = $this->db->get_where('users', ['social_id' => $this->input->post('socialId')])->row_array();
      if (!empty($checkSocialId)) {
        $data['social_id'] = $this->input->post('socialId');
        $data['reg_id'] = $this->input->post('reg_id');
        $data['deviceId'] = $this->input->post('device_id');
        $data['latitude'] = $this->input->post('latitude');
        $data['longitude'] = $this->input->post('longitude');
        $data['name'] = $this->input->post('name');
        $data['email'] = $this->input->post('email');
        $data['country'] = $this->input->post('country');
        $data['setFrameId'] = '1';
        $data['updated'] = date('Y-m-d H:i:s');
        // $data['']
        $data['registerType'] = 'Social';
        $update = $this->db->update('users', $data, ['social_id' => $this->input->post('socialId')]);
        if ($update) {

          $detail = $this->db->get_where('users', ['social_id' => $this->input->post('socialId')])->row_array();

          if ($detail['crown'] == '0') {
            $detail['crownStatus'] = false;
            $detail['crown'] = null;
          } else {
    
            $detail['crownStatus'] = true;
            $detail['crown'] = $this->db->get_where('crown', ['id' => $get['crown']])->row_array();
          }

          $message = [
            'success' => '1',
            'message' => 'Login Done',
            'details' => $detail
          ];
        } else {
          $message = [
            'success' => '0',
            'message' => 'Please Try After Some Time'
          ];
        }
      } else {
        $data['social_id'] = $this->input->post('socialId');
        $data['reg_id'] = $this->input->post('reg_id');
        $data['deviceId'] = $this->input->post('device_id');
        $data['latitude'] = $this->input->post('latitude');
        $data['longitude'] = $this->input->post('longitude');
        $data['name'] = $this->input->post('name');
        $data['created'] = date('Y-m-d');

        $getUserName = $this->db->select('username')->from('users')->order_by('id', 'desc')->get()->row_array();
        if (empty($getUserName)) {
          $data['username'] = '@500000001';
        } else {
          $uname = $getUserName['username'];
          $data['username'] = ++$uname;
        }

        $data['country'] = $this->input->post('country');
        // $data['password'] = md5(substr($this->input->post('password'), 0, 4) . date('his'));
        $hash = md5($data['password']);
        $data['email'] = $this->input->post('email');
        $data['registerType'] = 'normal';
        $data['setFrameId'] = '1';
        $insert = $this->db->insert('users', $data);
        $lastId = $this->db->insert_id();
        if ($insert) {
          $datas['userId'] = $lastId;;
          $detail = $this->db->get_where('users', ['id' => $lastId])->row_array();

          $check = $this->db->get_where('loginGiftUser', ['userId' => $lastId])->row_array();

          if (empty($check)) {
            $date = date('Y-m-d H:i:s');
            $date = strtotime($date);
            $date = strtotime("+7 day", $date);
            // echo date('Y-m-d H:i:s', $date);
            $datas['userId'] = $lastId;
            $datas['framId'] = 1;
            $datas['created'] = date('Y-m-d H:i:s');
            $datas['expried'] = date('Y-m-d H:i:s', $date);

            $giftInsert = $this->db->insert('loginGiftUser', $datas);
          }
          if (!empty($detail['password'])) {
            $detail['password'] = $hash;
          } else {
            $detail['password'] =  '';
          }

          if ($detail['crown'] == '0') {
            $detail['crownStatus'] = false;
            $detail['crown'] = null;
          } else {
    
            $detail['crownStatus'] = true;
            $detail['crown'] = $this->db->get_where('crown', ['id' => $get['crown']])->row_array();
          }


          $message = [
            'success' => '1',
            'message' => 'Registration Done',
            'details' => $detail
          ];
        } else {
          $message = [
            'success' => '0',
            'message' => 'Please Try After Some Time'
          ];
        }
      }
    } else {
      $message = [
        'success' => '0',
        'message' => 'Please Enter Valid Parameters'
      ];
    }
    echo json_encode($message);
  }

  public function getUserDetails()
  {

    $get = $this->db->get_where("users", ['id' => $this->input->post("userId")])->row_array();

    if (!!$get) {

      $getId = $get['id'];
      //
      // print_r($getId);
      // die;

      $getstatus = $this->db->get_where("userFollow", ['userId' => $getId])->row_array();

      // ===============/ followers /=================== 

      $followersCount = $this->db->select('*')
        ->from('userFollow')
        ->where('followingUserId', $this->input->post('userId'))
        ->where('status', '1')
        ->get()->num_rows();


      // =====================/ following /=================

      $followingCount = $this->db->select('*')
        ->from('userFollow')
        ->where('userId', $this->input->post('userId'))
        ->where('status', '1')
        ->get()->num_rows();


      $get['followersCount'] = '' . $followersCount . '';
      $get['followingCount'] = '' . $followingCount . '';

      $get['frameImage'] = "";

      $created = $get['created'];
      $dateCreated = date_create($created);
      $addedDate = date_add($dateCreated, date_interval_create_from_date_string("7 days"));
      $addedDateArray = ((array) $addedDate);
      $finalDate = date_create($addedDateArray['date']);
      $dateSevenDay = date_format($finalDate, "Y-m-d");

      // print_r($LIST);exit;
      if ($get['setFrameId'] == '1') {

        if (date('Y-m-d') > $dateSevenDay) {
          $this->db->set('setFrameId', '0')->where('id', $get['id'])->update('users');
        } else {
          $get['frameImage'] = base_url() . 'assets/gifts/epgifts/frame1.svga';
        }
      } else if ($get['setFrameId'] == '0') {
      } else {

        $getFrame = $this->db->get_where('Ep_admin_add_frames', ['id' => $get['setFrameId']])->row_array();

        $get['frameImage'] = $getFrame['frame_img'];
      }

      $getEffects = $this->db->get('entryEffectsData')->result_array();

      foreach ($getEffects as $effects) {
        if ($get['my_level'] >= $effects['levelFrom'] && $get['my_level'] <= $effects['levelTo']) {
          $get['entry'] = $effects['image'];
        }
      }

      $get['invitations'] = $this->db->get_where('familyJoinRequest', ['userId' => $this->input->post('userId')])->num_rows();

      if ($get['crown'] == '0') {
        $get['crownStatus'] = false;
        $get['crown'] = null;
      } else {

        $get['crownStatus'] = true;
        $get['crown'] = $this->db->get_where('crown', ['id' => $get['crown']])->row_array();
      }

      echo json_encode([
        "message" => "details found",
        "success" => "1",
        "details" => $get,
      ]);
      exit;
    } else {
      echo json_encode([
        "message" => "details not found!",
        "success" => "0",
      ]);
      exit;
    }
  }

  public function searchUser()
  {

    try {

      $records = $this->db->from("users");

      if (!!$this->input->post("search"))
        $records = $records->like("name", $this->input->post("search"));


      $records = $records->get()->result_array();

      if (!!$records) {
        echo json_encode([
          "success"  =>  "1",
          "message"  =>  "Record found successfully",
          "details"  =>  $records,
        ]);
        exit;
      }

      echo json_encode([
        "success"  =>  "0",
        "message"  =>  "HasTag not found",
      ]);
      exit;
    } catch (Exception $ex) {
      echo json_encode([
        "success"  =>  "0",
        "message"  =>  $ex->getMessage(),
      ]);
      exit;
    }
  }

  public function getUserProfile()
  {
    if ($this->input->post()) {

      $checkUserId = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
      if ($checkUserId) {

        $checkOtherUserId = $this->db->get_where('users', ['id' => $this->input->post('followingUserId')])->row_array();
        if ($checkOtherUserId) {

          $checkFollow = $this->db->get_where('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' => $this->input->post('followingUserId'), 'status' => '1'])->row_array();
          if (!!$checkFollow) {

            $checkOtherUserId['followStatus'] = 1;
          } else {
            $checkOtherUserId['followStatus'] = 2;
          }

          if ($checkOtherUserId['followStatus'] == 2) {

            $checkRequest = $this->db->get_where('userFollowRequest', ['userId' => $this->input->post('followingUserId'), 'AcceptUserId' => $this->input->post('userId')])->row_array();
            // print_r($checkRequest);exit;

            if (empty($checkRequest)) {
              $checkOtherUserId['followStatus'] = 2;
            } else {
              if ($checkRequest['type'] == '0') {
                $checkOtherUserId['followStatus'] = 3;
              } else if ($checkRequest['type'] == '2') {
                $checkOtherUserId['followStatus'] = 4;
              }
            }
          }

          $checkOtherUserId['frameImage'] = "";

          $created = $checkOtherUserId['created'];
          $dateCreated = date_create($created);
          $addedDate = date_add($dateCreated, date_interval_create_from_date_string("7 days"));
          $addedDateArray = ((array) $addedDate);
          $finalDate = date_create($addedDateArray['date']);
          $dateSevenDay = date_format($finalDate, "Y-m-d");

          // print_r($LIST);exit;
          if ($checkOtherUserId['setFrameId'] == '1') {

            if (date('Y-m-d') > $dateSevenDay) {
              $this->db->set('setFrameId', '0')->where('id', $checkOtherUserId['id'])->update('users');
            } else {
              $checkOtherUserId['frameImage'] = base_url() . 'assets/gifts/epgifts/frame1.svga';
            }
          } else if ($checkOtherUserId['setFrameId'] == '0') {
          } else {

            $getFrame = $this->db->get_where('Ep_admin_add_frames', ['id' => $checkOtherUserId['setFrameId']])->row_array();

            $LIST['frameImage'] = base_url() . $getFrame['frame_img'];
          }

          if ($checkOtherUserId['crown'] == '0') {
            $checkOtherUserId['crownStatus'] = false;
            $checkOtherUserId['crown'] = null;
          } else {

            $checkOtherUserId['crownStatus'] = true;
            $checkOtherUserId['crown'] = $this->db->get_where('crown', ['id' => $checkOtherUserId['crown']])->row_array();
          }

          $getachieve = $this->db->select('achievment_to_user.*, achievment_list.media')
                                  ->from('achievment_to_user')
                                  ->join('achievment_list', 'achievment_list.id = achievment_to_user.achievment_id', 'left')
                                  ->where('user_id', $checkOtherUserId['id'])
                                  ->order_by('id', 'desc')
                                  ->limit(4)
                                  ->get()->result_array();

          $checkOtherUserId['achievment'] = $getachieve;

          echo json_encode([
            'status' => '1',
            'messsage' => 'Details found',
            'datails' => $checkOtherUserId
          ]);
          exit;
        } else {
          echo json_encode([
            'status' => '0',
            'messsage' => 'otherUserId user not found'
          ]);
          exit;
        }
      } else {
        echo json_encode([
          'status' => '0',
          'messsage' => 'UserId user not found'
        ]);
        exit;
      }
    } else {
      echo json_encode([
        'status' => '0',
        'messsage' => 'Please Enter Valid Data'
      ]);
    }
  }

  public function privateUnprivate()
  {
    if ($this->input->post()) {

      $getStatus = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
      if (!!$getStatus) {
        if ($getStatus['privateAccount'] == '0') {

          $setStatus['privateAccount'] = '1';
          $update = $this->db->set($setStatus)->where('id', $this->input->post('userId'))->update('users');
          if ($update) {

            $get = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            echo json_encode([
              'status' => '1',
              'message' => 'Account Status changed to Private',
              'details' => $get
            ]);
          } else {
            echo json_encode([
              'status' => '0',
              'message' => 'some problem occured'
            ]);
            exit;
          }
        } else {

          $setStatus['privateAccount'] = '0';
          $update = $this->db->set($setStatus)->where('id', $this->input->post('userId'))->update('users');

          if ($update) {

            $get = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            echo json_encode([
              'status' => '1',
              'message' => 'Account Status changed To Public',
              'details' => $get
            ]);
          } else {
            echo json_encode([
              'status' => '0',
              'message' => 'some problem occured'
            ]);
            exit;
          }
        }
      } else {
        echo json_encode([
          'status' => '0',
          'message' => 'No'
        ]);
        exit;
      }
    } else {
      echo json_encode([
        'status' => '0',
        'message' => 'Please Enter Valid Parameters'
      ]);
    }
  }

  public function getFollowUsers()
  {

    $getDetails = $this->db->select("userFollow.id userFollowId,userFollow.userId,userFollow.followingUserId,users.*")
      ->from("userFollow")
      ->join("users", "userFollow.followingUserId = users.id")
      ->where("userFollow.userId", $this->input->post("userId"))
      ->where("userFollow.status", '1')
      ->get()
      ->result_array();

    if (!!$getDetails) {

      echo json_encode([

        "success" => "1",
        "message" => "details found successfully",
        "details" => $getDetails,
      ]);
      exit;
    } else {
      echo json_encode([

        "success" => "0",
        "message" => "details not found!",
      ]);
      exit;
    }
  }

  public function getFollowingUsers()
  {

    $getDetails = $this->db->select("userFollow.id userFollowId,userFollow.userId,userFollow.followingUserId,users.*")
      ->from("userFollow")
      ->join("users", "userFollow.userId = users.id")
      ->where("userFollow.followingUserId", $this->input->post("userId"))
      ->where("userFollow.status", '1')
      ->get()
      ->result_array();

    if (!!$getDetails) {

      foreach ($getDetails as $key => $LIST) {

        $getId = $LIST['id'];

        $getFollowingStatus = $this->db->get_where("userFollow", ['followingUserId' => $getId, 'userId' => $this->input->post('userId')])->row_array();
        $getStatus = $this->db->get_where("userFollow", ['followingUserId' => $this->input->post('userId'), 'userId' => $getId])->row_array();

        if (!!$getStatus) {

          $LIST['following'] = TRUE;
        } else {

          $LIST['following'] = FALSE;
        }

        if (!!$getFollowingStatus) {

          $LIST['followingStatus'] = TRUE;
        } else {

          $LIST['followingStatus'] = FALSE;
        }
        $check[$key] = $LIST;
      }

      echo json_encode([

        "success" => "1",
        "message" => "details found successfully",
        "details" => $check,
      ]);
      exit;
    } else {
      echo json_encode([

        "success" => "0",
        "message" => "details not found!",
      ]);
      exit;
    }
  }

  public function getLiveUsersList()
  {

    // $get = $this->db->get_where("userLive",['userId' => $this->input->post("userId"),'status' => 'live'])->result_array();

    // $get = $this->db->select("userLive.*,users.username,users.firstname,users.lastname,users.coin,users.purchasedCoin,users.image")
    //                 ->distinct('userId')
    //                 ->from("userLive")
    //                 ->join("users","users.id = userLive.userId","left")
    //                 ->where("userLive.userId !=",$this->input->post("userId"))
    //                 ->where("userLive.status","live")
    //                 ->order_by('userId', 'desc')
    //                 ->get()
    //                 ->result_array();

    $userId = $this->input->post("userId");

    $get[] = $this->db->query("SELECT DISTINCT userLive.userId, userLive.*, users.* FROM userLive LEFT JOIN users ON users.id = userLive.userId WHERE userLive.userId != $userId AND userLive.status = 'live' ORDER BY userLive.created DESC")->row_array();

    if (!!$get) {

      echo json_encode([

        "success" => "1",
        "message" => "details found",
        "details" => $get,
      ]);
      exit;
    } else {

      echo json_encode([

        "success" => "0",
        "message" => "details not found",
      ]);
      exit;
    }
  }

  public function getFollowing()
  {


    $getDetails = $this->db->get_where("userFollow", ['userId' => $this->input->post("userId"), 'status' => '1'])->result_array();
    $myId = $this->input->post('userId');

    if (!!$getDetails) {

      $final = [];

      foreach ($getDetails as $get) {

        $getId = $get['followingUserId'];

        $getDetails = $this->db->query("SELECT DISTINCT userLive.userId, userLive.*,users.my_level,users.country,users.username,users.name,users.coin,users.purchasedCoin,users.image FROM userLive LEFT JOIN users ON users.id = userLive.userId WHERE userLive.userId = $getId AND userLive.userId != $myId AND userLive.status = 'live' ORDER BY created DESC")->row_array();
        $get['cameraFrame'] = base_url() . 'assets/gifts/epgifts/cAMERA_.svg';



        if (!!$getDetails) {

          $getuserdetails = $this->db->get_where('users', ['id' => $getDetails['userId']])->row_array();

          $get['frameImage'] = "";

          $created = $getuserdetails['created'];
          $dateCreated = date_create($created);
          $addedDate = date_add($dateCreated, date_interval_create_from_date_string("7 days"));
          $addedDateArray = ((array) $addedDate);
          $finalDate = date_create($addedDateArray['date']);
          $dateSevenDay = date_format($finalDate, "Y-m-d");

          // print_r($LIST);exit;
          if ($getuserdetails['setFrameId'] == '1') {

            if (date('Y-m-d') > $dateSevenDay) {
              $this->db->set('setFrameId', '0')->where('id', $getuserdetails['id'])->update('users');
            } else {
              $get['frameImage'] = base_url() . 'assets/gifts/epgifts/frame1.svga';
            }
          } else if ($getuserdetails['setFrameId'] == '0') {
          } else {

            $getFrame = $this->db->get_where('Ep_admin_add_frames', ['id' => $getuserdetails['setFrameId']])->row_array();

            $get['frameImage'] = $getFrame['frame_img'];
          }

          if ($getuserdetails['setGrageId'] == '1') {

            if (date('Y-m-d') > $dateSevenDay) {
              $this->db->set('setGrageId', '0')->where('id', $getuserdetails['userId'])->update('users');
            } else {
              $get['garageImage'] = base_url() . 'assets/gifts/garage/blackCar.svga';
            }
          } else if ($getuserdetails['setGrageId'] == '0') {
          } else {

            $garage = $this->db->get_where('garageData', ['id' => $getuserdetails['setGrageId']])->row_array();


            $get['garageImage'] = $garage['mainImage'];
            // print_r($value);exit;
          }

          $getEffects = $this->db->get('entryEffectsData')->result_array();

          foreach ($getEffects as $effects) {
            if ($getDetails['my_level'] >= $effects['levelFrom'] && $getDetails['my_level'] <= $effects['levelTo']) {
              $getDetails['entry'] = $effects['image'];
            }
          }

          $getDetails['followingTo'] = $get['userId'];
          $getDetails['status'] = $get['status'];
          $getDetails['cameraFrame'] = $get['cameraFrame'];
          $getDetails['frameImage'] = $get['frameImage'];
          $getDetails['garageImage'] = $get['garageImage'];
          $final[] = $getDetails;
        } else {
        }
      }

      if (!!$final) {

        echo json_encode([
          "success" => "1",
          "message" => "details found",
          "details" => $final,
        ]);
        exit;
      } else {

        echo json_encode([
          "success" => "0",
          "message" => "details not found",
        ]);
        exit;
      }
    } else {

      echo json_encode([

        "success" => "0",
        "message" => "details not found!"
      ]);
      exit;
    }
  }

  public function getPopularLiveUser()
  {
    // $list = [];
    $get = $this->db->select_sum('userGiftHistory.coins')
      ->select('users.id userId,users.country, users.my_level ,users.username,users.name,users.image,users.setFrameId,users.setGrageId, users.created ucreated, userLive.*')
      ->from('userGiftHistory')
      ->join('users', 'users.id = userGiftHistory.giftUserId', 'left')
      ->join('userLive', 'userLive.userId = userGiftHistory.giftUserId', 'left')
      ->where('userLive.status', 'live')
      ->where('userLive.userId !=', $this->input->post('userId'))
      ->group_by('giftUserId')
      ->order_by('userLive.id', 'desc')
      ->order_by('coins', 'desc')
      ->get()->result_array();

    // $list = [];
    // $list['cameraFrame'] =  
    if (!!$get) {
      $list = [];
      foreach ($get as  $value) {

        // print_r($value);exit;
        $value['cameraFrame'] = base_url() . 'assets/gifts/epgifts/cAMERA_.svg';

        $value['frameImage'] = "";

        $created = $value['ucreated'];
        $dateCreated = date_create($created);
        $addedDate = date_add($dateCreated, date_interval_create_from_date_string("7 days"));
        $addedDateArray = ((array) $addedDate);
        $finalDate = date_create($addedDateArray['date']);
        $dateSevenDay = date_format($finalDate, "Y-m-d");

        // print_r($LIST);exit;
        if ($value['setFrameId'] == '1') {

          if (date('Y-m-d') > $dateSevenDay) {
            $this->db->set('setFrameId', '0')->where('id', $value['userId'])->update('users');
          } else {
            $value['frameImage'] = base_url() . 'assets/gifts/epgifts/frame1.svga';
          }
        } else if ($value['setFrameId'] == '0') {
        } else {

          $getFrame = $this->db->get_where('Ep_admin_add_frames', ['id' => $value['setFrameId']])->row_array();

          $value['frameImage'] = $getFrame['frame_img'];
        }

        if ($value['setGrageId'] == '1') {

          if (date('Y-m-d') > $dateSevenDay) {
            $this->db->set('setGrageId', '0')->where('id', $value['userId'])->update('users');
          } else {
            $value['garageImage'] = base_url() . 'assets/gifts/garage/blackCar.svga';
          }
        } else if ($value['setGrageId'] == '0') {
        } else {

          $garage = $this->db->get_where('garageData', ['id' => $value['setGrageId']])->row_array();


          $value['garageImage'] = $garage['mainImage'];
          // print_r($value);exit;
        }

        $getEffects = $this->db->get('entryEffectsData')->result_array();

        foreach ($getEffects as $effects) {
          if ($value['my_level'] >= $effects['levelFrom'] && $value['my_level'] <= $effects['levelTo']) {
            $value['entry'] = $effects['image'];
          }
        }

        // print_r($value);exit;

        $list[] = $value;
      }

      echo json_encode([
        "success" => "1",
        "message" => "details found",
        "details" => $list,
      ]);
      exit;
    } else {

      echo json_encode([

        "success" => "0",
        "message" => "details not found",
      ]);
      exit;
    }
  }




  public function getAllCountries()
  {
    $get = $this->db->get('country')->result_array();

    if (!!$get) {

      echo json_encode([
        'status' => 1,
        'message' => 'country list found',
        'details' => $get
      ]);
      exit;
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'No Country List Found'
      ]);
      exit;
    }
  }

  public function getLiveUserByCountry()
  {
    if ($this->input->post()) {

      $getUser = $this->db->select('id')
        ->from('users')
        ->where('country', $this->input->post('country'))
        ->get()->result_array();
      if (!!$getUser) {

        $main = [];
        foreach ($getUser as $value) {
          $getLive = $this->db->select('userLive.*, users.country, users.name, users.username, users.image')
            ->from('userLive')
            ->join('users', 'users.id = userLive.userId', 'left')
            ->where('userId', $value['id'])
            ->where('userLive.status', 'live')
            ->order_by('id', 'desc')
            ->get()->result_array();

          if (!!$getLive) {
            $main = $getLive[0];
          }
        }

        if (!!$main) {

          echo json_encode([
            'status' => 1,
            'message' => 'LiveUsers Found in ' . $this->input->post('country'),
            'deatils' => $main
          ]);
          exit;
        } else {
          echo json_encode([
            'status' => 0,
            'message' => 'No LiveUser found in ' . $this->input->post('country')
          ]);
          exit;
        }
      } else {
        echo json_encode([
          'status' => 0,
          'message' => 'No user found from this country'
        ]);
        exit;
      }
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'Enter valid data'
      ]);
      exit;
    }
  }

  public function blockUnblockUser()
  {
    if ($this->input->post()) {

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      $checkBlockUser = $this->db->get_where('users', ['id' => $this->input->post('blockUserId')])->row_array();

      if (empty($checkUser)) {
        echo json_encode([
          'status' => '0',
          'message' => 'Invalid userId'
        ]);
        exit;
      }

      if (empty($checkBlockUser)) {
        echo json_encode([
          'status' => '0',
          'message' => 'Invalid Block UserId'
        ]);
        exit;
      }

      $checkBlockStatus = $this->db->get_where('bockuser', ['userId' => $this->input->post('userId'), 'blockUserId' => $this->input->post('blockUserId')])->row_array();
      if (!!$checkBlockStatus) {

        $where = [
          'userId' => $this->input->post('userId'),
          'blockUserId' => $this->input->post('blockUserId')
        ];
        if ($this->db->delete('bockuser', $where)) {

          echo json_encode([
            'status' => '1',
            'message' => $this->input->post('blockUserId') . ' user Un-Blocked.'
          ]);
          exit;
        } else {

          echo json_encode([
            'status' => '0',
            'message' => 'some error ocured'
          ]);
          exit;
        }
      } else {

        $data['userId'] = $this->input->post('userId');
        $data['blockUserId'] = $this->input->post('blockUserId');
        $data['createdDate'] = date('Y-m-d');
        $data['createdTime'] = date('H:i:s');

        if ($this->db->insert('bockuser', $data)) {

          echo json_encode([
            'status' => '0',
            'message' => $this->input->post('blockUserId') . ' user Blocked.'
          ]);
          exit;
        } else {

          echo json_encode([
            'status' => '0',
            'message' => 'some error ocured'
          ]);
          exit;
        }
      }
    } else {
      echo json_encode([
        'status' => '0',
        'message' => 'enter valid data'
      ]);
    }
  }

  public function getBlockedUserList()
  {
    if ($this->input->post()) {

      $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
      if (empty($checkuser)) {
        echo json_encode([
          'status' => '0',
          'message' => $this->input->post('userId') . ' userId not exists'
        ]);
        exit;
      }

      $get = $this->db->select('users.*')
        ->from('bockuser')
        ->join('users', 'users.id = bockuser.blockUserId', 'left')
        ->where('userId', $this->input->post('userId'))
        ->get()->result_array();
      if (!!$get) {

        echo json_encode([
          'status' => '1',
          'message' => 'Blocked user list found',
          'details' => $get
        ]);
        exit;
      } else {
        echo json_encode([
          'status' => '0',
          'message' => 'No Block list found'
        ]);
        exit;
      }
    } else {
      echo json_encode([
        'status' => '0',
        'message' => 'enter valid data'
      ]);
    }
  }

  public function getBanners()
  {

    $get = $this->db->get_where('banners', ['country' => $this->input->post('country')])->result_array();

    if (empty($get)) {
      $get = $this->db->get_where('banners', ['country' => 'All'])->result_array();

      echo json_encode([
        'status' => 1,
        'message' => 'all data found',
        'data' => $get
      ]);
      exit;
    }

    echo json_encode([
      'status' => 1,
      'message' => 'country data found',
      'data' => $get
    ]);
    exit;
  }

  public function stayInApp()
  {
    if ($this->input->post()) {

      $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if (empty($checkuser)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);
        exit;
      }

      $data['userId'] = $this->input->post('userId');
      $data['entryTime'] = $this->input->post('entryTime');
      $data['exitTime'] = $this->input->post('exitTime');
      $data['totalTime'] = $this->input->post('totalTime');

      if ($this->db->insert('userStayInApp', $data)) {

        $sum = $this->db->select_sum('totalTime')
          ->from('userStayInApp')
          ->where('userId', $this->input->post('userId'))
          ->get()->row_array();

        if ($this->db->set('exp', $sum['totalTime'])->where('id', $this->input->post('userId'))->update('users')) {

          echo json_encode([
            'status' => 1,
            'message' => 'Data inserted'
          ]);
          exit;
        } else {
          echo json_encode([
            'status' => 0,
            'message' => 'tech error'
          ]);
          exit;
        }
      }
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'Enter Valid Data'
      ]);
      exit;
    }
  }

  // public function serverApi(){

  // }

  // function GenerateSignature($appId, $signatureNonce, $serverSecret, $timestamp)
  // {
  //     $str = $appId.$signatureNonce.$serverSecret.$timestamp;
  //     $signature = md5($str);
  //     return $signature;
  // }

  //Generate a random hex string of 16 hex digits.
  // $signatureNonce = bin2hex(random_bytes(8));
  // //Use the AppID and ServerSecret of your project.
  // $appId = 12345;
  // $serverSecret = "9193cc662a4c0ec135ec71fb57194b38";
  // $timestamp = time();
  // $signature = GenerateSignature($appId, $signatureNonce, $serverSecret, $timestamp);
  // echo $signature;


  public function uploadFrame()
  {
    if (!empty($_FILES["fram1"]["name"])) {
      $datas['fram1'] = $this->uploadVideo($_FILES["fram1"]);
    }
    if (!empty($_FILES["fram2"]["name"])) {
      $datas['fram2'] = $this->uploadVideo($_FILES["fram2"]);
    }
    $datas['created'] = date('Y-m-d H:i:s');
    $insert = $this->db->insert('loginGift', $datas);
    if ($insert) {
      echo json_encode([
        'success' => 1,
        'message' => 'frame upload successfully',
      ]);
    }
  }




  public function userPurchaseFrame()
  {
    if ($this->input->post()) {

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if (empty($checkUser)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);
        exit;
      }

      $checkFrameId = $this->db->get_where('Ep_admin_add_frames', ['id' => $this->input->post('frameId')])->row_array();

      if (empty($checkFrameId)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid frameId'
        ]);
        exit;
      }

      $checkPurchase = $this->db->get_where('userPurchaseFram', ['userId' => $this->input->post('userId'), 'framId' => $this->input->post('frameId')])->row_array();
      if (!empty($checkPurchase)) {

        echo json_encode([
          'status' => 0,
          'message' => 'frame already purchased'
        ]);
        exit;
      }

      if ($checkUser['purchasedCoin'] < $checkFrameId['price']) {
        echo json_encode([
          'status' => 0,
          'message' => 'insufficient balance'
        ]);
        exit;
      }

      // deduct coins

      $data['purchasedCoin'] = $checkUser['purchasedCoin'];

      $data['purchasedCoin'] -= $checkFrameId['price'];

      // update coins

      $frameData['userId'] = $this->input->post('userId');
      $frameData['framId'] = $this->input->post('frameId');
      $frameData['created'] = date('Y-m-d H:i:s');
      $frameData['expired'] = date('Y-m-d H:i:s', strtotime("+" . $checkFrameId['validity'] . " days"));



      if ($this->db->set($data)->where('id', $this->input->post('userId'))->update('users') && $this->db->insert('userPurchaseFram', $frameData)) {

        echo json_encode([
          'status' => 1,
          'message' => 'frame purchased'
        ]);
        exit;
      } else {

        echo json_encode([
          'status' => 0,
          'message' => 'frame not purchased'
        ]);
        exit;
      }
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid parameters'
      ]);
      exit;
    }
  }

  public function getUserFrames()
  {
    if ($this->input->post()) {

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if (empty($checkUser)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);
        exit;
      }

      $getframe = $this->db->get_where('userPurchaseFram', ['userId' => $this->input->post('userId')])->result_array();

      if (empty($getframe)) {
        echo json_encode([
          'status' => 0,
          'message' => 'no data found'
        ]);
        exit;
      }

      $final = [];
      foreach ($getframe as $frame) {
        if ($frame['expired'] < date("Y-m-d H:i:s")) {

          $this->db->delete('userPurchaseFram', ['id' => $frame['id']]);
        } else {

          $details = $this->db->get_where('Ep_admin_add_frames', ['id' => $frame['framId']])->row_array();

          $details['applied'] = false;

          $get = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

          $details['applied'] = false;
          if ($get['setFrameId'] == $frame['framId']) {
            $details['applied'] = true;
          }

          $final[] = $details;
        }
      }

      echo json_encode([
        'status' => 1,
        'message' => 'list found',
        'details' => $final
      ]);
      exit;
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid parameters'
      ]);
      exit;
    }
  }


  public function getUserFram()
  {
    $userId = $this->input->post('userId');
    $expired = date('Y-m-d H:i:s');
    $getUserFrame = $this->db->select('userPurchaseFram.*')
      ->from('userPurchaseFram')
      ->where('userId', $userId)
      ->where('expired >=', $expired)
      ->get()
      ->result_array();
    $get = [];
    if ($getUserFrame) {
      foreach ($getUserFrame as $list) {
        $getFID = $list['framId'];
        $getList = $this->db->get_where('Ep_admin_add_frames', ['id' => $getFID])->row_array();
        $getList['frame_img'] = base_url() . $getList['frame_img'];
        $get[] = $getList;
      }
      echo json_encode([
        'status' => 1,
        'message' => 'your oun frame',
        'details' => $get
      ]);
    }
  }

  public function topReciever()
  {

    $today = $this->db->select_sum('coins')
      ->select('giftUserId')
      ->from('userGiftHistory')
      ->group_by('giftUserId')
      ->where('created_at', date('Y-m-d'))
      ->order_by('coins', 'desc')
      ->limit(11)
      ->get()->result_array();

    foreach ($today as $key => $list) {

      $userInfo = $this->db->get_where('users', ['id' => $list['giftUserId']])->row_array();

      $today[$key]['userInfo'] = $userInfo;
    }

    $dateLimit = date("Y-m-d", strtotime("-1 week"));

    $weekly = $this->db->select_sum('coins')
      ->select('giftUserId')
      ->from('userGiftHistory')
      ->group_by('giftUserId')
      ->where('created_at >=', $dateLimit)
      ->order_by('coins', 'desc')
      ->limit(11)
      ->get()->result_array();


    foreach ($weekly as $key => $list) {

      $userInfo = $this->db->get_where('users', ['id' => $list['giftUserId']])->row_array();

      $weekly[$key]['userInfo'] = $userInfo;
    }

    // $detail['weekly']['userInfo'] =


    $dateLimit = date("Y-m-d", strtotime("-1 month"));

    $monthly = $this->db->select_sum('coins')
      ->select('giftUserId')
      ->from('userGiftHistory')
      ->group_by('giftUserId')
      ->where('created_at >=', $dateLimit)
      ->order_by('coins', 'desc')
      ->limit(11)
      ->get()->result_array();

    foreach ($monthly as $key => $list) {

      $userInfo = $this->db->get_where('users', ['id' => $list['giftUserId']])->row_array();

      $monthly[$key]['userInfo'] = $userInfo;
    }



    $overAll = $this->db->select_sum('coins')
      ->select('giftUserId')
      ->from('userGiftHistory')
      ->group_by('giftUserId')
      ->order_by('coins', 'desc')
      ->limit(11)
      ->get()->result_array();


    foreach ($overAll as $key => $list) {

      $userInfo = $this->db->get_where('users', ['id' => $list['giftUserId']])->row_array();

      $overAll[$key]['userInfo'] = $userInfo;
    }


    if ($this->input->post('type') == '1') {
      if (empty($today)) {
        echo json_encode([
          'status' => 0,
          'message' => 'No Gifting Done Today'
        ]);
        exit;
      }
      echo json_encode([
        'status' => '1',
        'message' => 'top reciever today',
        'details' => $today
      ]);
      exit;
    } else if ($this->input->post('type') == '2') {
      if (empty($weekly)) {

        echo json_encode([
          'status' => 0,
          'message' => 'No Gifting Done This Week'
        ]);
        exit;
      } else {

        echo json_encode([
          'status' => '1',
          'message' => 'top reciever weekly',
          'details' => $weekly
        ]);
        exit;
      }
    } else if ($this->input->post('type') == '3') {

      if (empty($monthly)) {

        echo json_encode([
          'status' => '0',
          'message' => 'No Gifting Done This Month'
        ]);
        exit;
      } else {

        echo json_encode([
          'status' => '1',
          'message' => 'top reciever monthly',
          'details' => $monthly
        ]);
        exit;
      }
    } else if ($this->input->post('type') == '4') {

      if (empty($overAll)) {

        echo json_encode([
          'status' => '0',
          'message' => 'No Gifting Done Overall'
        ]);
        exit;
      } else {

        echo json_encode([
          'status' => '1',
          'message' => 'top reciever overAll',
          'details' => $overAll
        ]);
        exit;
      }
    } else {
      echo json_encode([
        'status' => '0',
        'message' => 'Enter valid Type'
      ]);
      exit;
    }
  }

  public function topGifter()
  {

    $today = $this->db->select_sum('coins')
      ->select('userId')
      ->from('userGiftHistory')
      ->group_by('userId')
      ->where('created_at', date('Y-m-d'))
      ->order_by('coins', 'desc')
      ->limit(11)
      ->get()->result_array();

    foreach ($today as $key => $list) {

      $userInfo = $this->db->get_where('users', ['id' => $list['userId']])->row_array();

      $today[$key]['userInfo'] = $userInfo;
    }

    $dateLimit = date("Y-m-d", strtotime("-1 week"));

    $weekly = $this->db->select_sum('coins')
      ->select('userId')
      ->from('userGiftHistory')
      ->group_by('userId')
      ->where('created_at >=', $dateLimit)
      ->order_by('coins', 'desc')
      ->limit(11)
      ->get()->result_array();


    foreach ($weekly as $key => $list) {

      $userInfo = $this->db->get_where('users', ['id' => $list['userId']])->row_array();

      $weekly[$key]['userInfo'] = $userInfo;
    }

    // $detail['weekly']['userInfo'] =


    $dateLimit = date("Y-m-d", strtotime("-1 month"));

    $monthly = $this->db->select_sum('coins')
      ->select('userId')
      ->from('userGiftHistory')
      ->group_by('userId')
      ->where('created_at >=', $dateLimit)
      ->order_by('coins', 'desc')
      ->limit(11)
      ->get()->result_array();

    foreach ($monthly as $key => $list) {

      $userInfo = $this->db->get_where('users', ['id' => $list['userId']])->row_array();

      $monthly[$key]['userInfo'] = $userInfo;
    }



    $overAll = $this->db->select_sum('coins')
      ->select('userId')
      ->from('userGiftHistory')
      ->group_by('userId')
      ->order_by('coins', 'desc')
      ->limit(11)
      ->get()->result_array();


    foreach ($overAll as $key => $list) {

      $userInfo = $this->db->get_where('users', ['id' => $list['userId']])->row_array();

      $overAll[$key]['userInfo'] = $userInfo;
    }


    if ($this->input->post('type') == '1') {
      if (empty($today)) {
        echo json_encode([
          'status' => 0,
          'message' => 'No Gifting Done Today'
        ]);
        exit;
      }
      echo json_encode([
        'status' => '1',
        'message' => 'top Gifter today',
        'details' => $today
      ]);
      exit;
    } else if ($this->input->post('type') == '2') {
      if (empty($weekly)) {

        echo json_encode([
          'status' => 0,
          'message' => 'No Gifting Done This Week'
        ]);
        exit;
      } else {

        echo json_encode([
          'status' => '1',
          'message' => 'top Gifter weekly',
          'details' => $weekly
        ]);
        exit;
      }
    } else if ($this->input->post('type') == '3') {

      if (empty($monthly)) {

        echo json_encode([
          'status' => '0',
          'message' => 'No Gifting Done This Month'
        ]);
        exit;
      } else {

        echo json_encode([
          'status' => '1',
          'message' => 'top Gifter monthly',
          'details' => $monthly
        ]);
        exit;
      }
    } else if ($this->input->post('type') == '4') {

      if (empty($overAll)) {

        echo json_encode([
          'status' => '0',
          'message' => 'No Gifting Done Overall'
        ]);
        exit;
      } else {

        echo json_encode([
          'status' => '1',
          'message' => 'top Gifter overAll',
          'details' => $overAll
        ]);
        exit;
      }
    } else {
      echo json_encode([
        'status' => '0',
        'message' => 'Enter valid Type'
      ]);
      exit;
    }
  }

  public function setFrame()
  {
    if ($this->input->post()) {

      $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if (empty($checkuser)) {

        echo json_encode([
          'status' => 0,
          'message' => 'inavlid userId'
        ]);
        exit;
      }

      $checkFrame = $this->db->get_where('Ep_admin_add_frames', ['id' => $this->input->post('frameId')])->row_array();

      if (empty($checkFrame)) {
        echo json_encode([
          'status' => 0,
          'message' => 'inavid FrameID'
        ]);
        exit;
      }

      $checkFramePurchase = $this->db->get_where('userPurchaseFram', ['framId' => $this->input->post('frameId'), 'userId' => $this->input->post('userId')])->row_array();

      if (empty($checkFramePurchase)) {

        echo json_encode([
          'status' => 0,
          'message' => 'frame not purchased'
        ]);
        exit;
      } else {

        if ($checkFramePurchase['expired'] < date('Y-m-d H:i:s')) {
          echo json_encode([
            'status' => 0,
            'message' => 'frame expired'
          ]);
          exit;
        }

        if ($checkuser['setFrameId'] == 0) {

          if ($this->db->set('setFrameId', $this->input->post('frameId'))->where('id', $this->input->post('userId'))->update('users')) {

            echo json_encode([
              'status' => 1,
              'message' => 'frame applied'
            ]);
            exit;
          } else {
            echo json_encode([
              'status' => 0,
              'message' => 'tech error'
            ]);
            exit;
          }
        } else {

          if ($this->db->set('setFrameId', $this->input->post('frameId'))->where('id', $this->input->post('userId'))->update('users')) {

            echo json_encode([
              'status' => 1,
              'message' => 'frame updated'
            ]);
            exit;
          } else {
            echo json_encode([
              'status' => 0,
              'message' => 'tech error'
            ]);
            exit;
          }
        }
      }
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid parameters'
      ]);
      exit;
    }
  }


  public function setGarage()
  {
    if ($this->input->post()) {

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if (empty($checkUser)) {
        echo json_encode([
          'status' => 0,
          'message' => 'inavlid userId'
        ]);
        exit;
      }

      $checkGarage = $this->db->get_where('garageData', ['id' => $this->input->post('garageId')])->row_array();

      if (empty($checkGarage)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid garageId'
        ]);
        exit;
      }

      $checkGaragePurchase = $this->db->get_where('userPurchaseGarage', ['userId' => $this->input->post('userId'), 'garageId' => $this->input->post('garageId')])->row_array();

      if (empty($checkGaragePurchase)) {
        echo json_encode([
          'status' => 0,
          'message' => 'garage not purchased'
        ]);
        exit;
      } else {
        if (date('Y-m-d') > $checkGaragePurchase['validTo']) {
          echo json_encode([
            'status' => 0,
            'message' => 'garage expired'
          ]);
          exit;
        }
      }

      if ($this->db->set('setGrageId', $this->input->post('garageId'))->where('id', $this->input->post('userId'))->update('users')) {
        echo json_encode([
          'status' => 1,
          'message' => 'garage applied'
        ]);
        exit;
      } else {
        echo json_encode([
          'status' => 0,
          'message' => 'tech error'
        ]);
        exit;
      }
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'eneter valid parameters'
      ]);
      exit;
    }
  }

  public function createFamily()
  {
    if ($this->input->post()) {

      $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if (empty($checkUser)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);
        exit;
      }

      // print_r($checkUser);

      if ($checkUser['my_level'] < 20) {
        echo json_encode([
          'status' => 0,
          'message' => 'user level 20 required'
        ]);
        exit;
      }

      $checkFamily = $this->db->get_where('family', ['adminId' => $this->input->post('userId')])->row_array();

      if (!!$checkFamily) {
        echo json_encode([
          'status' => 0,
          'message' => 'user can not create more than one family'
        ]);
        exit;
      }

      $data['adminId'] = $this->input->post('userId');
      $data['familyName'] = $this->input->post('familyName');
      $data['familyDescription'] = $this->input->post('familyDescription');
      if (!empty($_FILES["familyImage"]["name"])) {
        $data['familyImage'] = $this->uploadVideo($_FILES["familyImage"]);
      }
      $data['dateCreated'] = date('Y-m-d');

      $this->db->insert('family', $data);
      $id = $this->db->insert_id();
      $this->db->set(['familyPosition' => '1', 'familyId' => $id])->where('id', $this->input->post('userId'))->update('users');
      $memberData['userId'] = $this->input->post('userId');
      $memberData['familyId'] = $id;
      $memberData['positionType'] = '1';
      $memberData['date'] = date('Y-m-d');
      $this->db->insert('familyMembers', $memberData);
      $family = $this->db->get_where('family', ['id' => $id])->row_array();
      echo json_encode([
        'status' => 1,
        'message' => 'Family created',
        'details' => $family
      ]);
      exit;
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid parametrs'
      ]);
      exit;
    }
  }



  public function getNextLeveldetails()
  {

    // header("Access-Control-Allow-Methods: GET");
    if ($this->input->post()) {

      $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if (empty($checkuser)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);
        exit;
      }

      $get['image'] = $checkuser['image'];
      $get['myLevel'] = $checkuser['my_level'];
      $get['talentLevel'] = $checkuser['talent_level'];
      $get['recieveCoin'] = $checkuser['coin'];
      $get['experience'] = $checkuser['exp'];
      $get['sendCoin'] = $checkuser['total_send_coin'];

      $get['recieve'] = $this->db->get_where('setRecieverLevel', ['levelFrom' => $checkuser['talent_level']])->row_array();
      $get['send'] = $this->db->get_where('setsenderLevel', ['levelFrom' => $checkuser['my_level']])->row_array();


      http_response_code(200);
      echo json_encode([
        'status' => 1,
        'message' => 'level found',
        'details' => $get
      ]);
      exit;
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid parameters'
      ]);
      exit;
    }
  }

  public function getFamilyList()
  {
    $get = $this->db->get('family')->result_array();

    if (empty($get)) {
      echo json_encode([
        'status' => 0,
        'message' => 'Db empty'
      ]);
      exit;
    }

    $final = [];
    foreach ($get as $gets) {

      $id = $this->db->get_where('users', ['id' => $gets['adminId']])->row_array();
      $gets['adminId'] = $id['name'];
      $gets['adminDetails'] = $id;

      $final[] = $gets;
    }

    echo json_encode([
      'status' => 1,
      'message' => 'list found',
      'details' => $final
    ]);
    exit;
  }


  public function deleteFamily()
  {
    if ($this->input->post()) {

      $checkAdmin = $this->db->get_where('family', ['adminId' => $this->input->post('adminId')])->row_array();

      if (empty($checkAdmin)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid adminId'
        ]);
        exit;
      }

      $getFamily = $this->db->get_where('familyMembers', ['familyId' => $checkAdmin['id']])->result_array();

      foreach ($getFamily as $family) {

        $this->db->delete('familyMembers', ['id' => $family['id']]);
        $this->db->set(['familyPosition' => '0', 'familyId' => '0'])->where('id', $family['userId'])->update('users');
        $this->db->delete('familyJoinRequest', ['familyId' => $family['id']]);
      }

      if ($this->db->delete('family', ['adminId' => $this->input->post('adminId')])) {

        echo json_encode([
          'status' => 1,
          'message' => 'family deleted'
        ]);
        exit;
      } else {
        echo json_encode([
          'status' => 0,
          'message' => 'Database error'
        ]);
        exit;
      }
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid parameters'
      ]);
      exit;
    }
  }

  public function getFamilyDetails()
  {
    if ($this->input->post()) {

      $checkFamily = $this->db->get_where('family', ['id' => $this->input->post('familyId')])->row_array();

      if (empty($checkFamily)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid familyId'
        ]);
        exit;
      }

      $getFamilyusers = $this->db->get_where('familyMembers', ['familyId' => $this->input->post('familyId')])->result_array();

      $finaly = [];

      foreach ($getFamilyusers as $users) {
        $get = $this->db->get_where('users', ['id' => $users['userId']])->row_array();
        if ($get['familyPosition'] == '1') {
          $get['familyPosition'] = 'admin';
        } else {
          $get['familyPosition'] = 'member';
        }

        // print_r()

        $finaly[] = $get;
      }

      if (empty($finaly)) {
        echo json_encode([
          'status' => 0,
          'message' => 'no data found'
        ]);
        exit;
      }

      $final['familyDetails'] = $checkFamily;
      $final['memebers'] = $finaly;

      echo json_encode([
        'status' => 1,
        'message' => 'data found',
        'data' => $final
      ]);
      exit;
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid parameters'
      ]);
      exit;
    }
  }

  public function getEntryEffects()
  {
    $get = $this->db->get('entryEffectsData')->result_array();

    if (empty($get)) {
      echo json_encode([
        'status' => 0,
        'message' => 'no data found'
      ]);
      exit;
    }


    echo json_encode([
      'status' => 1,
      'message' => 'data found',
      'details' => $get
    ]);
    exit;
  }


  public function sendInvitation()
  {
    if ($this->input->post()) {

      if ($this->input->post('adminId') == $this->input->post('userId')) {
        echo json_encode([
          'status' => 0,
          'message' => "both id's can not be same"
        ]);
        exit;
      }

      $admin = $this->db->get_where('family', ['adminId' => $this->input->post('adminId')])->row_array();

      if (empty($admin)) {
        echo json_encode([
          'status' => 0,
          'message' => 'enter valid adminId'
        ]);
        exit;
      }

      $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if (empty($user)) {
        echo json_encode([
          'status' => 0,
          'message' => 'enter valid userId'
        ]);
        exit;
      }

      $checkFamilyJoin = $this->db->get_where('familyMembers', ['userId' => $this->input->post('userId')])->row_array();

      if (!!$checkFamilyJoin) {
        echo json_encode([
          'status' => 0,
          'message' => 'user already in a family'
        ]);
        exit;
      }

      $data['familyId'] = $admin['id'];
      $data['userId'] = $this->input->post('userId');
      $data['created'] = date('Y-m-d H:i:s');

      if ($this->db->insert('familyJoinRequest', $data)) {

        echo json_encode([
          'status' => 1,
          'message' => 'request sent'
        ]);
        exit;
      } else {
        echo json_encode([
          'status' => 0,
          'message' => 'tech error'
        ]);
        exit;
      }
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid parameters'
      ]);
      exit;
    }
  }

  public function getInvitation()
  {
    if ($this->input->post()) {

      $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if (empty($user)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);
        exit;
      }

      $getInvitation = $this->db->get_where('familyJoinRequest', ['userId' => $this->input->post('userId')])->result_array();

      if (empty($getInvitation)) {
        echo json_encode([
          'status' => 0,
          'message' => 'no invitations found'
        ]);
        exit;
      }

      $final = [];
      foreach ($getInvitation as $invitation) {
        $get = $this->db->get_where('family', ['id' => $invitation['familyId']])->row_array();

        if (!!$get) {
          $final[] = $get;
        }
      }

      if (empty($final)) {
        echo json_encode([
          'status' => 0,
          'message' => 'no data found'
        ]);
        exit;
      }

      echo json_encode([
        'status' => 1,
        'message' => 'data found',
        'details' => $final
      ]);
      exit;
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid parametrs'
      ]);
      exit;
    }
  }

  public function getAllUsers()
  {
    if ($this->input->post()) {

      $family = $this->db->get_where('family', ['id' => $this->input->post('familyid')])->row_array();

      if (empty($family)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid familyId'
        ]);
        exit;
      }

      $get = $this->db->get('users')->result_array();

      if (empty($get)) {
        echo json_encode([
          'status' => 0,
          'message' => 'no users found'
        ]);
        exit;
      }

      $final = [];
      foreach ($get as $gets) {

        // print_r($gets['id']);exit;

        $member = $this->db->get_where('familyMembers', ['userId' => $gets['id'], 'familyid' => $this->input->post('familyid')])->row_array();
        if (empty($member)) {

          $gets['memberStatus'] = false;
        } else {
          $gets['memberStatus'] = true;
        }

        $invite = $this->db->get_where('familyJoinRequest', ['userId' => $gets['id'], 'familyId' => $this->input->post('familyid')])->row_array();

        if (empty($invite)) {

          $gets['inviteStatus'] = false;
        } else {

          $gets['inviteStatus'] = true;
        }

        $final[] = $gets;
      }

      if (empty($final)) {
        echo json_encode([
          'status' => 0,
          'message' => 'no data found'
        ]);
        exit;
      }

      echo json_encode([
        'status' => 1,
        'message' => 'data found',
        'details' => $final
      ]);
      exit;
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'enter valid parameters'
      ]);
      exit;
    }
  }

  public function getUserFamilyDetails()
  {
    if ($this->input->post()) {

      $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

      if (empty($user)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);
        exit;
      }

      if ($user['familyId'] == '0') {
        echo json_encode([
          'status' => 0,
          'message' => 'user not in any family'
        ]);
        exit;
      }

      $family = $this->db->get_where('family', ['id' => $user['familyId']])->row_array();

      if (empty($family)) {
        echo json_encode([
          'status' => 0,
          'message' => 'family not found'
        ]);
        exit;
      }

      $getMembers = $this->db->get_where('familyMembers', ['familyId' => $user['familyId']])->result_array();

      $final = [];
      foreach ($getMembers as $members) {

        $getuser = $this->db->get_where('users', ['id' => $members['userId']])->row_array();

        if ($members['positionType'] == '1') {
          $getuser['position'] = 'admin';
        } else {
          $getuser['position'] = 'member';
        }

        $final[] = $getuser;
      }

      $family['members'] = $final;

      // print_r($family);

      echo json_encode([
        'status' => 1,
        'message' => 'family found',
        'details' => $family
      ]);
      exit;
    } else {

      echo json_encode([
        'status' => 0,
        'message' => 'enter valid parameters'
      ]);
      exit;
    }
  }

  public function notify()
  {

    $regId = 'e5ZHsVkTQcC5VChs0EY6V8:APA91bEmGgbbt1krNZ4OevEbI78OByY4nriZkEDbLBqbhg_wYWFanM0b9KTIPJgi_wfOmVaqCkKkNUZjN2PF9iisjVuMnGLISjzFHc3uIrUuLcxzb_a3GsQRxv1qi542Ej-kh7i9C-cW';
    $message = 'Jai shree RAM';
    $title = 'test';
    $type = 'live';
    $imgpath = 'https://healthvideos12.s3.us-west-2.amazonaws.com/1672124324IMG-20221227-WA0001.jpg';


    pushNotification($regId, $message, $title, $type, $imgpath);
  }

  public function messageNotify()
  {

    if ($this->input->post()) {
      $sender = $this->db->get_where('users', ['id' => $this->input->post('senderId')])->row_array();
      if (empty($sender)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid senderId'
        ]);
        exit;
      }

      $reciever = $this->db->get_where('users', ['id' => $this->input->post('recieverId')])->row_array();
      if (empty($reciever)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid recieverId'
        ]);
        exit;
      }

      $regId = $reciever['reg_id'];
      $title = $sender['name'];
      $message = $sender['username'] . ' sent you message';
      $type = 'mess';
      $imgpath = $sender['image'];


      if (pushNotification($regId, $message, $title, $type, $imgpath)) {
        echo json_encode([
          'status' => 1,
          'message' => 'notification sent'
        ]);
        exit;
      } else {
        echo json_encode([
          'status' => 0,
          'message' => 'notification failed'
        ]);
        exit;
      }
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'method not allowed'
      ]);
      exit;
    }
  }

  public function searchHistory()
  {
    if ($this->input->post()) {

      $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
      if (empty($user)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);
        exit;
      }

      $searchUser = $this->db->get_where('users', ['id' => $this->input->post('searchUserId')])->row_array();
      if (empty($searchUser)) {
        echo json_encode([
          'status' => 0,
          'message' => 'invalid searchUserId'
        ]);
        exit;
      }

      $getSearch = $this->db->get_where('searchhistory', ['userId' => $this->input->post('userId'), 'searchUserId' => $this->input->post('searchUserId')])->row_array();
      if (!empty($getSearch)) {
        echo json_encode([
          'status' => 0,
          'message' => 'already searched'
        ]);
        exit;
      } else {

        $data['userId'] = $user['id'];
        $data['searchUserId'] = $searchUser['id'];
        $data['date'] = date('Y-m-d H:i:s');

        if ($this->db->insert('searchhistory', $data)) {

          echo json_encode([
            'status' => 1,
            'message' => 'user searched'
          ]);
          exit;
        } else {

          echo json_encode([
            'status' => 0,
            'message' => 'DB error'
          ]);
          exit;
        }
      }
    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'method not allowed'
      ]);
      exit;
    }
  }


  public function getSearchHistory()
  {
    if ($this->input->post()) {

      $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
      if (empty($user)) {
        echo json_encode([
          'status' => 0,
          'message' => 'inavlid userId'
        ]);
        exit;
      }

      $get = $this->db->select('searchhistory.*, users.*')
        ->from('searchhistory')
        ->join('users', 'users.id = searchhistory.searchUserId', 'left')
        ->where('searchhistory.userId', $this->input->post('userId'))
        ->order_by('searchhistory.id', 'desc')
        ->get()->result_array();

      if (empty($get)) {
        echo json_encode([
          'status' => 0,
          'message' => 'no search history found'
        ]);
        exit;
      } else {
        echo json_encode([
          'status' => 1,
          'message' => 'search history found',
          'details' => $get
        ]);
        exit;
      }

    } else {
      echo json_encode([
        'status' => 0,
        'message' => 'Method not allowed'
      ]);
      exit;
    }
  }



  public function my_achievements(){
    if($this->input->post()){

      $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
      if(empty($user)){
        echo json_encode([
          'status' => 0,
          'message' => 'invalid userId'
        ]);exit;
      }

      $get = $this->db->get('achievment_list')->result_array();
      if(empty($get)){
        echo json_encode([
          'status' => 0,
          'message' => 'no achievements from admin'
        ]);exit;
      }

      $final = [];
      foreach($get as $gets){

        $gets['achieved'] = false;
        $check = $this->db->get_where('achievment_to_user', ['achievment_id' => $gets['id'], 'user_id' => $user['id']])->row_array();
        if(!!$check){
          $gets['achieved'] = true;
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
        'message' => 'Method not allowed'
      ]);exit;
    }
  }

















}
