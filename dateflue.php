<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Razorpay\Api\Api;
use Aws\S3\S3Client;
use Twilio\Rest\Client;

require APPPATH . '/libraries/razorpay-php/Razorpay.php';


class DateFlue extends CI_Controller
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



	/**
	 * userLogin with username & password
	 */

	public function userLoginNc()
	{
		if ($this->input->post()) {
			$emailPhone = $this->input->post('username');
			$password = md5($this->input->post('password'));
			$checkPhone = $this->db->query("SELECT * FROM users where password = '$password' and username = '$emailPhone'")->row_array();

			if (!empty($checkPhone)) {

				$datas = array('reg_id' => $this->input->post('reg_id'), 'device_type' => $this->input->post('device_type'), 'deviceId' => $this->input->post('deviceId'));
				$update = $this->db->update('users', $datas, array('id' => $checkPhone['id']));
				$datas1 = $this->db->get_where('users', array('id' => $checkPhone['id']))->row_array();

				$message = array(
					'success' => '1',
					'message' => 'user login successfully',
					'details' => $datas1
				);
			} else {
				$message = array(
					'success' => '0',
					'message' => 'Please enter valid login credentials!'
				);
			}
		} else {
			$message = array(
				'success' => '0',
				'message' => 'please enter parameters'
			);
		}
		echo json_encode($message);
	}

	public function getCountries()
	{

		$getCountries = $this->db->get("countries")->result_array();
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
		echo 	$url = $upload->get('ObjectURL');
	}



	public function uploadVideosNew()
	{

		// print_r($_FILES['videoPath']);
		// echo "hiii";
		// die;


		require APPPATH . '/libraries/vendor/autoload.php';

		$checkUser = $this->db->get_where("users", ['id' => $this->input->post('userId')])->row_array();

		if (empty($checkUser)) {

			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid userId",
			]);
			exit;
		}

		if ($this->input->post('userId') == null || $this->input->post('hashTag') == null || $this->input->post('allowDownloads') == null || $this->input->post('description') == null || $this->input->post('allowComment') == null || $this->input->post('allowDuetReact') == null || $this->input->post('viewVideo') == null || $_FILES['videoPath'] == null || $_FILES['thumbnail'] == null) {
			echo json_encode([

				"success" => "0",
				"message" => "Param cannot be null!"
			]);
			exit;
		}

		if ($_FILES['videoPath']['error'] == '1') {

			echo json_encode([

				"success" => "0",
				"message" => "videoPath not found!"
			]);
			exit;
		}


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
		$data['status'] = '1';
		$data['viewVideo']  = $this->input->post('viewVideo');
		$data['created'] = date('Y-m-d H:i:s');

		$s3 = new Aws\S3\S3Client([
			'version' => 'latest',
			'region'  => 'ap-south-1',
			'credentials' => [
				'key'    => 'AKIAUMUJK362A7DE4CUK',
				'secret' => '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'
			]
		]);
		$bucket = 'dateflulamp';

		$upload = $s3->upload($bucket, $_FILES['videoPath']['name'], fopen($_FILES['videoPath']['tmp_name'], 'rb'), 'public-read');
		$url = $upload->get('ObjectURL');
		if (!empty($url)) {
			$data['videoPath'] = 'https://dateflulamp.s3.ap-south-1.amazonaws.com/' . $_FILES['videoPath']['name'];
			$data['downloadPath'] = 'https://dateflulamp.s3.ap-south-1.amazonaws.com/' . $_FILES['videoPath']['name'];
		} else {
			$data['videoPath'] = '';
			$data['downloadPath'] = '';
		}

		$upload2 = $s3->upload($bucket, $_FILES['thumbnail']['name'], fopen($_FILES['thumbnail']['tmp_name'], 'rb'), 'public-read');
		$url2 = $upload2->get('ObjectURL');
		if (!empty($url2)) {
			$data['thumbnail'] = 'https://dateflulamp.s3.ap-south-1.amazonaws.com/' . $_FILES['thumbnail']['name'];
		} else {
			$data['thumbnail'] = '';
		}

		$insert = $this->db->insert('userVideos', $data);
		if (!empty($insert)) {
			$vIDs = $this->db->insert_id();

			$getDetails = $this->db->get_where("userVideos", ['id' => $vIDs])->row_array();

			$uiD = $getDetails['userId'];
			$this->db->set('addVideoCounts', 'addVideoCounts +1', false)->where('id', $uiD)->update("users");


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

	public function removeVideos()
	{

		$checVideo = $this->db->get_where("userVideos", ['id' => $this->input->post('mediaId'), 'userId' => $this->input->post('userId')])->row_array();

		if (empty($checVideo)) {

			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid mediaId & userId",
			]);
			exit;
		}


		$remove = $this->db->delete("userVideos", ['id' => $this->input->post('mediaId'), 'userId' => $this->input->post('userId')]);

		if ($remove == true) {

			echo json_encode([

				"success" => "1",
				"message" => "video removed",
			]);
			exit;
		} else {
			echo json_encode([

				"success" => "0",
				"message" => "Something went wrong!",
			]);
			exit;
		}
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


		// 		$add_username_gif = "convert ".$watermark." \
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




	public function otpTestingMall()
	{

		$curl = curl_init();

		$phone = "+917901759085";
		$otp =  "123456";
		$message12 = "Hi Simran, OTP Testing Twilio account " . $otp;


		$a = $phone;
		//require dirname(dirname(dirname(_FILE_))).'/libraries/twilio-php-master/Twilio/autoload.php';
		//require APPPATH.'/libraries/twilio/twilio-php-master/Twilio/autoload.php';
		require APPPATH . '/libraries/twilio-php-master/Twilio/autoload.php';
		$sid    = "AC28cbb8b04a32be13f3f97e165452c1a7";
		$token  = "5091b9d944422d906bcbd4c7c268e1a7";
		$twilio = new Client($sid, $token);
		$message23 = $twilio->messages
			->create(
				$a, // to
				array(
					"from" => "+16182055887",
					"body" =>  $message12,
				)
			);

		print_r($message23);
		die;
	}

	public function pawan()
	{
		$this->load->view('liveVideo');
	}

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
			$lists =  $this->db->query("select users.username,users.image,users.phone,users.name,gift.title as giftTitle,gift.primeAccount as giftCoin,concat('$url',gift.image) as giftImage		, userGiftHistory.userId,userGiftHistory.created from userGiftHistory left JOIN users on users.id = userGiftHistory.userId left join gift on gift.id = userGiftHistory.giftId where userGiftHistory.giftUserId = $userId")->result_array();
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
	// 	 $data['userId'] = $this->input->post('userId');
	// 	 $data['giftUserId'] = $this->input->post('giftUserId');
	//   $data['giftId'] = $this->input->post('giftId');
	// 	 $data['coin'] = $this->input->post('coin');
	// 	 $data['created'] = date('Y-m-d H:i:s');
	// 	 $insert = $this->db->insert('userGiftHistory',$data);
	// 	 if(!empty($insert)){
	// 		 $loginUserDetails = $this->db->get_where('users',array('id' => $this->input->post('userId')))->row_array();
	//       $expCoin = $loginUserDetails['expCoin'];
	// 		 $loginUpdateCoin['purchasedCoin'] = $loginUserDetails['purchasedCoin'] - $this->input->post('coin');
	//       $calcuLateExpCoin = $this->input->post('coin') * 5;
	//      $loginUpdateCoin['expCoin'] = $expCoin + $calcuLateExpCoin;
	//      $allExpCoin = $loginUpdateCoin['expCoin'];
	//      $levalList  =  $this->db->query("SELECT * FROM leval WHERE expCount BETWEEN 300 AND $allExpCoin order by id desc limit 1")->row_array();
	//      $loginUpdateCoin['leval'] = $levalList['leval'];
	// 		 $this->Common_Model->update('users',$loginUpdateCoin,'id',$this->input->post('userId'));


	// 		 $giftUserDetails = $this->db->get_where('users',array('id' => $this->input->post('giftUserId')))->row_array();
	//      $expCoin1 = $giftUserDetails['expCoin'];
	// 		 $giftUserUpdate['coin'] = $giftUserDetails['coin'] + $this->input->post('coin');
	//      $calcuLateExpCoin1 = $this->input->post('coin') * 3;
	//      $giftUserUpdate['expCoin'] = $expCoin1 + $calcuLateExpCoin1;
	//      $allExpCoin1 = $giftUserUpdate['expCoin'];
	//      $levalList1  =  $this->db->query("SELECT * FROM leval WHERE expCount BETWEEN 300 AND $allExpCoin1 order by id desc limit 1")->row_array();
	//      $giftUserUpdate['leval'] = $levalList1['leval'];
	// 		 $this->Common_Model->update('users',$giftUserUpdate,'id',$this->input->post('giftUserId'));


	// 		 $regId = $giftUserDetails['reg_id'];
	//      if(!empty($loginUserDetails['name'])){
	//       $manavName = $loginUserDetails['name'];
	//      }
	//      else{
	//       $manavName = $loginUserDetails['username'];
	//      }
	// 		 $mess = 'You received a gift from '.$manavName;
	//      $purchasedCoinstotal = $giftUserDetails['purchasedCoin'];
	//      $receivedCointotal = $giftUserUpdate['coin'];
	// 		 $this->giftNotification($regId,$mess,'gift',$this->input->post('userId'),$this->input->post('giftUserId'),$purchasedCoinstotal,$receivedCointotal);

	// 		 $notiMess['loginId'] = $this->input->post('userId');
	// 		 $notiMess['userId'] = $this->input->post('giftUserId');
	// 		 $notiMess['message'] = $mess;
	// 		 $notiMess['type'] = 'gift';
	// 		 $notiMess['notiDate'] = date('Y-m-d');
	// 		 $notiMess['created'] = date('Y-m-d H:i:s');
	// 		 $this->db->insert('userNotification',$notiMess);


	//      $outMess['myLevel'] =  $loginUpdateCoin['leval'] ;
	//      $outMess['liveLevel'] =  $giftUserUpdate['leval'] ;
	//      $outMess['myStar'] =  '0';
	//      $outMess['liveStar'] =  '0' ;
	// 		 $message['success'] = '1';
	// 		 $message['message'] = 'Gift send successfully';
	//      $message['details'] = $outMess;
	// 	 }
	// 	 else{
	// 		 $message['success'] = '0';
	// 		 $message['message'] = 'Please try after some time';
	// 	 }
	// 	 echo json_encode($message);
	//  }



	//  public function sendLiveGift(){
	// 	 $data['userId'] = $this->input->post('userId');
	// 	 $data['giftUserId'] = $this->input->post('giftUserId');
	// 	 $data['giftId'] = $this->input->post('giftId');
	//   	 $data['coin'] = $this->input->post('coin');
	// 	 $data['type'] = 1;


	// 	// setting send coin check

	// 	$coinLimitCheck = $data['coin'];
	// 	if($coinLimitCheck > '2000'){
	// 			$message['success'] = '0';
	// 			$message['message'] = 'user exceeding the coin limit of 2000 coins';
	// 			echo json_encode($message);

	// 	}else{

	// 		if(!empty($this->input->post('pkHostId'))){
	// 			$data['pkHostId'] = $this->input->post('pkHostId');
	// 		  }
	// 		if(!empty($this->input->post('liveId'))){
	// 			$data['liveId'] = $this->input->post('liveId');
	// 		  }
	// 		  $data['created'] = date('Y-m-d H:i:s');
	// 		  $insert = $this->db->insert('userGiftHistory',$data);

	// 		  if(!empty($insert)){


	// 			// getting user send coin details and setting user level and tallent level
	// 			$getUserCoin = $this->db->select(['total_send_coin', 'my_level'])
	// 			->from('users')
	// 			->where('id' , $data['userId'])
	// 			->get()
	// 			->row_array();


	// 			$coin = $data['coin'];
	// 			$oldcoin = $getUserCoin['total_send_coin'];
	// 			$level = $getUserCoin['my_level'];

	// 			$userCoinAmount = $coin + $oldcoin;

	// 			$updateUserSendCoin = $this->db->set(['total_send_coin' => $userCoinAmount])
	// 			->where(['id' => $data['userId']])
	// 			->update('users');


	// 			//updating user level and getting level details form user_level tables
	// 			$level += 1;
	// 			$getLevelDetails = $this->db->select('experience')
	// 										->from('user_levels')
	// 										->where('level', $level)
	// 										->get()->row_array();
	// 			$experience = $getLevelDetails['experience'];

	// 			if($userCoinAmount >= $experience){
	// 				$updateUserLevel = $this->db->set(['my_level' => $level])
	// 											->where(['id' => $data['userId']])
	// 											->update('users');
	// 			}


	// 			// updating user talent level

	// 			$getUserTalentCoin = $this->db->select(['coin', 'talent_level'])
	// 			->from('users')
	// 			->where('id' , $data['giftUserId'])
	// 			->get()
	// 			->row_array();

	// 			$newUserCoin = $data['coin'];
	// 			$oldUserCoin = $getUserTalentCoin['coin'];
	// 			$talentLevel = $getUserTalentCoin['talent_level'];
	// 			$totalTalentCoin = $newUserCoin + $oldUserCoin;

	// 			$talentLevel += 1;
	// 			$getTalentLevelDetails = $this->db->select('experince')
	// 											  ->from('user_talent_levels')
	// 											  ->where('level', $talentLevel)
	// 											  ->get()->row_array();

	// 			$talentExperience = $getTalentLevelDetails['experince'];

	// 			if($totalTalentCoin >= $talentExperience){
	// 				$updateUserLevel = $this->db->set(['talent_level' => $talentLevel])
	// 											->where(['id' => $data['giftUserId']])
	// 											->update('users');
	// 			}

	// 		  if(!empty($this->input->post('pkHostId'))){
	// 			$checkPkHis = $this->db->get_where('pkHostLiveGift',array('pkHostId' => $this->input->post('pkHostId'),'giftUserId' => $this->input->post('giftUserId')))->row_array();
	// 			if(empty($checkPkHis)){
	// 			  $insPKHOST['pkHostId'] = $this->input->post('pkHostId');
	// 			  $insPKHOST['giftUserId'] = $this->input->post('giftUserId');
	// 			  $insPKHOST['coin'] = $this->input->post('coin');
	// 			  $this->db->insert('pkHostLiveGift',$insPKHOST);
	// 			}
	// 			else{
	// 			  $insPKHOST['pkHostId'] = $this->input->post('pkHostId');
	// 			  $insPKHOST['giftUserId'] = $this->input->post('giftUserId');
	// 			  $insPKHOST['coin'] = $this->input->post('coin') + $checkPkHis['coin'];
	// 			  $this->Common_Model->update('pkHostLiveGift',$insPKHOST,'id',$checkPkHis['id']);
	// 			}
	// 		  }
	// 		  $todayD = date('Y-m-d');
	// 		  $checkStar = $this->db->get_where('userStar',array('userId' => $this->input->post('giftUserId'),'created' => $todayD))->row_array();
	// 		  if(!empty($checkStar)){
	// 			$starCount = $this->input->post('coin') + $checkStar['starCount'];
	// 			$checkStarLevel  =  $this->db->query("SELECT * FROM starList WHERE starCount BETWEEN 1 AND $starCount order by id desc limit 1")->row_array();
	// 			$insStart['userId'] = $this->input->post('giftUserId');
	// 			$insStart['starCount'] = $starCount;
	// 			if(!empty($checkStarLevel)){
	// 			  $insStart['star'] = $checkStarLevel['star'];
	// 			}
	// 			else{
	// 			  $insStart['star'] = '0';
	// 			}
	// 			$insStart['created'] = date('Y-m-d');
	// 			$this->Common_Model->update('userStar',$insStart,'id',$checkStar['id']);
	// 		  }
	// 		  else{
	// 			$starCount = $this->input->post('coin');
	// 			$checkStarLevel  =  $this->db->query("SELECT * FROM starList WHERE starCount BETWEEN 1 AND $starCount order by id desc limit 1")->row_array();
	// 			$insStart['userId'] = $this->input->post('giftUserId');
	// 			$insStart['starCount'] = $this->input->post('coin');
	// 			if(!empty($checkStarLevel)){
	// 			  $insStart['star'] = $checkStarLevel['star'];
	// 			}
	// 			else{
	// 			  $insStart['star'] = '0';
	// 			}
	// 			$insStart['created'] = date('Y-m-d');
	// 			$this->db->insert('userStar',$insStart);
	// 		  }



	// 			  $loginUserDetails = $this->db->get_where('users',array('id' => $this->input->post('userId')))->row_array();
	// 		   $expCoin = $loginUserDetails['expCoin'];
	// 			  $loginUpdateCoin['purchasedCoin'] = $loginUserDetails['purchasedCoin'] - $this->input->post('coin');
	// 		   $calcuLateExpCoin = $this->input->post('coin') * 5;
	// 		  $loginUpdateCoin['expCoin'] = $expCoin + $calcuLateExpCoin;
	// 		  $allExpCoin = $loginUpdateCoin['expCoin'];
	// 		  $levalList  =  $this->db->query("SELECT * FROM leval WHERE expCount BETWEEN 300 AND $allExpCoin order by id desc limit 1")->row_array();
	// 		  $loginUpdateCoin['leval'] = $levalList['leval'];
	// 			  $this->Common_Model->update('users',$loginUpdateCoin,'id',$this->input->post('userId'));


	// 			  $giftUserDetails = $this->db->get_where('users',array('id' => $this->input->post('giftUserId')))->row_array();
	// 		  $expCoin1 = $giftUserDetails['expCoin'];
	// 			  $giftUserUpdate['coin'] = $giftUserDetails['coin'] + $this->input->post('coin');
	// 		  $calcuLateExpCoin1 = $this->input->post('coin') * 3;
	// 		  $giftUserUpdate['expCoin'] = $expCoin1 + $calcuLateExpCoin1;
	// 		  $allExpCoin1 = $giftUserUpdate['expCoin'];
	// 		  $levalList1  =  $this->db->query("SELECT * FROM leval WHERE expCount BETWEEN 300 AND $allExpCoin1 order by id desc limit 1")->row_array();
	// 		  $giftUserUpdate['leval'] = $levalList1['leval'];
	// 			  $this->Common_Model->update('users',$giftUserUpdate,'id',$this->input->post('giftUserId'));


	// 			  $regId = $giftUserDetails['reg_id'];
	// 		  if(!empty($loginUserDetails['name'])){
	// 			$manavName = $loginUserDetails['name'];
	// 		  }
	// 		  else{
	// 			$manavName = $loginUserDetails['username'];
	// 		  }
	// 			  $mess = 'You received a gift from '.$manavName;
	// 		  $purchasedCoinstotal = $giftUserDetails['purchasedCoin'];
	// 		  $receivedCointotal = $giftUserUpdate['coin'];
	// 			  $this->giftNotification($regId,$mess,'gift',$this->input->post('userId'),$this->input->post('giftUserId'),$purchasedCoinstotal,$receivedCointotal);

	// 			  $notiMess['loginId'] = $this->input->post('userId');
	// 			  $notiMess['userId'] = $this->input->post('giftUserId');
	// 			  $notiMess['message'] = $mess;
	// 			  $notiMess['type'] = 'gift';
	// 			  $notiMess['notiDate'] = date('Y-m-d');
	// 			  $notiMess['created'] = date('Y-m-d H:i:s');
	// 			  $this->db->insert('userNotification',$notiMess);
	// 		  $todyDD = date('Y-m-d');
	// 		  $checkStarStatus1 = $this->db->get_where('userStar',array('userId' => $this->input->post('userId'),'created' => $todyDD))->row_array();
	// 		  if(!empty($checkStarStatus1)){
	// 			$starStatus1 = $checkStarStatus1['star'];
	// 			if($starStatus != 0){
	// 			  $checkBoxCount1 = $this->db->get_where('starList',array('star' => $starStatus1))->row_array();
	// 			  $myBox = $checkBoxCount1['box'];
	// 			}
	// 			else{
	// 			  $myBox = 0;
	// 			}
	// 		  }
	// 		  else{
	// 			$starStatus1 = '0';
	// 			$myBox = 0;
	// 		  }

	// 		  $checkStarStatus = $this->db->get_where('userStar',array('userId' => $this->input->post('giftUserId'),'created' => $todyDD))->row_array();
	// 		  if(!empty($checkStarStatus)){
	// 			$starStatus = $checkStarStatus['star'];
	// 			if($starStatus != 0){
	// 			  $checkBoxCount = $this->db->get_where('starList',array('star' => $starStatus))->row_array();
	// 			  $liveBox = $checkBoxCount['box'];
	// 			}
	// 			else{
	// 			  $liveBox = 0;
	// 			}

	// 		  }
	// 		  else{
	// 			$starStatus = '0';
	// 			$liveBox = 0;
	// 		  }

	// 		  $outMess['myLevel'] =  $loginUpdateCoin['leval'] ;
	// 		  $outMess['liveLevel'] =  $giftUserUpdate['leval'] ;
	// 		  $outMess['myStar'] =  $starStatus1;
	// 		  $outMess['myBox'] = (string)$myBox;
	// 		  $outMess['liveStar'] =  $starStatus ;
	// 		  $outMess['liveBox'] =  (string)$liveBox ;
	// 			  $message['success'] = '1';
	// 			  $message['message'] = 'Gift send successfully';
	// 		      $message['details'] = $outMess;
	// 		  }
	// 		  else{
	// 			  $message['success'] = '0';
	// 			  $message['message'] = 'Please try after some time';
	// 		  }
	// 		  echo json_encode($message);

	// 	}


	//  }

	public function sendLiveGift()
	{

		$data['userId'] = $this->input->post('userId');
		$data['giftUserId'] = $this->input->post('giftUserId');
		$data['giftId'] = $this->input->post('giftId');
		$data['coin'] = $this->input->post('coin');
		$data['type'] = 1;


		// setting send coin check

		$coinLimitCheck = $data['coin'];
		// if($coinLimitCheck > '2000'){
		// 		$message['success'] = '0';
		// 		$message['message'] = 'user exceeding the coin limit of 2000 coins';
		// 		echo json_encode($message);

		// }

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
		$data['created'] = date('Y-m-d H:i:s');
		$insert = $this->db->insert('userGiftHistory', $data);

		if (!empty($insert)) {


			if (!empty($this->input->post('pkHostId'))) {
				$checkPkHis = $this->db->get_where('pkHostLiveGift', array('pkHostId' => $this->input->post('pkHostId'), 'giftUserId' => $this->input->post('giftUserId')))->row_array();
				if (empty($checkPkHis)) {
					$insPKHOST['pkHostId'] = $this->input->post('pkHostId');
					$insPKHOST['giftUserId'] = $this->input->post('giftUserId');
					$insPKHOST['coin'] = $this->input->post('coin');
					$this->db->insert('pkHostLiveGift', $insPKHOST);
				} else {
					$insPKHOST['pkHostId'] = $this->input->post('pkHostId');
					$insPKHOST['giftUserId'] = $this->input->post('giftUserId');
					$insPKHOST['coin'] = $this->input->post('coin') + $checkPkHis['coin'];
					$this->Common_Model->update('pkHostLiveGift', $insPKHOST, 'id', $checkPkHis['id']);
				}
			}
			$todayD = date('Y-m-d');
			$checkStar = $this->db->get_where('userStar', array('userId' => $this->input->post('giftUserId'), 'created' => $todayD))->row_array();
			if (!empty($checkStar)) {
				$starCount = $this->input->post('coin') + $checkStar['starCount'];
				$checkStarLevel  =  $this->db->query("SELECT * FROM starList WHERE starCount BETWEEN 1 AND $starCount order by id desc limit 1")->row_array();
				$insStart['userId'] = $this->input->post('giftUserId');
				$insStart['starCount'] = $starCount;
				if (!empty($checkStarLevel)) {
					$insStart['star'] = $checkStarLevel['star'];
				} else {
					$insStart['star'] = '0';
				}
				$insStart['created'] = date('Y-m-d');
				$this->Common_Model->update('userStar', $insStart, 'id', $checkStar['id']);
			} else {
				$starCount = $this->input->post('coin');
				$checkStarLevel  =  $this->db->query("SELECT * FROM starList WHERE starCount BETWEEN 1 AND $starCount order by id desc limit 1")->row_array();
				$insStart['userId'] = $this->input->post('giftUserId');
				$insStart['starCount'] = $this->input->post('coin');
				if (!empty($checkStarLevel)) {
					$insStart['star'] = $checkStarLevel['star'];
				} else {
					$insStart['star'] = '0';
				}
				$insStart['created'] = date('Y-m-d');
				$this->db->insert('userStar', $insStart);
			}



			$loginUserDetails = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
			$expCoin = $loginUserDetails['expCoin'];
			if ($this->input->post('coin') > $loginUserDetails['purchasedCoin']) {

				echo json_encode([
					'success' => '0',
					'message' => 'Gift Can not sent due to Insufficient Balance'
				]);
				exit;
			}

			// getting user send coin details and setting user level and tallent level
			$getUserCoin = $this->db->select(['total_send_coin', 'my_level'])
				->from('users')
				->where('id', $data['userId'])
				->get()
				->row_array();


			$coin = $data['coin'];
			$oldcoin = $getUserCoin['total_send_coin'];
			$level = $getUserCoin['my_level'];

			$userCoinAmount = $coin + $oldcoin;

			$updateUserSendCoin = $this->db->set(['total_send_coin' => $userCoinAmount])
				->where(['id' => $data['userId']])
				->update('users');


			//updating user level and getting level details form user_level tables
			$level += 1;
			$getLevelDetails = $this->db->select('experince')
				->from('user_levels')
				->where('level', $level)
				->get()->row_array();
			$experience = $getLevelDetails['experience'];

			if ($userCoinAmount >= $experience) {
				$updateUserLevel = $this->db->set(['my_level' => $level])
					->where(['id' => $data['userId']])
					->update('users');
			}


			// updating user talent level

			$getUserTalentCoin = $this->db->select(['coin', 'talent_level'])
				->from('users')
				->where('id', $data['giftUserId'])
				->get()
				->row_array();

			$newUserCoin = $data['coin'];
			$oldUserCoin = $getUserTalentCoin['coin'];
			$talentLevel = $getUserTalentCoin['talent_level'];
			$totalTalentCoin = $newUserCoin + $oldUserCoin;

			$talentLevel += 1;
			$getTalentLevelDetails = $this->db->select('experince')
				->from('user_talent_levels')
				->where('level', $talentLevel)
				->get()->row_array();

			$talentExperience = $getTalentLevelDetails['experince'];

			if ($totalTalentCoin >= $talentExperience) {
				$updateUserLevel = $this->db->set(['talent_level' => $talentLevel])
					->where(['id' => $data['giftUserId']])
					->update('users');
			}


			$loginUpdateCoin['purchasedCoin'] = $loginUserDetails['purchasedCoin'] - $this->input->post('coin');
			$calcuLateExpCoin = $this->input->post('coin') * 5;
			$loginUpdateCoin['expCoin'] = $expCoin + $calcuLateExpCoin;
			$allExpCoin = $loginUpdateCoin['expCoin'];
			$levalList  =  $this->db->query("SELECT * FROM leval WHERE expCount BETWEEN 300 AND $allExpCoin order by id desc limit 1")->row_array();
			$loginUpdateCoin['leval'] = $levalList['leval'];
			$this->Common_Model->update('users', $loginUpdateCoin, 'id', $this->input->post('userId'));


			$myLevel = $this->db->select('my_level, talent_level')->from('users')->where('id', $this->input->post('giftUserId'))->get()->row_array();


			$giftUserDetails = $this->db->get_where('users', array('id' => $this->input->post('giftUserId')))->row_array();
			$expCoin1 = $giftUserDetails['expCoin'];
			$giftUserUpdate['coin'] = $giftUserDetails['coin'] + $this->input->post('coin');
			$calcuLateExpCoin1 = $this->input->post('coin') * 3;
			$giftUserUpdate['expCoin'] = $expCoin1 + $calcuLateExpCoin1;
			$allExpCoin1 = $giftUserUpdate['expCoin'];
			$levalList1  =  $this->db->query("SELECT * FROM leval WHERE expCount BETWEEN 300 AND $allExpCoin1 order by id desc limit 1")->row_array();
			$giftUserUpdate['leval'] = $levalList1['leval'];
			$this->Common_Model->update('users', $giftUserUpdate, 'id', $this->input->post('giftUserId'));


			$regId = $giftUserDetails['reg_id'];
			if (!empty($loginUserDetails['name'])) {
				$manavName = $loginUserDetails['name'];
			} else {
				$manavName = $loginUserDetails['username'];
			}
			$mess = 'You received a gift from ' . $manavName;
			$purchasedCoinstotal = $giftUserDetails['purchasedCoin'];
			$receivedCointotal = $giftUserUpdate['coin'];
			$this->giftNotification($regId, $mess, 'gift', $this->input->post('userId'), $this->input->post('giftUserId'), $purchasedCoinstotal, $receivedCointotal);

			$notiMess['loginId'] = $this->input->post('userId');
			$notiMess['userId'] = $this->input->post('giftUserId');
			$notiMess['message'] = $mess;
			$notiMess['type'] = 'gift';
			$notiMess['notiDate'] = date('Y-m-d');
			$notiMess['created'] = date('Y-m-d H:i:s');
			$this->db->insert('userNotification', $notiMess);
			$todyDD = date('Y-m-d');


			//   $checkStarStatus1 = $this->db->get_where('userStar',array('userId' => $this->input->post('userId'),'created' => $todyDD))->row_array();
			//   if(!empty($checkStarStatus1)){
			// 	$starStatus1 = $checkStarStatus1['star'];
			// 	if($starStatus != 0){
			// 	  $checkBoxCount1 = $this->db->get_where('starList',array('star' => $starStatus1))->row_array();
			// 	  $myBox = $checkBoxCount1['box'];
			// 	}
			// 	else{
			// 	  $myBox = 0;
			// 	}
			//   }
			//   else{
			// 	$starStatus1 = '0';
			// 	$myBox = 0;
			//   }

			//   $checkStarStatus = $this->db->get_where('userStar',array('userId' => $this->input->post('giftUserId'),'created' => $todyDD))->row_array();
			//   if(!empty($checkStarStatus)){
			// 	$starStatus = $checkStarStatus['star'];
			// 	if($starStatus != 0){
			// 	  $checkBoxCount = $this->db->get_where('starList',array('star' => $starStatus))->row_array();
			// 	  $liveBox = $checkBoxCount['box'];
			// 	}
			// 	else{
			// 	  $liveBox = 0;
			// 	}

			//   }
			//   else{
			// 	$starStatus = '0';
			// 	$liveBox = 0;
			//   }

			$countStar = $this->db->select_sum('coin')
				->from('userGiftHistory')
				->where('giftUserId', $this->input->post('giftUserId'))
				->where('created', date('Y-m-d'))
				->get()->row_array();

			if (!empty($this->input->post('liveId'))) {
				$recieveCoins = $this->db->select_sum('coin')
					->from('userGiftHistory')
					->where('giftUserId', $this->input->post('giftUserId'))
					->where('liveId', $this->input->post('liveId'))
					->get()->row_array();
			}

			if (!empty($this->input->post('pkId'))) {
				$recieveCoins = $this->db->select_sum('coin')
					->from('userGiftHistory')
					->where('giftUserId', $this->input->post('giftUserId'))
					->where('pkId', $this->input->post('pkId'))
					->get()->row_array();
			}






			$talentImage = $this->db->select('image')->from('user_talent_levels')->where('level', $myLevel['talent_level'])->get()->row_array();

			$outMess['myLevel'] =  $myLevel['my_level'];
			$outMess['liveLevel'] =  $myLevel['talent_level'];
			$outMess['talentImage'] =  $talentImage['image'];
			$outMess['myStar'] =  $countStar['coin'];
			$outMess['coinsRecieved'] = $recieveCoins['coin'];
			$outMess['liveStar'] =  '0';
			$outMess['liveBox'] =  '0';
			$message['success'] = '1';
			$message['message'] = 'Gift send successfully';
			$message['details'] = $outMess;
		} else {
			$message['success'] = '0';
			$message['message'] = 'Please try after some time';
		}
		echo json_encode($message);
	}

	public function getHostType()
	{
		if ($this->input->post()) {

			$getData = $this->db->get_where('userLive', ['id' => $this->input->post('liveId')])->row_array();
			if (!!$getData) {

				echo json_encode([
					'success' => 1,
					'message' => 'HostType found',
					'details' => $getData['hostType']
				]);
				exit;
			} else {
				echo json_encode([
					'success' => 0,
					'message' => 'Invalid Live ID'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => 0,
				'message' => 'Enter valid data'
			]);
		}
	}

	// public function get_highest_pk_battle_winner()

	public function getPkBattle()
	{

		if ($this->input->post()) {

			$checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (!!$checkUser) {

				$getLive = $this->db->select('id, otherUserLiveId, otherUserId')
					->from('pkbattle')
					->where('userId !=', $this->input->post('userId'))
					->where('otherUserId !=', $this->input->post('userId'))
					->where('battleStatus', 'live')
					->get()->result_array();
				if (!!$getLive) {

					// print_r($getLive);exit;
					foreach ($getLive as $value) {

						$getHostDeatils = $this->db->get_where('userLive', ['id' => $value['otherUserLiveId']])->row_array();

						$coinsTotal = $this->db->select_sum('coin')
							->from('userGiftHistory')
							->where('liveId', $value['id'])->get()->row_array();
						if ($coinsTotal['coin'] == null) {
							$coinsTotal['coin'] = '0';
						}
						$getHostDeatils['pkBattleGiftings'] = $coinsTotal['coin'];


						$countStar = $this->db->select_sum('coin')
							->from('userGiftHistory')
							->where('giftUserId', $value['otherUserId'])
							->where('created', date('Y-m-d'))
							->get()->row_array();
						if ($countStar['coin'] == null) {
							$countStar['coin'] = '0';
						}
						$getHostDeatils['countStar'] = $countStar['coin'];

						$getFollowStatus = $this->db->get_where('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' => $value['otherUserId'], 'status' => 1])->row_array();

						if (!!$getFollowStatus) {
							$getHostDeatils['followStatus'] = true;
						} else {
							$getHostDeatils['followStatus'] = false;
						}


						$pass[] = $getHostDeatils;
					}

					echo json_encode([
						'success' => 1,
						'message' => 'Live Users Found',
						'details' => $pass
					]);
					exit;
				} else {
					echo json_encode([
						'success' => 0,
						'message' => 'No live User List Found'
					]);
					exit;
				}
			} else {
				echo json_encode([
					'success' => 0,
					'message' => 'userId user not exist'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => 0,
				'message' => 'Enter Valid Data'
			]);
			exit;
		}
	}

	public function pkBattleArchieved()
	{
		if ($this->input->post()) {

			if ($this->input->post('type') == 1) {

				$data['userIdStatus'] = 0;

				$this->db->set($data)->where('id', $this->input->post('pkId'))->update('pkbattle');

				echo json_encode([
					'success' => 1,
					'message' => 'user 1 has left the battle'
				]);
				exit;
			}

			if ($this->input->post('type') == 2) {
				$data['otherUserStatus'] = 0;

				$this->db->set($data)->where('id', $this->input->post('pkId'))->update('pkbattle');

				echo json_encode([
					'success' => 1,
					'message' => 'user 2 has left the battle'
				]);
				exit;
			}

			$data['endDate'] = date('Y-m-d');
			$data['endTime'] = date('H:i:s');


			$getUser = $this->db->select('userId, otherUserId')->from('pkbattle')->where('id', $this->input->post('pkId'))->get()->row_array();



			$getAllForUserOne = $this->db->select_sum('coin')
				->from('userGiftHistory')
				->where('type', 2)
				->where('giftUserId', $getUser['userId'])
				->where('pkId', $this->input->post('pkId'))
				->get()->row_array();

			$getAllForUserTwo = $this->db->select_sum('coin')
				->from('userGiftHistory')
				->where('type', 2)
				->where('giftUserId', $getUser['otherUserId'])
				->where('pkId', $this->input->post('pkId'))
				->get()->row_array();

			if ($getAllForUserOne['coin'] == null || empty($getAllForUserOne['coin'])) {
				$getAllForUserOne['coin'] = 0;
			}
			if ($getAllForUserTwo['coin'] == null || empty($getAllForUserTwo['coin'])) {
				$getAllForUserTwo['coin'] = 0;
			}
			// print_r($getAllForUserOne);			print_r($getAllForUserTwo);exit;
			if ($getAllForUserOne['coin'] < $getAllForUserTwo['coin']) {
				$message = [[
					'winner' => $getUser['otherUserId'],
					'winnerCoin' => $getAllForUserTwo['coin'],
					'losser' => $getUser['userId'],
					'losserCoin' => $getAllForUserOne['coin'],
				]];
				$data['winner'] = $getUser['otherUserId'];
			} else if ($getAllForUserOne['coin'] > $getAllForUserTwo['coin']) {
				$message = [[
					'winner' => $getUser['userId'],
					'winnerCoin' => $getAllForUserOne['coin'],
					'losser' => $getUser['otherUserId'],
					'losserCoin' => $getAllForUserTwo['coin'],
				]];
				$data['winner'] = $getUser['userId'];
			} else if ($getAllForUserOne['coin'] == $getAllForUserTwo['coin']) {
				$message = [[
					'message' => 'draw',
					'coins' => $getAllForUserTwo
				]];
				$data['winner'] = 0;
			}

			$data['battleStatus'] = 'end';

			$update = $this->db->set($data)->where('id', $this->input->post('pkId'))->update('pkbattle');

			$hType['hostType'] = 1;

			$this->db->set($hType)->where('id', $this->input->post('userLiveId'))->update('userLive');
			$this->db->set($hType)->where('id', $this->input->post('otherLiveId'))->update('userLive');

			if (!!$update) {

				echo json_encode([
					'success' => 1,
					'message' => 'LIVE ARCHIEVED',
					'details' => $message
				]);
				exit;
			} else {
				echo json_encode([
					'success' => 0,
					'message' => 'LIVE not ARCHIEVED'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => 0,
				'message' => 'Enter valid data'
			]);
			exit;
		}
	}

	public function pkBattle()
	{

		$checkUserId = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
		if (empty($checkUserId)) {
			echo json_encode([
				'success' => 0,
				'message' => 'userId not Exist'
			]);
			exit;
		}
		$checkOtherUserId = $this->db->get_where('users', ['id' => $this->input->post('otherUserId')])->row_array();
		if (empty($checkOtherUserId)) {
			echo json_encode([
				'success' => 0,
				'message' => 'otherUserId not Exist'
			]);
			exit;
		}

		$checkLiveId = $this->db->get_where('userLive', ['id' => $this->input->post('liveId')])->row_array();
		if (empty($checkLiveId)) {
			echo json_encode([
				'success' => 0,
				'message' => 'Invalid LiveId'
			]);
			exit;
		}

		$checkOtherLiveId = $this->db->get_where('userLive', ['id' => $this->input->post('otherLiveId')])->row_array();
		if (empty($checkOtherLiveId)) {
			echo json_encode([
				'success' => 0,
				'message' => 'Invalid otherLiveId'
			]);
			exit;
		}

		$pkOn = $this->db->get_where('pkbattle', ['userId' => $this->input->post('userId'), 'otherUserLiveId' => $this->input->post('otherUserId'), 'battleStatus' => 'live'])->row_array();
		if(!!$pkOn){
			echo json_encode([
				'success' => 0,
				'message' => 'pk battle already hit'
			]);exit;
		}

		$host['hostType'] = 2;
		$this->db->set($host)
			->where(['id' => $this->input->post('liveId')])
			->update('userLive');

		$this->db->set($host)
			->where(['id' => $this->input->post('otherLiveId')])
			->update('userLive');


		$data['userId'] = $this->input->post('userId');
		$data['otherUserLiveId'] = $this->input->post('otherLiveId');
		$data['otherUserId'] = $this->input->post('otherUserId');
		$data['battleStatus'] = 'live';
		$data['userIdStatus'] = '1';
		$data['otherUserStatus'] = '1';
		$data['createdDate'] = date('Y-m-d');
		$data['createdTime'] = date('H:i:s');

		if ($this->db->insert('pkbattle', $data)) {

			$insertId = $this->db->insert_id();

			echo json_encode([
				'success' => 1,
				'message' => 'PKBattle started!! LADO BC',
				'details' => $insertId
			]);
			exit;
		} else {
			echo json_encode([
				'success' => 0,
				'message' => 'technical error'
			]);
			exit;
		}
	}

	public function getFriendsLiveList()
	{
		if ($this->input->post()) {

			$checkuser = $this->db->get_where('userFollow', ['userId' => $this->input->post('userId')])->result_array();
			// print_r($checkuser);exit;

			if (!!$checkuser) {

				foreach ($checkuser as $list) {
					$checkFriend = $this->db->select('userId')
						->from('userFollow')
						->where('userId', $list['followingUserId'])
						->where('followingUserId', $this->input->post('userId'))
						->where('userFollow.status', '1')
						->get()->row_array();
					// print_r($checkFriend);

					$getLiveFriend = $this->db->select('userLive.id liveId,userLive.*, users.*')
						->from('userLive')
						->join('users', 'users.id = userLive.userId', 'left')
						->where('userId', $checkFriend['userId'])
						->where('userLive.status', 'live')
						->order_by('userLive.id', 'desc')
						->get()->row_array();
					if ($getLiveFriend == null) {
						continue;
					}

					$coinsTotal = $this->db->select_sum('coin')
						->from('userGiftHistory')
						->where('liveId', $checkFriend['id'])->get()->row_array();

					if ($coinsTotal['coin'] == null) {
						$coinsTotal['coin'] = '0';
					}

					$getLiveFriend['pkBattleGiftings'] = $coinsTotal['coin'];


					$countStar = $this->db->select_sum('coin')
						->from('userGiftHistory')
						->where('giftUserId', $checkFriend['userId'])
						->where('created', date('Y-m-d'))
						->get()->row_array();
					if ($countStar['coin'] == null) {
						$countStar['coin'] = '0';
					}
					$getLiveFriend['countStar'] = $countStar['coin'];



					$final[] = $getLiveFriend;
				}

				echo json_encode([
					'success' => 1,
					'message' => 'live list found',
					'deatils' => $final
				]);
				exit;
			} else {
				echo json_encode([
					'success' => 0,
					'message' => 'Invalid userId'
				]);
				exit;
			}
		}
	}

	public function getpkResult()
	{
		if ($this->input->post()) {

			$checkBattleId = $this->db->get_where('pkbattle', ['id' => $this->input->post('battleId')])->row_array();
			if (!!$checkBattleId) {

				$userOne = $checkBattleId['userId'];
				$userTwo = $checkBattleId['otherUserId'];
				$winner = $checkBattleId['winner'];
				$final = [];


				if ($userOne == $winner) {
					// print_r($userOne);print_r($userTwo);exit;


					$check['parse'] = $this->db->select_sum('userGiftHistory.coin')
						->select('users.name, users.image, users.id')
						->from('userGiftHistory')
						->join('users', 'users.id = userGiftHistory.giftUserId', 'left')
						->where('pkId', $this->input->post('battleId'))
						->where('giftUserId', $userOne)
						->where('users.id', $userOne)
						->get()->row_array();

					$check['parse']['result'] = "WINNER";
					$check['parse']['battleId'] = $this->input->post('battleId');

					$final[] = $check['parse'];


					$check['parse'] = $this->db->select_sum('userGiftHistory.coin')
						->select('users.name, users.image, users.id')
						->from('userGiftHistory')
						->join('users', 'users.id = userGiftHistory.giftUserId', 'left')
						->where('pkId', $this->input->post('battleId'))
						->where('giftUserId', $userTwo)
						->where('users.id', $userTwo)
						->get()->row_array();

					$check['parse']['result'] = "LOSSER";
					$check['parse']['battleId'] = $this->input->post('battleId');

					if ($check['parse']['coin'] == null) {


						$check = $this->db->select('username, image, id')->from('users')->where('id', $userTwo)->get()->row_array();

						if (!!$check['image']) {

							$check['image'] = base_url() . $check['image'];
						} else {

							$check['image'] = "";
						}

						$check['coin'] = '0';
						$check['result'] = "LOSSER";
						$check['battleId'] = $this->input->post('battleId');
						$final[] = $check;
					} else {
						$final[] = $check['parse'];
					}


					echo json_encode([
						'success' => 1,
						'message' => 'Result',
						'details' => $final
					]);
					exit;
				} else if ($userTwo == $winner) {

					$check['parse'] = $this->db->select_sum('userGiftHistory.coin')
						->select('users.name, users.image, users.id')
						->from('userGiftHistory')
						->join('users', 'users.id = userGiftHistory.giftUserId', 'left')
						->where('pkId', $this->input->post('battleId'))
						->where('giftUserId', $userTwo)
						->where('users.id', $userTwo)
						->get()->row_array();

					$check['parse']['result'] = "WINNER";
					$check['parse']['battleId'] = $this->input->post('battleId');

					$final[] = $check['parse'];


					$check['parse'] = $this->db->select_sum('userGiftHistory.coin')
						->select('users.name, users.image, users.id')
						->from('userGiftHistory')
						->join('users', 'users.id = userGiftHistory.giftUserId', 'left')
						->where('pkId', $this->input->post('battleId'))
						->where('giftUserId', $userOne)
						->where('users.id', $userOne)
						->get()->row_array();

					$check['parse']['result'] = "LOSSER";
					$check['parse']['battleId'] = $this->input->post('battleId');

					if ($check['parse']['coin'] == null) {


						$check = $this->db->select('username, image, id')->from('users')->where('id', $userOne)->get()->row_array();

						if (!!$check['image']) {

							$check['image'] = base_url() . $check['image'];
						} else {

							$check['image'] = "";
						}

						$check['coin'] = '0';
						$check['result'] = "LOSSER";
						$check['battleId'] = $this->input->post('battleId');
						$final[] = $check;
					} else {
						$final[] = $check['parse'];
					}


					echo json_encode([
						'success' => 1,
						'message' => 'Result',
						'details' => $final
					]);
					exit;
				} else if ($winner == 0) {

					$check['parse'] = $this->db->select_sum('userGiftHistory.coin')
						->select('users.name, users.image, users.id')
						->from('userGiftHistory')
						->join('users', 'users.id = userGiftHistory.giftUserId', 'left')
						->where('pkId', $this->input->post('battleId'))
						->where('giftUserId', $userTwo)
						->where('users.id', $userTwo)
						->get()->row_array();

					$check['parse']['result'] = "TIE";
					$check['parse']['battleId'] = $this->input->post('battleId');

					if ($check['parse']['coin'] == null) {

						$check = $this->db->select('name, image, id')->from('users')->where('id', $userOne)->get()->row_array();

						if (!!$check['image']) {

							$check['image'] = base_url() . $check['image'];
						} else {

							$check['image'] = "";
						}

						$check['coin'] = '0';
						$check['result'] = "TIE";
						$check['battleId'] = $this->input->post('battleId');
						$final[] = $check;
					} else {
						$final[] = $check['parse'];
					}


					$check['parse'] = $this->db->select_sum('userGiftHistory.coin')
						->select('users.name, users.image, users.id')
						->from('userGiftHistory')
						->join('users', 'users.id = userGiftHistory.giftUserId', 'left')
						->where('pkId', $this->input->post('battleId'))
						->where('giftUserId', $userOne)
						->where('users.id', $userOne)
						->get()->row_array();

					$check['parse']['result'] = "LOSSER";
					$check['parse']['battleId'] = $this->input->post('battleId');

					if ($check['parse']['coin'] == null) {


						$check = $this->db->select('name, image, id')->from('users')->where('id', $userOne)->get()->row_array();

						if (!!$check['image']) {

							$check['image'] = base_url() . $check['image'];
						} else {

							$check['image'] = "";
						}

						$check['coin'] = '0';
						$check['result'] = "TIE";
						$check['battleId'] = $this->input->post('battleId');
						$final[] = $check;
					} else {
						$final[] = $check['parse'];
					}


					echo json_encode([
						'success' => 1,
						'message' => 'Result',
						'details' => $final
					]);
					exit;
				}
			} else {
				// echo json_encode([
				// 	'status' => 0,
				// 	'message' => 'Invalid battle Id and Battle not end'
				// ]);exit;
			}
		} else {
			echo json_encode([
				'success' => 0,
				'message' => 'Enter valid data'
			]);
			exit;
		}
	}

	public function giftNotification($regId, $message, $type, $loginId, $userId, $purchasedCoinstotal, $receivedCointotal)
	{
		$checkMuteNotifiaton = $this->db->get_where('muteUserNotification', array('userId' => $userId, 'muteId' => $loginId, 'status' => '1'))->row_array();
		if (empty($checkMuteNotifiaton)) {
			$registrationIds =  array($regId);
			define('API_ACCESS_KEY', 'AAAA0WgbM-c:APA91bGsdT5rx9u_QmaG9cjyapHiAY6NrzMw_WbMXgEuQi0f7ZbZ6-GCQZUDU6PvHY52KBBYwqC_Dxc9a22la0ad84p1tblRcK2evrYM2ONKU-Oa0xuL9d9jgkMrTynDadMO2OL1VIJz');
			$msg = array(
				'message' 	=> $message,
				'title'		=> 'LiveBazaar',
				'type'		=> $type,
				'subtitle'	=> $type,
				'loginId' => $loginId,
				'userId' => $userId,
				'purchasedCoins' => $purchasedCoinstotal,
				'receivedCoin' => $receivedCointotal,
				'vibrate'	=> 1,
				'sound'		=> 1,
				'largeIcon'	=> 'large_icon',
				'smallIcon'	=> 'small_icon',
			);
			$fields = array(
				'registration_ids' 	=> $registrationIds,
				'data'			=> $msg
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

	public function addPurchaseCoin()
	{
		if ($this->input->post()) {
			$data['userId'] = $this->input->post('userId');
			$data['coin'] = $this->input->post('coin');

			$getUserCoin = $this->db->select("purchasedCoin")
				->from('users')
				->where('id', $data['userId'])
				->get()->row_array();

			$updateCoin = $data['coin'] + $getUserCoin['purchasedCoin'];
			$updateUserCoins = $this->db->set('purchasedCoin', $updateCoin)
				->where('id', $data['userId'])
				->update('users');
			if ($updateUserCoins) {
				$in['userId'] = $data['userId'];
				$in['coin'] = $data['coin'];
				$in['transactionType'] = '1';

				$gameCoinHistory = $this->db->insert('gameCoinHistory', $in);
				if ($in) {
					echo json_encode([
						'success' => '1',
						'message' => 'coins credited successfully'
					]);
				} else {
					echo json_encode([
						'success' => '1',
						'message' => 'user coin credited, history not updated.'
					]);
				}
			} else {
				echo json_encode([
					'success' => '0',
					'message' => 'user coin not credited.'
				]);
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'Enter valid data.'
			]);
		}
	}


	public function minPurchaseCoin()
	{
		if ($this->input->post()) {
			$data['userId'] = $this->input->post('userId');
			$data['coin'] = $this->input->post('coin');

			$getUserCoin = $this->db->select("purchasedCoin")
				->from('users')
				->where('id', $data['userId'])
				->get()->row_array();

			if ($getUserCoin['purchasedCoin'] < $data['coin']) {
				echo json_encode([
					'success' => '0',
					'message' => 'insufficient balance'
				]);
			} else {

				$updateCoin = $getUserCoin['purchasedCoin'] - $data['coin'];
				$updateUserCoins = $this->db->set('purchasedCoin', $updateCoin)
					->where('id', $data['userId'])
					->update('users');

				if ($updateUserCoins) {
					$in['userId'] = $data['userId'];
					$in['coin'] = $data['coin'];
					$in['transactionType'] = '0';

					$gameCoinHistory = $this->db->insert('gameCoinHistory', $in);
					if ($in) {
						echo json_encode([
							'success' => '1',
							'message' => 'coins debited successfully'
						]);
					} else {
						echo json_encode([
							'success' => '1',
							'message' => 'user coin debited, history not updated.'
						]);
					}
				} else {
					echo json_encode([
						'success' => '0',
						'message' => 'user coin not debited.'
					]);
				}
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'Enter valid data.'
			]);
		}
	}

	public function getPurchasedCoin()
	{
		if ($this->input->post()) {
			$get = $this->db->select('purchasedCoin')
				->from('users')
				->where('id', $this->input->post('userId'))
				->get()->row_array();

			if ($get) {
				$amount = $get['purchasedCoin'];
				echo json_encode([
					'success' => '1',
					'message' => 'Data found.',
					'details' => $amount
				]);
			} else {
				echo json_encode([
					'success' => '1',
					'message' => 'Data not found.'
				]);
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'Enter valid data.'
			]);
		}
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
					$lists['thumbnail'] = base_url() . $lists['thumbnail'];
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
		//	$list =  $this->db->query("SELECT liveBroadcast.*,users.name,users.username,users.image,userFollow.followingUserId FROM liveBroadcast left join userFollow on userFollow.followingUserId = liveBroadcast.user_id left join users on users.id = userFollow.followingUserId where userFollow.userId = $userId and userFollow.status = '1' and liveBroadcast.type = 'live' order by liveBroadcast.id desc")->result_array();
		// $list =  $this->db->query("SELECT liveBroadcast.*,users.name,users.username,users.image FROM liveBroadcast  left join users on users.id = liveBroadcast.user_id where liveBroadcast.type = 'live' order by liveBroadcast.id desc")->result_array();
		// if(!empty($list)){
		// 	$message['success'] = '1';
		// 	$message['message'] = 'list found successfully';
		// 	foreach($list as $lists){
		// 		if(empty($lists['image'])){
		// 				$lists['image'] = base_url().'uploads/no_image_available.png';
		// 		}
		// 		$message['details'][] = $lists;
		// 	}
		// }
		// else{
		// 	$message['success'] = '0';
		// 	$message['message'] = 'No list found';
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
			$appID = "cba3368e67374d4ebbe8ab082ae725be";
			$appCertificate = "9e2335bfc56c434296a8133ff30cdc94";
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
					// $lists = $this->db->get_where('userFollow',array('followingUserId' => $userId,'status' => '1'))->result_array();
					// if(!empty($lists)){
					//     foreach($lists as $list){
					//         $loginUserDetails = $this->db->get_where('users',array('id' => $userId))->row_array();
					//         $getUserId = $this->db->get_where('users',array('id' => $list['userId']))->row_array();
					//         $regId = $getUserId['reg_id'];
					//         $mess = $loginUserDetails['username'].' Just Live';
					//         if(empty($loginUserDetails['image'])){
					//           $liveuserimage['image'] = base_url().'uploads/no_image_available.png';
					//         }
					//         else{
					//          $liveuserimage['image'] = $loginUserDetails['image'];
					//         }
					//         $liveUsername = $loginUserDetails['username'];
					//         $this->liveNotification($regId,$mess,'liveUser',$list['userId'],$userId,$liveuserimage,$liveUsername,$this->input->post('channelName'),$this->input->post('latitude'),$this->input->post('longitude'),$token,$tokenb);
					//         $notiMess['loginId'] = $userId;
					//         $notiMess['userId'] = $list['userId'];
					//         $notiMess['message'] = $mess;
					//         $notiMess['type'] = 'liveUser';
					//         $notiMess['notiDate'] = date('Y-m-d');
					//         $notiMess['created'] = date('Y-m-d H:i:s');
					//         $this->db->insert('userNotification',$notiMess);
					//     }
					// }
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

					$countStar = $this->db->select_sum('coin')
						->from('userGiftHistory')
						->where('giftUserId', $this->input->post('userId'))
						->where('created', date('Y-m-d'))
						->get()->row_array();



					$outPut['name'] = $userDetails['name'];
					$outPut['hostImage'] = $userDetails['image'];
					$outPut['coin'] = $userDetails['coin'];
					$outPut['userLeval'] = $userDetails['leval'];
					$outPut['starCount'] = $countStar['coin'];
					// $outPut['starCount'] = $starStatus;
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
			//   }
			//   else{
			//       $message['success'] = '0';
			//       $message['message'] = 'user not host';
			//   }
			//   }
			//   else{
			//     $checkRequest = $this->db->get_where('userLiveRequest',array('userId' => $this->input->post('userId')))->row_array();
			//     if(!empty($checkRequest)){
			//       $message['requestStatus'] = '1';
			//     }
			//     else{
			//       $message['requestStatus'] = '0';
			//     }
			//     $message['success'] = '0';
			//     $message['message'] = 'Your Account is banned for live';
			//   }
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
			// 	$finalCommentCount =  $checkCommentCount['commentCount'] + $checkSubCommentCount;
			// }
			// else{
			// 	$finalCommentCount =  $checkCommentCount['commentCount'];
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

	//  public function getTopGifter(){
	// 		if($this->input->post()){

	// 		// 1 for daily
	// 		// 2 for weekly
	// 		// 3 for monthly

	// 		if($this->input->post('type') == '1'){

	// 			$getUserByDate = $this->db->select_sum('coin')
	// 									  ->select('userId')
	// 									  ->from('userGiftHistory')
	// 									  ->group_by('userId')
	// 									  ->where('giftUserId', $this->input->post('userId'))
	// 									  ->where('created', date('Y-m-d'))
	// 									  ->order_by('coin', 'desc')
	// 									  ->get()->result_array();

	// 			if(!!$getUserByDate){

	// 				foreach($getUserByDate as $key => $user){

	// 					$getUserByDate[$key]['userInfo'] = $this->db->get_where('users', ['id' => $user['userId']])->row_array();

	// 				}


	// 				echo json_encode([
	// 					'success' => '1',
	// 					'message' => 'Giftings Found for Today',
	// 					'details' => $getUserByDate
	// 				]);exit;

	// 			}else{
	// 				echo json_encode([
	// 					'success' => '0',
	// 					'message' => 'No gifting done Today'
	// 				]);exit;
	// 			}

	// 		}else if($this->input->post('type') == '2'){

	// 			$dateLimit = date("Y-m-d", strtotime("-1 week"));

	// 			$getUserByWeek = $this->db->select_sum('coin')
	// 									  ->select('userId')
	// 									  ->from('userGiftHistory')
	// 									  ->group_by('userId')
	// 									  ->where('giftUserId', $this->input->post('userId'))
	// 									  ->where('created >=', $dateLimit)
	// 									  ->order_by('coin', 'desc')
	// 									  ->get()->result_array();

	// 			if(!!$getUserByWeek){

	// 				foreach($getUserByWeek as $key => $user){

	// 					$getUserByWeek[$key]['userInfo'] = $this->db->get_where('users', ['id' => $user['userId']])->row_array();

	// 				}

	// 			echo json_encode([
	// 				'success' => '1',
	// 				'message' => 'Giftings Found from ' . $dateLimit . ' to Today',
	// 				'details' => $getUserByWeek
	// 			]);exit;

	// 		}else{
	// 			echo json_encode([
	// 				'success' => '0',
	// 				'message' => 'No gifting done this Week'
	// 			]);exit;
	// 		}




	// 		}else if($this->input->post('type') == '3'){

	// 			$dateLimit = date("Y-m-d", strtotime("-1 month"));

	// 			$getUserByMonth = $this->db->select_sum('coin')
	// 									  ->select('userId')
	// 									  ->from('userGiftHistory')
	// 									  ->group_by('userId')
	// 									  ->where('giftUserId', $this->input->post('userId'))
	// 									  ->where('created >=', $dateLimit)
	// 									  ->order_by('coin', 'desc')
	// 									  ->get()->result_array();

	// 			if(!!$getUserByMonth){

	// 				foreach($getUserByMonth as $key => $user){

	// 					$getUserByMonth[$key]['userInfo'] = $this->db->get_where('users', ['id' => $user['userId']])->row_array();

	// 				}

	// 			echo json_encode([
	// 				'success' => '1',
	// 				'message' => 'Giftings Found from ' . $dateLimit . ' to Today',
	// 				'details' => $getUserByMonth
	// 			]);exit;

	// 		}else{
	// 			echo json_encode([
	// 				'success' => '0',
	// 				'message' => 'No gifting done this Month'
	// 			]);exit;
	// 		}

	// 		}else{
	// 			echo json_encode([
	// 				'success' => '0',
	// 				'message' => 'Enter valid type'
	// 			]);exit;
	// 		}

	// 		}else{
	// 			echo json_encode([
	// 				'success' => '0',
	// 				'message' => 'Enter Valid Data!'
	// 			]);
	// 		}
	// 	  }

	public function getTopGifter()
	{

		$today = $this->db->select_sum('coin')
			->select('userId')
			->from('userGiftHistory')
			->group_by('userId')
			->where('created', date('Y-m-d'))
			->order_by('coin', 'desc')
			->limit(11)
			->get()->result_array();

		foreach ($today as $key => $list) {

			$userInfo = $this->db->get_where('users', ['id' => $list['userId']])->row_array();

			$today[$key]['userInfo'] = $userInfo;
			// $today[$key]['userInfo']['moredetails'] = $this->userVipAndFrame($userId['userId']);
		}

		$dateLimit = date("Y-m-d", strtotime("-1 week"));

		$weekly = $this->db->select_sum('coin')
			->select('userId')
			->from('userGiftHistory')
			->group_by('userId')
			->where('created >=', $dateLimit)
			->order_by('coin', 'desc')
			->limit(11)
			->get()->result_array();


		foreach ($weekly as $key => $list) {

			$userInfo = $this->db->get_where('users', ['id' => $list['userId']])->row_array();

			$weekly[$key]['userInfo'] = $userInfo;
			// $weekly[$key]['userInfo']['moredetails'] = $this->userVipAndFrame($userId['userId']);
		}

		// $detail['weekly']['userInfo'] =


		$dateLimit = date("Y-m-d", strtotime("-1 month"));

		$monthly = $this->db->select_sum('coin')
			->select('userId')
			->from('userGiftHistory')
			->group_by('userId')
			->where('created >=', $dateLimit)
			->order_by('coin', 'desc')
			->limit(11)
			->get()->result_array();

		foreach ($monthly as $key => $list) {

			$userInfo = $this->db->get_where('users', ['id' => $list['userId']])->row_array();

			$monthly[$key]['userInfo'] = $userInfo;
			// $monthly[$key]['userInfo']['moredetails'] = $this->userVipAndFrame($userId['userId']);
		}



		$overAll = $this->db->select_sum('coin')
			->select('userId')
			->from('userGiftHistory')
			->group_by('userId')
			->order_by('coin', 'desc')
			->limit(11)
			->get()->result_array();


		foreach ($overAll as $key => $list) {

			$userInfo = $this->db->get_where('users', ['id' => $list['userId']])->row_array();

			$overAll[$key]['userInfo'] = $userInfo;
			// $overAll[$key]['userInfo']['moredetails'] = $this->userVipAndFrame($userId['userId']);
		}


		if ($this->input->post('type') == '1') {
			echo json_encode([
				'status' => '1',
				'message' => 'top gifter today',
				'details' => $today
			]);
			exit;
		} else if ($this->input->post('type') == '2') {
			echo json_encode([
				'status' => '1',
				'message' => 'top gifter weekly',
				'details' => $weekly
			]);
			exit;
		} else if ($this->input->post('type') == '3') {
			echo json_encode([
				'status' => '1',
				'message' => 'top gifter monthly',
				'details' => $monthly
			]);
			exit;
		} else if ($this->input->post('type') == '4') {
			echo json_encode([
				'status' => '1',
				'message' => 'top gifter overAll',
				'details' => $overAll
			]);
			exit;
		} else {
			echo json_encode([
				'status' => '0',
				'message' => 'Enter valid Type'
			]);
			exit;
		}


		echo json_encode([
			'status' => '1',
			'message' => 'top gifter found',
			'details' => $detail
		]);
		exit;
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
				'message' 	=> $message,
				'title'		=> 'LiveBazaar',
				'type'		=> $type,
				'subtitle'	=> $type,
				'loginId' => $loginId,
				'userId' => $userId,
				'vibrate'	=> 1,
				'sound'		=> 1,
				'largeIcon'	=> 'large_icon',
				'smallIcon'	=> 'small_icon',
			);
			$fields = array(
				'registration_ids' 	=> $registrationIds,
				'data'			=> $msg
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

	//  public function userFollow(){
	// 	 $check_like =  $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'),'followingUserId' => $this->input->post('followingUserId')))->row_array();
	// 	 if(!empty($check_like)){
	// 		 if($check_like['status'] == '0'){
	// 			 $status = '1';
	// 		 }
	// 		 else{
	// 			 $status = '0';
	// 		 }
	// 		 $data = array(
	// 			 'userId' => $this->input->post('userId'),
	// 			 'followingUserId' => $this->input->post('followingUserId'),
	// 			 'status' => $status,
	// 			 'updated' => date('y-m-d h:i:s')
	// 		 );
	// 		 $update = $this->Common_Model->update('userFollow',$data,'id',$check_like['id']);
	// 	 }
	// 	 else{
	// 		 $status = '1';
	// 		 $data = array(
	// 			 'userId' => $this->input->post('userId'),
	// 			 'followingUserId' => $this->input->post('followingUserId'),
	// 			 'status' => $status,
	// 			 'created' => date('y-m-d h:i:s')
	// 		 );
	// 		 $insert = $this->db->insert('userFollow', $data);
	// 		 $insert_id = $this->db->insert_id();
	// 	 }
	// 	 $likeInformation = $this->db->get_where('userProfileInformation',array('userId' => $this->input->post('followingUserId')))->row_array();
	// 	 if(empty($check_like)){
	// 		 $userProfile['followers'] = 1 + $likeInformation['followers'];
	// 		 $message123 = 'user following successfully';
	// 		 $sendStatus = true;
	// 	 }
	// 	 else{
	// 		 if($status == '0'){
	// 			 $userProfile['followers'] = $likeInformation['followers'] - 1;
	// 			 $message123 = 'user unfollowing successfully';
	// 			 $sendStatus = false;
	// 		 }
	// 		 else{
	// 			 $userProfile['followers'] = 1 + $likeInformation['followers'];
	// 			 $message123 = 'user following successfully';
	// 			 $sendStatus = true;
	// 		 }
	// 	 }
	// 	 $UserDetails = $this->db->get_where('users',array('id' => $this->input->post('followingUserId')))->row_array();
	// 	 if($status == '1'){
	// 		 $loginUserDetails = $this->db->get_where('users',array('id' => $this->input->post('userId')))->row_array();
	// 		 $mess = $loginUserDetails['username']." started following you";
	// 		 $regId = $UserDetails['reg_id'];
	// 		 if($loginUserDetails['followersNotification'] == '1'){
	// 			 $this->notification($regId,$mess,'follow',$this->input->post('userId'),$this->input->post('followingUserId'));
	// 		 }
	// 		 $notiMess['loginId'] = $this->input->post('userId');
	// 		 $notiMess['userId'] = $this->input->post('followingUserId');
	// 		 $notiMess['message'] = $mess;
	// 		 $notiMess['type'] = 'follow';
	// 		 $notiMess['notiDate'] = date('Y-m-d');
	// 		 $notiMess['created'] = date('Y-m-d H:i:s');
	// 		 $this->db->insert('userNotification',$notiMess);

	// 		 $upFollowStatus['followerCount'] = $UserDetails['followerCount'] + 1;
	// 	 }
	// 	 else{
	// 		 $upFollowStatus['followerCount'] = $UserDetails['followerCount'] - 1;
	// 	 }

	// 		 $this->Common_Model->update('users',$upFollowStatus,'id',$this->input->post('followingUserId'));


	// 	 $update = $this->Common_Model->update('userProfileInformation',$userProfile,'id',$likeInformation['id']);
	// 	 $likeCount = $this->db->get_where('userFollow', array('followingUserId' => $this->input->post('followingUserId'),'status'=> '1'))->num_rows();
	// 	 $successmessage = array(
	// 			 'success'=>'1',
	// 			 'message' => $message123,
	// 			 'following_status'=>$sendStatus,
	// 			 'following_count'=>(string)$likeCount
	// 	 );
	// 	 echo json_encode($successmessage);
	//  }

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
			$sendBeans =  $this->db->query("SELECT sum(coin) as beans FROM `userGiftHistory` where userId = $userId")->row_array();
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
			if (!empty($list)) {
				$finalData['followers'] = $list['followers'];
				$finalData['likes'] = $list['likes'];
				$finalData['videoCount'] = $list['videoCount'];
			} else {
				$finalData['followers'] = "0";
				$finalData['likes'] = "0";
				$finalData['videoCount'] = "0";
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
			$selectFollowProvider = $this->db->query("SELECT a.*,b.*,users.id as uId,users.name as uname,users.image as userImage from userFollow as a LEFT JOIN userFollow as b on b.userId=a.followingUserId and b.followingUserId=$userId left join users on users.id=a.followingUserId where a.userId=$userId and a.status='1' HAVING a.followingUserId = b.userId and b.status='1'")->num_rows();
			if (!empty($selectFollowProvider)) {
				$finalData['friendCount'] = (string)$selectFollowProvider;
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
	// 	  $data['userId'] = $this->input->post('userId');
	// 	  $data['ownerId'] = $this->input->post('ownerId');
	// 	  $data['videoId'] = $this->input->post('videoId');
	// 	  $data['status'] = '1';
	// 	  $videoId = $this->input->post('videoId');

	// 	  $ckeckCount = $this->db->get_where("userVideos",['id' => $this->input->post('videoId')])->row_array();

	// 	  $getlike = $ckeckCount['likeCount'];

	// 	  if($getlike <= '0'){
	// 		$insert = $this->db->insert('videoLikeOrUnlike', $data);
	// 		if ($insert){

	// 		  $this->db->set('likeCount', 'likeCount +1', false)->where('id', $this->input->post('videoId'))->update("userVideos");
	// 		  $this->db->set('likes', 'likes +1', false)->where('userId', $this->input->post('userId'))->update("userProfileInformation");
	// 		//   $this->db->set('likeCount', 'likeCount +1', false)->where('id', $this->input->post('ownerId'))->update("userVideos");

	// 		  $Counts = $this->db->get_where("userVideos",['id' => $videoId])->row_array();

	// 		  $message['success'] = '1';
	// 		  $message['message'] = 'Video like succesfully';
	// 		  $message['details'] = $Counts;

	// 		}
	// 	  }
	// 	  else{

	// 	  $get = $this->db->get_where('videoLikeOrUnlike', ['userId' => $this->input->post('userId'), 'ownerId' => $this->input->post('ownerId')])->row_array();

	// 	  if (!empty($get)) {

	// 		$delete = $this->db->delete('videoLikeOrUnlike', ['userId' => $this->input->post('userId'), 'ownerId' => $this->input->post('ownerId')]);
	// 		if ($delete) {

	// 		  $update['status'] = '0';
	// 		  $this->db->update("videoLikeOrUnlike",$update,['userId' => $this->input->post('userId'), 'videoId' => $this->input->post('videoId')]);

	// 		  $this->db->set('likeCount', 'likeCount -1', false)->where('id', $this->input->post('videoId'))->update("userVideos");
	// 		  $this->db->set('likes', 'likes -1', false)->where('userId', $this->input->post('userId'))->update("userProfileInformation");

	// 		  $getCounts = $this->db->get_where("userVideos",['id' => $videoId])->row_array();



	// 		  $message['success'] = '2';
	// 		  $message['message'] = 'Video dislike successfully';
	// 		  $message['details'] = $getCounts;

	// 		}
	// 	  } else {

	// 		$insert = $this->db->insert('videoLikeOrUnlike', $data);
	// 		if ($insert) {

	// 		  $this->db->set('likeCount', 'likeCount +1', false)->where('id', $this->input->post('videoId'))->update("userVideos");
	// 		  $this->db->set('likes', 'likes +1', false)->where('userId', $this->input->post('userId'))->update("userProfileInformation");

	// 		  $Counts = $this->db->get_where("userVideos",['id' => $videoId])->row_array();

	// 		  $message['success'] = '1';
	// 		  $message['message'] = 'Video like succesfully';
	// 		  $message['details'] = $Counts;

	// 		}
	// 	  }
	// 	}
	// 	  echo json_encode($message);
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

	public function likeUnlikeUserVideo()
	{
		$data['userId'] = $this->input->post('userId');
		$data['videoId'] = $this->input->post('videoId');
		$get = $this->db->get_where('likedDislikeVideos', ['userId' => $this->input->post('userId'), 'videoId' => $this->input->post('videoId')])->row_array();
		if (!empty($get)) {
			$delete = $this->db->delete('likedDislikeVideos', ['userId' => $this->input->post('userId'), 'videoId' => $this->input->post('videoId')]);
			if ($delete) {

				$this->db->set('likeCount', 'likeCount -1', false)->where('id', $this->input->post('videoId'))->update("userVideos");

				$getDetails = $this->db->select("userVideos.*")
					->from("userVideos")
					->where("userVideos.id", $this->input->post('videoId'))
					->get()
					->row_array();

				$getUnlikeCount = $getDetails['likeCount'];
				$message['success'] = '2';
				$message['message'] = 'video Unliked';
				$message['videoLikeCounts'] = $getUnlikeCount;
				$message['videoId'] = $getDetails['id'];
				$message['userId'] = $this->input->post('userId');
				$message['status'] = '0';
			}
		} else {
			$insert = $this->db->insert('likedDislikeVideos', $data);

			$getId = $this->db->insert_id();

			if ($insert) {


				$this->db->set('likeCount', 'likeCount +1', false)->where('id', $this->input->post('videoId'))->update("userVideos");

				// $getDetails = $this->db->select("likeUnlikeUserPost.*,user_UploadPost.postlikeCount")
				//         			    ->from("likeUnlikeUserPost")
				//         			    ->join("user_UploadPost","user_UploadPost.id = likeUnlikeUserPost.PostId","left")
				//         			    ->get()
				//         			    ->row_array();

				$getDetailss = $this->db->select("userVideos.*")
					->from("userVideos")
					->where("userVideos.id", $this->input->post('videoId'))
					->get()
					->row_array();
				//      print_r($getDetails);
				//   die;
				$message['success'] = '1';
				$message['message'] = 'video Liked';
				$message['videoLikeCounts'] = $getDetailss['likeCount'];
				$message['videoId'] = $getDetailss['id'];
				$message['userId'] = $this->input->post('userId');
				$message['status'] = '1';
			}
		}
		echo json_encode($message);
	}






















	// 	public function getVideo(){
	//     	$startLimit = $this->input->post('startLimit');
	//         $endLimit = 10;
	// 		$userId = $this->input->post('userId');
	// 		$countNotification = $this->db->get_where('userNotification',array('userId' => $this->input->post('userId'),'status' => 0))->num_rows();

	// 		if(!empty($countNotification)){
	// 			$message['notificationCount'] = (string)$countNotification;
	// 		}
	// 		else{
	// 			$message['notificationCount'] = '0';
	// 		}

	// 		if($this->input->post('videoType') == 'following'){
	// 			// $list =  $this->db->query("SELECT sounds.title as soundTitle,sounds.id as soundId,users.username,users.name,users.followerCount as followers,users.image,userVideos.id, userVideos.userId,userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDownloads, userVideos.allowDuetReact,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join userFollow on userFollow.followingUserId = userVideos.userId left join users on users.id = userVideos.userId left join sounds on sounds.id = userVideos.soundId where userFollow.userId = $userId  and userFollow.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  ) order by RAND() LIMIT $startLimit , 5")->result_array();
	//         $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,userVideos.id, userVideos.userId,userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDownloads, userVideos.allowDuetReact,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join userFollow on userFollow.followingUserId = userVideos.userId left join users on users.id = userVideos.userId where userFollow.userId = $userId  and userFollow.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  )")->result_array();
	// 		}
	// 		else{
	// 		// $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,sounds.title as soundTitle,sounds.id as soundId, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join sounds on sounds.id = userVideos.soundId left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ORDER BY RAND() LIMIT $startLimit , 5")->result_array();

	//      	$list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' )")->result_array();

	//     }

	// 	 if(!empty($list)){
	//       $count = count($list);
	//       if($count < 9){
	//         $this->db->delete('viewVideo',array('userId' => $this->input->post('userId')));
	//       }

	// 			$message['success'] = '1';
	// 			$message['message'] = 'List Found Successfully';
	// 			foreach($list as $lists){

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
	// 				if(!empty($lists['downloadPath'])){
	// 					$lists['downloadPath'] = $lists['downloadPath'];
	// 				}
	// 				else{
	// 					$lists['downloadPath'] =  '';
	// 				}

	// 				if(empty($lists['image'])){
	// 					$lists['image'] = base_url().'uploads/no_image_available.png';
	// 				}
	// 				if(!empty($lists['hashtag'])){
	// 					$lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
	// 					$finalTagIds = explode(',',$lists['hashtag']);
	// 					foreach($finalTagIds as $finalTagId){
	// 						$hashArray = $this->db->get_where('hashtag',array('id' => $finalTagId))->row_array();
	// 						if(!empty($hashArray)){
	// 							$lists['hastagLists'][] = $hashArray;
	// 						}
	// 					}
	// 				}
	// 				else{
	// 					$lists['hashtagTitle'] = '';
	// 					$lists['hastagLists'] = [];
	// 				}
	// 				$likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'],'userId'=> $this->input->post('userId'),'status' => '1'))->row_array();
	// 				if(!empty($likeStatus)){
	// 					$lists['likeStatus'] = true;
	// 				}
	// 				else{
	// 					$lists['likeStatus'] = false;
	// 				}


	//         $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'),'followingUserId' =>$lists['userId'],'status' => '1'))->row_array();
	//         if(!empty($checkFollow)){
	//           $lists['followStatus'] = '1';
	//         }
	//         else{
	//           $lists['followStatus'] = '0';
	//         }

	// 				$message['details'][] = $lists;
	// 			}
	// 		}
	// 		else{
	//       $this->db->delete('viewVideo',array('userId' => $this->input->post('userId')));
	//       if($this->input->post('videoType') == 'following'){
	//         $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,userVideos.id, userVideos.userId,userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDownloads, userVideos.allowDuetReact,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos` left join userFollow on userFollow.followingUserId = userVideos.userId left join users on users.id = userVideos.userId  where userFollow.userId = $userId  and userFollow.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = $userId  ) ")->result_array();
	//       }
	//       else{
	//         $list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' ) ")->result_array();
	//       }

	//       if(!empty($list)){

	//   			$message['success'] = '1';
	//   			$message['message'] = 'List Found Successfully';
	//   			foreach($list as $lists){

	//           $viewVideoInsert['userId'] = $this->input->post('userId');
	//           $viewVideoInsert['videoId'] = $lists['id'];

	//           $this->db->insert('viewVideo',$viewVideoInsert);

	//           $updateVideoCount['viewCount'] = $videoLists['viewCount'] + 1;
	//           $this->Common_Model->update('userVideos',$updateVideoCount,'id',$videoLists['id']);

	//           if(!empty($lists['name'])){
	//             $lists['username'] = $lists['name'];
	//           }
	//           else{
	//             $lists['username'] = $lists['username'];
	//           }
	//   				if(!empty($lists['downloadPath'])){
	//   					$lists['downloadPath'] = $lists['downloadPath'];
	//   				}
	//   				else{
	//   					$lists['downloadPath'] =  '';
	//   				}

	//   				if(empty($lists['image'])){
	//   					$lists['image'] = base_url().'uploads/no_image_available.png';
	//   				}
	//   				if(!empty($lists['hashtag'])){
	//   					$lists['hashtagTitle'] = $this->hashTagName($lists['hashtag']);
	//   					$finalTagIds = explode(',',$lists['hashtag']);
	//   					foreach($finalTagIds as $finalTagId){
	//   						$hashArray = $this->db->get_where('hashtag',array('id' => $finalTagId))->row_array();
	//   						if(!empty($hashArray)){
	//   							$lists['hastagLists'][] = $hashArray;
	//   						}
	//   					}
	//   				}
	//   				else{
	//   					$lists['hashtagTitle'] = '';
	//   					$lists['hastagLists'] = [];
	//   				}
	//   				$likeStatus = $this->db->get_where('videoLikeOrUnlike', array('videoId' => $lists['id'],'userId'=> $this->input->post('userId'),'status'=> '1'))->row_array();
	//   				if(!empty($likeStatus)){
	//   					$lists['likeStatus'] = true;
	//   				}
	//   				else{
	//   					$lists['likeStatus'] = false;
	//   				}


	//           $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'),'followingUserId' =>$lists['userId'],'status' => '1'))->row_array();
	//           if(!empty($checkFollow)){
	//             $lists['followStatus'] = '1';
	//           }
	//           else{
	//             $lists['followStatus'] = '0';
	//           }

	//   				$message['details'][] = $lists;
	//   			}
	//   		}
	//       else{
	// 			     $message['success'] = '0';
	// 			     $message['message'] = 'NO List Found';
	//       }
	// 		}
	// 		echo json_encode($message);
	// 	}

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

	public function socialLogin()
	{
		$checkSocialId = $this->db->get_where('users', array('social_id' => $this->input->post('social_id')))->row_array();
		if (!empty($this->input->post('email'))) {
			$checkEmailId = $this->db->get_where('users', array('email' => $this->input->post('email')))->row_array();
		} else {
			$checkEmailId = '';
		}
		if (!empty($checkSocialId)) {
			if ($checkSocialId['status'] == 'Approved') {
				$checkRegId = $this->db->get_where('blockDeviceId', array('deviceId' => $checkSocialId['deviceId']))->row_array();
				if (empty($checkRegId)) {
					$datas = array('onlineStatus' => 1, 'reg_id' => $this->input->post('reg_id'), 'device_type' => $this->input->post('device_type'), 'deviceId' => $this->input->post('deviceId'));
					$update = $this->Common_Model->update('users', $datas, 'id', $checkSocialId['id']);
					if (!empty($update)) {
						$userDetails = $this->db->get_where('users', array('id' => $checkSocialId['id']))->row_array();
						if (empty($userDetails['image'])) {
							$userDetails['image'] =  base_url() . 'uploads/no_image_available.png';
						}
						if (!empty($userDetails['video'])) {
							$userDetails['video'] = base_url() . $userDetails['video'];
						} else {
							$userDetails['video'] = '';
						}
						$message['success'] = '1';
						$message['message'] = 'user login successfully';
						$message['details'] = $userDetails;
					}
				} else {
					$message['success'] = '0';
					$message['message'] = 'Your Account has been declined. Please contact support@LiveBazaar.com';
				}
			} else {
				$message = array(
					'success' => '0',
					'message' => 'Your Account has been declined. Please contact support@LiveBazaar.com',
				);
			}
		} elseif (!empty($checkEmailId)) {
			if ($checkEmailId['status'] == 'Approved') {
				$checkRegId = $this->db->get_where('blockDeviceId', array('deviceId' => $checkEmailId['deviceId']))->row_array();
				if (empty($checkRegId)) {
					$datas1 = array('onlineStatus' => 1, 'reg_id' => $this->input->post('reg_id'), 'device_type' => $this->input->post('device_type'), 'social_id' => $this->input->post('social_id'), 'deviceId' => $this->input->post('deviceId'));
					$update1 = $this->Common_Model->update('users', $datas1, 'id', $checkEmailId['id']);
					if (!empty($update1)) {
						$userDetails1 = $this->db->get_where('users', array('id' => $checkEmailId['id']))->row_array();
						if (empty($userDetails1['image'])) {
							$userDetails1['image'] =  base_url() . 'uploads/no_image_available.png';
						}
						$message['success'] = '1';
						$message['message'] = 'user login successfully';
						$message['details'] = $userDetails1;
					}
				} else {
					$message['success'] = '0';
					$message['message'] = 'Your Account has been declined. Please contact support@LiveBazaar.com';
				}
			} else {
				$message = array(
					'success' => '0',
					'message' => 'Your Account has been declined. Please contact support@LiveBazaar.com',
				);
			}
		} else {
			$checkRegId = $this->db->get_where('blockDeviceId', array('deviceId' => $this->input->post('deviceId')))->row_array();
			if (empty($checkRegId)) {
				$datass['username'] = '@' . rand(100000000, 999999999);
				$datass['name'] = $this->input->post('name');;
				$datass['social_id'] = $this->input->post('social_id');
				$datass['email'] = $this->input->post('email');
				$datass['phone'] = $this->input->post('phone');
				$datass['reg_id'] = $this->input->post('reg_id');
				$datass['deviceId'] = $this->input->post('deviceId');
				$datass['image'] = $this->input->post('image');
				$datass['expCoin'] = '0';
				$datass['leval'] = '0';
				$datass['wallet'] = '0';
				$datass['coin'] = '0';
				$datass['incomeDollar'] = '0';
				$datass['purchasedCoin'] = '0';
				$datass['device_type'] = $this->input->post('device_type');
				$datass['login_type'] = 'normal';
				$datass['created'] = date('Y-m-d H:i:s');
				$insert = $this->db->insert('users', $datass);
				if (!empty($insert)) {
					$insert_id = $this->db->insert_id();
					$userDetails = $this->db->get_where('users', array('id' => $insert_id))->row_array();

					$blockData['userId'] = $insert_id;
					$blockData['blockUserId'] = $insert_id;
					$blockData['created'] = date('Y-m-d H:i:s');
					$this->db->insert('blockUser', $blockData);

					$infoUserRegister['userId'] = $insert_id;
					$this->db->insert('userProfileInformation', $infoUserRegister);


					if (empty($userDetails['image'])) {
						$userDetails['image'] =  base_url() . 'uploads/no_image_available.png';
					}
					$message = array('success' => '1', 'message' => 'User login successfully', 'details' => $userDetails);
				} else {
					$message = array('success' => '0', 'message' => 'Please Try after some time');
				}
			} else {
				$message['success'] = '0';
				$message['message'] = 'Your Account has been declined. Please contact support@zebolive.com';
			}
		}
		echo json_encode($message);
	}


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

	//    $phone = $this->input->post('phone');

	//    if($phone != null){

	//    $checkData = $this->db->get_where('users',array('phone' => $this->input->post('phone')))->row_array();


	//   		 $otp = rand(1000,9999);
	//   		 $datas['loginOtp'] = $otp;
	//   		 $update = $this->Common_Model->update('users',$datas,'phone',$this->input->post('phone'));
	//   		 $message['success'] = '1';
	//   		 $message['message'] = 'Otp Send Successfully';
	//   		 $message['otp'] = (string)$otp;
	//      }
	//   	 else{
	//   		 $message['success'] = '0';
	//   		 $message['message'] = 'Please your parameter';
	//   	 }
	// //
	// 	 echo json_encode($message);
	//  }

	public function loginPhone()
	{

		if ($this->input->post('phone') == '7770000000') {

			echo json_encode([

				"success" => "1",
				"message" => "otp found",
				"otp" => 256889,
			]);
			exit;
		} else {

			$phone = $this->input->post('phone');

			if ($phone != null) {

				$checkData = $this->db->get_where('users', array('phone' => $this->input->post('phone')))->row_array();


				$otp = rand(1000, 9999);
				$datas['loginOtp'] = $otp;
				$update = $this->Common_Model->update('users', $datas, 'phone', $this->input->post('phone'));
				$message['success'] = '1';
				$message['message'] = 'Otp Send Successfully';
				$message['otp'] = (string)$otp;
			} else {
				$message['success'] = '0';
				$message['message'] = 'Please your parameter';
			}
			//
			echo json_encode($message);
		}
	}



	public function loginRegisterUser()
	{

		$phone = $this->input->post('phone');
		//  $otp = $this->input->post('otp');

		if ($phone != null) {

			$checkOtp = $this->db->get_where('users', ['phone' => $phone])->row_array();

			if (!!$checkOtp) {

				$checkData = $this->db->get_where('users', ['phone' => $phone])->row_array();


				$getId = $checkData['id'];

				if (!!$checkData) {

					$getStatus = $this->db->get_where('userLiveRequest', ['userId' => $getId])->row_array();

					if (!!$getStatus) {
						$checkData['host_status'] = $getStatus['host_status'];
					} else {
						$checkData['host_status'] = '0';
					}

					if (!!$checkData['image']) {

						$checkData['image'] = $checkData['image'];
					} else {
						$checkData['image'] = "";
					}

					$message['success'] = '1';
					$message['message'] = 'User login successully';
					$message['details'] = $checkData;
				} else {
					$message['success'] = '0';
					$message['message'] = 'Invalid OTP, Please enter valid OTP';
				}
			} else {
				if(!$this->input->post('city')){
					echo json_encode([
						'success' => '0',
						'message' => 'city required'
					]);exit;
				}
				$data['deviceId'] = $this->input->post('deviceId') ?? "";
				$data['phone'] = $this->input->post('phone');
				$data['reg_id'] = $this->input->post('reg_id') ?? "";
				$data['country'] = $this->input->post('country') ?? "";
				$data['expCoin'] = '0';
				$data['leval'] = '0';
				$data['coin'] = '0';
				$data['purchasedCoin'] = '0';
				//  $data['username'] = '@5000000'.rand(10,99);
				$data['username'] = substr($this->input->post('name'), 0, 4) . date('his');
				$data['wallet'] = '0';
				$data['incomeDollar'] = '0';
				$data['device_type'] = $this->input->post('device_type') ?? "";
				$data['login_type'] = 'normal';
				$data['onlineStatus'] = 1;
				$data['status'] = 'Approved';
				$data['city'] = $this->input->post('city');
				$data['created'] = date('Y-m-d H:i:s');
				$upload = $this->db->insert("users", $data);
				$insert_id = $this->db->insert_id();

				if ($upload == true) {
					$grtDetails = $this->db->get_where("users", ['id' => $insert_id])->row_array();

					$message['success'] = '1';
					$message['message'] = 'User register successfully';
					$message['details'] = $grtDetails;
				} else {
					$message['success'] = '0';
					$message['message'] = 'Something went wrong!';
				}
			}
		} else {
			$message['success'] = '0';
			$message['message'] = 'please enter valid params!';
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
			// 			if(!empty($this->input->post('email'))){
			// 			    	$check_email = $this->db->get_where('userDetails',array('email'=>$this->input->post('email')))->row_array();
			// 			}

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
				'dob' => $this->input->post('dob')
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
					//       	$this->load->library('email');
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
						'message' 	=> $message,
						'title'		=> 'PetroWagon',
						'subtitle'	=> 'Response',
						'vibrate'	=> 1,
						'sound'		=> 1,
						'largeIcon'	=> 'large_icon',
						'smallIcon'	=> 'small_icon',
						'type'      => $type
					);
					$fields = array(
						'registration_ids' 	=> $registrationIds,
						'data'			=> $msg
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
		static	$channelName = "joashp";
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
				}
				$dd[] = $list;
			}
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

	//  public function userBlock(){
	// 	 $checkBlock = $this->db->get_where('blockUser',array('userId' => $this->input->post('userId'),'blockUserId' => $this->input->post('blockUserId')))->row_array();
	// 	 if(!empty($checkBlock)){
	// 		 $this->db->delete('blockUser',array('id' => $checkBlock['id']));
	// 		 $message['success'] = '1';
	// 		 $message['message'] = 'user unblock successfully';
	// 	 }
	// 	 else{
	// 		 $data['userId'] = $this->input->post('userId');
	// 		 $data['blockUserId'] = $this->input->post('blockUserId');
	// 		 $data['created'] = date('Y-m-d H:i:s');
	// 		 $insert = $this->db->insert('blockUser',$data);
	// 		 if(!empty($insert)){
	// 			 $this->db->delete('userFollow',array('userId' => $this->input->post('userId'),'followingUserId' => $this->input->post('blockUserId')));
	// 			 $this->db->delete('userFollow',array('userId' => $this->input->post('blockUserId'),'followingUserId' => $this->input->post('userId')));
	// 			 $message['success'] = '1';
	// 			 $message['message'] = 'user block successfully';
	// 		 }
	// 		 else{
	// 			 $message['success'] = '0';
	// 			 $message['message'] = 'Please try after some time';
	// 		 }
	// 	 }
	// 	 echo json_encode($message);
	//  }

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
				'message' 	=> $message,
				'title'		=> 'LiveBazaar',
				'type'		=> $type,
				'subtitle'	=> $type,
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
				'vibrate'	=> 1,
				'sound'		=> 1,
				'largeIcon'	=> 'large_icon',
				'smallIcon'	=> 'small_icon',
			);


			$fields = array(
				'registration_ids' 	=> $registrationIds,
				'data'			=> $msg
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
		//	$receiverId = $this->input->post('receiverId');
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

	//   public function agoraToken(){
	//     $checkUser = $this->db->get_where('users',array('id' => $this->input->post('userId')))->row_array();
	//     // $chechRequest = $this->db->get_where('userLiveRequest',array('userId' => $this->input->post('userId')))->row_array();

	//     // $getStatus = $chechRequest['host_status'];
	//     if(empty($checkUser)){
	//       $message['success'] = '0';
	//       $message['message'] = 'please logout and login again';
	//     }
	//     // elseif(empty($chechRequest)){
	//     //     $message['success'] = '0';
	//     //     $message['message'] = 'please send Host request';
	//     // }
	//     else{

	//     //   if($checkUser['liveStatus'] == '1'){
	//         //   if($chechRequest['host_status'] == '2'){
	//         require APPPATH.'/libraries/agora/RtcTokenBuilder.php';
	//         require APPPATH.'/libraries/agora/RtmTokenBuilder.php';

	//         // $appID = "0ebf0179ad5f47ef93f32cf7f6851e1b";
	//         // $appCertificate = "0405943eabe04260acb48aedb6102605";
	//         $appID = "31fffdc5a307424981001739c2058aff";
	//         $appCertificate = "9505a67d7a5e4d73810774886ad0d290";
	//         $channelName = $this->input->post('channelName');
	//         $uid = '';
	//         $uidStr = '';
	//         $role = RtcTokenBuilder::RoleAttendee;
	//         $expireTimeInSeconds = 10800;
	//         $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
	//         $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
	//         $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);

	//         $roleb = RtmTokenBuilder::RoleRtmUser;
	//         $expireTimeInSecondsb = 10800;
	//         $currentTimestampb = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
	//         $privilegeExpiredTsb = $currentTimestampb + $expireTimeInSecondsb;
	//         $userii =  $this->input->post('userId');
	//         $tokenb = RtmTokenBuilder::buildToken($appID, $appCertificate, $userii, $roleb, $privilegeExpiredTsb);


	//         if(!empty($token)){
	//           $data['userId'] = $this->input->post('userId');
	//           $data['channelName'] = $this->input->post('channelName');
	//           $data['latitude'] = $this->input->post('latitude');
	//           $data['longitude'] = $this->input->post('longitude');
	//           $data['hostType'] = $this->input->post('hostType');
	//           $data['count'] = $this->input->post('count');
	//           $data['token'] = $token;
	//           $data['rtmToken'] = $tokenb;
	//           $data['created'] = date('Y-m-d H:i:s');
	//           $data['status'] = 'live';
	//           $insert = $this->db->insert('userLive',$data);
	//           $ids = $this->db->insert_id();
	//           if(!empty($insert)){
	//             $checkFollow = $this->db->get_where('userFollow', array('followingUserId' => $this->input->post('userId'),'status' => '1'))->num_rows();
	//       			 if(!empty($checkFollow)){
	//       				 $outPut['followerCount'] = (string)$checkFollow;
	//       			 }
	//       			 else{
	//       				 $outPut['followerCount'] = '0';
	//       			 }

	//              $userId = $this->input->post('userId');
	//              // $lists = $this->db->get_where('userFollow',array('followingUserId' => $userId,'status' => '1'))->result_array();
	//              // if(!empty($lists)){
	//              //     foreach($lists as $list){
	//              //         $loginUserDetails = $this->db->get_where('users',array('id' => $userId))->row_array();
	//              //         $getUserId = $this->db->get_where('users',array('id' => $list['userId']))->row_array();
	//              //         $regId = $getUserId['reg_id'];
	//              //         $mess = $loginUserDetails['username'].' Just Live';
	//              //         if(empty($loginUserDetails['image'])){
	//              //           $liveuserimage['image'] = base_url().'uploads/no_image_available.png';
	//              //         }
	//              //         else{
	//              //          $liveuserimage['image'] = $loginUserDetails['image'];
	//              //         }
	//              //         $liveUsername = $loginUserDetails['username'];
	//              //         $this->liveNotification($regId,$mess,'liveUser',$list['userId'],$userId,$liveuserimage,$liveUsername,$this->input->post('channelName'),$this->input->post('latitude'),$this->input->post('longitude'),$token,$tokenb);
	//              //         $notiMess['loginId'] = $userId;
	//              //         $notiMess['userId'] = $list['userId'];
	//              //         $notiMess['message'] = $mess;
	//              //         $notiMess['type'] = 'liveUser';
	//              //         $notiMess['notiDate'] = date('Y-m-d');
	//              //         $notiMess['created'] = date('Y-m-d H:i:s');
	//              //         $this->db->insert('userNotification',$notiMess);
	//              //     }
	//              // }
	//             $userDetails = $this->db->get_where('users',array('id' => $this->input->post('userId')))->row_array();
	//             $todyDD = date('Y-m-d');
	//             $checkStarStatus = $this->db->get_where('userStar',array('userId' => $this->input->post('userId'),'created' => $todyDD))->row_array();
	//             if(!empty($checkStarStatus)){
	//               $starStatus = $checkStarStatus['star'];
	//               $starStatusstarCount = $checkStarStatus['starCount'];
	//               $starListStatus =  $this->db->query("SELECT * FROM starList WHERE starCount BETWEEN 1 AND $starStatusstarCount order by id desc limit 1")->row_array();
	//               if(!empty($starListStatus['box'])){
	//                 $starBOX = $starListStatus['box'];
	//               }
	//               else{
	//                 $starBOX = 0;
	//               }
	//             }
	//             else{
	//               $starStatus = '0';
	//               $starBOX = 0;
	//             }
	//             $todyDD = date('Y-m-d');
	//             $mainUserId = $this->input->post('userId');
	//             $checkStarStatus12 = $this->db->query("SELECT * FROM `starBoxResult` where userId = $mainUserId and box = $starBOX and date(created) = '$todyDD'")->num_rows();
	//             if(!empty($checkStarStatus12)){
	//               $outPut['checkBoxStatus'] = '0';
	//             }
	//             else{
	//               $outPut['checkBoxStatus'] = '1';
	//             }


	//             $outPut['name'] = $userDetails['name'];
	//             $outPut['hostImage'] = $userDetails['image'];
	//             $outPut['coin'] = $userDetails['coin'];
	//             $outPut['userLeval'] = $userDetails['leval'];
	//          	$outPut['starCount'] = $starStatus;
	//          	$outPut['count'] = $data['count'];
	//             $outPut['toke'] = $token;
	//             $outPut['box'] = (string)$starBOX;
	//             $outPut['channelName'] = $this->input->post('channelName');
	//             $outPut['rtmToken'] = $tokenb;
	//             $outPut['mainId'] = (string)$ids;
	//             $message['success'] = '1';
	//             $message['message'] = 'Token Generate Successfully';
	//             $message['details'] = $outPut;
	//           }
	//           else{
	//             $message['success'] = '0';
	//             $message['message'] = 'Please try after some time';
	//           }
	//         }
	//         else{
	//           $message['success'] = '0';
	//           $message['message'] = 'Please Try after some time';
	//         }
	//     //   }
	//     //   else{
	//     //       $message['success'] = '0';
	//     //       $message['message'] = 'user not host';
	//     //   }
	//     //   }
	//     //   else{
	//     //     $checkRequest = $this->db->get_where('userLiveRequest',array('userId' => $this->input->post('userId')))->row_array();
	//     //     if(!empty($checkRequest)){
	//     //       $message['requestStatus'] = '1';
	//     //     }
	//     //     else{
	//     //       $message['requestStatus'] = '0';
	//     //     }
	//     //     $message['success'] = '0';
	//     //     $message['message'] = 'Your Account is banned for live';
	//     //   }
	//     }
	//     echo json_encode($message);
	//   }


	public function liveNotification($regId, $message, $type, $loginId, $userId, $liveuserimage, $liveUsername, $channelName, $latitude, $longitude, $token, $tokenb)
	{
		$checkMuteNotifiaton = $this->db->get_where('muteUserNotification', array('userId' => $userId, 'muteId' => $loginId, 'status' => '1'))->row_array();
		if (empty($checkMuteNotifiaton)) {
			$registrationIds =  array($regId);
			define('API_ACCESS_KEY', 'AAAA0WgbM-c:APA91bGsdT5rx9u_QmaG9cjyapHiAY6NrzMw_WbMXgEuQi0f7ZbZ6-GCQZUDU6PvHY52KBBYwqC_Dxc9a22la0ad84p1tblRcK2evrYM2ONKU-Oa0xuL9d9jgkMrTynDadMO2OL1VIJz');
			$msg = array(
				'message' 	=> $message,
				'title'		=> 'LiveBazaar',
				'type'		=> $type,
				'subtitle'	=> $type,
				'loginId' => $loginId,
				'userId' => $userId,
				'username' => $liveUsername,
				'liveuserimage' => (string)$liveuserimage,
				'channelName' => $channelName,
				'latitude' => $latitude,
				'longitude' => $longitude,
				'token' => $token,
				'rtmToken' => $tokenb,
				'vibrate'	=> 1,
				'sound'		=> 1,
				'largeIcon'	=> 'large_icon',
				'smallIcon'	=> 'small_icon',
			);
			$fields = array(
				'registration_ids' 	=> $registrationIds,
				'data'			=> $msg
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
			$list =  $this->db->query("SELECT users.id,users.name,users.coin,users.name,users.leval,users.image,users.followerCount,users.posterImage,userLive.* FROM (SELECT *, (((acos(sin(($latitude*pi()/180)) * sin((`latitude`*pi()/180))+cos(($latitude*pi()/180)) * cos((`latitude`*pi()/180)) * cos((($longitude- `longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance FROM `userLive`)userLive left join users on users.id = userLive.userId WHERE userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and userLive.status = 'live' and userLive.userId != $loginIdMain and distance <= 62.1371 AND posterImage IS NOT NULL")->result_array();
		} else {
			$loginIdMain = $this->input->post('userId');
			if ($this->input->post('type') == 1) {
				$list =  $this->db->query("select users.id,users.username,users.name,users.coin,users.leval,users.image,users.followerCount,users.posterImage,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and userLive.status = 'live' and userLive.userId != $loginIdMain ORDER BY userLive.id desc")->result_array();

				//  echo $this->db->last_query();
				//  die;


			} else {
				$loginIdMain = $this->input->post('userId');
				$follwerList = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'status' => '1'))->result_array();
				if (!empty($follwerList)) {
					foreach ($follwerList as $follwerLists) {
						$idList[] = $follwerLists['followingUserId'];
					}
					$fIds = implode(',', $idList);
					$list =  $this->db->query("select users.id,users.username,users.name,users.leval,users.coin,users.image,users.followerCount,users.posterImage,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and userLive.userId  IN ($fIds ) and userLive.status = 'live' AND posterImage IS NOT NULL ORDER BY userLive.id desc")->result_array();
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

				$id = $lists['id'];
				$posterImage = $this->db->select('host_status, profileStatus')->from('users')->where('id', $id)->get()->row_array();
				if ($posterImage) {

					$lists['host_status'] = $posterImage['host_status'];
					$lists['profileStatus'] = $posterImage['profileStatus'];
				} else {
					$lists['host_status'] = $posterImage['host_status'];
					$lists['profileStatus'] = $posterImage['profileStatus'];
				}


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

				$check_block = $this->db->get_where('blockUser', ['userId' => $this->input->post('userId'), 'blockUserId' => $lists['userId']])->row_array();
				if(empty($check_block)){

					$message['details'][] = $lists;
				}

			}
		} else {
			$message['success'] = '0';
			$message['message'] = 'No List found';
		}
		if(empty($message['details'])){
			echo json_encode([
				'success' => '0',
				'message' => 'no list found'
			]);exit;
		}
		echo json_encode($message);
	}

	//   public function getLiveUserList(){
	//     if(!empty($this->input->post('latitude'))){
	//       $latitude = $this->input->post('latitude');
	//       $longitude = $this->input->post('longitude');
	//       $loginIdMain = $this->input->post('userId');
	//       $list =  $this->db->query("SELECT users.id,users.name,users.coin,users.name,users.leval,users.image,users.followerCount,users.posterImage,userLive.* FROM (SELECT , (((acos(sin(($latitude*pi()/180))  sin((`latitude`*pi()/180))+cos(($latitude*pi()/180))  cos((`latitude`*pi()/180)) * cos((($longitude- `longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance FROM `userLive`)userLive left join users on users.id = userLive.userId WHERE userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and ( userLive.status = 'live' or userLive.status = 'archived' ) and userLive.userId != $loginIdMain and distance <= 62.1371 AND posterImage IS NOT NULL")->result_array();
	//     }
	//     else{
	//       $loginIdMain = $this->input->post('userId');
	//       $country = $this->input->post('country');
	//       if($this->input->post('type') == 1 && !empty($country)){
	//          $list =  $this->db->query("select users.id,users.username,users.name,users.coin,users.leval,users.image,users.followerCount,users.posterImage,users.country,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and users.country = '$country' and ( userLive.status = 'live' or userLive.status = 'archived' ) and userLive.userId != $loginIdMain AND posterImage IS NOT NULL ORDER BY userLive.id desc")->result_array();
	//       }else if($this->input->post('type') == 1 && empty($country)){
	// 		$list =  $this->db->query("select users.id,users.username,users.name,users.coin,users.leval,users.image,users.followerCount,users.posterImage,users.country,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and ( userLive.status = 'live' or userLive.status = 'archived' ) and userLive.userId != $loginIdMain AND posterImage IS NOT NULL ORDER BY userLive.id desc")->result_array();
	// 	  }
	//       else{
	//         $loginIdMain = $this->input->post('userId');
	//           $follwerList = $this->db->get_where('userFollow',array('userId' => $this->input->post('userId'),'status' => '1'))->result_array();
	//           if(!empty($follwerList)){
	//             foreach($follwerList as $follwerLists){
	//               $idList[] = $follwerLists['followingUserId'];
	//             }
	//             $fIds = implode(',',$idList);
	//           $list =  $this->db->query("select users.id,users.username,users.name,users.leval,users.coin,users.image,users.followerCount,users.posterImage,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and userLive.userId  IN ($fIds ) and ( userLive.status = 'live' or userLive.status = 'archived' ) AND posterImage IS NOT NULL ORDER BY userLive.id desc")->result_array();
	//          }
	//       }
	//      }
	//      $useriNfo = $this->db->get_where('users',array('id' => $this->input->post('userId')))->row_array();
	//      if(!empty($list)){
	//       $message['status'] = '1';
	//       $message['message'] = 'List found successfully';


	//         $ids = array_column($list, 'username');
	//         $ids = array_unique($ids);
	//         $list = array_filter($list, function ($key, $value) use ($ids) {
	//             return in_array($value, array_keys($ids));
	//         }, ARRAY_FILTER_USE_BOTH);

	//       //$input = array_map("unserialize", array_unique(array_map("serialize", $list)));

	//       // print_r($list);
	//       // die;
	//       foreach($list as $lists){

	// 		$id = $lists['userId'];
	// 		$liveId = $lists['id'];
	// 		$posterImage = $this->db->select('host_status, profileStatus')->from('users')->where('id', $id)->get()->row_array();
	// 		if($posterImage){

	// 			$lists['host_status'] = $posterImage['host_status'];
	// 			$lists['profileStatus'] = $posterImage['profileStatus'];

	// 		}else{
	// 			$lists['host_status'] = $posterImage['host_status'];
	// 			$lists['profileStatus'] = $posterImage['profileStatus'];
	// 		}
	// 		$coinsTotal = $this->db->select_sum('coin')
	// 								->from('userGiftHistory')
	// 								->where('liveId', $liveId)->get()->row_array();
	// 		$lists['liveGiftings'] = $coinsTotal['coin'];

	// 		$countStar = $this->db->select_sum('coin')
	// 		->from('userGiftHistory')
	// 		->where('giftUserId', $id)
	// 		->where('created', date('Y-m-d'))
	// 		->get()->row_array();


	// 		$lists['starCount'] = $countStar['coin'];




	//          $todyDD = date('Y-m-d');

	//          $checkStarStatus = $this->db->get_where('userStar',array('userId' => $lists['userId'],'created' => $todyDD))->row_array();
	//          if(!empty($checkStarStatus)){
	//           $starStatus = $checkStarStatus['star'];
	//           $starStatusstarCount = $checkStarStatus['starCount'];
	//           if($starStatus != 0){
	//              $checkBoxCount = $this->db->get_where('starList',array('star' => $starStatus))->row_array();
	//              $starBOX = $checkBoxCount['box'];
	//           }
	//           else{
	//              $starBOX = 0;
	//           }
	//          }
	//          else{
	//           $starStatus = '0';
	//           $starBOX = 0;
	//          }
	//          $todyDD = date('Y-m-d');
	//          $mainUserId = $this->input->post('userId');
	//          $mainLiveId = $lists['id'];
	//          $checkStarStatus12 = $this->db->query("SELECT * FROM `starBoxResult` where userId = $mainUserId and liveId= $mainLiveId and box = $starBOX and date(created) = '$todyDD'")->num_rows();
	//          if(!empty($checkStarStatus12)){
	//           $lists['checkBoxStatus'] = '0';
	//          }
	//          else{
	//           $lists['checkBoxStatus'] = '1';
	//          }

	//          $lists['box'] = (string)$starBOX;


	//          $lists['purchasedCoin'] = $lists['coin'];
	//          $lists['userLeval'] = $lists['leval'];
	//         //  $lists['startCount'] = $starStatus;
	//         if(empty($lists['image'])){
	//         //   $lists['image'] = base_url().'uploads/liveDummy.png';
	//           $lists['image'] = base_url().'uploads/logo (5).png';
	//         }
	//         else{
	//           $lists['image'] = $lists['image'];
	//         }
	//         $lists['created'] = $this->sohitTime($lists['created']);

	//         $checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'),'followingUserId' =>$lists['userId'],'status' => '1'))->row_array();
	//   			 if(!empty($checkFollow)){
	//   				 $lists['followStatus'] = '1';
	//   			 }
	//   			 else{
	//   				 $lists['followStatus'] = '0';
	//   			 }

	//         $message['details'][] = $lists;
	//       }
	//      }
	//      else{
	//       $message['status'] = '0';
	//       $message['message'] = 'No List found';
	//      }
	//      echo json_encode($message);
	//   }



	public function archivedLive()
	{
		$data['status'] = 'archived';
		$data['archivedDate'] = date('Y-m-d H:i:s');

		// $insData['userId'] = $this->input->post('id');
		// $insData['startLimit'] = $data['status'];
		// $insData['country'] = $data['archivedDate'];
		// $this->db->insert('testing', $insData);


		$this->Common_Model->update('userLive', $data, 'id', $this->input->post('id'));
		$message['success'] = '1';
		$message['message'] = 'Live Streming Archived Successfully';
		echo json_encode($message);
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

	//   public function banLive(){
	//    $checkBlock = $this->db->get_where('banLiveUser',array('userIdLive' => $this->input->post('userIdLive'),'userIdViewer' => $this->input->post('userIdViewer')))->row_array();
	//  	 if(!empty($checkBlock)){
	//  		 $this->db->delete('banLiveUser',array('id' => $checkBlock['id']));
	//  		 $message['success'] = '1';
	//      $message['status'] = false;
	//  		 $message['message'] = 'user unbanned successfully';
	//  	 }
	//  	 else{
	//      $data['userIdLive'] = $this->input->post('userIdLive');
	//      $data['userIdViewer'] = $this->input->post('userIdViewer');
	//      $data['created'] = date('Y-m-d H:i:s');
	//  		 $insert = $this->db->insert('banLiveUser',$data);
	//  		 if(!empty($insert)){
	//  			 $message['success'] = '1';
	//        $message['status'] = true;
	//  			 $message['message'] = 'user ban successfully';
	//  		 }
	//  		 else{
	//  			 $message['success'] = '0';
	//  			 $message['message'] = 'Please try after some time';
	//  		 }
	//  	 }
	//  	 echo json_encode($message);
	//   }

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
			// 				$this->db->delete('viewVideo',array('userId' => $this->input->post('userId')));
			// 			}
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
		//   	$this->db->delete('viewVideo',array('userId' => $this->input->post('userId')));

		//     $videoList =  $this->db->query("SELECT users.username,users.name,users.followerCount,users.image, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.thumbnail, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and (users.hotlist = '1'|| userVideos.status = '1') and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and  userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' )  ORDER BY RAND() LIMIT $startLimit , 10")->result_array();

		//     if(!empty($videoList)){
		//       $message['success'] = '1';
		//       $message['message'] = 'details found Successfully';
		//       foreach($videoList as $videoLists){
		//         $viewVideoInsert['userId'] = $this->input->post('userId');
		// 				$viewVideoInsert['videoId'] = $videoLists['id'];
		// 				$this->db->insert('viewVideo',$viewVideoInsert);
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
		$data['frist'] = 'We Advocate healthy and postive broadcasts. Live content involving  violence, vulgarity,alcohol and smoking are stricitly prohibited and in case or violation your account will be suspended.';
		$data['sec'] = 'Join the fun and grab.';
		$data['third'] = 'Welocme to LiveBazaar';
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
				$history =  $this->db->query("select users.username,users.image,users.phone,users.name,livegift.title as giftTitle,livegift.primeAccount as giftCoin,concat('$url',livegift.image) as giftImage		, userGiftHistory.userId,userGiftHistory.created from userGiftHistory left JOIN users on users.id = userGiftHistory.userId left join livegift on livegift.id = userGiftHistory.giftId where userGiftHistory.giftUserId = $userId and userGiftHistory.pkHostId = '$pkHostid'")->result_array();
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
	{ // dateFlu Api.
		$api_key = 'rzp_test_AzPWWLQ3g7iII9';
		$api_secret = 'y3cfAWGhx4wvRbBIDpQcDIPo';
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
			'message' 	=> "sdfsdf",
			'title'		=> 'LiveBazaar',
			'type'		=> "video",
			'subtitle'	=> "video",
			'loginId' => 1,
			'userId' => 2,
			'vibrate'	=> 1,
			'sound'		=> 1,
			'largeIcon'	=> 'large_icon',
			'smallIcon'	=> 'small_icon',
		);


		$fields = array(
			'registration_ids' 	=> $registrationIds,
			'data'			=> $msg
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
			$details = $this->db->query("select userGiftHistory.coin, u.id,u.userId,u.archivedDate,u.created,u.channelName,TIMEDIFF(TIME(u.archivedDate),TIME(u.created))/60 as duration from userLive u left join userGiftHistory on userGiftHistory.liveId = u.id where u.archivedDate != '' and DATE_FORMAT(u.created ,'%Y-%m') = '$dateTime' and u.userId = $userId")->result_array();
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
					'message' 	=> $notyMessage,
					'title'		=> 'LiveBazaar',
					'type'		=> 'liveUser',
					'subtitle'	=> 'liveUser',
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
					'vibrate'	=> 1,
					'sound'		=> 1,
					'largeIcon'	=> 'large_icon',
					'smallIcon'	=> 'small_icon',
				);

				// print_r($msg);
				// die;
				$fields = array(
					'registration_ids' 	=> $registrationIds,
					'data'			=> $msg
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
							'message' 	=> $notyMessage,
							'title'		=> 'LiveBazaar',
							'type'		=> 'liveUser',
							'subtitle'	=> 'liveUser',
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
							'vibrate'	=> 1,
							'sound'		=> 1,
							'largeIcon'	=> 'large_icon',
							'smallIcon'	=> 'small_icon',
						);
						$fields = array(
							'registration_ids' 	=> $registrationIds,
							'data'			=> $msg
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

	// 	public function liveShippingAddress()
	// 	{

	// 		  if (!$this->input->post("userId") || !$this->input->post("type")) {
	// 			echo json_encode([
	// 			  'success' => '0',
	// 			  'message' => 'Please enter valid param!'
	// 			]);
	// 			exit;
	// 		  }
	// 		$userId = $this->db->get_where('liveShippingAddress', array('userId' => $this->input->post('userId'),'type' => $this->input->post('type')))->row_array();
	// 		if (!empty($userId)) {
	// 			$data['fullname'] = $this->input->post("fullname");
	// 			$data['address'] = $this->input->post("address");
	// 			$data['address2'] = $this->input->post("address2");
	// 			$data['city'] = $this->input->post("city");
	// 			$data['state'] = $this->input->post("state");
	// 			$data['postal_code'] = $this->input->post("postal_code");
	// 			$data['country'] = $this->input->post("country");
	// 			$data['userId'] = $this->input->post("userId");
	// 			$data['phone'] = $this->input->post("phone");
	// 			$data['updated'] = date('Y-m-d H:i:s');
	// 			$type = $this->input->post("type");

	// 			$update = $this->db->update('liveShippingAddress', $data,  ['userId' => $this->input->post('userId'),'type' => $type]);

	// 			if ($update == true) {

	// 				$message = [
	// 					'success' => '1',
	// 					'message' => 'address updated Done'

	// 				];
	// 			} else {
	// 				$message = [
	// 					'success' => '0',
	// 					'message' => 'Something went wrong'
	// 				];
	// 			}
	// 		} else {
	// 			$data['fullname'] = $this->input->post("fullname");
	// 			$data['address'] = $this->input->post("address");
	// 			$data['address2'] = $this->input->post("address2");
	// 			$data['city'] = $this->input->post("city");
	// 			$data['state'] = $this->input->post("state");
	// 			$data['postal_code'] = $this->input->post("postal_code");
	// 			$data['country'] = $this->input->post("country");
	// 			$data['userId'] = $this->input->post("userId");
	// 			$data['phone'] = $this->input->post("phone");
	// 			$data['type'] = $this->input->post("type");
	// 			$data['created'] = date('Y-m-d H:i:s');

	// 			$insert = $this->db->insert('liveShippingAddress', $data);
	// 			if ($insert == true) {

	// 				$message = [
	// 					'success' => '1',
	// 					'message' => 'address addded succesfully'

	// 				];
	// 			} else {
	// 				$message = [
	// 					'success' => '0',
	// 					'message' => 'Please try After Some Time'
	// 				];
	// 			}
	// 		}


	// 		echo json_encode($message);
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

	public function sendGift()
	{
		$data['userId'] = $this->input->post('senderId');
		$data['giftUserId'] = $this->input->post('receiverId');
		$data['diamond'] = $this->input->post('diamond');
		$data['giftId'] = $this->input->post('giftId');
		$data['liveId'] = $this->input->post('liveId');
		$data['created'] = date('Y-m-d H:i:s');
		$insert = $this->db->insert("userGiftHistory", $data);

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

	//   public function searchUsers()
	//   {

	//     try {

	//     //   $records = $this->db->from("users");

	//     //   if (!!$this->input->post("search"))
	//     //     $records = $records->like("name", $this->input->post("search"));


	//     //   $records = $records->get()->result_array();

	//       $search = $this->input->post("search") ?? '';

	//       $records = $this->db->select('*')

	//                     ->from('users')

	//                     ->like('username', $search)
	//                     ->where('users.id !=',$this->input->post("userId"))

	//                      ->get()->result_array();

	//       if (!!$records) {

	//           foreach($records as $get){

	//               $get['image'] = base_url().$get['image'];

	//               $final[] = $get;
	//           }
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



	public function searchUsers()
	{
		try {

			$search = $this->input->post("search") ?? '';

			$records = $this->db->select('*')

				->from('users')
				->like('username', $search)
				->where('users.id !=', $this->input->post("userId"))
				// ->where("users.blockUser !=","1")
				->where("users.blockerBy !=", $this->input->post("userId"))
				->get()->result_array();

			if (!!$records) {

				foreach ($records as $get) {

					$Id = $get['id'];

					$checkStatus = $this->db->get_where("likeDislikeUser", ['userId' => $Id, 'likeDislike_type' => 'like'])->result_array();

					$checkSuperLike = $this->db->get_where("likeDislikeUser", ['userId' => $Id, 'likeDislike_type' => 'superlike'])->result_array();

					$checkSuperLikeReverse = $this->db->get_where("likeDislikeUser", ['otherUserId' => $Id, 'likeDislike_type' => 'superlike'])->result_array();

					$finall = [];

					foreach ($checkStatus as $getss) {

						$getiD = $getss['otherUserId'];
						$getuiD = $getss['userId'];

						$getOtherUser = $this->db->select("likeDislikeUser.id likeDislikeUserId")
							->from("likeDislikeUser")
							->where("likeDislikeUser.userId", $getiD)
							->where("likeDislikeUser.otherUserId", $getuiD)
							->where("likeDislikeUser.likeDislike_type", "like")
							->get()
							->num_rows();


						if (!empty($getOtherUser)) {

							$getss['connect_counts'] = $getOtherUser;

							$finall[] = $getss;
						}
					}

					$FinalSuper = [];

					foreach ($checkSuperLike as $getsss) {

						$getiD = $getsss['otherUserId'];
						$getuiD = $getsss['userId'];

						$getOtherUserr = $this->db->select("likeDislikeUser.id likeDislikeUserId")
							->from("likeDislikeUser")
							->where("likeDislikeUser.userId", $getuiD)
							->where("likeDislikeUser.likeDislike_type", "superlike")
							->get()
							->num_rows();

						if (!!$getOtherUserr) {

							$getsss['connect_countss'] = $getOtherUserr;

							$FinalSuper[] = $getsss;
						}
					}
					$FinalSuperReverse = [];

					foreach ($checkSuperLikeReverse as $getSuper) {

						$getiD = $getSuper['otherUserId'];
						$getuiD = $getSuper['userId'];

						$getOtherUse = $this->db->select("likeDislikeUser.id likeDislikeUserId")
							->from("likeDislikeUser")
							->where("likeDislikeUser.otherUserId", $getiD)
							->where("likeDislikeUser.likeDislike_type", "superlike")
							->get()
							->num_rows();

						if (!empty($getOtherUse)) {

							$getSuper['connect_countss'] = $getOtherUse;

							$FinalSuperReverse[] = $getSuper;
						}
					}

					$c = (string)count($finall);
					$s = (string)count($FinalSuper);
					$sr = (string)count($FinalSuperReverse);

					$get['image'] = $get['image'];
					$get['connectionCounts'] = $c + $s + $sr;

					$getSuperLikeStatus = $this->db->select("likeDislikeUser.id likeDislikeUserId")
						->from("likeDislikeUser")
						->where("likeDislikeUser.otherUserId", $Id)
						->where("likeDislikeUser.userId", $this->input->post("userId"))
						->where("likeDislikeUser.likeDislike_type", "superlike")
						->get()
						->num_rows();

					$getSuperLikeStatusReverse = $this->db->select("likeDislikeUser.id likeDislikeUserId")
						->from("likeDislikeUser")
						->where("likeDislikeUser.userId", $Id)
						->where("likeDislikeUser.otherUserId", $this->input->post("userId"))
						->where("likeDislikeUser.likeDislike_type", "superlike")
						->get()
						->num_rows();

					if (!!$getSuperLikeStatus || !!$getSuperLikeStatusReverse) {
						$get['superLike_connection_status'] = true;
					} else {
						$get['superLike_connection_status'] = false;
					}


					$getLikeStatuss = $this->db->select("likeDislikeUser.id likeDislikeUserId")
						->from("likeDislikeUser")
						->where("likeDislikeUser.otherUserId", $Id)
						->where("likeDislikeUser.userId", $this->input->post("userId"))
						->where("likeDislikeUser.likeDislike_type", "like")
						->get()
						->num_rows();

					$getLikeStatusReversee = $this->db->select("likeDislikeUser.id likeDislikeUserId")
						->from("likeDislikeUser")
						->where("likeDislikeUser.userId", $Id)
						->where("likeDislikeUser.otherUserId", $this->input->post("userId"))
						->where("likeDislikeUser.likeDislike_type", "like")
						->get()
						->num_rows();

					if (!!$getLikeStatuss && !!$getLikeStatusReversee) {
						$get['Like_connection_status'] = true;
					} else {
						$get['Like_connection_status'] = false;
					}



					$final[] = $get;
				}

				echo json_encode([
					"success"  =>  "1",
					"message"  =>  "Record found successfully",
					"details"  =>  $final,
				]);
				exit;
			}

			echo json_encode([
				"success"  =>  "0",
				"message"  =>  "Record not found",
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


	//   public function getFollowingVideos(){


	//         $getVideos = $this->db->select("userFollow.*,userVideos.*")
	//                                 ->from("userFollow")
	//                                 ->join("userVideos","userVideos.userId = userFollow.followingUserId")
	//                                 ->where("userFollow.userId",$this->input->post("userId"))
	//                                 ->get()
	//                                 ->result_array();

	//         if(!!$getVideos){

	//           $message['success'] = '1';
	//           $message['message'] = 'details found';
	//           $message['details'] = $getVideos;
	//       }
	//       else{
	//           $message['success'] = '0';
	//           $message['message'] = 'details not found!';
	//       }

	//       echo json_encode($message);
	//   }

	public function getFollowingVideos()
	{

		$checkStatus = $this->db->select("likeDislikeUser.*")
			->from("likeDislikeUser")
			->where("likeDislikeUser.userId", $this->input->post('userId'))
			->where("likeDislikeUser.likeDislike_type", 'like')
			->get()
			->result_array();

		$otherIdd = $this->db->select("likeDislikeUser.*")
			->from("likeDislikeUser")
			->where("likeDislikeUser.userId", $this->input->post('userId'))
			->where("likeDislikeUser.likeDislike_type", 'superlike')
			->get()
			->result_array() ?? "";

		if (!!$otherIdd) {

			foreach ($otherIdd as $key => $getotherIdd) {

				$getId = $otherIdd[$key]['otherUserId'];

				$otherIdd[$key]['videos'] = $this->db->select("userVideos.*,users.name,users.username,users.image")
					->from("userVideos")
					->join("users", "users.id = userVideos.userId", "left")
					->where("userVideos.userId", $getId)
					->get()
					->result_array();
			}
		}

		$otherId = $this->db->select("likeDislikeUser.*")
			->from("likeDislikeUser")
			->where("likeDislikeUser.otherUserId", $this->input->post('userId'))
			->where("likeDislikeUser.likeDislike_type", 'superlike')
			->get()
			->result_array() ?? "";

		if (!!$otherId) {

			foreach ($otherId as $key => $getotherIdd) {

				$getId = $otherId[$key]['userId'];

				$otherId[$key]['videos'] = $this->db->select("userVideos.*,users.name,users.username,users.image")
					->from("userVideos")
					->join("users", "users.id = userVideos.userId", "left")
					->where("userVideos.userId", $getId)
					->get()
					->result_array();
			}
		}

		// PRINT_R($otherId);
		// DIE;

		if (!!$checkStatus || !!$otherIdd || !!$otherId) {

			$finalLikeDisLike = [];

			foreach ($checkStatus as $gets) {

				$otherUserId = $gets['otherUserId'];
				$userId = $gets['userId'];

				$getOtherUser = $this->db->select("likeDislikeUser.id likeDislikeUserId,likeDislikeUser.userId,likeDislikeUser.otherUserId,likeDislikeUser.likeDislike_type")
					->from("likeDislikeUser")
					->where("likeDislikeUser.userId", $otherUserId)
					->where("likeDislikeUser.otherUserId", $userId)
					->where("likeDislikeUser.likeDislike_type", "like")
					->get()
					->row_array();

				if (!!$getOtherUser) {

					$getConId = $getOtherUser['userId'];

					$getOtherUser['videos'] = $this->db->select("userVideos.*,users.name,users.username,users.image")
						->from("userVideos")
						->join("users", "users.id = userVideos.userId", "left")
						->where("userVideos.userId", $getConId)
						->get()
						->result_array();

					$finalLikeDisLike[] = $getOtherUser;

					//  print_r($finalLikeDisLike);
				}
			}

			if (!!$finalLikeDisLike || !!$otherIdd || !!$otherId) {

				echo json_encode([

					"success" => "1",
					"message" => "details found",
					"details" => array_merge($finalLikeDisLike, $otherIdd, $otherId)
				]);
				exit;
			} else {

				echo json_encode([

					"success" => "0",
					"message" => "details not found!"
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
	{ // dateFlue

		$getLevels = $this->db->get("user_levels")->result_array();

		if (!!$getLevels) {

			foreach ($getLevels as $gets) {

				$gets['image'] = $gets['image'];

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

	public function getUserTalentLevels()
	{ // date flue

		$getLevels = $this->db->get("user_talent_levels")->result_array();

		if (!!$getLevels) {

			foreach ($getLevels as $gets) {

				$gets['image'] = $gets['image'];

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


	public function getBanner()
	{

		$data = $this->db->order_by('id', 'desc')->select('nursingBanner.id , nursingBanner.banner')->get('nursingBanner')->result_array();
		if ($data) {
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

	public function socialLoginNc()
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
				$data['city'] = $this->input->post('city');
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

	public function getUserCoin()
	{

		$get = $this->db->select("users.id,users.coin")
			->from("users")
			->where("users.id", $this->input->post("userId"))
			->get()
			->row_array();

		if (!!$get) {
			$message['success'] = '1';
			$message['message'] = 'details found successully';
			$message['details'] = $get;
		} else {
			$message['success'] = '1';
			$message['message'] = 'details found successully';
			$message['details'] = $get;
		}
		echo json_encode($message);
	}


	public function sendCoinHistory()
	{
		if ($this->input->post()) {
			$userId = $this->input->post('userId');


			$getData = $this->db->select(['username', 'name', 'userGiftHistory.coin', 'userGiftHistory.created'])
				->join("userGiftHistory", "userGiftHistory.userId = users.id", "left")
				->where(['users.id' => $userId])
				->get('users')->result_array();

			foreach ($getData as $data) {
				$checkCoin = $data['coin'];
				$checkCreated = $data['created'];
				if ($checkCoin == null) {

					$sendData['success'] = '0';
					$sendData['message'] = 'list not found';
					$sendData['details'] = [[
						'name' => null,
						'username' => null,
						'coin' => $data['coin'],
						'created' => $data['creted']

					]];

					echo json_encode($sendData);
				} else {

					$sendData['success'] = '1';
					$sendData['message'] = 'list found successfully';
					$sendData['details'] = [[
						'name' => $data['name'],
						'username' => $data['username'],
						'coin' => $data['coin'],
						'created' => $data['creted']

					]];
					echo json_encode($sendData);
				}
			}
		} else {
			$sendData['success'] = '0';
			$sendData['message'] = 'enter valid input';
			echo json_encode($sendData);
		}
	}

	public function receiveCoinHistory()
	{
		if ($this->input->post()) {

			$userId = $this->input->post('userId');
			$receiveData['success'] = '1';
			$receiveData['message'] = 'list found Successfully';
			$receiveData['details'] = $this->db->select(['username', 'name', 'userGiftHistory.coin', 'userGiftHistory.created'])
				->join("userGiftHistory", "userGiftHistory.giftUserId = users.id", "left")
				->where(['giftUserId' => $userId])
				->get('users')->result_array();
			echo json_encode($receiveData);
		} else {
			$receiveData['success'] = '0';
			$receiveData['message'] = 'enter valid input';
			echo json_encode($receiveData);
		}
	}


	public function userLevel()
	{
		if ($this->input->post()) {
			$userId = $this->input->post('userId');
			// get total send coin
			$getLevel = $this->db->select(['my_level'])
				->from('users')
				->where('id', $userId)
				->get()->row_array();

			$response['success'] = "1";
			$response['message'] = "List Found Successfully";
			$response['details'] = $getLevel;
			echo json_encode($response);
		} else {
			$response['success'] = "0";
			$response['message'] = "Enter Valid Input";
			echo json_encode($response);
		}
	}

	public function userTalentLevel()
	{
		if ($this->input->post()) {
			$userId = $this->input->post('userId');
			// get total send coin
			$getLevel = $this->db->select(['talent_level'])
				->from('users')
				->where('id', $userId)
				->get()->row_array();

			$response['success'] = "1";
			$response['message'] = "List Found Successfully";
			$response['details'] = $getLevel;
			echo json_encode($response);
		} else {
			$response['success'] = "0";
			$response['message'] = "Enter Valid Input";
			echo json_encode($response);
		}
	}

	public function getGif()
	{
		$get = $this->db->get("gifs")->result_array();

		if ($get) {

			foreach ($get as $key => $list) {
				$list['gifUrl'] = base_url() . $list['gifUrl'];
				$get[$key] = $list;
			}

			echo json_encode([
				'success' => '1',
				'message' => 'list found',
				'details' => $get
			]);
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'no list found'
			]);
		}
	}

	public function setSingleGif()
	{
		if ($this->input->post()) {
			$get = $this->db->get_where('userLive', ['userId' => $this->input->post('userId')])->row_array();
			if ($get) {
				$update = $this->db->set('gifId', $this->input->post('gifId'))
					->where('userId', $this->input->post('userId'))
					->update('userLive');

				if ($update) {
					echo json_encode([
						'success' => '1',
						'message' => 'Gif id updated'
					]);
				} else {
					echo json_encode([
						'success' => '0',
						'message' => 'Gif id not updated'
					]);
				}
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'enter valid data.'
			]);
		}
	}

	public function getSingleGif()
	{
		if ($this->input->post()) {
			$get = $this->db->select('gifId')
				->from('userLive')
				->where('userId', $this->input->post('userId'))
				->get()->row_array();
			if ($get) {

				$getGif = $this->db->select('gifUrl')
					->from('gifs')
					->where('id', $get['gifId'])
					->get()->row_array();
				if ($getGif) {
					echo json_encode([
						'success' => '1',
						'message' => 'Gif found',
						'details' => $getGif = base_url() . $getGif['gifUrl']
					]);
				} else {
					echo json_encode([
						'success' => '0',
						'message' => 'No Gif found'
					]);
				}
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'enter valid data.'
			]);
		}
	}

	public function totalCoins()
	{
		if ($this->input->post()) {

			$checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (!!$checkUser) {

				echo json_encode([
					'success' => '1',
					'message' => 'User found',
					'totalCoins' => $checkUser['coin']
				]);
				exit;
			} else {

				echo json_encode([
					'success' => '0',
					'message' => 'User not found'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'please enter valid data'
			]);
		}
	}

	public function recivePurchase()
	{
		if ($this->input->post()) {

			$checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (!!$checkUser) {

				if ($this->input->post('type') == '2') {

					// minuse coins from received coins

					$rCoin = $checkUser['coin'];
					$rCoin -= $this->input->post('amount');
					$updateCoins = $this->db->set('coin', $rCoin)
						->where('id', $this->input->post('userId'))
						->update('users');
					if ($updateCoins) {
						echo json_encode([
							'success' => '1',
							'message' => 'Coins Deducted !!'
						]);
						exit;
					} else {
						echo json_encode([
							'success' => '0',
							'message' => 'Problem Occured!!'
						]);
						exit;
					}
				} else if ($this->input->post('type') == '1') {

					// add coins to purchase coins

					$purchaseCoins = $checkUser['purchasedCoin'];
					$purchaseCoins += $this->input->post('amount');
					$rCoin = $checkUser['coin'];
					$rCoin += $this->input->post('amount');
					$updateCoins = $this->db->set(['purchasedCoin' => $purchaseCoins, 'coin' => $rCoin])
						->where('id', $this->input->post('userId'))
						->update('users');

					if ($updateCoins) {
						echo json_encode([
							'success' => '1',
							'message' => 'Coins Added !!'
						]);
						exit;
					} else {
						echo json_encode([
							'success' => '0',
							'message' => 'problem Occured'
						]);
						exit;
					}
				} else {
					echo json_encode([
						'success' => '0',
						'message' => 'Invalid Type'
					]);
					exit;
				}
			} else {
				echo json_encode([
					'success' => '0',
					'message' => 'User not Exist'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'enter valid data'
			]);
		}
	}

	public function deductCoin()
	{
		if ($this->input->post()) {
			$checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if ($checkUser) {

				$rCoin = $checkUser['coin'];
				$rCoin -= $this->input->post('amount');
				$updateCoins = $this->db->set('coin', $rCoin)
					->where('id', $this->input->post('userId'))
					->update('users');

				if ($updateCoins) {
					echo json_encode([
						'success' => '1',
						'message' => 'Coins Deducted !!'
					]);
					exit;
				} else {
					echo json_encode([
						'success' => '0',
						'message' => 'problem Occured'
					]);
					exit;
				}
			} else {
				echo json_encode([
					'success' => '0',
					'message' => 'User not Found'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'Enter valid data'
			]);
			exit;
		}
	}

	public function addCoin()
	{
		if ($this->input->post()) {

			$checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (!!$checkUser) {

				$rCoin = $checkUser['coin'];
				$rCoin += $this->input->post('amount');
				$updateCoins = $this->db->set('coin', $rCoin)
					->where('id', $this->input->post('userId'))
					->update('users');

				if ($updateCoins) {
					echo json_encode([
						'success' => '1',
						'message' => 'Coins Added !!'
					]);
					exit;
				} else {
					echo json_encode([
						'success' => '0',
						'message' => 'problem Occured'
					]);
					exit;
				}
			} else {
				echo json_encode([
					'success' => '0',
					'message' => 'User not Exist'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'Enter valid Data'
			]);
		}
	}

	public function addPosterImage()
	{
		if ($this->input->post()) {

			$checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (!!$checkUser) {

				if (!empty($_FILES['posterImage']['name'])) {
					// $name1= time().'_'.$_FILES["posterImage"]["name"];
					// $name= str_replace(' ', '_', $name1);
					// $tmp_name = $_FILES['posterImage']['tmp_name'];
					// $path = 'uploads/users/'.$name;
					// $this->uploadVideo($file);
					$posterImage['posterImage'] = $this->uploadVideo($_FILES['posterImage']);
				} else {
					echo json_encode([
						'success' => '0',
						'message' => 'poster image is compulsory'
					]);
					exit;
				}

				$update = $this->db->set('posterImage', $posterImage['posterImage'])
					->where('id', $this->input->post('userId'))
					->update('users');
				if ($update) {
					echo json_encode([
						'success' => '1',
						'message' => 'poster image added successfully'
					]);
					exit;
				} else {
					echo json_encode([
						'success' => '0',
						'message' => 'poster image not added'
					]);
					exit;
				}
			} else {
				echo json_encode([
					'success' => '0',
					'message' => 'user not exist'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'please enter valid data'
			]);
		}
	}

	public function getPosterImage()
	{
		if ($this->input->post()) {

			$get = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (!!$get) {

				echo json_encode([
					'success' => '1',
					'message' => 'Poster image found',
					'details' => $get['posterImage']
				]);
				exit;
			} else {
				echo json_encode([
					'success' => '0',
					'message' => 'this user not exist'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'enter valid data'
			]);
		}
	}

	//   public function someFunctionality(){//date flue
	// 	if($this->input->post()){

	// 		// $checkUserId = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

	// 		$checkUserId = $this->db->select("users.*,user_levels.image user_levelImage,user_talent_levels.image user_talent_levelImage")
	// 		->from("users")
	// 		->join("user_levels","user_levels.level = users.my_level","left")
	// 		->join("user_talent_levels","user_talent_levels.level = users.talent_level","left")
	// 		->where("users.id",$this->input->post('userId'))
	// 		->get()
	// 		->row_array();


	// 		if(!!$checkUserId){

	// 			// $checkOtherUserId = $this->db->get_where('users', ['id' => $this->input->post('otherUserId')])->row_array();

	// 			$checkOtherUserId = $this->db->select("users.*,user_levels.image user_levelImage,user_talent_levels.image user_talent_levelImage")
	// 									->from("users")
	// 									->join("user_levels","user_levels.level = users.my_level","left")
	// 									->join("user_talent_levels","user_talent_levels.level = users.talent_level","left")
	// 									->where("users.id",$this->input->post('otherUserId'))
	// 									->get()
	// 									->row_array();

	// 			if(!!$checkOtherUserId){

	// 				if($this->input->post('userId') === $this->input->post('otherUserId')){
	// 					echo json_encode([
	// 						'success' => '1',
	// 						'message' => 'Both ids are same',
	// 						'details' => $checkUserId
	// 					]);exit;
	// 				}else{

	// 					$checkFollowStatus = $this->db->get_where('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' => $this->input->post('otherUserId'), 'status' => '1'])->row_array();
	// 					if(!!$checkFollowStatus){
	// 						$checkOtherUserId['followStatus'] = TRUE;
	// 					}else{
	// 						$checkOtherUserId['followStatus'] = FALSE;
	// 					}

	// 					echo json_encode([
	// 						'success' => '1',
	// 						'message' => 'otherUserId user details',
	// 						'details' => $checkOtherUserId
	// 					]);exit;

	// 				}

	// 			}else{
	// 				echo json_encode([
	// 					'success' => '0',
	// 					'message' => 'otherUserId user not exists'
	// 				]);exit;
	// 			}

	// 		}else{
	// 			echo json_encode([
	// 				'success' => '0',
	// 				'message' => 'userId user not exists'
	// 			]);exit;
	// 		}

	// 	}else{
	// 		echo json_encode([
	// 			'success' => '0',
	// 			'message' => 'please enter valid data'
	// 		]);
	// 	}
	//   }

	public function someFunctionality()
	{ //date flue
		if ($this->input->post()) {

			// $checkUserId = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

			$checkUserId = $this->db->select("users.*,user_levels.image user_levelImage,user_talent_levels.image user_talent_levelImage")
				->from("users")
				->join("user_levels", "user_levels.level = users.my_level", "left")
				->join("user_talent_levels", "user_talent_levels.level = users.talent_level", "left")
				->where("users.id", $this->input->post('userId'))
				->get()
				->result_array();

				
				if (!!$checkUserId) {
					

				foreach ($checkUserId as $get) {

					$Id = $get['id'];

					$checkStatus = $this->db->get_where("likeDislikeUser", ['userId' => $Id, 'likeDislike_type' => 'like'])->result_array();

					$checkSuperLike = $this->db->get_where("likeDislikeUser", ['userId' => $Id, 'likeDislike_type' => 'superlike'])->result_array();

					$checkSuperLikeReverse = $this->db->get_where("likeDislikeUser", ['otherUserId' => $Id, 'likeDislike_type' => 'superlike'])->result_array();

					$finall = [];

					foreach ($checkStatus as $getss) {

						$getiD = $getss['otherUserId'];
						$getuiD = $getss['userId'];

						$getOtherUser = $this->db->select("likeDislikeUser.id likeDislikeUserId")
							->from("likeDislikeUser")
							->where("likeDislikeUser.userId", $getiD)
							->where("likeDislikeUser.otherUserId", $getuiD)
							->where("likeDislikeUser.likeDislike_type", "like")
							->get()
							->num_rows();


						if (!empty($getOtherUser)) {

							$getss['connect_counts'] = $getOtherUser;

							$finall[] = $getss;
						}
					}

					$FinalSuper = [];

					foreach ($checkSuperLike as $getsss) {

						$getiD = $getsss['otherUserId'];
						$getuiD = $getsss['userId'];

						$getOtherUserr = $this->db->select("likeDislikeUser.id likeDislikeUserId")
							->from("likeDislikeUser")
							->where("likeDislikeUser.userId", $getuiD)
							->where("likeDislikeUser.likeDislike_type", "superlike")
							->get()
							->num_rows();

						if (!!$getOtherUserr) {

							$getsss['connect_countss'] = $getOtherUserr;

							$FinalSuper[] = $getsss;
						}
					}
					$FinalSuperReverse = [];

					foreach ($checkSuperLikeReverse as $getSuper) {

						$getiD = $getSuper['otherUserId'];
						$getuiD = $getSuper['userId'];

						$getOtherUse = $this->db->select("likeDislikeUser.id likeDislikeUserId")
							->from("likeDislikeUser")
							->where("likeDislikeUser.otherUserId", $getiD)
							->where("likeDislikeUser.likeDislike_type", "superlike")
							->get()
							->num_rows();

						if (!empty($getOtherUse)) {

							$getSuper['connect_countss'] = $getOtherUse;

							$FinalSuperReverse[] = $getSuper;
						}
					}

					$c = (string)count($finall);
					$s = (string)count($FinalSuper);
					$sr = (string)count($FinalSuperReverse);

					$get['connectionCounts'] = $c + $s + $sr;

					// print_r($get['addressId']);exit;

					$get['address'] = $this->db->get_where('addAddress', ['id' => $get['addressId']])->row_array() ? : null ;

					$get['address']['country'] = $this->db->select('name')->get_where('countries', ['id' => $get['address']['country']])->row_array() ? : null;
					$get['address']['state'] = $this->db->select('name')->get_where('states', ['id' => $get['address']['state']])->row_array() ? : null;
					$get['address']['city'] = $this->db->select('name')->get_where('cities', ['id' => $get['address']['city']])->row_array() ? : null;
 
					// print_r($get);exit;
					$final[] = $get;
				}


				// $checkOtherUserId = $this->db->get_where('users', ['id' => $this->input->post('otherUserId')])->row_array();

				$checkOtherUserId = $this->db->select("users.*,user_levels.image user_levelImage,user_talent_levels.image user_talent_levelImage")
					->from("users")
					->join("user_levels", "user_levels.level = users.my_level", "left")
					->join("user_talent_levels", "user_talent_levels.level = users.talent_level", "left")
					->where("users.id", $this->input->post('otherUserId'))
					->get()
					->result_array();

				if (!!$checkOtherUserId) {

					foreach ($checkOtherUserId as $get) {

						$Id = $get['id'];

						$checkStatus = $this->db->get_where("likeDislikeUser", ['userId' => $Id, 'likeDislike_type' => 'like'])->result_array();

						$checkSuperLike = $this->db->get_where("likeDislikeUser", ['userId' => $Id, 'likeDislike_type' => 'superlike'])->result_array();

						$checkSuperLikeReverse = $this->db->get_where("likeDislikeUser", ['otherUserId' => $Id, 'likeDislike_type' => 'superlike'])->result_array();

						$finall = [];

						foreach ($checkStatus as $getss) {

							$getiD = $getss['otherUserId'];
							$getuiD = $getss['userId'];

							$getOtherUser = $this->db->select("likeDislikeUser.id likeDislikeUserId")
								->from("likeDislikeUser")
								->where("likeDislikeUser.userId", $getiD)
								->where("likeDislikeUser.otherUserId", $getuiD)
								->where("likeDislikeUser.likeDislike_type", "like")
								->get()
								->num_rows();


							if (!empty($getOtherUser)) {

								$getss['connect_counts'] = $getOtherUser;

								$finall[] = $getss;
							}
						}

						$FinalSuper = [];

						foreach ($checkSuperLike as $getsss) {

							$getiD = $getsss['otherUserId'];
							$getuiD = $getsss['userId'];

							$getOtherUserr = $this->db->select("likeDislikeUser.id likeDislikeUserId")
								->from("likeDislikeUser")
								->where("likeDislikeUser.userId", $getuiD)
								->where("likeDislikeUser.likeDislike_type", "superlike")
								->get()
								->num_rows();

							if (!!$getOtherUserr) {

								$getsss['connect_countss'] = $getOtherUserr;

								$FinalSuper[] = $getsss;
							}
						}
						$FinalSuperReverse = [];

						foreach ($checkSuperLikeReverse as $getSuper) {

							$getiD = $getSuper['otherUserId'];
							$getuiD = $getSuper['userId'];

							$getOtherUse = $this->db->select("likeDislikeUser.id likeDislikeUserId")
								->from("likeDislikeUser")
								->where("likeDislikeUser.otherUserId", $getiD)
								->where("likeDislikeUser.likeDislike_type", "superlike")
								->get()
								->num_rows();

							if (!empty($getOtherUse)) {

								$getSuper['connect_countss'] = $getOtherUse;

								$FinalSuperReverse[] = $getSuper;
							}
						}

						$c = (string)count($finall);
						$s = (string)count($FinalSuper);
						$sr = (string)count($FinalSuperReverse);

						$get['connectionCounts'] = $c + $s + $sr;

						$get['address'] = $this->db->get_where('addAddress', ['id' => $get['addressId']])->row_array() ? : null;

						$get['address']['country'] = $this->db->select('name')->get_where('countries', ['id' => $get['address']['country']])->row_array() ? : null;
						$get['address']['state'] = $this->db->select('name')->get_where('states', ['id' => $get['address']['state']])->row_array() ? : null;
						$get['address']['city'] = $this->db->select('name')->get_where('cities', ['id' => $get['address']['city']])->row_array() ? : null;

						$finalz[] = $get;
					}

					if ($this->input->post('userId') === $this->input->post('otherUserId')) {
						echo json_encode([
							'success' => '1',
							'message' => 'Both ids are same',
							'details' => $final
						]);
						exit;
					} else {

						$checkFollowStatus = $this->db->get_where('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' => $this->input->post('otherUserId'), 'status' => '1'])->row_array();
						if (!!$checkFollowStatus) {
							$checkOtherUserId['followStatus'] = TRUE;
						} else {
							$checkOtherUserId['followStatus'] = FALSE;
						}

						echo json_encode([
							'success' => '1',
							'message' => 'otherUserId user details',
							'details' => $finalz
						]);
						exit;
					}
				} else {
					echo json_encode([
						'success' => '0',
						'message' => 'otherUserId user not exists'
					]);
					exit;
				}
			} else {
				echo json_encode([
					'success' => '0',
					'message' => 'userId user not exists'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'please enter valid data'
			]);
		}
	}

	public function banLive()
	{
		if ($this->input->post()) {

			$checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (!!$checkuser) {

				$banUser = $this->db->get_where('users', ['id' => $this->input->post('banUserId')])->row_array();
				if (!!$banUser) {

					$checkBan = $this->db->get_where('userBan', ['userId' => $this->input->post('userId'), 'banUserId' => $this->input->post('banUserId')])->row_array();
					if (!!$checkBan) {

						$delete = $this->db->delete('userBan', ['userId' => $this->input->post('userId'), 'banUserId' => $this->input->post('banUserId')]);
						if ($delete) {
							echo json_encode([
								'success' => '1',
								'message' => $this->input->post('userId') . " unbanned " . $this->input->post('banUserId')
							]);
							exit;
						} else {
							echo json_encode([
								'success' => '0',
								'message' => "some problem occured"
							]);
							exit;
						}
					} else {
						$data['userId'] = $this->input->post('userId');
						$data['banUserId'] = $this->input->post('banUserId');

						$insert = $this->db->insert('userBan', $data);
						if ($insert) {
							echo json_encode([
								'success' => '1',
								'message' => $this->input->post('userId') . " banned " . $this->input->post('banUserId')
							]);
							exit;
						} else {
							echo json_encode([
								'success' => '0',
								'message' => "some problem occured"
							]);
							exit;
						}
					}
				} else {
					echo json_encode([
						'success' => '0',
						'message' => 'banUserId user not exist'
					]);
					exit;
				}
			} else {
				echo json_encode([
					'success' => '0',
					'message' => 'userId user not exist'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'enter valid data'
			]);
		}
	}

	public function getUserLiveRequestStatus()
	{
		if ($this->input->post()) {

			$get = $this->db->select('host_status')
				->from('users')
				->where('id', $this->input->post('userId'))
				->get()->row_array();

			if (!!$get) {

				echo json_encode([
					'success' => '1',
					'message' => 'User Found',
					'host_status' => $get['host_status']
				]);
				exit;
			} else {
				echo json_encode([
					'success' => '0',
					'message' => 'No user Found'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => '0',
				'mesage' => 'enter valid data'
			]);
		}
	}

	public function getBanUserList()
	{
		$get = $this->db->get_where('users', ['banUnban' => '1'])->result_array();
		if (!!$get) {

			echo json_encode([
				'success' => '1',
				'message' => 'Ban User List Found',
				'list' => $get
			]);
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'No Ban User Found'
			]);
		}
	}


	// =================== DateFlue Apis start ======================

	// 	 public function nearByUsers()
	// 	{
	// 		if ($this->input->post()) {

	// 			$getlat = $this->input->post('latitude');
	// 			$getlong = $this->input->post('longitude');
	// 			$ID = $this->input->post('userId');
	// 			$gethospital = $this->db->select("users.*, (6731 * acos( cos( radians($getlat) ) * cos( radians( users.latitude ) ) * cos( radians(users.longitude ) - radians($getlong) ) + sin( radians($getlat) ) * sin(radians(users.latitude)) ) ) AS distance")
	// 				->from("users")
	// 				->where("users.latitude !=", '')
	// 				->where("users.longitude !=", '')
	// 				->where("users.userSwipeID !=",$ID)
	// 				->group_start()
	// 						->or_where("users.likeDislikeId =", '')
	//  				->group_end()

	// 				->having("distance <", 5)
	// 				->order_by("distance", "ASC")
	// 				->get()
	// 				->result_array();

	// 			if (!empty($gethospital)) {

	// 				foreach ($gethospital as $chil) {
	// 					$chil['image'] = base_url() . $chil['image'];

	// 					$final[] = $chil;
	// 				}
	// 				$message = array(
	// 					'success' => '1',
	// 					'message' => 'Users found successfuly',
	// 					"details" => $final
	// 				);
	// 			} else {
	// 				$message['success'] = '0';
	// 				$message['message'] = 'List not found!';
	// 			}
	// 		} else {
	// 			$message['success'] = '0';
	// 			$message['message'] = 'Please enter valid parameters!';
	// 		}


	// 		echo json_encode($message);
	// 	}

	public function updateLatLong()
	{
		if (!empty($this->input->post('userId') and $this->input->post('latitude') and $this->input->post('longitude'))) {
			$userId = $this->input->post('userId');
			$data['latitude'] = $this->input->post('latitude');
			$data['longitude'] = $this->input->post('longitude');
			$update = $this->db->update("users", $data, array('id' => $userId));
			if ($update) {

				$getDetails = $this->db->select("users.id,users.latitude,users.longitude")
					->from("users")
					->where("users.id", $userId)
					->get()
					->row_array();
				$message['success'] = '1';
				$message['message'] = 'Latitude & Longitude updated';
				$message['details'] = $getDetails;
			} else {
				$message['success'] = '0';
				$message['message'] = 'Latitude & Longitude not update';
			}
		} else {
			$message['success'] = '0';
			$message['message'] = 'Parameter Missing';
		}
		echo json_encode($message);
	}

	public function updateUserProfile()
	{
		if ($this->input->post()) {
			$userId = $this->input->post('id');



			$checkId = $this->db->get_where("users", ['id' => $userId])->row_array();
			if (!!$checkId) {

				if (!empty($this->input->post('name'))) {
					$data['name'] = $this->input->post('name');
				}
				if (!empty($this->input->post('gender'))) {
					$data['gender'] = $this->input->post('gender');
				}
				if (!empty($this->input->post('dob'))) {
					$data['dob'] = $this->input->post('dob');
				}
				if (!empty($this->input->post('latitude'))) {
					$data['latitude'] = $this->input->post('latitude');
				}
				if (!empty($this->input->post('longitude'))) {
					$data['longitude'] = $this->input->post('longitude');
				}


				$data['updated'] = date("Y-m-d H:i:s");

				// if (!empty($_FILES["image"]["name"])) {
				// 	$name1= time().'_'.$_FILES["image"]["name"];
				// 	$name= str_replace(' ', '_', $name1);
				// 	$liciense_tmp_name=$_FILES["image"]["tmp_name"];
				// 	$error=$_FILES["image"]["error"];
				// 	$liciense_path='uploads/users/'.$name;
				// 	move_uploaded_file($liciense_tmp_name,$liciense_path);
				// 	$data['image'] = $liciense_path;
				// }

				if (!empty($_FILES["image"]["name"])) {
					// $name= time().'_'.$_FILES["image"]["name"];
					// $liciense_tmp_name=$_FILES["image"]["tmp_name"];
					// $error=$_FILES["image"]["error"];
					// $liciense_path='uploads/user/'.$name;
					// move_uploaded_file($liciense_tmp_name,$liciense_path);
					// $details['image']=base_url(). $liciense_path;
					$data['image'] = $this->uploadVideo($_FILES["image"]);
				}

				if (!empty($_FILES["backgroundImage"]["name"])) {
					// $name= time().'_'.$_FILES["image"]["name"];
					// $liciense_tmp_name=$_FILES["image"]["tmp_name"];
					// $error=$_FILES["image"]["error"];
					// $liciense_path='uploads/user/'.$name;
					// move_uploaded_file($liciense_tmp_name,$liciense_path);
					// $details['image']=base_url(). $liciense_path;
					$data['backgroundImage'] = $this->uploadVideo($_FILES["backgroundImage"]);
				}

				$update = $this->db->update('users', $data, array('id' => $userId));

				if (!empty($update)) {

					$details = $this->db->get_where('users', array('id' => $userId))->row_array();
					if (!empty($details['image'])) {
						$details['image'] = $details['image'];
						$details['backgroundImage'] = $details['backgroundImage'];
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

	public function likeDislikeUser()
	{

		$type = $this->input->post('type');

		if ($type == '0') {

			$checkLike = $this->db->get_where("likeDislikeUser", ['userId' => $this->input->post('userId'), 'otherUserId' => $this->input->post('otherUserId')])->row_array();

			if (!!$checkLike) {

				echo json_encode([

					"success" => "0",
					"message" => "user already liked/disliked by this user!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post('userId');
			$data['otherUserId'] = $this->input->post('otherUserId');
			$data['likeDislike_type'] = "like";

			$Ins = $this->db->insert('likeDislikeUser', $data);

			$getId = $this->db->insert_id();
			if ($Ins) {

				$getDetails = $this->db->get_where("likeDislikeUser", ['id' => $getId])->row_array();

				$getOtherid = $getDetails['otherUserId'];
				$getSwipeid = $getDetails['userId'];


				$updateeee['likeDislikeId'] = $getOtherid;
				$updateeee['userSwipeID'] = $getSwipeid;


				$edit = $this->db->update("users", $updateeee, ['id' => $getOtherid]);

				$message['success'] = '1';
				$message['message'] = 'User Liked';
			} else {
				$message['success'] = '0';
				$message['message'] = 'somthing went wrong!';
			}
		}

		if ($type == '1') {

			$checkDisLike = $this->db->get_where("likeDislikeUser", ['userId' => $this->input->post('userId'), 'otherUserId' => $this->input->post('otherUserId')])->row_array();

			if (!!$checkDisLike) {

				echo json_encode([

					"success" => "0",
					"message" => "user already liked/disliked by this user!"
				]);
				exit;
			}
			$data['userId'] = $this->input->post('userId');
			$data['otherUserId'] = $this->input->post('otherUserId');
			$data['likeDislike_type'] = "dislike";

			$update = $this->db->insert('likeDislikeUser', $data);

			$getId = $this->db->insert_id();

			if ($update) {

				$getDetails = $this->db->get_where("likeDislikeUser", ['id' => $getId])->row_array();

				$getOtherid = $getDetails['otherUserId'];
				$getSwipeid = $getDetails['userId'];


				$updateeee['likeDislikeId'] = $getOtherid;
				$updateeee['userSwipeID'] = $getSwipeid;


				$edit = $this->db->update("users", $updateeee, ['id' => $getOtherid]);

				$message['success'] = '1';
				$message['message'] = 'User Unliked';
			} else {
				$message['success'] = '0';
				$message['message'] = 'somthing went wrong!';
			}
		}


		echo json_encode($message);
	}

	public function superLike()
	{

		$checkSuperLike = $this->db->get_where("likeDislikeUser", ['userId' => $this->input->post('userId'), 'otherUserId' => $this->input->post('otherUserId')])->row_array();

		if (!!$checkSuperLike) {

			echo json_encode([

				"success" => "0",
				"message" => "user already liked/disliked/SuperLiked by this user!"
			]);
			exit;
		}

		if ($this->input->post('userId') == null || $this->input->post('otherUserId') == null) {

			echo json_encode([
				"success" => "0",
				"message" => "Param cannot be null!"
			]);
			exit;
		}

		$checkUser = $this->db->get_where("users", ['id' => $this->input->post('userId')])->row_array();

		if (empty($checkUser)) {

			echo json_encode([
				"success" => "0",
				"message" => "Please enter valid userId!"
			]);
			exit;
		}

		$checkUser = $this->db->get_where("users", ['id' => $this->input->post('otherUserId')])->row_array();

		if (empty($checkUser)) {

			echo json_encode([
				"success" => "0",
				"message" => "Please enter valid otherUserId!"
			]);
			exit;
		}

		$data['userId'] = $this->input->post('userId');
		$data['otherUserId'] = $this->input->post('otherUserId');
		$data['likeDislike_type'] = "superlike";

		$Ins = $this->db->insert('likeDislikeUser', $data);

		$getId = $this->db->insert_id();
		if ($Ins == true) {

			$getDetails = $this->db->get_where("likeDislikeUser", ['id' => $getId])->row_array();

			$getOtherid = $getDetails['otherUserId'];
			$getSwipeid = $getDetails['userId'];


			$updateeee['superLikeById'] = $getSwipeid;
			$updateeee['superLikeToId'] = $getOtherid;


			$edit = $this->db->update("users", $updateeee, ['id' => $getSwipeid]);

			$swip['superLikeById'] = $getOtherid;
			$swip['superLikeToId'] = $getSwipeid;
			$this->db->update("users", $swip, ['id' => $getOtherid]);

			$message['success'] = '1';
			$message['message'] = 'User SuperLiked';
		} else {
			$message['success'] = '0';
			$message['message'] = 'Something went wrong!';
		}
		echo json_encode($message);
	}

	public function getSuperLikeDetails()
	{

		$getDetails = $this->db->select("likeDislikeUser.id superLikeUsersId,likeDislikeUser.userId,likeDislikeUser.otherUserId,users.*")
			->from("likeDislikeUser")
			->join("users", "users.id = likeDislikeUser.userId", "left")
			->where("likeDislikeUser.otherUserId", $this->input->post('userId'))
			->where("likeDislikeUser.likeDislike_type", 'superlike')
			->get()
			->result_array();

		if (!!$getDetails) {

			foreach ($getDetails as $gets) {

				if (!!$gets['image']) {

					$gets['image'] = $gets['image'];
				} else {
					$gets['image'] = "";
				}

				$final[] = $gets;
			}

			echo json_encode([
				"success" => "1",
				"message" => "details found",
				"details" => $final
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

	public function getProductCategories()
	{

		// $get = $this->db->get("productCategory_byAdmin")->result_array();

		$get = $this->db->select("productCategory_byAdmin.*,addProducts.shipping")
			->from("productCategory_byAdmin")
			->join("addProducts", "addProducts.catId = productCategory_byAdmin.id", "left")
			->get()
			->result_array();

		if (!!$get) {

			echo json_encode([

				"success" => "1",
				"message" => "details found successfully",
				"details" => $get,
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


	public function getProductSubCategories()
	{

		$checkCatId = $this->db->get_where("productCategory_byAdmin", ['id' => $this->input->post("catId")])->row_array();

		if (empty($checkCatId)) {

			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid catId",
			]);
			exit;
		}

		if (!!$checkCatId) {

			$getId = $checkCatId['id'];

			$checkCatId['product_subCat'] = $this->db->get_where("productSubCategory_byAdmin", ['catId' => $getId])->result_array();

			echo json_encode([

				"success" => "1",
				"message" => "details found",
				"details" => $checkCatId
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


	public function addProducts()
	{

		if ($this->input->post()) {

			$data["userId"] = $this->input->post("userId");
			$data["catId"] = $this->input->post("catId");
			$data["product_name"] = $this->input->post("product_name");
			$data["coins"] = $this->input->post("coins");
			$data["specification"] = $this->input->post("specification");
			$data["description"] = $this->input->post("description");
			$data["quantity"] = $this->input->post("quantity");
			$data["country"] = $this->input->post("country");
			$data["shipping"] = $this->input->post("shipping");
			$data["created"] = date("Y-m-d H:i:s");

			$upload = $this->db->insert("addProducts", $data);

			$getId = $this->db->insert_id();

			if ($upload == true) {

				$this->db->set('addProductCounts', 'addProductCounts +1', false)->where('id', $this->input->post('userId'))->update("users");

				$States = explode(',', $this->input->post("states"));

				foreach ($States as $key => $val) {
					$state = $this->db->get_where('states', ['id' => $val])->row_array();
					$insert_data = [];
					$insert_data[] = [
						"ProductId"	=>	$getId,
						"states"	=>	$state['name'],
					];

					$ins = $this->db->insert_Batch("product_states", $insert_data);
				}

				if (!empty($_FILES['image']['name'])) {
				    $total = count($_FILES['image']['name']);
				    for ($i = 0; $i < $total; $i++) {
						$img = [
							'name' => $_FILES['image']['name'][$i],
							'tmp_name' => $_FILES['image']['tmp_name'][$i]
						];
						$datas['addProductId'] = $getId;
						$datas['image'] = $this->uploadVideo($img);
						$input = $this->db->insert('addProduct_images', $datas);
				    }
				}
				echo json_encode([

					"success" => "1",
					"message" => "Product added successfully",
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "Something went wrong!"
				]);
				exit;
			}
		} else {

			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid params!"
			]);
			exit;
		}
	}

	public function getAddProducts()
	{

		$get = $this->db->get("addProducts")->result_array();


		if (!!$get) {

			foreach ($get as $key => $val) {

				$getId = $get[$key]["id"];

				$get[$key]["product_images"] = $this->db->select("addProduct_images.id,addProduct_images.addProductId,addProduct_images.image")
					->from("addProduct_images")
					->where("addProduct_images.addProductId", $getId)
					->get()
					->result_array();

				$get[$key]["states"] = $this->db->select("product_states.*")
					->from("product_states")
					->where("product_states.ProductId", $getId)
					->get()
					->result_array();
			}

			echo json_encode([

				"success" => "1",
				"message" => "details found",
				"details" => $get,
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




	public function getAddProductById()
	{


		$hosid = $this->db->get_where("addProducts", ['userId' => $this->input->post("userId")])->result_array();

		if (!!$hosid) {

			foreach ($hosid as $key => $val) {

				$getId = $hosid[$key]["id"];

				$hosid[$key]["product_images"] = $this->db->select("addProduct_images.id,addProduct_images.addProductId,addProduct_images.image")
					->from("addProduct_images")
					->where("addProduct_images.addProductId", $getId)
					->get()
					->result_array();

					$productStates = $this->db->get_where('product_states', ['ProductId' => $hosid[$key]["id"]])->result_array();
					if(empty($productStates)){
						$hosid[$key]["states"] = [];
					}else{
						$hosid[$key]["states"] = $productStates;

					}
			}



			echo json_encode([

				"message" => 'details found',
				"success" => '1',
				"details" => $hosid,
			]);
			exit;
		} else {
			echo json_encode([

				"message" => 'details not found',
				"success" => '0',
			]);
			exit;
		}
	}

	public function filterProducts()
	{

		if ($this->input->post("min_coin") == null || $this->input->post("max_coin") == null) {

			echo json_encode([

				"success" => "0",
				"message" => "Something went wrong! param cannot be null",
			]);
			exit;
		}


		if ($catId = $this->input->post("catId") ?? "") {

			$min = $this->input->post("min_coin");
			$max = $this->input->post("max_coin");


			$records  =  $this->db->query("SELECT * FROM addProducts WHERE catId = $catId AND coins BETWEEN $min AND $max order by coins desc")->result_array();

			if (!!$records) {
				echo json_encode([
					"success"  =>  "1",
					"message"  =>  "Record found successfully",
					"details"  =>  $records,
				]);
				exit;
			} else {
				echo json_encode([
					"success"  =>  "0",
					"message"  =>  "Record not found!",
				]);
				exit;
			}
		} else {
			$min = $this->input->post("min_coin");
			$max = $this->input->post("max_coin");
			$records  =  $this->db->query("SELECT * FROM addProducts WHERE coins BETWEEN $min AND $max order by coins desc")->result_array();

			if (!!$records) {
				echo json_encode([
					"success"  =>  "1",
					"message"  =>  "Record found successfully",
					"details"  =>  $records,
				]);
				exit;
			} else {
				echo json_encode([
					"success"  =>  "0",
					"message"  =>  "Record not found!",
				]);
				exit;
			}
		}
	}

	public function wishlist()
	{

		if ($this->input->post()) {

			$data['userId'] = $this->input->post('userId');
			$data['productId'] = $this->input->post('productId');
			$data['created'] = date("Y-m-d H:i:s");

			$upload =  $this->db->insert("product_wishlist", $data);

			if ($upload == true) {

				echo json_encode([

					"success" => "1",
					"message" => "details added successfully",
				]);
				exit;
			} else {

				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!",
				]);
				exit;
			}
		} else {

			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid param!",
			]);
			exit;
		}
	}

	public function removeWishlist()
	{

		if ($this->input->post('userId') == null || $this->input->post('productId') == null) {

			echo json_encode([

				"success" => "0",
				"message" => "Something went wrong! param cannot be null",
			]);
			exit;
		}

		$checkDetails = $this->db->get_where("product_wishlist", ['userId' => $this->input->post('userId'), 'productId' => $this->input->post('productId')])->row_array();

		if (!!$checkDetails) {

			$removeWishList = $this->db->delete("product_wishlist", ['userId' => $this->input->post('userId'), 'productId' => $this->input->post('productId')]);

			echo json_encode([

				"success" => "1",
				"mesage" => "record removed"
			]);
			exit;
		} else {

			echo json_encode([

				"success" => "0",
				"message" => "please enter valid details",
			]);
			exit;
		}
	}



	public function getWishlist()
	{

		$getDetails = $this->db->select("product_wishlist.id  product_wishlistId,product_wishlist.userId,product_wishlist.productId,addProducts.id addProductsId,addProducts.catId,addProducts.product_name,addProducts.coins,addProducts.specification,addProducts.description,users.username")
			->from("product_wishlist")
			->join("addProducts", "addProducts.id = product_wishlist.productId", "left")
			->join("users", "users.id = product_wishlist.userId", "left")
			->where("product_wishlist.userId", $this->input->post('userId'))
			->get()
			->result_array();
		if (!!$getDetails) {

			foreach ($getDetails as $key => $val) {

				$getId = $getDetails[$key]["productId"];

				$getDetails[$key]["product_images"] = $this->db->select("addProduct_images.id,addProduct_images.addProductId,addProduct_images.image")
					->from("addProduct_images")
					->where("addProduct_images.addProductId", $getId)
					->get()
					->result_array();
			}



			echo json_encode([

				"success" => "1",
				"message" => "details found successfully",
				"details" => $getDetails,
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

	public function checkWishlistStatus()
	{

		if ($this->input->post('userId') == null || $this->input->post('productId') == null) {

			echo json_encode([

				"success" => "0",
				"message" => "Something went wrong! param cannot be null"
			]);
			exit;
		}

		$checkStatus = $this->db->get_where("product_wishlist", ['userId' => $this->input->post('userId'), 'productId' => $this->input->post('productId')])->row_array();

		if (!!$checkStatus) {

			echo json_encode([

				"success" => "1",
				"message" => "status found",
				"success" => "1"
			]);
			exit;
		} else {
			echo json_encode([

				"success" => "0",
				"message" => "status not found!",
				"success" => "0"
			]);
			exit;
		}
	}

	//   public function addToCart(){

	//       if($this->input->post()){

	//           $data['userId'] = $this->input->post("userId");
	//           $data['productId'] = $this->input->post("productId");
	//           $data['created'] = date("Y-m-d H:i:s");

	//           $addCart = $this->db->insert("addToCart",$data);

	//           if($addCart == true){

	//               echo json_encode([

	//               "success" => "1",
	//               "message" => "Product added to cart",
	//             ]);exit;

	//           }else{
	//               echo json_encode([

	//               "success" => "0",
	//               "message" => "Something went wrong!"
	//             ]);exit;

	//           }
	//       }
	//       else{

	//           echo json_encode([

	//               "success" => "0",
	//               "message" => "Please enter valid params!"
	//             ]);exit;
	//       }
	//   }

	public function add_to_cart(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$product = $this->db->get_where('addProducts', ['id' => $this->input->post('productId')])->row_array();
			if(empty($product)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid productId'
				]);exit;
			}

			if($this->input->post('quantity') <= '0'){
				echo json_encode([
					'status' => 0,
					'message' => 'quantity can not be less then 1'
				]);exit;
			}

			$check_in_cart = $this->db->get_where('addToCart', ['userId' => $user['id'], 'productId' => $product['id']])->row_array();
			if(!!$check_in_cart){
				
				$cartdata['quantity'] = $check_in_cart['quantity'];
				$cartdata['quantity'] += $this->input->post('quantity');

				$this->db->set($cartdata)->where('id', $check_in_cart['id'])->update('addToCart');
				echo json_encode([
					'status' => 1,
					'message' => 'quantity updated'
				]);exit;
			}

			$data['userId'] = $user['id'];
			$data['productId'] = $product['id'];
			$data['quantity'] = $this->input->post('quantity');
			$data['created'] = date('Y-m-d H:i:s');

			if($this->db->insert('addToCart', $data) && $this->db->insert('addToCartDetails', $data)){

				echo json_encode([
					'status' => 1,
					'message' => 'product added to cart'
				]);exit;

			}else{

				echo json_encode([
					'status' => 0,
					'message' => 'DB error'
				]);exit;

			}
 


		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}
	}

	// public function getCart()
	// {

	// 	$getDetails = $this->db->select("addToCart.id addToCartId, addToCart.quantity, addToCart.userId,addToCart.productId,addProducts.id addProductsId,addProducts.catId,addProducts.product_name,addProducts.coins,addProducts.specification,addProducts.description,users.username")
	// 		->from("addToCart")
	// 		->join("addProducts", "addProducts.id = addToCart.productId", "left")
	// 		->join("users", "users.id = addToCart.userId", "left")
	// 		->where("addToCart.userId", $this->input->post("userId"))
	// 		->get()
	// 		->result_array();

	// 	if (!!$getDetails) {

	// 		foreach ($getDetails as $key => $val) {

	// 			$getId = $getDetails[$key]["productId"];

	// 			$getDetails[$key]["product_images"] = $this->db->select("addProduct_images.id,addProduct_images.addProductId,addProduct_images.image")
	// 				->from("addProduct_images")
	// 				->where("addProduct_images.addProductId", $getId)
	// 				->get()
	// 				->result_array();
	// 		}

	// 		echo json_encode([

	// 			"success" => "1",
	// 			"message" => "cart details found",
	// 			"details" => $getDetails,
	// 		]);
	// 		exit;
	// 	} else {

	// 		echo json_encode([

	// 			"success" => "0",
	// 			"message" => "cart details not found",
	// 		]);
	// 		exit;
	// 	}
	// }

	public function checkCartStatus()
	{

		if ($this->input->post('userId') == null || $this->input->post('productId') == null) {

			echo json_encode([

				"success" => "0",
				"message" => "Something went wrong! param cannot be null"
			]);
			exit;
		}

		$checkStatus = $this->db->get_where("addToCart", ['userId' => $this->input->post('userId'), 'productId' => $this->input->post('productId')])->row_array();

		if (!!$checkStatus) {

			echo json_encode([

				"success" => "1",
				"message" => "status found",
				"success" => "1"
			]);
			exit;
		} else {
			echo json_encode([

				"success" => "0",
				"message" => "status not found!",
				"success" => "0"
			]);
			exit;
		}
	}

	public function removeCartProduct()
	{

		if ($this->input->post('userId') == null || $this->input->post('productId') == null) {

			echo json_encode([

				"success" => "0",
				"message" => "Something went wrong! param cannot be null",
			]);
			exit;
		}

		$checkDetails = $this->db->get_where("addToCart", ['userId' => $this->input->post('userId'), 'productId' => $this->input->post('productId')])->row_array();

		if (!!$checkDetails) {

			$removeWishList = $this->db->delete("addToCart", ['userId' => $this->input->post('userId'), 'productId' => $this->input->post('productId')]);

			echo json_encode([

				"success" => "1",
				"mesage" => "cart product removed"
			]);
			exit;
		} else {

			echo json_encode([

				"success" => "0",
				"message" => "please enter valid details",
			]);
			exit;
		}
	}

	// public function checkLikeStatus(){

	// 	if($this->input->post("userId") == null){

	// 		echo json_encode([

	// 			"success" => "0",
	// 			"message" => "Something went wrong! param cannot be null",
	// 			]);exit;
	// 	}

	//   //   $checkStatus = $this->db->get_where("likeDislikeUser",['userId' => $this->input->post('userId'),'likeDislike_type' => 'like'])->result_array();

	// 	$checkStatus = $this->db->select("likeDislikeUser.*")
	// 	->from("likeDislikeUser")
	// 	->where("likeDislikeUser.userId",$this->input->post('userId'))
	// 	->group_start()
	// 	->or_where("likeDislikeUser.likeDislike_type",'like')
	// 	->or_where("likeDislikeUser.likeDislike_type",'superlike')
	// 	->group_end()
	// 	->get()
	// 	->result_array();

	// 	// print_r($checkStatus);
	// 	// exit;

	// 	if(!!$checkStatus){

	// 		$final = [];

	// 		foreach($checkStatus as $get){

	// 			$getiD = $get['otherUserId'];
	// 		    $getuiD = $get['userId'];


	// 				$getOtherUser = $this->db->select("likeDislikeUser.id likeDislikeUserId,likeDislikeUser.userId,likeDislikeUser.otherUserId,likeDislikeUser.likeDislike_type,users.*")
	// 									 ->from("likeDislikeUser")
	// 									 ->join("users","users.id = likeDislikeUser.userId","left")
	// 								     ->where("likeDislikeUser.userId",$getiD)
	// 									 ->where("likeDislikeUser.otherUserId",$getuiD)
	// 									 ->group_start()
	// 									 ->or_where("likeDislikeUser.likeDislike_type","like")
	// 									 ->or_where("likeDislikeUser.likeDislike_type","superlike")
	// 									 ->group_end()
	// 									 ->get()
	// 									 ->row_array();


	// 			  if(!empty($getOtherUser)){

	// 				  $getOtherUser['image'] = $getOtherUser['image'];


	// 			  $final[] = $getOtherUser;


	// 			  }

	// 		}

	// 		if(!!$final){
	// 		   echo json_encode([

	// 				"success" => "1",
	// 				"message" => "details found",
	// 				"details" => $final,
	// 				]);exit;
	// 			}
	// 			else{
	// 				echo json_encode([

	// 			"success" => "0",
	// 			"message" => "details not found!",
	// 			]);exit;
	// 			}

	// 	}
	// 	else{
	// 		 echo json_encode([

	// 			"success" => "0",
	// 			"message" => "details not found!",
	// 			]);exit;
	// 	}

	// }


	// public function checkLikeStatus(){

	// 	if($this->input->post("userId") == null){

	// 		echo json_encode([

	// 			"success" => "0",
	// 			"message" => "Something went wrong! param cannot be null",
	// 			]);exit;
	// 	}

	// 	$checkStatus = $this->db->select("likeDislikeUser.*")
	// 	->from("likeDislikeUser")
	// 	->where("likeDislikeUser.userId",$this->input->post('userId'))
	// 	->where("likeDislikeUser.likeDislike_type",'like')
	// 	->get()
	// 	->result_array();


	// 	// $otherId = $this->db->select("likeDislikeUser.*")
	// 	// ->from("likeDislikeUser")
	// 	// ->where("likeDislikeUser.otherUserId",$this->input->post('userId'))
	// 	// ->where("likeDislikeUser.likeDislike_type",'superlike')
	// 	// ->get()
	// 	// ->result_array();

	// 	$otherIdd = $this->db->select("likeDislikeUser.*")
	// 	->from("likeDislikeUser")
	// 	->where("likeDislikeUser.userId",$this->input->post('userId'))
	// 	->where("likeDislikeUser.likeDislike_type",'superlike')
	// 	->get()
	// 	->result_array();

	// 	if(!!$checkStatus){

	// 		foreach($checkStatus as $gets){

	// 			$otherUserId = $gets['otherUserId'];
	// 		    $userId = $gets['userId'];

	// 			$getOtherUser = $this->db->select("likeDislikeUser.id likeDislikeUserId,likeDislikeUser.userId,likeDislikeUser.otherUserId,likeDislikeUser.likeDislike_type,users.*")
	// 									 ->from("likeDislikeUser")
	// 									 ->join("users","users.id = likeDislikeUser.userId","left")
	// 								     ->where("likeDislikeUser.userId",$otherUserId)
	// 									 ->where("likeDislikeUser.otherUserId",$userId)
	// 									 ->where("likeDislikeUser.likeDislike_type","like")
	// 									 ->get()
	// 									 ->row_array();



	// 			$getOtherUserr = $this->db->select("likeDislikeUser.id likeDislikeUserId,likeDislikeUser.userId,likeDislikeUser.otherUserId,likeDislikeUser.likeDislike_type,users.*")
	// 									 ->from("likeDislikeUser")
	// 									 ->join("users","users.id = likeDislikeUser.otherUserId","left")
	//  									 ->where("likeDislikeUser.userId",$this->input->post('userId'))
	// 									 ->where("likeDislikeUser.likeDislike_type","superlike")
	//  									 ->get()
	// 									 ->row_array();

	// 			$getOther = $this->db->select("likeDislikeUser.id likeDislikeUserId,likeDislikeUser.userId,likeDislikeUser.otherUserId,likeDislikeUser.likeDislike_type,users.*")
	// 									 ->from("likeDislikeUser")
	// 									 ->join("users","users.id = likeDislikeUser.userId","left")
	//  									 ->where("likeDislikeUser.otherUserId",$this->input->post('userId'))
	// 									 ->where("likeDislikeUser.likeDislike_type","superlike")
	//  									 ->get()
	// 									 ->row_array();

	// 									 if(!empty($getOtherUser)){

	// 										$getOtherUser['image'] = $getOtherUser['image'];

	// 									    $final[] = $getOtherUser;

	// 									}
	// 									if(!empty($getOtherUserr)){

	// 										$getOtherUserr['image'] = $getOtherUserr['image'];
	// 										$final[] = $getOtherUserr;

	// 									  }
	// 									  if(!empty($getOther)){

	// 										$getOther['image'] = $getOther['image'];
	// 										$final[] = $getOther;

	// 									  }
	// 		}

	// 		if(!!$final){
	// 			echo json_encode([

	// 				 "success" => "1",
	// 				 "message" => "details found",
	// 				 "details" => $final,
	// 				 ]);exit;
	// 			 }
	// 			 else{
	// 				 echo json_encode([

	// 			 "success" => "0",
	// 			 "message" => "details not found!",
	// 			 ]);exit;
	// 			 }


	// 	}
	// 	// elseif(!!$otherId){

	// 	// 	foreach($otherId as $otherIdd){

	// 	// 		$otherUserId = $otherIdd['otherUserId'];
	// 	// 	    $userId = $otherIdd['userId'];

	// 	// 		$getOtherUserrr = $this->db->select("likeDislikeUser.id likeDislikeUserId,likeDislikeUser.userId,likeDislikeUser.otherUserId,likeDislikeUser.likeDislike_type,users.*")
	// 	// 								 ->from("likeDislikeUser")
	// 	// 								 ->join("users","users.id = likeDislikeUser.userId","left")
	//  	// 								 ->where("likeDislikeUser.otherUserId",$otherUserId)
	// 	// 								 ->where("likeDislikeUser.likeDislike_type","superlike")
	//  	// 								 ->get()
	// 	// 								 ->row_array();

	// 	// 								 $getOtherDetails = $this->db->select("likeDislikeUser.id likeDislikeUserId,likeDislikeUser.userId,likeDislikeUser.otherUserId,likeDislikeUser.likeDislike_type,users.*")
	// 	// 								 ->from("likeDislikeUser")
	// 	// 								 ->join("users","users.id = likeDislikeUser.otherUserId","left")
	//  	// 								 ->where("likeDislikeUser.userId",$otherUserId)
	// 	// 								 ->where("likeDislikeUser.likeDislike_type","superlike")
	//  	// 								 ->get()
	// 	// 								 ->row_array();
	// 	// 								//  print_r($getOtherDetails);
	// 	// 								//  die;
	// 	// 								 if(!empty($getOtherUserrr)){

	// 	// 									$getOtherUserrr['image'] = $getOtherUserrr['image'];

	// 	// 								    $final[] = $getOtherUserrr;

	// 	// 								}

	// 	// 								if(!empty($getOtherDetails)){

	// 	// 									$getOtherDetails['image'] = $getOtherDetails['image'];

	// 	// 								    $final[] = $getOtherDetails;

	// 	// 								}

	// 	// 	}

	// 	// 	if(!!$final){
	// 	// 		echo json_encode([

	// 	// 			 "success" => "1",
	// 	// 			 "message" => "details found",
	// 	// 			 "details" => $final,
	// 	// 			 ]);exit;
	// 	// 		 }
	// 	// 		 else{
	// 	// 			 echo json_encode([

	// 	// 		 "success" => "0",
	// 	// 		 "message" => "details not found!",
	// 	// 		 ]);exit;
	// 	// 		 }
	// 	// }
	// 	// elseif(!!$otherIdd){

	// 	// 	foreach($otherIdd as $otherIddd){

	// 	// 		$otherUserId = $otherIddd['otherUserId'];
	// 	// 	    $userId = $otherIddd['userId'];

	// 	// 		$getOtherUserrrr = $this->db->select("likeDislikeUser.id likeDislikeUserId,likeDislikeUser.userId,likeDislikeUser.otherUserId,likeDislikeUser.likeDislike_type,users.*")
	// 	// 								 ->from("likeDislikeUser")
	// 	// 								 ->join("users","users.id = likeDislikeUser.userId","left")
	//  	// 								 ->where("likeDislikeUser.userId",$userId)
	// 	// 								 ->where("likeDislikeUser.likeDislike_type","superlike")
	//  	// 								 ->get()
	// 	// 								 ->row_array();

	// 	// 								 $getOtherUserrrrr = $this->db->select("likeDislikeUser.id likeDislikeUserId,likeDislikeUser.userId,likeDislikeUser.otherUserId,likeDislikeUser.likeDislike_type,users.*")
	// 	// 								 ->from("likeDislikeUser")
	// 	// 								 ->join("users","users.id = likeDislikeUser.userId","left")
	//  	// 								 ->where("likeDislikeUser.otherUserId",$userId)
	// 	// 								 ->where("likeDislikeUser.likeDislike_type","superlike")
	//  	// 								 ->get()
	// 	// 								 ->row_array();
	// 	// 								//  print_r($getOtherUserrr);
	// 	// 								//  die;
	// 	// 								 if(!empty($getOtherUserrrr)){

	// 	// 									$getOtherUserrrr['image'] = $getOtherUserrrr['image'];

	// 	// 								    $final[] = $getOtherUserrrr;

	// 	// 								}
	// 	// 								if(!empty($getOtherUserrrrr)){

	// 	// 									$getOtherUserrrrr['image'] = $getOtherUserrrr['image'];

	// 	// 								    $final[] = $getOtherUserrrrr;

	// 	// 								}

	// 	// 	}

	// 	// 	if(!!$final){
	// 	// 		echo json_encode([

	// 	// 			 "success" => "1",
	// 	// 			 "message" => "details found",
	// 	// 			 "details" => $final,
	// 	// 			 ]);exit;
	// 	// 		 }
	// 	// 		 else{
	// 	// 			 echo json_encode([

	// 	// 		 "success" => "0",
	// 	// 		 "message" => "details not found!",
	// 	// 		 ]);exit;
	// 	// 		 }
	// 	// }
	// 	else{
	// 		echo json_encode([

	// 			"success" => "0",
	// 			"message" => "details not found!",
	// 			]);exit;

	// 	}


	// } 
	
	protected function get_likes($userId){
		$connections = [];

		$likes = [];
		$my_likes = $this->db->get_where('likeDislikeUser', ['userId' => $userId, 'likeDislike_type' => 'like'])->result_array();
		if(!!$my_likes){

			foreach($my_likes as $like){
				$get = $this->db->select('userId otherUserIds, likeDislike_type')->get_where('likeDislikeUser', ['userId' => $like['otherUserId'], 'otherUserId' => $userId])->row_array();

				if(!!$get){
					$likes[] = $get;
				}
			}
		}


		$superlikes = [];
		$userIds = [];
		$otherUser = [];
		$my_superlike = $this->db->select('otherUserId otherUserIds, likeDislike_type')->get_where('likeDislikeUser', ['userId' => $userId, 'likeDislike_type' => 'superlike'])->result_array();
		if(!!$my_superlike){
			
			foreach($my_superlike as $superlike){
				$userIds[] = $superlike;
			}
		}

		$getSuperlike = $this->db->select('userId otherUserIds, likeDislike_type')->get_where('likeDislikeUser', ['otherUserId' => $userId, 'likeDislike_type' => 'superlike'])->result_array();
		if(!!$getSuperlike){
			foreach($getSuperlike as $superlikes){
				// print_r($getSuperlike);exit;
				$otherUser[] = $superlikes;
			}
		}

		$ar = array_merge($likes, $userIds, $otherUser);

		return $ar;
	}

	public function check_like_status(){

		if($_SERVER['REQUEST_METHOD'] === 'POST'){
			if(!$this->input->post('userId')){
				echo json_encode([
					'success' =>  '0',
					'message' => 'userId required'
				]);exit;
			}

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'success' => '0',
					'message' => 'invalid userId'
				]);exit;
			}


			$likes = $this->get_likes($user['id']);

			if(empty($likes)){
				echo json_encode([
					'success' => '0',
					'message' => 'no connections found'
				]);exit;
			}else{
				// print_r($likes);exit;
				$final = [];
				foreach($likes as $like){
					$like['userinfo'] = $this->db->select('username, name, image, backgroundImage')->get_where('users', ['id' => $like['otherUserIds']])->row_array();
					$like['product_count'] = $this->db->get_where('addProducts', ['userId' => $like['otherUserIds']])->num_rows();

					$connection_count = $this->get_likes($like['otherUserIds']);
					if(empty($connection_count)){
						$like['connection_count'] = 0;
					}else{
						$like['connection_count'] = count($connection_count);
					}

					$like['video_count'] = $this->db->get_where('userVideos', ['userId' => $like['otherUserIds']])->num_rows();

					$final[] = $like;
				}
			}

			if(empty($final)){
				echo json_encode([
					'success' =>  '0',
					'message' => 'no data found'
				]);exit;
			}

				echo json_encode([
					'success' =>  '1',
					'message' => 'data found',
					'details' => $final
				]);exit;
			
			
		}else{
			echo json_encode([
				'success' => '0',
				'message' => 'method not allowed0'
			]);exit;
		}
	}


	public function checkLikeStatus()
	{

		if ($this->input->post("userId") == null) {

			echo json_encode([

				"success" => "0",
				"message" => "Something went wrong! param cannot be null",
			]);
			exit;
		}

		$checkBlockUser = $this->db->get_where("users", ['id' => $this->input->post('userId')])->row_array();

		$getStatus = $checkBlockUser['blockUser'];

		if ($getStatus == '1') {
			echo json_encode([

				"success" => "0",
				"message" => "user blocked!",
			]);
			exit;
		}

		$checkStatus = $this->db->select("likeDislikeUser.*")
			->from("likeDislikeUser")
			->where("likeDislikeUser.userId", $this->input->post('userId'))
			->where("likeDislikeUser.likeDislike_type", 'like')
			->get()
			->result_array();

		$otherIdd = $this->db->select("likeDislikeUser.*")
			->from("likeDislikeUser")
			->where("likeDislikeUser.userId", $this->input->post('userId'))
			->where("likeDislikeUser.likeDislike_type", 'superlike')
			->get()
			->result_array();


		$otherId = $this->db->select("likeDislikeUser.*")
			->from("likeDislikeUser")
			->where("likeDislikeUser.otherUserId", $this->input->post('userId'))
			->where("likeDislikeUser.likeDislike_type", 'superlike')
			->get()
			->result_array();


		if (!!$checkStatus || !!$otherIdd || !!$otherId) {

			$final = [];

			foreach ($checkStatus as $gets) {

				$otherUserId = $gets['otherUserId'];
				$userId = $gets['userId'];

				$getOtherUser = $this->db->select("likeDislikeUser.id likeDislikeUserId,likeDislikeUser.userId,likeDislikeUser.otherUserId,likeDislikeUser.likeDislike_type,users.*")
					->from("likeDislikeUser")
					->join("users", "users.id = likeDislikeUser.userId", "left")
					->where("users.blockerTo !=", $otherUserId)
					//  ->where("users.blockUser !=","1")
					->where("likeDislikeUser.userId", $otherUserId)
					->where("likeDislikeUser.otherUserId", $userId)
					->where("likeDislikeUser.likeDislike_type", "like")
					->get()
					->row_array();

				if (!empty($getOtherUser)) {

					$get = $getOtherUser['otherUserId'];
					$gettt = $getOtherUser['userId'];

					$check = $this->db->get_where("blockUser", ['userId' => $gettt, 'blockUserId' => $get])->row_array();

					if (!!$check) {
					} else {
						$checkkk = $this->db->get_where("blockUser", ['userId' => $get, 'blockUserId' => $gettt])->row_array();
						if (!!$checkkk) {
						} else {
							$getOtherUser['image'] = $getOtherUser['image'];

							$final[] = $getOtherUser;
						}
					}
				}
			}

			$array = [];
			foreach ($otherIdd as $getss) {

				$otherUserId = $getss['otherUserId'];
				$userId = $getss['userId'];


				// print_r($otherUserId);

				$getOtherUserr = $this->db->select("likeDislikeUser.id likeDislikeUserId,likeDislikeUser.userId,likeDislikeUser.otherUserId,likeDislikeUser.likeDislike_type,users.*")
					->from("likeDislikeUser")
					->join("users", "users.id = likeDislikeUser.otherUserId", "left")
					->where("users.blockerTo !=", $otherUserId)
					//  ->where("users.blockUser !=","1")
					->where("likeDislikeUser.otherUserId", $otherUserId)
					->where("likeDislikeUser.likeDislike_type", "superlike")
					->get()
					->row_array();




				if (!empty($getOtherUserr)) {

					$get = $getOtherUserr['otherUserId'];
					$gettt = $getOtherUserr['userId'];

					$check = $this->db->get_where("blockUser", ['userId' => $gettt, 'blockUserId' => $get])->row_array();

					if (!!$check) {
					} else {
						$checkkk = $this->db->get_where("blockUser", ['userId' => $get, 'blockUserId' => $gettt])->row_array();
						if (!!$checkkk) {
						} else {
							$getOtherUserr['image'] = $getOtherUserr['image'];

							$array[] = $getOtherUserr;
						}
					}
				}

				//   }
			}
			// die;
			$arrayy = [];
			foreach ($otherId as $gett) {

				$otherUserId = $gett['otherUserId'];
				$userId = $gett['userId'];


				// print_r($otherUserId);

				$getOtherUserrr = $this->db->select("likeDislikeUser.id likeDislikeUserId,likeDislikeUser.userId,likeDislikeUser.otherUserId,likeDislikeUser.likeDislike_type,users.*")
					->from("likeDislikeUser")
					->join("users", "users.id = likeDislikeUser.userId", "left")
					->where("users.blockerTo !=", $otherUserId)
					//  ->where("users.blockUser !=","1")	
					->where("likeDislikeUser.userId", $userId)
					->where("likeDislikeUser.likeDislike_type", "superlike")
					->get()
					->row_array();

				//  print_r($getOtherUserr);


				if (!empty($getOtherUserrr)) {

					$get = $getOtherUserrr['otherUserId'];
					$gettt = $getOtherUserrr['userId'];

					$check = $this->db->get_where("blockUser", ['userId' => $gettt, 'blockUserId' => $get])->row_array();

					if (!!$check) {
					} else {
						$checkkk = $this->db->get_where("blockUser", ['userId' => $get, 'blockUserId' => $gettt])->row_array();
						if (!!$checkkk) {
						} else {
							$getOtherUserrr['image'] = $getOtherUserrr['image'];

							$arrayy[] = $getOtherUserrr;
						}
					}
				}

				//   }
			}
			if (!!$final || !!$array || !!$arrayy) {
				$last = array_merge($final, $array, $arrayy);

				$connections_count = count($last);

				echo json_encode([

					"success" => "1",
					"message" => "details found",
					"connection_count" => $connections_count,
					"details" => $last
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "details not found!",
				]);
				exit;
			}
		} else {
			echo json_encode([

				"success" => "0",
				"message" => "details not found!",
			]);
			exit;
		}
	}


	public function getDetails()
	{

		$get = $this->db->select("users.id")
			->from("users")
			->where("users.id", $this->input->post("userId"))
			->get()
			->row_array();

		//   print_r($get);
		//   die;

		if (!!$get) {

			$userId = $get['id'];

			//   print_r($userId);
			//   die;

			$checkStatus = $this->db->select("likeDislikeUser.otherUserId,likeDislikeUser.userId")
				->from("likeDislikeUser")
				->where("likeDislikeUser.userId", $userId)
				->where("likeDislikeUser.likeDislike_type", "like")
				->get()
				->result_array();

			//       print_r($checkStatus);
			//   die;

			$final = [];

			foreach ($checkStatus as $gets) {

				$getiD = $gets['otherUserId'];
				$getuiD = $gets['userId'];



				$getOtherUser = $this->db->select("likeDislikeUser.id likeDislikeUserId")
					->from("likeDislikeUser")
					->where("likeDislikeUser.userId", $getiD)
					->where("likeDislikeUser.otherUserId", $getuiD)
					->where("likeDislikeUser.likeDislike_type", "like")
					->get()
					->num_rows();



				if (!empty($getOtherUser)) {


					$gets['connect_counts'] = $getOtherUser;

					$final[] = $gets;
				}
			}

			$c = count($final);
			// print_r(count($final));exit;


			echo json_encode([

				"success" => "1",
				"message" => "details found",
				"details" => $c,
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

	// ============ ADD USER'S WALLET BY ADMIN ============


	public function getWalletAmount()
	{


		$get = $this->db->get_where("userWallet", ['userId' => $this->input->post("userId")])->row_array();

		if (!!$get) {

			echo json_encode([

				"success" => "1",
				"message" => "details found successfully",
				"details" => $get,
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

	// ================ sendProductRequest to admin =================

	public function sendProductRequest()
	{

		if ($this->input->post()) {

			$checkAddToCart = $this->db->get_where("addToCart", ['userId' => $this->input->post("userId"), 'productId' => $this->input->post("productId")])->row_array();

			if (empty($checkAddToCart)) {

				echo json_encode([

					"success" => "0",
					"message" => "This Product is not added in cart - by this user!",
				]);
				exit;
			}

			$checkAmount = $this->db->get_where("addProducts", ['id' => $this->input->post("productId")])->row_array();

			$gets = $checkAmount['coins'];

			$checkWalletAmount = $this->db->get_where("userWallet", ['userId' => $this->input->post("userId")])->row_array();

			$get = $checkWalletAmount['wallet_amount'];

			if ($get >= $gets) {

				$data['userId'] = $this->input->post("userId");
				$data['otheruserId'] = $this->input->post("otheruserId");
				$data['productId'] = $this->input->post("productId");
				$data['created'] = date("Y-m-d H:i:s");

				$upload = $this->db->insert("sendProductRequest_to_admin", $data);

				$getId = $this->db->insert_id();

				if ($upload == true) {

					$update['wallet_amount'] = $get - $gets;

					$this->db->update("userWallet", $update, ['userId' => $this->input->post("userId")]);

					$getDetails = $this->db->get_where("sendProductRequest_to_admin", ['id' => $getId])->row_array();

					echo json_encode([
						"success" => "1",
						"message" => "Product Request send successfully",
						"details" => $getDetails,
					]);
					exit;
				} else {
					echo json_encode([
						"success" => "0",
						"message" => "something went wrong!",
					]);
					exit;
				}
			} else {

				echo json_encode([
					"success" => "0",
					"message" => "invalid walletAmount!",
				]);
				exit;
			}
		} else {
			echo json_encode([
				"suucess" => "0",
				"message" => "Please enter valid params!",
			]);
			exit;
		}
	}

	public function addAddress()
	{

		if ($this->input->post()) {

			$city = $this->db->get_where('cities', ['id' => $this->input->post('city_id')])->row_array();
			if(empty($city)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid city_id'
				]);exit;
			}

			// $state = $this->db->get_where('states', ['id' => $city['state_id']])->row_array();
			$state = $this->db->get_where('states', ['id' => $this->input->post('state_id')])->row_array();
			if(empty($state)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid state_id'
				]);exit;
			}

			// $country = $this->db->get_where('countries', ['id' => $state['country_id']])->row_array();
			$country = $this->db->get_where('countries', ['id' => $this->input->post('country_id')])->row_array();
			if(empty($country)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid country_id'
				]);exit;
			}

			$data['name'] = $this->input->post("name");
			$data['userId'] = $this->input->post("userId");
			$data['number'] = $this->input->post("number");
			$data['alternate_number'] = $this->input->post("alternate_number");
			$data['pincode'] = $this->input->post("pincode");
			$data['country'] = $country['id'];
			$data['state'] = $state['id'];
			$data['city'] = $city['id'];
			$data['address'] = $this->input->post("address");
			$data['created'] = date("Y-m-d H:i:s");

			$upload = $this->db->insert("addAddress", $data);

			$getId = $this->db->insert_id();

			if ($upload == true) {

				$getDetails = $this->db->get_where("addAddress", ['id' => $getId])->row_array();

				echo json_encode([

					"success" => "1",
					"message" => "Address added",
					"details" => $getDetails,
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!",
				]);
				exit;
			}
		} else {

			echo json_encode([

				"success" => "0",
				"message" => "please enter valid params!",
			]);
			exit;
		}
	}

	public function editAddress()
	{

		if ($this->input->post()) {

			$check = $this->db->get_where("addAddress", ['userId' => $this->input->post("userId"), 'id' => $this->input->post("addressId")])->row_array();

			if (!!$check) {

				if (!!$this->input->post("name")) {
					$data['name'] = $this->input->post("name");
				}
				if (!!$this->input->post("number")) {
					$data['number'] = $this->input->post("number");
				}
				if (!!$this->input->post("alternate_number")) {
					$data['alternate_number'] = $this->input->post("alternate_number");
				}
				if (!!$this->input->post("pincode")) {
					$data['pincode'] = $this->input->post("pincode");
				}
				if (!!$this->input->post("country")) {
					$data['country'] = $this->input->post("country");
				}
				if (!!$this->input->post("state")) {
					$data['state'] = $this->input->post("state");
				}
				if (!!$this->input->post("city")) {
					$data['city'] = $this->input->post("city");
				}
				if (!!$this->input->post("address")) {
					$data['address'] = $this->input->post("address");
				}

				$edit = $this->db->update("addAddress", $data, ['userId' => $this->input->post("userId"), 'id' => $this->input->post("addressId")]);

				if ($edit == true) {

					$getDetails = $this->db->get_where("addAddress", ['userId' => $this->input->post("userId"), 'id' => $this->input->post("addressId")])->row_array();

					echo json_encode([

						"success" => "1",
						"message" => "details edit successfully",
						"details" => $getDetails,
					]);
					exit;
				} else {
					echo json_encode([

						"success" => "0",
						"message" => "something went wrong!",
					]);
					exit;
				}
			} else {

				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!please enter valid details.",
				]);
				exit;
			}
		} else {

			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid params!",
			]);
			exit;
		}
	}

	public function removeAddress()
	{

		$check = $this->db->get_where("addAddress", ['id' => $this->input->post("addressId"), 'userId' => $this->input->post("userId")])->row_array();

		if (!!$check) {

			$remove = $this->db->delete("addAddress", ['id' => $this->input->post("addressId"), 'userId' => $this->input->post("userId")]);

			if ($remove == true) {

				echo json_encode([

					"success" => "1",
					"message" => "address deleted",
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!"
				]);
				exit;
			}
		} else {

			echo json_encode([

				"success" => "0",
				"message" => "something went wrong!please enter valid details."
			]);
			exit;
		}
	}

	public function getAddress()
	{


		$get = $this->db->get_where("addAddress", ['userId' => $this->input->post("userId")])->result_array();

		if (!!$get) {

			$final = [];
			foreach($get as $gets){

				$gets['country'] = $this->db->select('name')->get_where('countries', ['id' => $gets['country']])->row_array();
				$gets['state'] = $this->db->select('name')->get_where('states', ['id' => $gets['state']])->row_array();
				$gets['city'] = $this->db->select('name')->get_where('cities', ['id' => $gets['city']])->row_array();

				$ad = $this->db->get_where('users', ['id' => $this->input->post("userId")])->row_array();
				if($ad['addressId'] == $gets['id']){
					$gets['applied'] = true;
				}else{
					$gets['applied'] = false;
				}
				
				$final[] = $gets;

			}

			echo json_encode([

				"success" => "1",
				"message" => "details found successfully",
				"details" => $final,
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

	public function purchaseProduct()
	{

		if ($this->input->post()) {

			$checkAddToCart = $this->db->get_where("addToCart", ['userId' => $this->input->post("userId"), 'productId' => $this->input->post("productId")])->row_array();

			if (empty($checkAddToCart)) {

				echo json_encode([

					"success" => "0",
					"message" => "This Product is not added in cart - by this user!",
				]);
				exit;
			}

			$checkAddress = $this->db->get_where("addAddress", ['userId' => $this->input->post("userId"), 'id' => $this->input->post("addressId")])->row_array();

			if (empty($checkAddress)) {

				echo json_encode([

					"success" => "0",
					"message" => "Invalid address!",
				]);
				exit;
			}

			$checkWalletAmount = $this->db->get_where("userWallet", ['userId' => $this->input->post("userId")])->row_array();

			$get = $checkWalletAmount['wallet_amount'];

			if ($get >= $this->input->post("amount")) {

				$data['userId'] = $this->input->post("userId");
				$data['addressId'] = $this->input->post("addressId");
				$data['productId'] = $this->input->post("productId");
				$data['amount'] = $this->input->post("amount");
				$data['created'] = date("Y-m-d H:i:s");

				$upload = $this->db->insert("purchaseProduct", $data);

				$getId = $this->db->insert_id();

				if ($upload == true) {

					$update['wallet_amount'] = $get - $data['amount'];

					$this->db->update("userWallet", $update, ['userId' => $this->input->post("userId")]);

					$getDetails = $this->db->get_where("purchaseProduct", ['id' => $getId])->row_array();

					echo json_encode([
						"success" => "1",
						"message" => "Product purchased successfully",
						"details" => $getDetails,
					]);
					exit;
				} else {
					echo json_encode([
						"success" => "0",
						"message" => "something went wrong!",
					]);
					exit;
				}
			} else {

				echo json_encode([
					"success" => "0",
					"message" => "invalid walletAmount!",
				]);
				exit;
			}
		} else {
			echo json_encode([
				"success" => "0",
				"message" => "Please enter valid params!",
			]);
			exit;
		}
	}

	public function getPurchaseProducts()
	{

		$get = $this->db->select("purchaseProduct.*,addProducts.product_name")
			->from("purchaseProduct")
			->join("addProducts", "addProducts.id = purchaseProduct.productId", "left")
			->where("purchaseProduct.userId", $this->input->post("userId"))
			->get()
			->result_array();

		if (!!$get) {

			foreach ($get as $key => $val) {

				$getId = $get[$key]["productId"];

				$get[$key]["product_images"] = $this->db->select("addProduct_images.id,addProduct_images.addProductId,addProduct_images.image")
					->from("addProduct_images")
					->where("addProduct_images.addProductId", $getId)
					->get()
					->result_array();
			}

			echo json_encode([
				"success" => "1",
				"message" => "details found",
				"details" => $get,
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

	public function useraAudioCall()
	{


		require APPPATH . '/libraries/agora/RtcTokenBuilder.php';
		require APPPATH . '/libraries/agora/RtmTokenBuilder.php';

		$appID = "baf0c9c870104305a1e84065750d53f4";
		$appCertificate = "bd92952ce5d94266b198c810706c36a3";
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
			$data['otherUserId'] = $this->input->post('otherUserId');
			$data['channelName'] = $this->input->post('channelName');
			$data['token'] = $token;
			$data['rtmToken'] = $tokenb;
			$data['created'] = date('Y-m-d H:i:s');
			$insert = $this->db->insert('useraAudioCall', $data);
			$ids = $this->db->insert_id();
			if ($insert == true) {

				$get = $this->db->select("useraAudioCall.id useraAudioCallId,useraAudioCall.userId,useraAudioCall.otherUserId,useraAudioCall.channelName,useraAudioCall.token,useraAudioCall.rtmToken,users.*")
					->from("useraAudioCall")
					->join("users", "users.id = useraAudioCall.userId", "left")
					->where("useraAudioCall.id", $ids)
					->get()
					->row_array();

				echo json_encode([

					"message" => "token generate successfully",
					"success" => "1",
					"details" => $get,
				]);
				exit;
			} else {

				echo json_encode([

					"message" => "Please try after some time",
					"success" => "0",
				]);
				exit;
			}
		} else {
			echo json_encode([

				"message" => "Please try after some time",
				"success" => "0",
			]);
			exit;
		}
	}

	public function commentsOnUploadVideo()
	{

		if ($this->input->post()) {

			$data['userId'] = $this->input->post("userId");
			$data['videoId'] = $this->input->post("videoId");
			$data['comment'] = $this->input->post("comment");
			$data['created'] = date("Y-m-d H:i:s");

			$upload = $this->db->insert("videoComments", $data);

			if ($upload == true) {

				$this->db->set('commentCount', 'commentCount +1', false)->where('id', $this->input->post('videoId'))->update("userVideos");

				echo json_encode([

					"success" => "1",
					"message" => "comment added successfully",
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!",
				]);
				exit;
			}
		} else {

			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid params!",
			]);
			exit;
		}
	}

	public function getUserUploadVideoComments()
	{

		$get = $this->db->select("videoComments.id videoCommentId,videoComments.userId,videoComments.videoId,videoComments.comment,videoComments.comment_like_counts,userVideos.hashTag,userVideos.description video_description,userVideos.videoPath,userVideos.allowComment,userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.commentCount,users.username,users.name,users.image")
			->from("videoComments")
			->join("userVideos", "userVideos.id = videoComments.videoId", "left")
			->join("users", "users.id = videoComments.userId", "left")
			->where("videoComments.videoId", $this->input->post("videoId"))
			->get()
			->result_array();

		// print_r($get);
		// die;

		if (!!$get) {

			foreach ($get as $gets) {

				$getid = $gets['videoCommentId'];

				$likeStatus = $this->db->get_where('likedComments', array('commentId' => $getid, 'userId' => $this->input->post('userId')))->row_array();
				if (!empty($likeStatus)) {
					$gets['commment_likeStatus'] = '1';
				} else {
					$gets['commment_likeStatus'] = '0';
				}

				$gets['image'] = $gets['image'];

				$final[] = $gets;
			}

			echo json_encode([
				"success" => "1",
				"message" => "comments details found",
				"details" => $final,
			]);
			exit;
		} else {

			echo json_encode([
				"success" => "0",
				"message" => "comments details not found!",
			]);
			exit;
		}
	}


	public function deleteVideoComments()
	{

		$checkVid = $this->db->get_where("videoComments", ['id' => $this->input->post('comment_id'), 'userId' => $this->input->post('userId')])->row_array();

		if (empty($checkVid)) {

			echo json_encode([

				"success" => "0",
				"message" => "please enter valid details",
			]);
			exit;
		}

		$removeVideo = $this->db->delete("videoComments", ['id' => $this->input->post('comment_id'), 'userId' => $this->input->post('userId')]);

		if ($removeVideo == true) {

			$getCommentCounts = $this->db->get_where("userVideos", ['id' => $checkVid['videoId']])->row_array();

			$commentsCounts = $getCommentCounts['commentCount'];

			$commentsCounts -= 1;

			$this->db->set(['commentCount' => $commentsCounts])
				->where(['id' => $checkVid['videoId']])
				->update('userVideos');



			echo json_encode([

				"success" => "1",
				"message" => "video comment deleted",
			]);
			exit;
		} else {
			echo json_encode([

				"success" => "0",
				"message" => "Something went wrong!",
			]);
			exit;
		}
	}

	public function likedComments()
	{
		$data['userId'] = $this->input->post('userId');
		$data['commentId'] = $this->input->post('commentId');
		$get = $this->db->get_where('likedComments', ['userId' => $this->input->post('userId'), 'commentId' => $this->input->post('commentId')])->row_array();
		if (!empty($get)) {
			$delete = $this->db->delete('likedComments', ['userId' => $this->input->post('userId'), 'commentId' => $this->input->post('commentId')]);

			$this->db->set('comment_like_counts', 'comment_like_counts -1', false)->where('id', $this->input->post('commentId'))->update("videoComments");
			if ($delete) {

				$getDetails = $this->db->select("videoComments.*")
					->from("videoComments")
					->where("videoComments.id", $this->input->post('commentId'))
					->get()
					->row_array();

				$checkCommentStatus = $this->db->get_where('likedComments', ['userId' => $this->input->post('userId'), 'commentId' => $this->input->post('commentId')])->row_array();

				if (!!$checkCommentStatus) {

					$message['comment_like_status'] = '1';
				} else {
					$message['comment_like_status'] = '0';
				}

				$getUnlikeCount = $getDetails['comment_like_counts'];
				$message['success'] = '2';
				$message['message'] = 'video_comment Unliked';
				$message['comment_like_count'] = $getUnlikeCount;
			}
		} else {
			$insert = $this->db->insert('likedComments', $data);

			$this->db->set('comment_like_counts', 'comment_like_counts +1', false)->where('id', $this->input->post('commentId'))->update("videoComments");
			if ($insert) {

				$getDetailss = $this->db->select("videoComments.*")
					->from("videoComments")
					->where("videoComments.id", $this->input->post('commentId'))
					->get()
					->row_array();

				$checkCommentStatus = $this->db->get_where('likedComments', ['userId' => $this->input->post('userId'), 'commentId' => $this->input->post('commentId')])->row_array();

				if (!!$checkCommentStatus) {

					$message['comment_like_status'] = '1';
				} else {
					$message['comment_like_status'] = '0';
				}
				$message['success'] = '1';
				$message['message'] = 'video_comment Liked';
				$message['comment_like_count'] = $getDetailss['comment_like_counts'];
			}
		}
		echo json_encode($message);
	}

	public function userFollow()
	{
		$data['userId'] = $this->input->post('userId'); //follow_by
		$data['followingUserId'] = $this->input->post('followingUserId'); //follow_to
		$get = $this->db->get_where('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' => $this->input->post('followingUserId')])->row_array();
		if (!empty($get)) {
			$delete = $this->db->delete('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' => $this->input->post('followingUserId')]);
			if ($delete) {
				$this->db->set('followingUser', 'followingUser -1', false)->where('id', $this->input->post('userId'))->update("users");
				$this->db->set('followerCount', 'followerCount -1', false)->where('id', $this->input->post('followingUserId'))->update("users");

				$get = $this->db->select("id,followingUser")
					->from("users")
					->where("users.id", $this->input->post('userId'))
					->get()
					->row_array();

				$checkCommentStatus = $this->db->get_where('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' => $this->input->post('followingUserId')])->row_array();

				if (!!$checkCommentStatus) {

					$message['follow_status'] = '1';
				} else {
					$message['follow_status'] = '0';
				}

				$message['success'] = '2';
				$message['message'] = 'User Un_follow successfully';
				$message['followingUser_count'] = $get['followingUser'];
			}
		} else {
			$insert = $this->db->insert('userFollow', $data);
			if ($insert) {

				$this->db->set('followingUser', 'followingUser +1', false)->where('id', $this->input->post('userId'))->update("users");
				$this->db->set('followerCount', 'followerCount +1', false)->where('id', $this->input->post('followingUserId'))->update("users");

				$get = $this->db->select("id,followingUser")
					->from("users")
					->where("users.id", $this->input->post('userId'))
					->get()
					->row_array();

				$checkCommentStatus = $this->db->get_where('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' => $this->input->post('followingUserId')])->row_array();

				if (!!$checkCommentStatus) {

					$message['follow_status'] = '1';
				} else {
					$message['follow_status'] = '0';
				}

				$message['success'] = '1';
				$message['message'] = 'User follow succesfully';
				$message['followingUser_count'] = $get['followingUser'];
			}
		}
		echo json_encode($message);
	}

	public function getFollowUnfollowStatus()
	{

		$get = $this->db->get_where("userFollow", ['userId' => $this->input->post("userId"), 'followingUserId' => $this->input->post("otheruserid")])->row_array();

		if (!!$get) {

			echo json_encode([

				"message" => "status found",
				"success" => "1",
				"status" => "1"
			]);
			exit;
		} else {
			echo json_encode([

				"message" => "status not found",
				"success" => "0",
				"status" => "0"
			]);
			exit;
		}
	}

	/**
	 * S3 BUCKET.
	 */


	protected function uploadVideo($file)
	{
		require APPPATH . '/libraries/vendor/autoload.php';

		try {
			$client = \Aws\S3\S3Client::factory([
				'version' => 'latest',
				'region'  => 'ap-south-1',
				'credentials' => [
					'key'    => "AKIAUMUJK362A7DE4CUK",
					'secret' => "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",
				]
			]); //exit;

			$return = $client->putObject([
				'Bucket'     => 'dateflulamp',
				'Key'        => time() . $file["name"], // we can define custom name //
				'SourceFile' => $file["tmp_name"],    // like /var/www/vhosts/mysite/file.csv
				'ACL'        => 'public-read',
			]);

			$aws_result = new \Aws\Result();
			return $return->get("ObjectURL");
		} catch (Exception $e) {
			// Catch an S3 specific exception.
			echo json_encode([
				"success"    =>    "0",
				"message"    =>    $e->getMessage(),
			]);
		}
	}



	/**
	 * Test Bucket
	 */

	public function uploadFunctions()
	{


		//if($this->input->post()){
		if (!empty($_FILES["image"]["name"])) {
			// $name= time().'_'.$_FILES["image"]["name"];
			// $liciense_tmp_name=$_FILES["image"]["tmp_name"];
			// $error=$_FILES["image"]["error"];
			// $liciense_path='uploads/user/'.$name;
			// move_uploaded_file($liciense_tmp_name,$liciense_path);
			// $details['image']=base_url(). $liciense_path;
			$details['image'] = $this->uploadVideo($_FILES["image"]);
		}

		$insert = $this->db->insert('TestBucket', $details);

		$getId = $this->db->insert_id();
		// echo $this->db->last_query();
		// die;

		if ($insert) {
			$getuploadfile = $this->db->get_where("TestBucket", ['id' => $getId])->row_array();
			$message['success'] = '1';
			$message['message'] = 'file upload successfuly';
			$message['return'] = $getuploadfile;
		} else {
			$message['success'] = '0';
			$message['message'] = 'error..!';
		}
		//  }
		//  else{
		// 	 $message['success'] = '0';
		// 	 $message['message'] = 'Please enter valid parameters!';
		// }
		echo json_encode($message);
	}

	// dateFlue Api.

	public function blockUnblock()
	{
		if ($this->input->post()) {

			if (!$this->input->post('userId')) { // blocker
				echo json_encode([
					'success' => '0',
					'message' => 'userId id required',
				]);
				exit;
			}

			if (!$this->input->post('blockUserId')) { // blockerTo
				echo json_encode([
					'success' => '0',
					'message' => 'blockUserId id required'
				]);
				exit;
			}

			$checkBlocker = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

			if (empty($checkBlocker)) {
				echo json_encode([
					'success' => '0',
					'message' => 'invalid userId'
				]);
				exit;
			}

			$checkBlockerTo = $this->db->get_where('users', ['id' => $this->input->post('blockUserId')])->row_array();

			if (empty($checkBlockerTo)) {
				echo json_encode([
					'success' => '0',
					'message' => 'invalid blockUserId'
				]);
				exit;
			}

			$checkBlock = $this->db->get_where('blockUser', ['userId' => $this->input->post('userId'), 'blockUserId' => $this->input->post('blockUserId')])->row_array();

			if (empty($checkBlock)) {
				$data['userId'] = $this->input->post('userId');
				$data['blockUserId'] = $this->input->post('blockUserId');
				$data['reason'] = $this->input->post('reason');
				$data['created'] = date('Y-m-d H:i:s');

				if ($this->db->insert('blockUser', $data)) {

					$this->db->delete('likeDislikeUser', ['userId' => $checkBlocker['id'], 'otherUserId' => $checkBlockerTo['id']]);
					$this->db->delete('likeDislikeUser', ['userId' => $checkBlockerTo['id'], 'otherUserId' => $checkBlocker['id']]);

					echo json_encode([
						'success' => '1',
						'message' => 'user blocked'
					]);
					exit;
				} else {
					echo json_encode([
						'success' => '0',
						'message' => 'user not blocked'
					]);
					exit;
				}
			} else {

				echo json_encode([
					'success' => '0',
					'message' => 'user already blocked by this user!'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'enter valid data'
			]);
			exit;
		}
	}

	// dateFlue Api.

	public function getBlockUsers()
	{

		$getDetails = $this->db->select("blockUser.id blockUserId,blockUser.userId,blockUser.blockUserId,blockUser.reason,users.*")
			->from("blockUser")
			->join("users", "users.id = blockUser.blockUserId", "left")
			->where("blockUser.userId", $this->input->post('userId'))
			->get()
			->result_array();

		if (!!$getDetails) {

			echo json_encode([

				"success" => "1",
				"message" => "details found",
				"details" => $getDetails
			]);
			exit;
		} else {
			echo json_encode([
				'success' => "0",
				'message' => 'details not found!'
			]);
			exit;
		}
	}

	/**
	 * unBlock-User Api.
	 */

	public function unBlockUser()
	{

		if (!$this->input->post('userId')) { // blocker
			echo json_encode([
				'success' => '0',
				'message' => 'userId id required',
			]);
			exit;
		}

		if (!$this->input->post('blockUserId')) { // blockerTo
			echo json_encode([
				'success' => '0',
				'message' => 'blockUserId id required'
			]);
			exit;
		}

		$checkBlocker = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

		if (empty($checkBlocker)) {
			echo json_encode([
				'success' => '0',
				'message' => 'invalid userId'
			]);
			exit;
		}

		$checkBlockerTo = $this->db->get_where('users', ['id' => $this->input->post('blockUserId')])->row_array();

		if (empty($checkBlockerTo)) {
			echo json_encode([
				'success' => '0',
				'message' => 'invalid blockUserId'
			]);
			exit;
		}

		$checkBlock = $this->db->get_where('blockUser', ['userId' => $this->input->post('userId'), 'blockUserId' => $this->input->post('blockUserId')])->row_array();

		if (!!$checkBlock) {

			$unBlockUser = $this->db->delete("blockUser", ['userId' => $this->input->post('userId'), 'blockUserId' => $this->input->post('blockUserId')]);

			if ($unBlockUser == true) {

				$unblock['blockUser'] = '0';
				$unblock['blockerBy'] = '0';
				$unblock['blockerTo'] = '0';

				$this->db->update("users", $unblock, ['id' => $this->input->post('blockUserId')]);

				echo json_encode([
					'success' => '1',
					'message' => 'User unBlocked successfully'
				]);
				exit;
			} else {
				echo json_encode([
					'success' => '0',
					'message' => 'something went wrong!'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'Please enter valid details!'
			]);
			exit;
		}
	}

	public function getUserRepotActions()
	{

		$get = $this->db->get("admin_sideAddUserReportActions")->result_array();

		if (!!$get) {

			echo json_encode([

				"success" => "1",
				"message" => "details found",
				"details" => $get
			]);
			exit;
		} else {
			echo json_encode([

				"success" => "0",
				"message" => "details not found!",

			]);
		}
	}

	public function userReport()
	{

		if ($this->input->post()) {

			$data['userReportActionId'] = $this->input->post("userReportActionId");
			$data['userId'] = $this->input->post("userId");
			$data['otherUserId'] = $this->input->post("otherUserId");

			$data['created'] = date("Y-m-d H:i:s");

			$upload = $this->db->insert("userReport", $data);

			$getId = $this->db->insert_id();

			if (!!$upload == true) {

				$getdetails = $this->db->get_where("userReport", ['id' => $getId])->row_array();

				echo json_encode([

					"success" => "1",
					"message" => "user report added",
					"details" => $getdetails,
				]);
				exit;
			} else {

				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!"
				]);
				exit;
			}
		} else {

			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid params!"
			]);
			exit;
		}
	}


	public function generateRtcToken()
	{

		$getDetails = $this->db->get_where("users", ['id' => $this->input->post('userId')])->row_array();

		if (empty($getDetails)) {

			echo json_encode([
				"success" => "0",
				"message" => "Please enter valid userId!"
			]);
			exit;
		}

		require APPPATH . '/libraries/agora/RtcTokenBuilder.php';
		require APPPATH . '/libraries/agora/RtmTokenBuilder.php';
		$appID = "978b62640a8d4aa79158ace167173bde";
		$appCertificate = "0e2df496ea9f4f0baa7d8051b9b354d2";
		$channelName = $this->input->post('channelName');
		$userId = $this->input->post('userId');
		$uid = '';
		$uidStr = '';
		$role = RtcTokenBuilder::RoleAttendee;
		$expireTimeInSeconds = 10800;
		$currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
		$privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
		$token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);

		if ($token == true) {

			$userDetails = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();

			$resposne['success'] = '1';
			$resposne['message'] = 'token generated';
			$resposne['details'] = $userDetails;
			$resposne['details']['token'] = $token;
			$resposne['details']['channelName'] = $this->input->post('channelName');

			echo json_encode($resposne);
			exit;
		} else {
			echo json_encode([

				"success" => "0",
				"message" => "token not generate!",
			]);
			exit;
		}
	}

	public function name()
	{

		$string = "My name is Rahul";

		$words = explode(" ", $string);

		// echo"<pre>";

		print_r($words);

		echo  $words[1] . " " . $words[3] . " " . $words[2] . " " . $words[0];
	}


	public function getVideo()
	{

		if ($this->input->post('userId') == null) {

			echo json_encode([
				"success" => "0",
				"message" => "userId cannot be null!"
			]);
			exit;
		}
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

			$list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,users.backgroundImage,users.addProductCounts product_counts,users.addVideoCounts video_counts, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount,userVideos.thumbnail, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId != '$userId' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' )")->result_array();
		} else {

			$list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,users.backgroundImage,users.addProductCounts product_counts,users.addVideoCounts video_counts, userVideos.id, userVideos.userId, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount,userVideos.thumbnail, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId != '$userId' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' )")->result_array();
		}

		if (!!$list) {

			$message['success'] = '1';
			$message['message'] = 'List Found Successfully';
			foreach ($list as $lists) {

				$get = $this->db->get_where('users', ['id' => $lists['userId']])->row_array();
				// print_r($get);exit;

				if (!empty($lists['name'])) {
					$lists['username'] = $get['name'];
				} else {
					$lists['username'] = $get['username'];
				}
				if (!empty($lists['downloadPath'])) {
					$lists['downloadPath'] = $lists['downloadPath'];
				} else {
					$lists['downloadPath'] =  '';
				}
				if (!empty($lists['image'])) {
					$lists['image'] = "";
				} else {
					$lists['image'] = $get['image'];
				}
				if (!empty($lists['backgroundImage'])) {
					$lists['backgroundImage'] = "";
				} else {
					$lists['backgroundImage'] = $get['backgroundImage'];
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

				$likeStatus = $this->db->get_where('likedDislikeVideos', array('videoId' => $lists['id'], 'userId' => $this->input->post('userId')))->row_array();
				if (!empty($likeStatus)) {
					$lists['likeStatus'] = '1';
				} else {
					$lists['likeStatus'] = '0';
				}

				$checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
				if (!empty($checkFollow)) {
					$lists['followStatus'] = '1';
				} else {
					$lists['followStatus'] = '0';
				}

				// Get user's connection counts.

				$connection_count = $this->get_likes($userId);
				if(empty($connection_count)){

					$lists['connectionCounts'] = 0;
				}else{

					$lists['connectionCounts'] = count($connection_count);
				}




				//GET connection status

				$getSuperLikeStatus = $this->db->select("likeDislikeUser.id likeDislikeUserId")
					->from("likeDislikeUser")
					->where("likeDislikeUser.otherUserId", $lists['userId'])
					->where("likeDislikeUser.userId", $this->input->post("userId"))
					->where("likeDislikeUser.likeDislike_type", "superlike")
					->get()
					->num_rows();

				$getSuperLikeStatusReverse = $this->db->select("likeDislikeUser.id likeDislikeUserId")
					->from("likeDislikeUser")
					->where("likeDislikeUser.userId", $lists['userId'])
					->where("likeDislikeUser.otherUserId", $this->input->post("userId"))
					->where("likeDislikeUser.likeDislike_type", "superlike")
					->get()
					->num_rows();

				if (!!$getSuperLikeStatus || !!$getSuperLikeStatusReverse) {
					$lists['superLike_connection_status'] = true;
				} else {
					$lists['superLike_connection_status'] = false;
				}

				$getLikeStatuss = $this->db->select("likeDislikeUser.id likeDislikeUserId")
					->from("likeDislikeUser")
					->where("likeDislikeUser.otherUserId", $lists['userId'])
					->where("likeDislikeUser.userId", $this->input->post("userId"))
					->where("likeDislikeUser.likeDislike_type", "like")
					->get()
					->num_rows();

				$getLikeStatusReversee = $this->db->select("likeDislikeUser.id likeDislikeUserId")
					->from("likeDislikeUser")
					->where("likeDislikeUser.userId", $lists['userId'])
					->where("likeDislikeUser.otherUserId", $this->input->post("userId"))
					->where("likeDislikeUser.likeDislike_type", "like")
					->get()
					->num_rows();

				if (!!$getLikeStatuss && !!$getLikeStatusReversee) {
					$lists['Like_connection_status'] = true;
				} else {
					$lists['Like_connection_status'] = false;
				}

				$lists['video_count'] = $this->db->get_where('userVideos', ['userId' => $lists['userId']])->num_rows();
				$lists['product_count'] = $this->db->get_where('addProducts', ['userId' => $lists['userId']])->num_rows();

				$message['details'][] = $lists;
			}
		} else {

			if ($this->input->post('videoType') == 'following') {

				$list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,users.backgroundImage,users.addProductCounts product_counts,users.addVideoCounts video_counts, userVideos.id, userVideos.userId,userVideos.thumbnail, userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId != '$userId' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' )")->result_array();
			} else {

				$list =  $this->db->query("SELECT users.username,users.name,users.followerCount as followers,users.image,users.backgroundImage,users.addProductCounts product_counts,users.addVideoCounts video_counts, userVideos.id, userVideos.userId,userVideos.thumbnail,	 userVideos.hashtag, userVideos.description, userVideos.videoPath,userVideos.viewCount, userVideos.allowComment, userVideos.allowDuetReact,userVideos.allowDownloads,userVideos.viewVideo,userVideos.likeCount,userVideos.commentCount,userVideos.downloadPath FROM `userVideos`  left join users on users.id = userVideos.userId where userVideos.viewVideo = 0 and userVideos.status = '1' and userVideos.userId != '$userId' and userVideos.userId NOT IN (select blockUserId from blockUser where userId = '$userId' ) and userVideos.id NOT IN (select videoId from  viewVideo where userId = '$userId' )")->result_array();
			}

			if (!empty($list)) {

				$message['success'] = '1';
				$message['message'] = 'List Found Successfully';
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

					if (!empty($lists['image'])) {
						$lists['image'] = $lists['image'];
					} else {

						$lists['image'] = "";
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

					$likeStatus = $this->db->get_where('likedDislikeVideos', array('videoId' => $lists['id'], 'userId' => $this->input->post('userId')))->row_array();
					if (!empty($likeStatus)) {
						$lists['likeStatus'] = '1';
					} else {
						$lists['likeStatus'] = '0';
					}


					$checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $lists['userId'], 'status' => '1'))->row_array();
					if (!empty($checkFollow)) {
						$lists['followStatus'] = '1';
					} else {
						$lists['followStatus'] = '0';
					}



					// Get user's connection counts.

					$checkStatus = $this->db->get_where("likeDislikeUser", ['userId' => $lists['userId'], 'likeDislike_type' => 'like'])->result_array();

					$checkSuperLike = $this->db->get_where("likeDislikeUser", ['userId' => $lists['userId'], 'likeDislike_type' => 'superlike'])->result_array();

					$checkSuperLikeReverse = $this->db->get_where("likeDislikeUser", ['otherUserId' => $lists['userId'], 'likeDislike_type' => 'superlike'])->result_array();

					$finall = [];

					foreach ($checkStatus as $getss) {

						$getiD = $getss['otherUserId'];
						$getuiD = $getss['userId'];

						$getOtherUser = $this->db->select("likeDislikeUser.id likeDislikeUserId")
							->from("likeDislikeUser")
							->where("likeDislikeUser.userId", $getiD)
							->where("likeDislikeUser.otherUserId", $getuiD)
							->where("likeDislikeUser.likeDislike_type", "like")
							->get()
							->num_rows();


						if (!empty($getOtherUser)) {

							$getss['connect_counts'] = $getOtherUser;

							$finall[] = $getss;
						}
					}

					$FinalSuper = [];

					foreach ($checkSuperLike as $getsss) {

						$getiD = $getsss['otherUserId'];
						$getuiD = $getsss['userId'];

						$getOtherUserr = $this->db->select("likeDislikeUser.id likeDislikeUserId")
							->from("likeDislikeUser")
							->where("likeDislikeUser.userId", $getuiD)
							->where("likeDislikeUser.likeDislike_type", "superlike")
							->get()
							->num_rows();

						if (!!$getOtherUserr) {

							$getsss['connect_countss'] = $getOtherUserr;

							$FinalSuper[] = $getsss;
						}
					}

					$FinalSuperReverse = [];

					foreach ($checkSuperLikeReverse as $getSuper) {

						$getiD = $getSuper['otherUserId'];
						$getuiD = $getSuper['userId'];

						$getOtherUse = $this->db->select("likeDislikeUser.id likeDislikeUserId")
							->from("likeDislikeUser")
							->where("likeDislikeUser.otherUserId", $getiD)
							->where("likeDislikeUser.likeDislike_type", "superlike")
							->get()
							->num_rows();

						if (!empty($getOtherUse)) {

							$getSuper['connect_countss'] = $getOtherUse;

							$FinalSuperReverse[] = $getSuper;
						}
					}
					$c = (string)count($finall);
					$s = (string)count($FinalSuper);
					$sr = (string)count($FinalSuperReverse);

					$lists['connectionCounts'] = $c + $s + $sr;

					//GET connection status

					$getSuperLikeStatus = $this->db->select("likeDislikeUser.id likeDislikeUserId")
						->from("likeDislikeUser")
						->where("likeDislikeUser.otherUserId", $lists['userId'])
						->where("likeDislikeUser.userId", $this->input->post("userId"))
						->where("likeDislikeUser.likeDislike_type", "superlike")
						->get()
						->num_rows();

					$getSuperLikeStatusReverse = $this->db->select("likeDislikeUser.id likeDislikeUserId")
						->from("likeDislikeUser")
						->where("likeDislikeUser.userId", $lists['userId'])
						->where("likeDislikeUser.otherUserId", $this->input->post("userId"))
						->where("likeDislikeUser.likeDislike_type", "superlike")
						->get()
						->num_rows();

					if (!!$getSuperLikeStatus || !!$getSuperLikeStatusReverse) {
						$lists['superLike_connection_status'] = true;
					} else {
						$lists['superLike_connection_status'] = false;
					}

					$getLikeStatuss = $this->db->select("likeDislikeUser.id likeDislikeUserId")
						->from("likeDislikeUser")
						->where("likeDislikeUser.otherUserId", $lists['userId'])
						->where("likeDislikeUser.userId", $this->input->post("userId"))
						->where("likeDislikeUser.likeDislike_type", "like")
						->get()
						->num_rows();

					$getLikeStatusReversee = $this->db->select("likeDislikeUser.id likeDislikeUserId")
						->from("likeDislikeUser")
						->where("likeDislikeUser.userId", $lists['userId'])
						->where("likeDislikeUser.otherUserId", $this->input->post("userId"))
						->where("likeDislikeUser.likeDislike_type", "like")
						->get()
						->num_rows();

					if (!!$getLikeStatuss && !!$getLikeStatusReversee) {
						$lists['Like_connection_status'] = true;
					} else {
						$lists['Like_connection_status'] = false;
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


	public function nearByUsers()
	{
		if ($this->input->post()) {

			// $getlat = $this->input->post('latitude');
			// $getlong = $this->input->post('longitude');
			$ID = $this->input->post('userId');
			// $gethospital = $this->db->select("users.*, (6731 * acos( cos( radians($getlat) ) * cos( radians( users.latitude ) ) * cos( radians(users.longitude ) - radians($getlong) ) + sin( radians($getlat) ) * sin(radians(users.latitude)) ) ) AS distance")
			$gethospital = $this->db->select("users.*")
				->from("users")
				->where("users.latitude !=", '')
				->where("users.longitude !=", '')
				->where("users.id !=", $ID)
				// ->having("distance <", 10)
				// ->order_by("distance", "ASC")
				->get()
				->result_array();

			// print_r($gethospital);
			// die;

			if (!empty($gethospital)) {

				foreach ($gethospital as $chil) {

					$get = $chil['id'];

					

					// GET CONNECTION COUNTS START //

					$checkStatus = $this->db->get_where("likeDislikeUser", ['userId' => $get, 'likeDislike_type' => 'like'])->result_array();

					$checkSuperLike = $this->db->get_where("likeDislikeUser", ['userId' => $get, 'likeDislike_type' => 'superlike'])->result_array();

					$checkSuperLikeReverse = $this->db->get_where("likeDislikeUser", ['otherUserId' => $get, 'likeDislike_type' => 'superlike'])->result_array();

					$finall = [];

					foreach ($checkStatus as $getss) {

						$getiD = $getss['otherUserId'];
						$getuiD = $getss['userId'];

						$getOtherUser = $this->db->select("likeDislikeUser.id likeDislikeUserId")
							->from("likeDislikeUser")
							->where("likeDislikeUser.userId", $getiD)
							->where("likeDislikeUser.otherUserId", $getuiD)
							->where("likeDislikeUser.likeDislike_type", "like")
							->get()
							->num_rows();


						if (!empty($getOtherUser)) {

							$getss['connect_counts'] = $getOtherUser;

							$finall[] = $getss;
						}
					}

					$FinalSuper = [];

					foreach ($checkSuperLike as $getsss) {

						$getiD = $getsss['otherUserId'];
						$getuiD = $getsss['userId'];

						$getOtherUserr = $this->db->select("likeDislikeUser.id likeDislikeUserId")
							->from("likeDislikeUser")
							->where("likeDislikeUser.userId", $getuiD)
							->where("likeDislikeUser.likeDislike_type", "superlike")
							->get()
							->num_rows();

						if (!!$getOtherUserr) {

							$getsss['connect_countss'] = $getOtherUserr;

							$FinalSuper[] = $getsss;
						}
					}

					$FinalSuperReverse = [];

					foreach ($checkSuperLikeReverse as $getSuper) {

						$getiD = $getSuper['otherUserId'];
						$getuiD = $getSuper['userId'];

						$getOtherUse = $this->db->select("likeDislikeUser.id likeDislikeUserId")
							->from("likeDislikeUser")
							->where("likeDislikeUser.otherUserId", $getiD)
							->where("likeDislikeUser.likeDislike_type", "superlike")
							->get()
							->num_rows();

						if (!empty($getOtherUse)) {

							$getSuper['connect_countss'] = $getOtherUse;

							$FinalSuperReverse[] = $getSuper;
						}
					}

					// GET CONNECTION COUNTS END //

					$check = $this->db->get_where("blockUser", ['userId' => $ID, 'blockUserId' => $get])->row_array();
					$checkSuperLike = $this->db->get_where("likeDislikeUser", ['userId' => $ID, 'otherUserId' => $get, 'likeDislike_type' => 'superlike'])->row_array();
					$checkLike = $this->db->get_where("likeDislikeUser", ['userId' => $ID, 'otherUserId' => $get, 'likeDislike_type' => 'like'])->row_array();

					$checkdislike = $this->db->get_where("likeDislikeUser", ['userId' => $ID, 'otherUserId' => $get, 'likeDislike_type' => 'dislike'])->row_array();

					if (!!$check || !!$checkSuperLike || !!$checkLike || !!$checkdislike) {
					} else {

						$checkk = $this->db->get_where("blockUser", ['userId' => $get, 'blockUserId' => $ID])->row_array();
						$checkSuperLikeReverse = $this->db->get_where("likeDislikeUser", ['userId' => $get, 'otherUserId' => $ID, 'likeDislike_type' => 'superlike'])->row_array();
						$checkLikeReverse = $this->db->get_where("likeDislikeUser", ['userId' => $get, 'otherUserId' => $ID, 'likeDislike_type' => 'like'])->row_array();

						if (!!$checkk || $checkSuperLikeReverse || !!$checkLikeReverse) {
						} else {

							$c = (string)count($finall);
							$s = (string)count($FinalSuper);
							$sr = (string)count($FinalSuperReverse);

							$chil['connectionCounts'] = $c + $s + $sr;
							$final[] = $chil;
						}
						// $chil['image'] = $chil['image'];


					}
				}

				if (!!$final) {

					$message = array(
						'success' => '1',
						'message' => 'Users found successfuly',
						"details" => $final
					);
				} else {
					$message['success'] = '0';
					$message['message'] = 'List not found!';
				}
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

	public function getReceiverEndTotalGifting()
	{ // dateFlu Api.

		$getDetails = $this->db->select_sum("cust.coin")
			->select("cust.userId")
			->select("cust.giftUserId")
			->select("cust.giftId")
			->select("receiver.name")
			->select("receiver.username")
			->select("receiver.my_level receiver_my_level")
			->select("receiver.image")
			->select("receiver.backgroundImage")
			->select("receiver.posterImage")
			->select("receiver.talent_level receiver_talent_level")
			->select("receiver.talent_level receiver_talent_level")
			->select("receiver.talent_level receiver_talent_level")
			->select("live.*")
			->select("user_levels.image user_levelImage,user_talent_levels.image user_talent_levelImage")
			->from("userGiftHistory cust")
			->join("users receiver", "receiver.id = cust.giftUserId")
			->join("userLive live", "live.userId = cust.giftUserId")
			->join("user_levels", "user_levels.level = receiver.my_level", "left")
			->join("user_talent_levels", "user_talent_levels.level = receiver.talent_level", "left")
			->where("live.status", 'live')
			->group_by("cust.giftUserId")
			->order_by('coin', 'desc')
			->get()
			->result_array();

		if (!!$getDetails) {

			$final = [];

			foreach ($getDetails as $get) {

				$Id = $get['giftUserId'];

				$getSuperLikeStatus = $this->db->select("likeDislikeUser.id likeDislikeUserId")
					->from("likeDislikeUser")
					->where("likeDislikeUser.otherUserId", $Id)
					->where("likeDislikeUser.userId", $this->input->post("userId"))
					->where("likeDislikeUser.likeDislike_type", "superlike")
					->get()
					->num_rows();

				$getSuperLikeStatusReverse = $this->db->select("likeDislikeUser.id likeDislikeUserId")
					->from("likeDislikeUser")
					->where("likeDislikeUser.userId", $Id)
					->where("likeDislikeUser.otherUserId", $this->input->post("userId"))
					->where("likeDislikeUser.likeDislike_type", "superlike")
					->get()
					->num_rows();

				if (!!$getSuperLikeStatus || !!$getSuperLikeStatusReverse) {
					$get['superLike_connection_status'] = true;
				} else {
					$get['superLike_connection_status'] = false;
				}


				$getLikeStatuss = $this->db->select("likeDislikeUser.id likeDislikeUserId")
					->from("likeDislikeUser")
					->where("likeDislikeUser.otherUserId", $Id)
					->where("likeDislikeUser.userId", $this->input->post("userId"))
					->where("likeDislikeUser.likeDislike_type", "like")
					->get()
					->num_rows();

				$getLikeStatusReversee = $this->db->select("likeDislikeUser.id likeDislikeUserId")
					->from("likeDislikeUser")
					->where("likeDislikeUser.userId", $Id)
					->where("likeDislikeUser.otherUserId", $this->input->post("userId"))
					->where("likeDislikeUser.likeDislike_type", "like")
					->get()
					->num_rows();

				if (!!$getLikeStatuss && !!$getLikeStatusReversee) {
					$get['Like_connection_status'] = true;
				} else {
					$get['Like_connection_status'] = false;
				}

				$final[] = $get;
			}


			echo json_encode([

				"success" => "1",
				"message" => "details found",
				"details" => $final
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

	public function get_live_user_token(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' =>  0,
					'message' => 'invalid userId'
				]);exit;
			}

			$users = $this->db->get('users')->result_array();

			$final = [];

			foreach($users as $userr){

				if($user['id'] == $userr['id']){

				}else{

					$get = $this->db->select('userLive.createdDate, userLive.status userstatus, userLive.*, users.*')
									->from('userLive')
									->join('users', 'users.id = userLive.userId', 'left')
									->where('userId', $userr['id'])
									->order_by('userLive.id', 'desc')
									->get()->row_array();

									$get['followstatus'] = false;
									$follow = $this->db->get_where('userFollow', ['userId' => $userr['id'], 'followingUserId' => $user['id'], 'status' => '1'])->row_array();
									if(!!$follow){
										$get['followstatus'] = true;
									}

									$followers = $this->db->get_where('userFollow', ['userId' => $userr['id'], 'status', '1'])->num_rows();
									$get['followerCount'] = $followers;

									if($get['userstatus'] == 'live'){

										$check_block = $this->db->get_where('blockUser', ['userId' => $user['id'], 'blockUserId' => $userr['id']])->row_array();
										if(empty($check_block)){

											$final[] = $get;
										}

									}
				}

			}

			if(empty($final)){
				echo json_encode([
					'status' => 0,
					'message' => 'no data found'
				]);exit;
			}

			rsort($final);
			echo json_encode([
				'status' => 1,
				'message' => 'list found',
				'details' => $final
			]);exit;



			
		}else{
			echo json_encode([
				'status' =>  0,
				'message' => 'method not allowed'
			]);exit;
		}
	}


	public function getCountryStates()
	{

		$getDetails = $this->db->get_where("countries", ['id' => $this->input->post("countryId")])->row_array();

		if (!!$getDetails) {

			$getId = $getDetails['id'];

			$getDetails['states'] = $this->db->get_where("states", ['country_id' => $getId])->result_array();

			echo json_encode([

				"success" => "1",
				"message" => "details found",
				"details" => $getDetails
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

	public function bank_details()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {

			$banks = $this->db->select('bank_name, bank_id')
				->from('bank_details')
				->group_by('bank_name')
				->order_by('bank_name')
				->get()->result_array();

			if (empty($banks)) {
				echo json_encode([
					'status' => 0,
					'message' => 'No banks found'
				]);
				exit;
			}

			echo json_encode([
				'status' => 1,
				'message' => 'bank details found',
				'details' => $banks
			]);
			exit;
		} else {
			echo json_encode([
				'status' => 0,
				'message' => 'Method not allowed'
			]);
			exit;
		}
	}

	public function add_bank_account()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			// print_r($user);exit;
			if (empty($user)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);
				exit;
			}

			$bank = $this->db->get_where('bank_details', ['bank_id' => $this->input->post('bankId')])->row_array();
			if (empty($bank)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid bankId'
				]);
				exit;
			}

			if (!$this->input->post('ifsc_code')) {
				echo json_encode([
					'status' => 0,
					'message' => 'ifsc_code required'
				]);
				exit;
			}

			if (!$this->input->post('account_number')) {
				echo json_encode([
					'status' => 0,
					'message' => 'account_number required'
				]);
				exit;
			}

			// if(!$this->input->post('nickname')){
			// 	echo json_encode([
			// 		'status' => 0,
			// 		'message' => 'nickname required'
			// 	]);exit;
			// }

			$check = $this->db->get_where('user_bank_account', ['userId' => $user['id'], 'bankId' => $bank['bank_id'], 'account_number' => $this->input->post('account_number')])->row_array();
			if (!!$check) {
				echo json_encode([
					'status' => 0,
					'message' => 'this account number has already registered by the user'
				]);
				exit;
			}

			$data['userId'] = $user['id'];
			$data['bankId'] = $bank['bank_id'];
			$data['ifsc_code'] = $this->input->post('ifsc_code');
			$data['account_number'] = $this->input->post('account_number');
			$data['nickname'] = $this->input->post('nickname');
			$data['holder_name'] = $this->input->post('holder_name');
			$data['created'] = date('Y-m-d H:i:s');

			if ($this->db->insert('user_bank_account', $data)) {

				echo json_encode([
					'status' => 1,
					'message' => 'user bank account registered'
				]);
				exit;
			} else {
				echo json_encode([
					'status' => 0,
					'message' => 'DB error'
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


	public function remove_bank_account()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (empty($user)) {
				echo json_encode([
					'status' => 0,
					'message' => 'inavlid userId'
				]);
				exit;
			}

			$check = $this->db->get_where('user_bank_account', ['userId' => $user['id'], 'id' => $this->input->post('detailId')])->row_array();
			if (empty($check)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid details'
				]);
				exit;
			}

			if ($this->db->delete('user_bank_account', ['id' => $check['id']])) {
				echo json_encode([
					'status' => 1,
					'message' => 'deleted successfuly'
				]);
				exit;
			} else {
				echo json_encode([
					'status' => 0,
					'message' => 'DB error'
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

	public function user_bank_account_list()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (empty($user)) {
				echo json_encode([
					'status' => 1,
					'message' => 'invalid userId'
				]);
				exit;
			}

			// $bank_details = $this->db->get_where('user_bank_account', ['userId' => $user['id']])->result_array();
			$bank_details = $this->db->select('bank_details.bank_name, user_bank_account.*')
				->from('user_bank_account')
				->join('bank_details', 'bank_details.bank_id = user_bank_account.bankId', 'left')
				->where('userId', $user['id'])
				->order_by('id', 'desc')
				->get()->result_array();

			if (empty($bank_details)) {
				echo json_encode([
					'status' => 0,
					'message' => 'no details found'
				]);
				exit;
			}

			echo json_encode([
				'status' => 1,
				'message' => 'details found',
				'details' => $bank_details
			]);
			exit;
		} else {
			echo json_encode([
				'status' => 0,
				'message' => 'Method not allowed'
			]);
			exit;
		}
	}

	public function generate_order_id()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			try {

				$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
				if (empty($user)) {
					echo json_encode([
						'status' => 0,
						'message' => 'invalid userId'
					]);
					exit;
				}

				if (!$this->input->post('amount')) {
					echo json_encode([
						'status' => 0,
						'message' => 'amount can not be empty'
					]);
					exit;
				}

				$amount = $this->input->post('amount');

				$api = new Api('rzp_test_l0iCxNFrSqR4nG', 'SoVLw255d1MwZ2ugsrKEI8F9');

				$orderData = [
					'amount' => $amount * 100,
					'currency' => 'INR',
					'payment_capture' => 1
				];

				$pay = $api->order->create($orderData);
				$data['userId'] = $user['id'];
				$data['orderId'] = $pay['id'];
				$data['amount'] = $amount;
				$data['status'] = 'pending';
				$data['dateCreated'] = date('Y-m-d H:i:s');

				$this->db->insert('purchased_coins_history', $data);


				echo json_encode([
					'status' => 1,
					'message' => 'orderId generated',
					'orderId' => $pay['id']
				]);
				exit;
			} catch (exception $e) {

				echo json_encode([
					'status' => 0,
					'message' => $e->getMessage()
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

	public function confirm_payment()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (empty($user)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);
				exit;
			}

			if (!$this->input->post('paymentId')) {
				echo json_encode([
					'status' => 0,
					'message' => 'paymentId required'
				]);
				exit;
			}

			if (!$this->input->post('signature')) {
				echo json_encode([
					'status' => 0,
					'message' => 'signature required'
				]);
				exit;
			}

			$order = $this->db->get_where('purchased_coins_history', ['userId' => $user['id'], 'orderId' => $this->input->post('orderId')])->row_array();
			if (empty($order)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid orderId'
				]);
				exit;
			}

			if ($order['status'] == 'completed') {
				echo json_encode([
					'status' => 0,
					'message' => 'order already completed'
				]);
				exit;
			}

			$userdata['purchasedCoin'] = $user['purchasedCoin'];
			$userdata['purchasedCoin'] += $order['amount'];

			$data['paymentId'] = $this->input->post('paymentId');
			$data['signature'] = $this->input->post('signature');
			$data['status'] = 'completed';
			$data['dateUpdated'] = date('Y-m-d H:i:s');

			$this->db->set($userdata)->where('id', $user['id'])->update('users');
			$this->db->set($data)->where('id', $order['id'])->update('purchased_coins_history');

			echo json_encode([
				'status' => 1,
				'message' => 'coin purchase complete'
			]);
			exit;
		} else {
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);
			exit;
		}
	}

	public function my_purchased_coins()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (empty($user)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);
				exit;
			}

			$purchased_coins = $user['purchasedCoin'] ?: '0';
			$coins = $user['coin'] ?: '0';


			echo json_encode([
				'status' => 1,
				'message' => 'details found',
				'wallet' => [
					'purchased_conins' => $purchased_coins,
					'coins' => $coins
				]
			]);
			exit;
		} else {
			echo json_encode([
				'status' => 0,
				'message' => 'Method not allowed'
			]);
			exit;
		}
	}

	public function send_withdraw_request()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (empty($user)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);
				exit;
			}

			if (!$this->input->post('coins')) {
				echo json_encode([
					'status' => 0,
					'message' => 'coins required'
				]);
				exit;
			}

			if ($this->input->post('coins') < '2000' || $this->input->post('coins') > '2000000') {
				echo json_encode([
					'status' => 0,
					'message' => 'min 2000 or maximum 2000000 coins can withdraw'
				]);
				exit;
			}

			if ($this->input->post('coins') > $user['coin']) {
				echo json_encode([
					'status' => 0,
					'message' => 'insufficient balance'
				]);
				exit;
			}

			$bank = $this->db->get_where('user_bank_account', ['id' => $this->input->post('bankId'), 'userId' => $user['id']])->row_array();
			if (empty($bank)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid bankId'
				]);
				exit;
			}

			$data['userId'] = $user['id'];
			$data['bankId'] = $bank['id'];
			$data['coins'] = $this->input->post('coins');
			$data['status'] = '0';
			$data['date'] = date('Y-m-d');
			$data['time'] = date('H:i:s');


			$check = $this->db->where('userId', $user['id'])->order_by('id', 'desc')->get('withdraw_requests')->row_array();
			if ($check['date'] == date("Y-m-d")) {
				echo json_encode([
					'status' => 0,
					'message' => 'you can send only one request per day'
				]);
				exit;
			}

			if ($this->db->insert('withdraw_requests', $data)) {
				echo json_encode([
					'status' => 1,
					'message' => 'request sent to the admin'
				]);
				exit;
			} else {
				echo json_encode([
					'status' => 0,
					'message' => 'DB error'
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

	public function get_withdraw_request()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (empty($user)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);
				exit;
			}

			$get = $this->db->get_where('withdraw_requests', ['userId' => $user['id']])->result_array();
			if (empty($get)) {
				echo json_encode([
					'status' => 0,
					'message' => 'no requests found'
				]);
				exit;
			}

			$final = [];
			foreach ($get as $gets) {
				$gets['bankId'] = $this->db->select('user_bank_account.*, bank_details.bank_name')
					->from('user_bank_account')
					->join('bank_details', 'bank_details.bank_id = user_bank_account.bankId', 'left')
					->where('id', $gets['bankId'])
					->get()->row_array();

				$final[] = $gets;
			}

			echo json_encode([
				'status' => 1,
				'message' => 'requests found',
				'details' => $final
			]);
			exit;
		} else {
			echo json_encode([
				'status' => 0,
				'message' => 'Method not allowed'
			]);
			exit;
		}
	}

	public function direct_buy(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$product = $this->db->get_where('addProducts', ['id' => $this->input->post('productId')])->row_array();
			if(empty($product)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid productId'
				]);
			}

			if($this->input->post('quantity') < '1')
			{
				echo json_encode([
					'status' => 0,
					'message' => 'quantity can not be less then 1'
				]);exit;
			}

			// checking old orders, if exist then remove it
			$check_pending = $this->db->get_where('orderDetails', ['userId' => $user['id'], 'status' => '1', 'direct_buy' => '1'])->row_array();
			if(!!$check_pending){
				$this->db->delete('orderDetails', ['id' => $check_pending['id']]);
			}


			// generating ordrId
			$order = $this->db->order_by('id', 'desc')->get('orderDetails')->row_array();
			if(empty($order)){
				$data['orderId'] = 'DFODRID001';
			}else{

				$data['orderId'] = ++$order['orderId'];

			}

			// $tax = $this->db->select('tax')->from('tax')->get()->row_array();
			$tax_amount = 2.0;

			$data['userId'] = $user['id'];
			$data['subtotal'] = $product['coins'] * $this->input->post('quantity');
			$data['shipping'] = $product['shipping'];
			$data['tax'] = $tax_amount;
			$data['product_ids'] = $product['id'];
			$data['status'] = 1;
			$data['direct_buy'] = 1;
			$tax = $data['subtotal'] + $data['shipping'];
			$data['total'] = $tax;
			$data['quantity'] = $this->input->post('quantity');
			$tax = ($tax_amount / 100) * $tax;
			$data['total'] += $tax;
			$data['date'] = date('Y-m-d H:i:s');

			$this->db->insert('orderDetails', $data);

			echo json_encode([
				'status' => 1,
				'message' => 'order generated',
				'details' => $data
			]);exit;



		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}
	}


	public function order_from_cart(){

		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$cart = $this->db->get_where('addToCart', ['userId' => $user['id']])->result_array();
			if(empty($cart)){
				echo json_encode([
					'status' => 0,
					'message' => 'cart is empty'
				]);exit;
			}

			$final = [];
			$ids = [];
			$subtotal = 0;
			$shipping = 0;
			// $tax = $this->db->select('tax')->from('tax')->get()->row_array();
			$tax_amount = 2.0;
			$product_ids = 0;
			foreach($cart as $product){

				$product_detail = $this->db->get_where('addProducts', ['id' => $product['productId']])->row_array();

				$product['price'] = $product_detail['coins'] * $product['quantity'];

				$subtotal += $product['price'];
				$shipping += $product_detail['shipping'];			

				$ids[] = $product['id'];
				$final[] = $product;
			}

			$check_pending = $this->db->get_where('orderDetails', ['userId' => $user['id'], 'status' => '1'])->row_array();
			if(!!$check_pending){
				$this->db->delete('orderDetails', ['id' => $check_pending['id']]);
			}

			$order = $this->db->order_by('id', 'desc')->get('orderDetails')->row_array();
			if(empty($order)){
				$data['orderId'] = 'DFODRID001';
			}else{

				$data['orderId'] = ++$order['orderId'];

			}

			$id = implode(',', $ids);

			$data['userId'] = $user['id'];
			$data['subtotal'] = $subtotal;
			$data['shipping'] = $shipping;
			$data['tax'] = $tax_amount;
			$data['product_ids'] = $id;
			$data['status'] = 1;
			$data['direct_buy'] = 0;
			$tax = $subtotal + $shipping;
			$data['total'] = $tax;
			$tax = ($tax_amount / 100) * $tax;
			$data['total'] += $tax;
			$data['date'] = date('Y-m-d H:i:s');

			
			

			$this->db->insert('orderDetails', $data);

			echo json_encode([
				'status' => 1,
				'message' => 'order generated',
				'details' => $data
			]);exit;



		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}

	}

	public function buy_order(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}


			$order = $this->db->get_where('orderDetails', ['orderId' => $this->input->post('orderId'), 'userId' => $this->input->post('userId')])->row_array();
			if(empty($order)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid orderId'
				]);exit;
			}

			if($order['status'] == '2'){
				echo json_encode([
					'status' => 0,
					'message' => 'payment already done'
				]);exit;
			}

			if($order['direct_buy'] == '0'){

				$ids = explode(',',$order['product_ids']);
				foreach($ids as $id){


					$details = $this->db->get_where('addToCart', ['id' => $id])->row_array();
					$quantity = $details['quantity'];

					$product = $this->db->get_where('addProducts', ['id' => $details['productId']])->row_array();
					$current_quantity = $product['quantity'];

					if($current_quantity < $quantity){
						echo json_encode([
							'status' => 0,
							'message' => 'product out of stock remove from cart',
							'details' => $product
						]);exit;
					}else{

						$pdata['quantity'] = $current_quantity - $quantity;
						$this->db->set($pdata)->where('id', $product['id'])->update('addProducts');

					}

					$this->db->delete('addToCart', ['id' => $id]);

				}

			}else if($order['direct_buy'] == '1'){

				$quantity = $order['quantity'];

				$product = $this->db->get_where('addProducts', ['id' => $order['product_ids']])->row_array();
				$current_quantity = $product['quantity'];

				if($current_quantity < $quantity){
					echo json_encode([
						'status' => 0,
						'message' => 'product out of stock remove from cart',
						'details' => $product
					]);exit;
				}else{

					$pdata['quantity'] = $current_quantity - $quantity;
					$this->db->set($pdata)->where('id', $product['id'])->update('addProducts');

				}

			}

			$user_purchased_coins = $user['purchasedCoin'];
			$order_total = $order['total'];

			if($order_total > $user_purchased_coins){
				echo json_encode([
					'status' => 0,
					'message' => 'insufficient purchased coins'
				]);exit;
			}

			
			$address = $this->db->get_where('addAddress', ['id' => $this->input->post('addressId'), 'userId' => $user['id']])->row_array();
			if(empty($address)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid addressId'
				]);exit;
			}


			$updata['purchasedCoin'] = $user_purchased_coins - $order_total;
			$this->db->set($updata)->where('id', $user['id'])->update('users');

			$orderdata['addressId'] = $address['id'];
			$orderdata['status'] = 2;

			$this->db->set($orderdata)->where('id', $order['id'])->update('orderDetails');
			echo json_encode([
				'status' => 1,
				'message' => 'payment done'
			]);exit;




		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}
	}

	public function my_cart(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$cart = $this->db->get_where('addToCart', ['userId' => $user['id']])->result_array();
			if(empty($cart)){
				echo json_encode([
					'status' => 0,
					'message' => 'cart is empty'
				]);exit;
			}


			$final = [];
			$total = 0;
			foreach($cart as $carts){
				$product = $this->db->get_where('addProducts', ['id' => $carts['productId']])->row_array();
				$product_images = $this->db->get_where('addProduct_images', ['addProductId' => $carts['productId']])->result_array();

				$carts['productId'] = $product;
				$carts['product_images'] = $product_images;
				$carts['product_total'] = $carts['quantity'] * $product['coins'];


				$total += $carts['product_total'];
				$final[] = $carts;

			}

			if(empty($final)){
				echo json_encode([
					'status' => 0,
					'message' => 'no ids found'
				]);exit;
			}

			echo json_encode([
				'status' => 1,
				'message' => 'details found',
				'subtotal' => $total,
				'details' => $final
			]);exit;


		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}
	}

	public function select_address(){
		if($this->input->post()){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$address = $this->db->get_where('addAddress', ['id' => $this->input->post('addressId'), 'userId' => $user['id']])->row_array();
			if(empty($address)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid addressId'
				]);exit;
			}

			$data['addressId'] = $address['id'];
			$this->db->set($data)->where('id', $user['id'])->update('users');

			echo json_encode([
				'status' => 1,
				'message' => 'address updated'
			]);exit;

		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}
	}

	public function update_product_quantity(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$product = $this->db->get_where('addProducts', ['id' => $this->input->post('productId'), 'userId' => $user['id']])->row_array();
			if(empty($product)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid productId'
				]);exit;
			}

			if($this->input->post('quantity') < '1'){
				echo json_encode([
					'status' => 0,
					'message' => 'quantity can not be less then 1'
				]);exit;
			}

			$data['quantity'] = $product['quantity'] + $this->input->post('quantity');

			$this->db->set($data)->where('id', $product['id'])->update('addProducts');
			echo json_encode([
				'status' => 1,
				'message' => 'quantity updated',
				'details' => $data
			]);exit;

		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}
	}


	public function delete_product(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$product = $this->db->get_where('addProducts', ['id' => $this->input->post('productId'), 'userId' => $user['id']])->row_array();
			if(empty($product)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid productId'
				]);exit;
			}

			$this->db->delete('addProducts', ['id' => $product['id']]);
			echo json_encode([
				'status' => 1,
				'message' => 'product removed'
			]);exit;

		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}
	}

	public function get_cities_by_state(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$state = $this->db->get_where('states', ['id' => $this->input->post('stateId')])->row_array();
			if(empty($state)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid stateId'
				]);exit;
			}

			$cities = $this->db->get_where('cities', ['state_id' => $state['id']])->result_array();
			if(empty($cities)){
				echo json_encode([
					'status' => 0,
					'message' => 'no cities found'
				]);exit;
			}

			echo json_encode([
				'status' => 1,
				'message' => 'list found',
				'details' => $cities
			]);exit;

		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}
	}

	public function my_purchase_history(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$orders = $this->db->get_where('orderDetails', ['userId' => $user['id']])->result_array();
			if(empty($orders)){
				echo json_encode([
					'status' => 0,
					'message' => 'no orders found'
				]);exit;
			}

			$final = [];
			foreach($orders as $order){

				$arr = false;

				if($order['status'] > '1'){

					if($order['direct_buy'] == '1'){

						// if order is direct buy 

						$product = $this->db->get_where('addProducts', ['id' => $order['product_ids']])->row_array() ?? null;
						

					}else{

						// if order is from the cart

						$ids = explode(',', $order['product_ids']);

						$product = [];

						$arr = true;

						foreach($ids as $id){

							$details = $this->db->get_where('addToCartDetails', ['id' => $id])->row_array();

							$products = $this->db->get_where('addProducts', ['id' => $details['productId']])->row_array() ?? null;

							$product[] = $products;

						}

					}

					if($arr == true){

						foreach($product as $p){

							if($p == null){

							}else{

								$final[] = $p;
							}
						}
					}else{

						$final[] = $product;
					}

	
					unset($product);
				}


			}

			if(empty($final)){
				echo json_encode([
					'status' => 0,
					'message' => 'no details found'
				]);exit;
			}

			$last = [];

			foreach($final as $finals){
				$finals['images'] = $this->db->get_where('addProduct_images', ['addProductId' => $finals['id']])->result_array();

				$last[] = $finals;
			}

			echo json_encode([
				'status' => 1,
				'message' => 'details found',
				'details' => $last
			]);exit;

		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}
	}






	// keyid rzp_test_l0iCxNFrSqR4nG
	// key secret SoVLw255d1MwZ2ugsrKEI8F9




}

