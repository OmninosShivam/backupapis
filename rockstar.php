<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Razorpay\Api\Api;
use Aws\S3\S3Client;
use Twilio\Rest\Client;

require APPPATH . '/libraries/razorpayli/autoload.php';

class RockStar extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		error_reporting(0);
		$this->load->model('api/Common_Model');
		$this->load->model('api/User_model');
		date_default_timezone_set('Asia/Kolkata');
		// print_r($_SERVER);exit;
		// if (!!$_SERVER['HTTP_AUTHORIZATION']) {
		// 	if ($_SERVER['HTTP_AUTHORIZATION'] == 'Bearer hdbfgrtsn6df3cCIsImlzcyI6ICJhcHBsaWNhdGlvbi0xQDEyMzQ1Njc4OSIsInNjb3Bl') {
		// 	} else {
		// 		http_response_code(401);
		// 		echo json_encode([
		// 			'status' => "401",
		// 			'message' => 'Unauthorized Access'
		// 		]);
		// 		exit;
		// 	}
		// } else {
		// 	http_response_code(401);
		// 	echo json_encode([
		// 		'status' => "401",
		// 		'message' => 'Unauthorized Access'
		// 	]);
		// 	exit;
		// }
	}

	public function details()
	{

		echo "das";
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



	public function sendLiveGift()
	{
		$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
		if(empty($user)){
			echo json_encode([
				'status' => 0,
				'message' => 'invalid userId'
			]);exit;
		}

		$otherUser = $this->db->get_where('users', ['id' => $this->input->post('giftUserId')])->row_array();
		if(empty($otherUser)){
			echo json_encode([
				'status' => 0,
				'message' => 'invalid giftUserId'
			]);exit;
		}
		$data['userId'] = $this->input->post('userId');
		$data['giftUserId'] = $this->input->post('giftUserId');
		$data['giftId'] = $this->input->post('giftId');
		$data['coin'] = $this->input->post('coin');
		$data['type'] = 1;
		if (!empty($this->input->post('pkHostId'))) {
			$data['pkHostId'] = $this->input->post('pkHostId');
		}
		if (!empty($this->input->post('liveId'))) {
			$data['liveId'] = $this->input->post('liveId');
		}
		$data['created'] = date('Y-m-d H:i:s');
		$insert = $this->db->insert('userGiftHistory', $data);
		if (!empty($insert)) {
			$this->check_gift_to_family($user['id'], $otherUser['id']);
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
			$loginUpdateCoin['purchasedCoin'] = $loginUserDetails['purchasedCoin'] - $this->input->post('coin');
			$calcuLateExpCoin = $this->input->post('coin') * 5;
			$loginUpdateCoin['expCoin'] = $expCoin + $calcuLateExpCoin;
			$allExpCoin = $loginUpdateCoin['expCoin'];
			$levalList  =  $this->db->query("SELECT * FROM leval WHERE expCount BETWEEN 300 AND $allExpCoin order by id desc limit 1")->row_array();
			$loginUpdateCoin['leval'] = $levalList['leval'];
			$this->Common_Model->update('users', $loginUpdateCoin, 'id', $this->input->post('userId'));


			$giftUserDetails = $this->db->get_where('users', array('id' => $this->input->post('giftUserId')))->row_array();
			$expCoin1 = $giftUserDetails['expCoin'];
			$giftUserUpdate['coins'] = $giftUserDetails['coins'] + $this->input->post('coin');
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
			$receivedCointotal = $giftUserUpdate['coins'];
			$this->giftNotification($regId, $mess, 'gift', $this->input->post('userId'), $this->input->post('giftUserId'), $purchasedCoinstotal, $receivedCointotal);

			$notiMess['loginId'] = $this->input->post('userId');
			$notiMess['userId'] = $this->input->post('giftUserId');
			$notiMess['message'] = $mess;
			$notiMess['type'] = 'gift';
			$notiMess['notiDate'] = date('Y-m-d');
			$notiMess['created'] = date('Y-m-d H:i:s');
			$this->db->insert('userNotification', $notiMess);
			$todyDD = date('Y-m-d');
			$checkStarStatus1 = $this->db->get_where('userStar', array('userId' => $this->input->post('userId'), 'created' => $todyDD))->row_array();
			if (!empty($checkStarStatus1)) {
				$starStatus1 = $checkStarStatus1['star'];
				if ($starStatus != 0) {
					$checkBoxCount1 = $this->db->get_where('starList', array('star' => $starStatus1))->row_array();
					$myBox = $checkBoxCount1['box'];
				} else {
					$myBox = 0;
				}
			} else {
				$starStatus1 = '0';
				$myBox = 0;
			}

			$checkStarStatus = $this->db->get_where('userStar', array('userId' => $this->input->post('giftUserId'), 'created' => $todyDD))->row_array();
			if (!empty($checkStarStatus)) {
				$starStatus = $checkStarStatus['star'];
				if ($starStatus != 0) {
					$checkBoxCount = $this->db->get_where('starList', array('star' => $starStatus))->row_array();
					$liveBox = $checkBoxCount['box'];
				} else {
					$liveBox = 0;
				}
			} else {
				$starStatus = '0';
				$liveBox = 0;
			}

			$outMess['myLevel'] =  $loginUpdateCoin['leval'];
			$outMess['liveLevel'] =  $giftUserUpdate['leval'];
			$outMess['myStar'] =  $starStatus1;
			$outMess['myBox'] = (string)$myBox;
			$outMess['liveStar'] =  $starStatus;
			$outMess['liveBox'] =  (string)$liveBox;
			$message['success'] = '1';
			$message['message'] = 'Gift send successfully';
			$message['details'] = $outMess;
		} else {
			$message['success'] = '0';
			$message['message'] = 'Please try after some time';
		}
		echo json_encode($message);
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


	public function getVideo()
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
					$viewVideoInsert['videoId'] = $videoLists['id'];
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
			$data['username'] = '@' . rand(100000000, 999999999);
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


	public function loginPhone()
	{
		$this->db->delete('user_otp', array('phone' => $this->input->post('phone')));
		$data['phone'] = $this->input->post('phone');
		$otp = rand(100000, 999999);
		$data['loginOtp'] = $otp;
		$insert = $this->db->insert('user_otp', $data);
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
		$checkOTP = $this->db->get_where('user_otp', array('phone' => $this->input->post('phone'), 'loginOtp' => $this->input->post('otp')))->row_array();
		if (!empty($checkOTP)) {
			$checkUser = $this->db->get_where('users', array('phone' => $this->input->post('phone')))->row_array();
			if (!empty($checkUser)) {
				$message['success'] = '1';
				$message['message'] = 'User login successully';
				$message['details'] = $checkUser;
			} else {
				$data['deviceId'] = $this->input->post('deviceId') ?? "";
				$data['phone'] = $this->input->post('phone');
				$data['reg_id'] = $this->input->post('reg_id') ?? "";
				$data['expCoin'] = '0';
				$data['leval'] = '0';
				$data['coin'] = '0';
				$data['purchasedCoin'] = '0';
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

	public function agoraToken()
	{
		$checkUser = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
		if (empty($checkUser)) {
			$message['success'] = '0';
			$message['message'] = 'please logout and login again';
		} else {
			//   if($checkUser['liveStatus'] == '1'){
			require APPPATH . '/libraries/agora/RtcTokenBuilder.php';
			require APPPATH . '/libraries/agora/RtmTokenBuilder.php';

			// $appID = "0ebf0179ad5f47ef93f32cf7f6851e1b";
			// $appCertificate = "0405943eabe04260acb48aedb6102605";
			$appID = "501b7a11731a4f8483c87f9779948f14";
			$appCertificate = "7c4fbb74a0904636a128425368909837";
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
				$data['created'] = date('Y-m-d');
				$data['status'] = 'live';
				$data['count'] = $this->input->post('count');
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

					$outPut['name'] = $userDetails['name'];
					$outPut['coin'] = $userDetails['coin'];
					$outPut['userLeval'] = $userDetails['leval'];
					$outPut['starCount'] = $this->input->post('count');
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

	//   public function getLiveUserList(){
	//     if(!empty($this->input->post('latitude'))){
	//       $latitude = $this->input->post('latitude');
	//       $longitude = $this->input->post('longitude');
	//       $loginIdMain = $this->input->post('userId');
	//       $list =  $this->db->query("SELECT users.username,users.coins,users.name,users.leval,users.image,users.followerCount,userLive.* FROM (SELECT *, (((acos(sin(($latitude*pi()/180)) * sin((`latitude`*pi()/180))+cos(($latitude*pi()/180)) * cos((`latitude`*pi()/180)) * cos((($longitude- `longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance FROM `userLive`)userLive left join users on users.id = userLive.userId WHERE userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and userLive.status = 'live' and userLive.userId != $loginIdMain and distance <= 62.1371 ")->result_array();
	//     }
	//     else{
	//       $loginIdMain = $this->input->post('userId');
	//       if($this->input->post('type') == 1 ){
	//          $list =  $this->db->query("select users.username,users.name,users.coins,users.leval,users.image,users.followerCount,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and userLive.status = 'live' and userLive.userId != $loginIdMain ORDER BY userLive.id desc")->result_array();
	//       }
	//       else{
	//         $loginIdMain = $this->input->post('userId');
	//           $follwerList = $this->db->get_where('userFollow',array('userId' => $this->input->post('userId'),'status' => '1'))->result_array();
	//           if(!empty($follwerList)){
	//             foreach($follwerList as $follwerLists){
	//               $idList[] = $follwerLists['followingUserId'];
	//             }
	//             $fIds = implode(',',$idList);
	//           $list =  $this->db->query("select users.username,users.name,users.leval,users.coins,users.image,users.followerCount,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and userLive.userId  IN ($fIds ) and userLive.status = 'live' ORDER BY userLive.id desc")->result_array();
	//          }
	//       }
	//      }
	//      $useriNfo = $this->db->get_where('users',array('id' => $this->input->post('userId')))->row_array();
	//      if(!empty($list)){
	//       $message['success'] = '1';
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
	//          $lists['startCount'] = $starStatus;
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
	//       $message['success'] = '0';
	//       $message['message'] = 'No List found';
	//      }
	//      echo json_encode($message);
	//   }

	public function getLiveUserList()
	{
		if (!empty($this->input->post('latitude'))) {
			$latitude = $this->input->post('latitude');
			$longitude = $this->input->post('longitude');
			$loginIdMain = $this->input->post('userId');
			$list =  $this->db->query("SELECT users.id,users.name,users.coins,users.name,users.leval,users.image,users.followerCount,users.posterImage,userLive.* FROM (SELECT *, (((acos(sin(($latitude*pi()/180)) * sin((`latitude`*pi()/180))+cos(($latitude*pi()/180)) * cos((`latitude`*pi()/180)) * cos((($longitude- `longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance FROM `userLive`)userLive left join users on users.id = userLive.userId WHERE userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and ( userLive.status = 'live' or userLive.status = 'archived' ) and userLive.userId != $loginIdMain and distance <= 62.1371 AND posterImage IS NOT NULL")->result_array();
		} else {
			$loginIdMain = $this->input->post('userId');
			$country = $this->input->post('country');
			if ($this->input->post('type') == 1 && !empty($country)) {
				$list =  $this->db->query("select users.id,users.username,users.name,users.coins,users.leval,users.image,users.followerCount,users.posterImage,users.country,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and users.country = '$country' and ( userLive.status = 'live' or userLive.status = 'archived' ) and userLive.userId != $loginIdMain AND posterImage IS NOT NULL ORDER BY userLive.id desc")->result_array();
			} else if ($this->input->post('type') == 1 && empty($country)) {
				$list =  $this->db->query("select users.id,users.username,users.name,users.coins,users.leval,users.image,users.followerCount,users.posterImage,users.country,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and ( userLive.status = 'live' or userLive.status = 'archived' ) and userLive.userId != $loginIdMain AND posterImage IS NOT NULL ORDER BY userLive.id desc")->result_array();
			} else {
				$loginIdMain = $this->input->post('userId');
				$follwerList = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'status' => '1'))->result_array();
				if (!empty($follwerList)) {
					foreach ($follwerList as $follwerLists) {
						$idList[] = $follwerLists['followingUserId'];
					}
					$fIds = implode(',', $idList);
					$list =  $this->db->query("select users.id,users.username,users.name,users.leval,users.coins,users.image,users.followerCount,users.posterImage,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and userLive.userId  IN ($fIds ) and ( userLive.status = 'live' or userLive.status = 'archived' ) AND posterImage IS NOT NULL ORDER BY userLive.id desc")->result_array();
				}
			}
		}
		$useriNfo = $this->db->get_where('users', array('id' => $this->input->post('userId')))->row_array();
		if (!empty($list)) {
			$message['status'] = '1';
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

				$id = $lists['userId'];
				$liveId = $lists['id'];
				$posterImage = $this->db->select('host_status, profileStatus')->from('users')->where('id', $id)->get()->row_array();
				if ($posterImage) {

					$lists['host_status'] = $posterImage['host_status'];
					$lists['profileStatus'] = $posterImage['profileStatus'];
				} else {
					$lists['host_status'] = $posterImage['host_status'];
					$lists['profileStatus'] = $posterImage['profileStatus'];
				}
				$coinsTotal = $this->db->select_sum('coin')
					->from('userGiftHistory')
					->where('liveId', $liveId)->get()->row_array();
				$lists['liveGiftings'] = $coinsTotal['coin'];

				$countStar = $this->db->select_sum('coin')
					->from('userGiftHistory')
					->where('giftUserId', $id)
					->where('created', date('Y-m-d'))
					->get()->row_array();


				$lists['starCount'] = $countStar['coin'];




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
				//  $lists['startCount'] = $starStatus;
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

				$message['details'][] = $lists;
			}
		} else {
			$message['status'] = '0';
			$message['message'] = 'No List found';
		}
		echo json_encode($message);
	}

	public function getFollowingLiveUser()
	{

		$get = $this->db->get_where("userFollow", ['userId' => $this->input->post("userId")])->result_array();

		if (!!$get) {

			foreach ($get as $gets) {

				$getId = $gets['followingUserId'];

				$list =  $this->db->query("select users.id userssid,users.username,users.name,users.coins,users.leval,users.image,users.followerCount,users.posterImage,users.country,users.host_status,users.profileStatus,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $getId ) and userLive.status = 'live' and userLive.userId = $getId AND posterImage IS NOT NULL ORDER BY userLive.id desc")->result_array();

				foreach ($list as $lists) {

					$userLiveId = $lists['id'];
					$id = $lists['userId'];

					$coinsTotal = $this->db->select_sum('coin')
						->from('userGiftHistory')
						->where('liveId', $userLiveId)->get()->row_array();

					if (!!$coinsTotal['coin']) {

						$lists['liveGiftings'] = $coinsTotal['coin'];
					} else {

						$lists['liveGiftings'] = "";
					}



					$countStar = $this->db->select_sum('coin')
						->from('userGiftHistory')
						->where('giftUserId', $id)
						->where('created', date('Y-m-d'))
						->get()->row_array();

					if (!!$countStar['coin']) {

						$lists['starCount'] = $countStar['coin'];
					} else {

						$lists['starCount'] = "";
					}

					$todyDD = date('Y-m-d');

					$checkStarStatus = $this->db->get_where('userStar', array('userId' => $id, 'created' => $todyDD))->row_array();
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
					//  $mainLiveId = $lists['id'];
					$checkStarStatus12 = $this->db->query("SELECT * FROM `starBoxResult` where userId = $mainUserId and liveId= $userLiveId and box = $starBOX and date(created) = '$todyDD'")->num_rows();
					if (!empty($checkStarStatus12)) {
						$lists['checkBoxStatus'] = '0';
					} else {
						$lists['checkBoxStatus'] = '1';
					}

					$lists['box'] = (string)$starBOX;



					if (!!$lists['coin']) {

						$lists['purchasedCoin'] = $lists['coin'];
					} else {

						$lists['purchasedCoin'] = "";
					}

					if (!!$lists['leval']) {

						$lists['userLeval'] = $lists['leval'];
					} else {

						$lists['userLeval'] = "";
					}


					$checkFollow = $this->db->get_where('userFollow', array('userId' => $this->input->post('userId'), 'followingUserId' => $id))->row_array();
					if (!empty($checkFollow)) {
						$lists['followStatus'] = '1';
					} else {
						$lists['followStatus'] = '0';
					}

					if (!!$lists) {
						$final[] = $lists;
					} else {
					}
				}
			}
			if (empty($final)) {
				echo json_encode([

					"success" => "0",
					"message" => "details not found!"
				]);
				exit;
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
				"message" => "details not found!"
			]);
			exit;
		}
	}







	//   public function archivedLive(){
	//     $data['status'] = 'archived';
	//     $data['archivedDate'] = date('Y-m-d H:i:s');

	//     $insData['userId'] = $this->input->post('id');
	//     $insData['startLimit'] = $data['status'];
	//     $insData['country'] = $data['archivedDate'];
	//     $this->db->insert('testing',$insData);


	//     $this->Common_Model->update('userLive',$data,'id',$this->input->post('id'));
	//     $message['success'] = '1';
	//     $message['message'] = 'Live Streming Archived Successfully';
	//     echo json_encode($message);
	//   }


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
		$list =  $this->db->query("select users.username,users.name,users.coins,users.leval,users.image,users.followerCount,userLive.* from userLive left join users on users.id = userLive.userId where userLive.userId NOT IN (select userIdLive from banLiveUser where userIdViewer = $loginIdMain ) and userLive.status = 'live' and userLive.userId != $loginIdMain ORDER BY userLive.id desc LIMIT $startLimit , 20")->result_array();
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
		$lists =  $this->db->query("select users.username,users.name,users.coins,users.image,users.followerCount,userLive.* from userLive left join users on users.id = userLive.userId where   userLive.id = $id")->row_array();
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
		$lists =  $this->db->query("select users.username,users.name,users.coins,users.leval,users.image,users.followerCount,userLive.* from userLive left join users on users.id = userLive.userId where userLive.id = $liveId ")->row_array();
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


	public function uniqueApi()
	{

		if ($this->input->post("phone") != null) {

			$checkPh = $this->db->get_where("users", ['phone' => $this->input->post("phone")])->row_array();

			if (!!$checkPh) {

				$message['success'] = '0';
				$message['message'] = 'Ph already exist';
			} else {
				$message['success'] = "1";
				$message['message'] = "OTP send successfully";
				$message['otp'] = (string)rand(1000, 9999);
			}
		} else {
			$message['success'] = '0';
			$message['message'] = 'please enter valid param!';
		}
		echo json_encode($message);
	}

	public function loginUser()
	{
		if ($this->input->post()) {
			$emailPhone = $this->input->post('phone');
			$password = md5($this->input->post('password'));
			$checkPhone = $this->db->query("SELECT * FROM users where password = '$password' and phone = '$emailPhone'")->row_array();

			if (!empty($checkPhone)) {
				// $datas = array('reg_id' => $this->input->post('reg_id'), 'device_type' => $this->input->post('device_type'), 'login_type' => $this->input->post('login_type'), 'latitude' => $this->input->post('latitude'), 'longitude' => $this->input->post('longitude'));
				// $update = $this->db->update('users', $datas, array('id' => $checkPhone['id']));
				$datas1 = $this->db->get_where('users', array('id' => $checkPhone['id']))->row_array();
				if ($datas1['image']) {
					$datas1['image'] = base_url() . $datas1['image'];
				}

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

	public function userRegisterProfile()
	{

		if ($this->input->post('username') != null && $this->input->post('phone') != null && $this->input->post('gender') != null && $this->input->post('dob') != null && $this->input->post('password') != null) {
			$checkUsername = $this->db->get_where('users', ['username' => $this->input->post('username')])->row_array();
			if (!!$checkUsername) {
				echo json_encode([
					'success' => '0',
					'message' => 'username already taken',
				]);
				exit;
			}
			$data['username'] = $this->input->post('username');
			$data['phone'] = $this->input->post('phone');
			$data['gender'] = $this->input->post('gender');
			$data['dob'] = $this->input->post('dob');
			$data['password'] = md5($this->input->post('password'));
			$data['created_at'] = date('Y-m-d H:i:s');
			$insert = $this->db->insert('users', $data);
			$lastId = $this->db->insert_id();
			if ($insert) {
				$details = $this->db->get_where('users', ['id' => $lastId])->row_array();
				$message = [
					'success' => '1',
					'message' => 'Registration Done',
					'details' => $details
				];
			} else {
				$message = [
					'success' => '0',
					'message' => 'Registration Not Done'
				];
			}
		} else {
			$message = array(
				'success' => '0',
				'message' => 'please enter parameters'
			);
		}

		echo json_encode($message);
	}

	public function updateUserProfile()
	{

		if ($this->input->post()) {
			$userId = $this->input->post('userId');

			$checkId = $this->db->get_where("users", ['id' => $userId])->row_array();
			if (!!$checkId) {
				if (!empty($this->input->post('dob'))) {
					$data['dob'] = $this->input->post('dob');
				}
				if (!empty($this->input->post('gender'))) {
					$data['gender'] = $this->input->post('gender');
				}
				if (!empty($this->input->post('address'))) {
					$data['address'] = $this->input->post('address');
				}
				if (!empty($this->input->post('about'))) {
					$data['about'] = $this->input->post('about');
				}
				if (!empty($this->input->post('name'))) {
					$data['name'] = $this->input->post('name');
				}
				if (!empty($this->input->post('education'))) {
					$data['education'] = $this->input->post('education');
				}
				if (!empty($this->input->post('work'))) {
					$data['work'] = $this->input->post('work');
				}
				if (!empty($this->input->post('relationship_status'))) {
					$data['relationship_status'] = $this->input->post('relationship_status');
				}
				if (!empty($this->input->post('tag'))) {
					$data['tag'] = $this->input->post('tag');
				}
				if (!empty($this->input->post('latitude'))) {
					$data['latitude'] = $this->input->post('latitude');
				}
				if (!empty($this->input->post('longitude'))) {
					$data['longitude'] = $this->input->post('longitude');
				}
				$data['updated'] = date("Y-m-d H:i:s");

				if (!empty($_FILES["image"]["name"])) {
					$name1 = time() . '_' . $_FILES["image"]["name"];
					$name = str_replace(' ', '_', $name1);
					$liciense_tmp_name = $_FILES["image"]["tmp_name"];
					$error = $_FILES["image"]["error"];
					$liciense_path = 'uploads/users/' . $name;
					move_uploaded_file($liciense_tmp_name, $liciense_path);
					$data['image'] = $liciense_path;
				}

				$update = $this->db->update('users', $data, array('id' => $userId));

				if (!empty($update)) {

					$details = $this->db->get_where('users', array('id' => $userId))->row_array();
					if (!empty($details['image'])) {
						$details['image'] = base_url() . $details['image'];
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


	public function liveMultiLiveToken()
	{
		require APPPATH . '/libraries/agora/RtcTokenBuilder.php';
		if ($this->input->post()) {
			$getData = $this->db->from('h_liveMultiLiveToken')
				->where('userId', $this->input->post('userId'))
				->get()->row_array();
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

				$data = [
					'userId' => $this->input->post('userId'),
					'liveType' => $this->input->post('liveType'),
					'liveStatus' => $this->input->post('liveStatus'),
					'channelName' => $this->input->post('channelName'),
					'token' => $token,
					'created' => date('Y-m-d H:i:s'),
				];
				$datas = [
					'userId' => $this->input->post('userId'),
					'liveType' => $this->input->post('liveType'),
					'liveStatus' => $this->input->post('liveStatus'),
					'channelName' => $this->input->post('channelName'),
					'token' => $token,
					'created' => date('Y-m-d H:i:s'),
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
				if (!empty($userDetails['name'])) {
					$data['name'] = $userDetails['name'];
				} else {
					$data['name'] = '';
				}

				// if (empty($followersCount['count'])) {
				//   $data['count'] = '0';
				// } else {
				//   $data['count'] = $followersCount['count'];
				// }
				$insert = $this->db->insert('h_liveMultiLiveToken', $datas);
				$id = $this->db->insert_id();
				if ($insert) {
					$getData = $this->db->get_where("h_liveMultiLiveToken", array('id' => $id))->row_array();
					$getData['name'] = $data['name'];
					$getData['image'] = $data['image'];
					echo json_encode([
						'success' => '1',
						'message' => 'Token Generated',
						'token' => $getData
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
					'liveType' => $this->input->post('liveType'),
					'liveStatus' => $this->input->post('liveStatus'),
					'channelName' => $this->input->post('channelName'),
					'updated' => date('Y-m-d H:i:s')
				];
				$datas = [
					'token' => $token,
					'liveType' => $this->input->post('liveType'),
					'liveStatus' => $this->input->post('liveStatus'),
					'channelName' => $this->input->post('channelName'),
					'updated' => date('Y-m-d H:i:s')
				];
				$userDetails = $this->db->select('image , name')
					->from('users')
					->where('id', $this->input->post('userId'))
					->get()->row_array();
				// $userId = $this->input->post('userId');
				// $followersCount = $this->db->query("SELECT COUNT(followedTo) AS COUNT FROM h_followers WHERE followedTo = $userId")->row_array();
				if (!empty($userDetails['image'])) {
					$data['image'] = base_url() . $userDetails['image'];
				} else {
					$data['image'] = '';
				}
				if (!empty($userDetails['name'])) {
					$data['name'] = $userDetails['name'];
				} else {
					$data['name'] = '';
				}

				// if (empty($followersCount['count'])) {
				//   $data['count'] = '0';
				// } else {
				//   $data['count'] = $followersCount['count'];
				// }
				$update = $this->db->where('userId', $this->input->post('userId'))
					->update('h_liveMultiLiveToken', $datas);
				if ($update) {
					$id = $this->input->post('userId');
					$getData = $this->db->get_where("h_liveMultiLiveToken", array('userId' => $id))->row_array();
					$getData['name'] = $data['name'];
					$getData['image'] = $data['image'];
					echo json_encode([
						'success' => '1',
						'message' => 'Token Generated',
						'token' => $getData
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

	public function getLiveMultiLive()
	{
		$get = $this->db->get("h_liveMultiLiveToken")->result_array();
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

	public function forgetPassword()
	{
		$number = $this->input->post('phone');
		$check = $this->db->get_where('users', array('phone' => $number))->row_array();
		if (!empty($check)) {
			$message['success'] = '1';
			$message['message'] = 'otp  sent successfully';
			$message['otp'] = (string)rand(1000, 9999);
		} else {
			$message['success'] = '0';
			$message['message'] = 'Invalid phone number!';
		}

		echo json_encode($message);
	}


	public function changeUserPassword()
	{
		$phone = $this->input->post('phone');
		$data['password'] = md5($this->input->post('password'));
		$update = $this->db->update('users', $data, array('phone' => $phone));
		if (!empty($update)) {
			$message['success'] = '1';
			$message['message'] = 'Password changed successfully';
		} else {
			$message['success'] = '0';
			$message['message'] = 'error occured';
		}
		echo json_encode($message);
	}

	public function multiImage()
	{

		if ($this->input->post()) {
			$datas['userId'] = $this->input->post('userId');
			$datas['created'] = date("Y-m-d H:i:s");
			if (!empty($_FILES['image']['name'])) {
				$total = count($_FILES['image']['name']);
				for ($i = 0; $i < $total; $i++) {
					$tmpFilePath = $_FILES['image']['tmp_name'][$i];
					if ($tmpFilePath != "") {

						$img_name = $_FILES['image']['name'][$i];
						$tmpFilePath = $_FILES['image']['tmp_name'][$i];
						$liciense_path = 'uploads/users/' . $img_name;
						move_uploaded_file($tmpFilePath, $liciense_path);
						$datas['image'] = $liciense_path;


						$input = $this->db->insert("multi_img", $datas);
					}
				}
			}
			if ($input) {
				$this->fortune_of_wheel_user_cover_check($datas['userId']);
				$message = [
					'success' => '1',
					'message' => 'Image added Successfully'
				];
			} else {
				$message = [
					'success' => '0',
					'message' => 'try again!'
				];
			}
		} else {
			$message = [
				'success' => '0',
				'message' => 'somthing went wrong!'
			];
		}

		echo json_encode($message);
	}

	public function getMultiImage()
	{

		$get = $this->db->get_where("multi_img", ['userId' => $this->input->post("userId")])->result_array();

		if (!!$get) {

			foreach ($get as $gets) {

				$gets['image'] = base_url() . $gets['image'];

				$final[] = $gets;
			}

			echo json_encode([

				"message" => 'details found',
				"success" => '1',
				"details" => $final,
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

	public function getHastags()
	{

		$get = $this->db->select("hashtag.id,hashtag.hashtag")->from("hashtag")->get()->result_array();

		if (!!$get) {

			echo json_encode([

				"message" => "hastags found",
				"success" => "1",
				"details" => $get,
			]);
			exit;
		} else {

			echo json_encode([

				"message" => "hastag not found",
				"success" => "0",
			]);
			exit;
		}
	}

	protected function fortune_of_wheel_user_cover_check($userId){

		$date = date('Y-m-d');
	
		$get = $this->db->get_where('wheel_of_fortune', ['date' => $date, 'userId' => $userId])->row_array();
	
		$data['userId'] = $userId;
		$data['date'] = date('Y-m-d');
		$data['cover_image'] = 1;
	
		if(empty($get)){
	
			$this->db->insert('wheel_of_fortune', $data);
			return true;
	
		}else{
	
			if($get['cover_image'] == '1'){
	
				return true;
	
			}else{
	
				$this->db->set(['cover_image' => '1'])->where('id', $get['id'])->update('wheel_of_fortune');
				return true;
	
			}
	
		}
	
	}

	public function userUploadPost()
	{

		if ($this->input->post()) {

			$data['userId'] = $this->input->post("userId");
			$data['description'] = $this->input->post("description");
			$data['hashtagId'] = $this->input->post("hashtagId");
			$data['latitude'] = $this->input->post("latitude");
			$data['longitude'] = $this->input->post("longitude");
			$data['restrictions'] = $this->input->post("restrictions");
			$data['created'] = date("Y-m-d H:i:s");
			if (!empty($_FILES["postimage"]["name"])) {
				$name1 = time() . '_' . $_FILES["postimage"]["name"];
				$name = str_replace(' ', '_', $name1);
				$liciense_tmp_name = $_FILES["postimage"]["tmp_name"];
				$error = $_FILES["postimage"]["error"];
				$liciense_path = 'uploads/users/' . $name;
				move_uploaded_file($liciense_tmp_name, $liciense_path);
				$data['postimage'] = $liciense_path;
			}

			$upload = $this->db->insert("user_UploadPost", $data);

			$getId = $this->db->insert_id();

			if ($upload == true) {

				$here = $this->fortune_of_wheel_user_cover_check($data['userId']);
				// print_r($here);

				$getDetails = $this->db->get_where("user_UploadPost", ['id' => $getId])->row_array();

				if (!!$getDetails) {

					$getDetails['postimage'] = base_url() . $getDetails['postimage'];
				}

				$addUserId = explode(',', $this->input->post("otheruserId"));

				foreach ($addUserId as $key => $val) {
					$insert_data = [];
					$insert_data[] = [
						"userUploadPostId"	=>	$getId,
						"otheruserId"	=>	$val,
					];

					$ins = $this->db->insert_Batch("uploadPostMultiple_users", $insert_data);
				}

				echo json_encode([

					"message" => "post upload successully",
					"success" => "1",
					"details" => $getDetails,
				]);
				exit;
			} else {
				echo json_encode([

					"message" => "Please try after sometimes",
					"success" => "0",
				]);
				exit;
			}
		} else {

			echo json_encode([

				"message" => "Please enter valid params!",
				"success" => "0",
			]);
			exit;
		}
	}

	public function userUploadVideo()
	{

		if ($this->input->post()) {

			$data['userId'] = $this->input->post("userId");
			$data['description'] = $this->input->post("description");
			$data['hashtagId'] = $this->input->post("hashtagId");
			$data['latitude'] = $this->input->post("latitude");
			$data['longitude'] = $this->input->post("longitude");
			$data['restrictions'] = $this->input->post("restrictions");
			$data['created'] = date("Y-m-d H:i:s");
			$data['type'] = "video";
			if (!empty($_FILES["thumbnail"]["name"])) {
				$name1 = time() . '_' . $_FILES["thumbnail"]["name"];
				$name = str_replace(' ', '_', $name1);
				$liciense_tmp_name = $_FILES["thumbnail"]["tmp_name"];
				$error = $_FILES["thumbnail"]["error"];
				$liciense_path = 'uploads/users/' . $name;
				move_uploaded_file($liciense_tmp_name, $liciense_path);
				$data['thumbnail'] = $liciense_path;
			}
			$name1 = time() . '_' . $this->input->post('videopath');
			$name = str_replace(' ', '_', $name1);
			$liciense_tmp_name = $_FILES["videopath"]["tmp_name"];
			$error = $_FILES["videopath"]["error"];
			$liciense_path = 'uploads/users/' . $name;
			move_uploaded_file($liciense_tmp_name, $liciense_path);
			$data['videopath'] = $liciense_path;


			$upload = $this->db->insert("user_UploadPost", $data);

			$getId = $this->db->insert_id();

			if ($upload == true) {

				$this->fortune_of_wheel_user_cover_check($data['userId']);

				$getDetails = $this->db->get_where("user_UploadPost", ['id' => $getId])->row_array();

				if (!!$getDetails) {

					$getDetails['videopath'] = base_url() . $getDetails['videopath'];
					$getDetails['thumbnail'] = base_url() . $getDetails['thumbnail'];
				} else {

					$getDetails['videopath'] = "";
					$getDetails['thumbnail'] = "";
				}

				$addUserId = explode(',', $this->input->post("otheruserId"));

				foreach ($addUserId as $key => $val) {
					$insert_data = [];
					$insert_data[] = [
						"userUploadPostId"	=>	$getId,
						"otheruserId"	=>	$val,
					];

					$ins = $this->db->insert_Batch("uploadPostMultiple_users", $insert_data);
				}

				echo json_encode([

					"message" => "video upload successully",
					"success" => "1",
					"details" => $getDetails,
				]);
				exit;
			} else {
				echo json_encode([

					"message" => "Please try after sometimes",
					"success" => "0",
				]);
				exit;
			}
		} else {

			echo json_encode([

				"message" => "Please enter valid params!",
				"success" => "0",
			]);
			exit;
		}
	}

	public function getUserPostVideos()
	{

		$get = $this->db->select("user_UploadPost.*,hashtag.hashtag,users.image")
			->from("user_UploadPost")
			->join("hashtag", "hashtag.id = user_UploadPost.hashtagId", "left")
			->join("users", "users.id = user_UploadPost.userId", "left")
			->where("user_UploadPost.type", "video")
			->get()
			->result_array();

		if (!!$get) {

			foreach ($get as $gets) {
				$id = $gets['id'];

				$getss = $this->db->select("uploadPostMultiple_users.*")
					->from("uploadPostMultiple_users")
					->where("uploadPostMultiple_users.userUploadPostId", $id)
					->get()
					->result_array();

				if (!empty($gets)) {
					$gets['others'] = $getss;
				} else {

					$gets['others'] = [];
				}

				$gets['postimage'] = base_url() . $gets['postimage'];
				$gets['thumbnail'] = base_url() . $gets['thumbnail'];
				$gets['videopath'] = base_url() . $gets['videopath'];
				$gets['image'] = base_url() . $gets['image'];



				$final[] = $gets;
			}

			echo json_encode([

				"message" => "details found successully",
				"success" => "1",
				"details" => $final,
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

	public function getUserPost()
	{

		$get = $this->db->select("user_UploadPost.*,hashtag.hashtag,users.image,users.name")
			->from("user_UploadPost")
			->join("hashtag", "hashtag.id = user_UploadPost.hashtagId", "left")
			->join("users", "users.id = user_UploadPost.userId", "left")
			->where("user_UploadPost.type !=", "video")
			->get()
			->result_array();

		if (!!$get) {

			foreach ($get as $gets) {
				$id = $gets['id'];

				$getss = $this->db->select("uploadPostMultiple_users.*")
					->from("uploadPostMultiple_users")
					->where("uploadPostMultiple_users.userUploadPostId", $id)
					->get()
					->result_array();

				if (!empty($gets)) {
					$gets['others'] = $getss;
				} else {

					$gets['others'] = [];
				}

				$gets['postimage'] = base_url() . $gets['postimage'];
				$gets['thumbnail'] = base_url() . $gets['thumbnail'];
				$gets['videopath'] = base_url() . $gets['videopath'];
				$gets['image'] = base_url() . $gets['image'];

				$getUserLikePost = $this->db->select('likeUnlikeUserPost.userId')
					->from('likeUnlikeUserPost')
					->where('postId', $id)
					->get()->result_array();
				if (!!$getUserLikePost) {
					$gets['usersLikePost'] = $getUserLikePost;
				} else {
					$gets['usersLikePost'] = "0";
				}

				$final[] = $gets;
			}

			echo json_encode([

				"message" => "details found successully",
				"success" => "1",
				"details" => $final,
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

	public function likeUnlikeUserPost()
	{
		$data['userId'] = $this->input->post('otherUserId');
		$data['postId'] = $this->input->post('postId');
		$get = $this->db->get_where('likeUnlikeUserPost', ['userId' => $this->input->post('otherUserId'), 'postId' => $this->input->post('postId')])->row_array();
		if (!empty($get)) {
			$delete = $this->db->delete('likeUnlikeUserPost', ['userId' => $this->input->post('otherUserId'), 'postId' => $this->input->post('postId')]);
			if ($delete) {

				$this->db->set('postlikeCount', 'postlikeCount -1', false)->where('id', $this->input->post('postId'))->update("user_UploadPost");

				$getDetails = $this->db->select("user_UploadPost.*")
					->from("user_UploadPost")
					->where("user_UploadPost.id", $this->input->post('postId'))
					->get()
					->row_array();

				$getUnlikeCount = $getDetails['postlikeCount'];
				$message['success'] = '2';
				$message['message'] = 'Post Unliked';
				$message['PostlikeCount'] = $getUnlikeCount;
				$message['PostId'] = $getDetails['id'];
				$message['userId'] = $this->input->post('otherUserId');
			}
		} else {
			$insert = $this->db->insert('likeUnlikeUserPost', $data);

			$getId = $this->db->insert_id();

			if ($insert) {


				$this->db->set('postlikeCount', 'postlikeCount +1', false)->where('id', $this->input->post('postId'))->update("user_UploadPost");

				// $getDetails = $this->db->select("likeUnlikeUserPost.*,user_UploadPost.postlikeCount")
				//         			    ->from("likeUnlikeUserPost")
				//         			    ->join("user_UploadPost","user_UploadPost.id = likeUnlikeUserPost.PostId","left")
				//         			    ->get()
				//         			    ->row_array();

				$getDetailss = $this->db->select("user_UploadPost.*")
					->from("user_UploadPost")
					->where("user_UploadPost.id", $this->input->post('postId'))
					->get()
					->row_array();
				//      print_r($getDetails);
				//   die;
				$message['success'] = '1';
				$message['message'] = 'Post Liked';
				$message['PostlikeCount'] = $getDetailss['postlikeCount'];
				$message['PostId'] = $getDetailss['id'];
				$message['userId'] = $this->input->post('otherUserId');
			}
		}
		echo json_encode($message);
	}

	public function likeUnlikeUserVideo()
	{
		$data['userId'] = $this->input->post('otherUserId');
		$data['videoId'] = $this->input->post('videoId');
		$get = $this->db->get_where('likeUnlikeUserPost', ['userId' => $this->input->post('otherUserId'), 'videoId' => $this->input->post('videoId')])->row_array();
		if (!empty($get)) {
			$delete = $this->db->delete('likeUnlikeUserPost', ['userId' => $this->input->post('otherUserId'), 'videoId' => $this->input->post('videoId')]);
			if ($delete) {

				$this->db->set('videoLikeCounts', 'videoLikeCounts -1', false)->where('id', $this->input->post('videoId'))->update("user_UploadPost");

				$getDetails = $this->db->select("user_UploadPost.*")
					->from("user_UploadPost")
					->where("user_UploadPost.id", $this->input->post('videoId'))
					->get()
					->row_array();

				$getUnlikeCount = $getDetails['videoLikeCounts'];
				$message['success'] = '2';
				$message['message'] = 'video Unliked';
				$message['videoLikeCounts'] = $getUnlikeCount;
				$message['videoId'] = $getDetails['id'];
				$message['userId'] = $this->input->post('otherUserId');
			}
		} else {
			$insert = $this->db->insert('likeUnlikeUserPost', $data);

			$getId = $this->db->insert_id();

			if ($insert) {


				$this->db->set('videoLikeCounts', 'videoLikeCounts +1', false)->where('id', $this->input->post('videoId'))->update("user_UploadPost");

				// $getDetails = $this->db->select("likeUnlikeUserPost.*,user_UploadPost.postlikeCount")
				//         			    ->from("likeUnlikeUserPost")
				//         			    ->join("user_UploadPost","user_UploadPost.id = likeUnlikeUserPost.PostId","left")
				//         			    ->get()
				//         			    ->row_array();

				$getDetailss = $this->db->select("user_UploadPost.*")
					->from("user_UploadPost")
					->where("user_UploadPost.id", $this->input->post('videoId'))
					->get()
					->row_array();
				//      print_r($getDetails);
				//   die;
				$message['success'] = '1';
				$message['message'] = 'video Liked';
				$message['videoLikeCounts'] = $getDetailss['videoLikeCounts'];
				$message['videoId'] = $getDetailss['id'];
				$message['userId'] = $this->input->post('otherUserId');
			}
		}
		echo json_encode($message);
	}

	public function getLikeUnlikePostDetails()
	{

		$get = $this->db->get_where("likeUnlikeUserPost", ['PostId' => $this->input->post('PostId')])->result_array();

		if (!!$get) {

			echo json_encode([

				"message" => "details found",
				"success" => "1",
				"details" => $get,
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


	public function getAllUserDetails()
	{

		$get = $this->db->get("users")->result_array();

		if (!!$get) {

			foreach ($get as $gets) {

				$gets['image'] = base_url() . $gets['image'];

				$final[] = $gets;
			}

			echo json_encode([

				"message" => "details found",
				"success" => "1",
				"details" => $final,
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


	public function getPostImage()
	{
		if ($this->input->post()) {


			$data = $this->db->query("SELECT users.id userId FROM users WHERE id = " . $this->input->post('userId'))->result_array();

			// 			print_r($data);
			// 			die;
			if (!!$data) {
				foreach ($data as $key => $value) {
					// 	$where = "userId = ".$this->input->post('userId')." AND postimage <> '' ";
					// 	$get = $this->db->get_where('user_UploadPost', $where)->result_array();

					$get = $this->db->select("user_UploadPost.*,commentsOnUserUploadPost.subComment_count")
						->from("user_UploadPost")
						->join("commentsOnUserUploadPost", "commentsOnUserUploadPost.postId = user_UploadPost.id", "left")
						->where("user_UploadPost.userId", $this->input->post('userId'))
						->get()
						->result_array();
					$userInfo = $this->db->query("SELECT users.id, users.username , concat('" . base_url() . "', image) as userImage FROM users WHERE id = " . $this->input->post('userId'))->result_array();

					$data['postImage'] = $get;

					foreach ($get as $key1 => $value1) {
						$data['postImage'][$key1]['postimage'] = base_url() . $data['postImage'][$key1]['postimage'];

						$getLike = $this->db->select('userId')
							->from('likeUnlikeUserPost')
							->where(['PostId' => $get[$key1]['id']])
							->get()->result_array();
						$data['postImage'][$key1]['postLikeDetails'] = $getLike;
						$data['postImage'][$key1]['userInfo'] = $userInfo;
					}
				}

				if (empty($data['postimage'])) {
					echo json_encode([
						'status' => '0',
						'message' => 'empty image'
					]);
					exit;
				}

				echo json_encode([
					'status' => 'success',
					'message' => 'List Found',
					'details' => $data['postImage']
				]);
				exit;
			}
		} else {

			echo json_encode([
				'status' => '0',
				'message' => 'please enter valid data'
			]);
		}
	}

	public function getPostVideos()
	{
		if ($this->input->post()) {


			$data = $this->db->query("SELECT users.id, users.username , concat('" . base_url() . "', image) as userImage FROM users WHERE id = " . $this->input->post('userId'))->result_array();
			if (!!$data) {
				foreach ($data as $key => $value) {
					$where = "userId = " . $this->input->post('userId') . " AND videopath <> '' ";
					$get = $this->db->get_where('user_UploadPost', $where)->result_array();
					$userInfo = $this->db->query("SELECT users.id, users.username , concat('" . base_url() . "', image) as userImage FROM users WHERE id = " . $this->input->post('userId'))->result_array();

					$data['videopath'] = $get;

					foreach ($get as $key1 => $value1) {
						$data['videopath'][$key1]['videopath'] = base_url() . $data['videopath'][$key1]['videopath'];

						$getLike = $this->db->select('userId')
							->from('likeUnlikeUserPost')
							->where(['videoId' => $get[$key1]['id']])
							->get()->result_array();
						$data['videopath'][$key1]['postLikeDetails'] = $getLike;
						$data['videopath'][$key1]['userInfo'] = $userInfo;
					}
				}

				echo json_encode([
					'status' => 'success',
					'message' => 'List Found',
					'details' => $data['videopath']
				]);
				exit;
			}
		} else {

			echo json_encode([
				'status' => '0',
				'message' => 'please enter valid data'
			]);
		}
	}

	public function testinggg()
	{
		$userId = $this->input->post('userId');
		// 		$data = $this->db->query("SELECT * FROM Packagetest_orders Where userId = '$userId' AND status = '0'")->result_array();
		$data = $this->db->query("SELECT users.id,users.username,concat('" . base_url() . "', image) as userImage FROM users Where id = '$userId'")->result_array();


		if (!empty($data)) {
			foreach ($data as $key => $value) {
				$id = $data[$key]['id'];
				$get = $this->db->query("SELECT user_UploadPost.id  user_UploadPostId,user_UploadPost.userId,user_UploadPost.hashtagId,user_UploadPost.description,concat('" . base_url() . "', postimage) as postimage FROM user_UploadPost WHERE user_UploadPost.userId = '$id' AND user_UploadPost.type != 'video'")->result_array();

				// print_r($get);
				// die;

				$data[$key]['PostImage'] = $get;
				foreach ($get as $key1 => $value1) {
					$gets = $this->db->get_where('postLike', ['postId' => $get[$key1]['user_UploadPostId'], 'status' => '1'])->result_array();
					$data[$key]['PostImage'][$key1]['postLikeDetails'] = $gets;
				}
			}
			$message['success'] = '1';
			$message['message'] = 'details found successfuly';
			$message['details'] = $data;
		} else {
			$message['success'] = '0';
			$message['message'] = 'details not found';
		}
		echo json_encode($message);
	}

	public function commentsOnUserUploadPost()
	{

		if ($this->input->post()) {

			$data['userId'] = $this->input->post("userId");
			$data['postId'] = $this->input->post("postId");
			$data['comment'] = $this->input->post("comment");

			$upload = $this->db->insert("commentsOnUserUploadPost", $data);

			if ($upload == true) {

				$this->db->set('post_comment_counts', 'post_comment_counts +1', false)->where('id', $this->input->post('postId'))->update("user_UploadPost");

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

	public function getUserUploadPostComments()
	{

		$get = $this->db->select("commentsOnUserUploadPost.id commentsOnUserUploadPostId,commentsOnUserUploadPost.userId,commentsOnUserUploadPost.postId,commentsOnUserUploadPost.comment,commentsOnUserUploadPost.subComment_count,user_UploadPost.hashtagId,user_UploadPost.description post_description,user_UploadPost.postimage,user_UploadPost.latitude,user_UploadPost.longitude,user_UploadPost.restrictions,user_UploadPost.thumbnail,user_UploadPost.videopath,user_UploadPost.type,user_UploadPost.postlikeCount,user_UploadPost.post_comment_counts,users.username,users.image")
			->from("commentsOnUserUploadPost")
			->join("user_UploadPost", "user_UploadPost.id = commentsOnUserUploadPost.postId", "left")
			->join("users", "users.id = commentsOnUserUploadPost.userId", "left")
			->where("commentsOnUserUploadPost.postId", $this->input->post("postId"))
			->get()
			->result_array();

		if (!!$get) {

			foreach ($get as $key => $gets) {

				$getId = $get[$key]['commentsOnUserUploadPostId'];
				$get[$key]['postimage'] = base_url() . $gets['postimage'];
				$get[$key]['image'] = base_url() . $gets['image'];

				$getDetails = $this->db->get_where("subCommentsOnUserUploadPost", ['CommentId' => $getId])->result_array();

				$get[$key]['subcomments'] = $getDetails;
				// $final[] = $getDetails;
			}

			echo json_encode([
				"success" => "1",
				"message" => "comments details found",
				"details" => $get,
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

	public function subCommentsOnUserUploadPost()
	{

		if ($this->input->post()) {

			$data['userId'] = $this->input->post("userId");
			$data['CommentId'] = $this->input->post("CommentId");
			$data['comment'] = $this->input->post("comment");
			$data['created'] = date("Y-m-d H:i:s");
			$upload = $this->db->insert("subCommentsOnUserUploadPost", $data);

			if ($upload == true) {

				$this->db->set('subComment_count', 'subComment_count +1', false)->where('id', $this->input->post('CommentId'))->update("commentsOnUserUploadPost");

				echo json_encode([

					"success" => "1",
					"message" => "Subcomment added successfully",
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

	public function commentsOnUserUploadVideo()
	{

		if ($this->input->post()) {

			$data['userId'] = $this->input->post("userId");
			$data['videoId'] = $this->input->post("videoId");
			$data['comment'] = $this->input->post("comment");

			$upload = $this->db->insert("videoComments", $data);

			if ($upload == true) {

				$this->db->set('video_comment_counts', 'video_comment_counts +1', false)->where('id', $this->input->post('videoId'))->update("user_UploadPost");

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

	public function GiftingOnVideos()
	{

		if ($this->input->post()) {

			$checkPurchasedCoin = $this->db->get_where("users", ['id' => $this->input->post("userId")])->row_array();

			$getAmount = $checkPurchasedCoin['purchasedCoin'];

			if ($getAmount >= $this->input->post("price")) {

				$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
				if(empty($user)){
					echo json_encode([
						'status' => 0,
						'message' => 'invalid userId'
					]);exit;
				}

				$otherUser = $this->db->get_where('users', ['id' => $this->input->post('giftUserId')])->row_array();
				if(empty($otherUser)){
					echo json_encode([
						'status' => 0,
						'message' => 'invalid giftUserId'
					]);exit;
				}

				$data['videoId'] = $this->input->post("videoId");
				$data['userId'] = $this->input->post("userId");
				$data['giftUserId'] = $this->input->post("giftUserId");
				$data['giftId'] = $this->input->post("giftId");
				$data['coin'] = $this->input->post("price");

				$upload = $this->db->insert("userGiftHistory", $data);

				if ($upload == true) {

					$this->check_gift_to_family($user['id'], $otherUser['id']);

					$deduct['purchasedCoin'] = $getAmount - $data['coin'];

					$this->db->update("users", $deduct, ['id' => $this->input->post("userId")]);

					$getpRICE = $this->db->get_where("user_UploadPost", ['id' => $this->input->post("videoId"), 'userId' => $this->input->post("giftUserId")])->row_array();


					$getVideoPrice = $getpRICE['total_price_post'];

					$add['total_price_post'] = $data['coin'] + $getVideoPrice;

					$this->db->update("user_UploadPost", $add, ['id' => $this->input->post("videoId"), 'userId' => $this->input->post("giftUserId")]);


					echo json_encode([

						"success" => "1",
						"message" => "details added succssfully"
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
					"message" => "Invalid price!"
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

	public function GiftingOnPost()
	{

		if ($this->input->post()) {

			$checkPurchasedCoin = $this->db->get_where("users", ['id' => $this->input->post("userId")])->row_array();

			$getAmount = $checkPurchasedCoin['purchasedCoin'];

			if ($getAmount >= $this->input->post("price")) {

				$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
				if(empty($user)){
					echo json_encode([
						'status' => 0,
						'message' => 'invalid userId'
					]);exit;
				}

				$otherUser = $this->db->get_where('users', ['id' => $this->input->post('giftUserId')])->row_array();
				if(empty($otherUser)){
					echo json_encode([
						'status' => 0,
						'message' => 'invalid giftUserId'
					]);exit;
				}

				$data['postId'] = $this->input->post("postId");
				$data['userId'] = $this->input->post("userId");
				$data['giftUserId'] = $this->input->post("giftUserId");
				$data['giftId'] = $this->input->post("giftId");
				$data['coin'] = $this->input->post("price");

				$upload = $this->db->insert("userGiftHistory", $data);

				if ($upload == true) {

					$this->check_gift_to_family($user['id'], $otherUser['id']);

					$deduct['purchasedCoin'] = $getAmount - $data['coin'];

					$this->db->update("users", $deduct, ['id' => $this->input->post("userId")]);

					$getpRICE = $this->db->get_where("user_UploadPost", ['id' => $this->input->post("postId"), 'userId' => $this->input->post("giftUserId")])->row_array();


					$getVideoPrice = $getpRICE['total_price_post'];

					$add['total_price_post'] = $data['coin'] + $getVideoPrice;

					$this->db->update("user_UploadPost", $add, ['id' => $this->input->post("postId"), 'userId' => $this->input->post("giftUserId")]);


					echo json_encode([

						"success" => "1",
						"message" => "details added succssfully"
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
					"message" => "Invalid price!"
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

	public function userFollow()
	{
		$data['userId'] = $this->input->post('userId');
		$data['followingUserId'] = $this->input->post('followingUserId');
		$get = $this->db->get_where('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' => $this->input->post('followingUserId')])->row_array();
		if (!empty($get)) {
			$delete = $this->db->delete('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' => $this->input->post('followingUserId')]);
			if ($delete) {
				$this->db->set('followerCount', 'followerCount -1', false)->where('id', $this->input->post('userId'))->update("users");
				$this->db->set('followingUser', 'followingUser -1', false)->where('id', $this->input->post('followingUserId'))->update("users");

				$message['success'] = '2';
				$message['message'] = 'User Un_follow successfully';
			}
		} else {
			$insert = $this->db->insert('userFollow', $data);
			if ($insert) {

				$this->db->set('followerCount', 'followerCount +1', false)->where('id', $this->input->post('userId'))->update("users");
				$this->db->set('followingUser', 'followingUser +1', false)->where('id', $this->input->post('followingUserId'))->update("users");

				$message['success'] = '1';
				$message['message'] = 'User follow succesfully';
			}
		}
		echo json_encode($message);
	}

	public function getFollowUsers()
	{

		$getDetails = $this->db->select("userFollow.id userFollowId,userFollow.userId,userFollow.followingUserId,users.*")
			->from("userFollow")
			->join("users", "userFollow.followingUserId = users.id")
			->where("userFollow.userId", $this->input->post("userId"))
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
			->get()
			->result_array();


		if (!!$getDetails) {

			foreach ($getDetails as $key => $LIST) {

				$getId = $LIST['userId'];

				$getStatus = $this->db->get_where("userFollow", ['followingUserId' => $this->input->post('userId'), 'userId' => $getId])->row_array();

				if (!!$getStatus) {

					$LIST['following'] = "TRUE";
				} else {

					$LIST['following'] = "FALSE";
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

	public function uploadStories()
	{

		if ($this->input->post()) {

			$data['userId'] = $this->input->post("userId");
			$data['mentions'] = $this->input->post("mentions");
			$data['latitude'] = $this->input->post("latitude");
			$data['longitude'] = $this->input->post("longitude");
			$data['created'] = date("Y-m-d H:i:s");

			if (!empty($_FILES["image"]["name"])) {
				$name1 = time() . '_' . $_FILES["image"]["name"];
				$name = str_replace(' ', '_', $name1);
				$liciense_tmp_name = $_FILES["image"]["tmp_name"];
				$error = $_FILES["image"]["error"];
				$liciense_path = 'uploads/users/' . $name;
				move_uploaded_file($liciense_tmp_name, $liciense_path);
				$data['image'] = $liciense_path;
			}

			$upload = $this->db->insert("uploadStories", $data);

			$getId = $this->db->insert_id();

			if ($upload == true) {
				$this->fortune_of_wheel_user_cover_check($data['userId']);

				$getDetails = $this->db->get_where("uploadStories", ['id' => $getId])->row_array();

				if (!!$getDetails['image']) {

					$getDetails['image'] = base_url() . $getDetails['image'];
				} else {
					$getDetails['image'] = '';
				}

				echo json_encode([

					"success" => "1",
					"message" => "Story upload successfully",
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
				"message" => "Please enter valid params!",
			]);
			exit;
		}
	}

	public function getUploadStories()
	{

		$get = $this->db->select("uploadStories.*,users.username,users.image userImage")
			->from("uploadStories")
			->join("users", "users.id = uploadStories.userId", "left")
			->where("uploadStories.userId", $this->input->post("userId"))
			->get()
			->result_array();

		if (!!$get) {

			foreach ($get as $gets) {

				$gets['image'] = base_url() . $gets['image'];
				$gets['userImage'] = base_url() . $gets['userImage'];

				$final[] = $gets;
			}

			echo json_encode([
				"success" => "1",
				"message" => "details found successully",
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

	public function getUniqueStory()
	{

		$get = $this->db->select("uploadStories.*,users.username,users.image userImage")
			->from("uploadStories")
			->join("users", "users.id = uploadStories.userId", "left")
			->where("uploadStories.id", $this->input->post("storyId"))
			->get()
			->result_array();

		if (!!$get) {

			foreach ($get as $gets) {

				$gets['image'] = base_url() . $gets['image'];
				$gets['userImage'] = base_url() . $gets['userImage'];

				$final[] = $gets;
			}

			echo json_encode([
				"success" => "1",
				"message" => "details found successully",
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

	public function getUniquePosts()
	{

		$get = $this->db->select("user_UploadPost.*,users.username,users.image userImage")
			->from("user_UploadPost")
			->join("users", "users.id = user_UploadPost.userId", "left")
			->where("user_UploadPost.id", $this->input->post("postId"))
			->get()
			->result_array();

		if (!!$get) {

			foreach ($get as $gets) {

				$gets['postimage'] = base_url() . $gets['postimage'];
				$gets['userImage'] = base_url() . $gets['userImage'];

				$final[] = $gets;
			}

			echo json_encode([
				"success" => "1",
				"message" => "details found successully",
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

	public function removePost()
	{
		$delete = $this->db->delete('user_UploadPost', array('id' => $this->input->post('id')));
		if ($delete) {
			$message['success'] = '1';
			$message['message'] = 'post deleted';
		} else {
			$message['success'] = '0';
			$message['message'] = 'Please try after some time';
		}
		echo json_encode($message);
	}

	public function highlightPost()
	{

		if ($this->input->post()) {

			$data['userId'] = $this->input->post("userId");
			$data['postId'] = $this->input->post("postId");
			$data['created'] = date("Y-m-d H:i:s");


			$upload = $this->db->insert("highlightPost", $data);

			$getId = $this->db->insert_id();

			if ($upload == true) {

				$getDetails = $this->db->get_where("highlightPost", ['id' => $getId])->row_array();

				// if(!!$getDetails['image']){

				//     $getDetails['image'] = base_url().$getDetails['image'];
				// }
				// else{
				//     $getDetails['image'] = '';
				// }

				echo json_encode([

					"success" => "1",
					"message" => "Post highlight successfully",
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
				"message" => "Please enter valid params!",
			]);
			exit;
		}
	}

	public function getHighlightPost()
	{

		$get = $this->db->select("highlightPost.id highlightPostId,highlightPost.postId,highlightPost.userId,user_UploadPost.*")
			->from("highlightPost")
			->join("user_UploadPost", "user_UploadPost.id = highlightPost.postId", "left")
			->where("highlightPost.userId", $this->input->post("userId"))
			->get()
			->result_array();

		if (!!$get) {

			foreach ($get as $gets) {

				$gets['postimage'] = base_url() . $gets['postimage'];

				$getData[] = $gets;
			}


			echo json_encode([
				"success" => "1",
				"message" => "details found successully",
				"details" => $getData,
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


	public function getUserDetails()
	{

		$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
		if(empty($user)){
			echo json_encode([
				'status' => 0,
				'message' => 'invalid userId'
			]);exit;
		}

		$other_user = $this->db->get_where('users', ['id' => $this->input->post('other_user')])->row_array();
		if(empty($other_user)){
			echo json_encode([
				'status' => 0,
				'message' => 'invalid other_user'
			]);exit;
 		}

		$other_user['follow_status'] = false;
		$follow_status = $this->db->get_where('userFollow', ['userId' => $user['id'], 'followingUserId' => $other_user['id'], 'status' => '1'])->row_array();
		if(!empty($follow_status)){
			$other_user['follow_status'] = true;
		}


		$getDetails = $this->db->select("user_UploadPost.id user_UploadPostId,user_UploadPost.userId,user_UploadPost.hashtagId,user_UploadPost.description,user_UploadPost.postimage,user_UploadPost.restrictions,user_UploadPost.thumbnail,user_UploadPost.videopath,user_UploadPost.type,user_UploadPost.postlikeCount,user_UploadPost.post_comment_counts,user_UploadPost.video_comment_counts,user_UploadPost.total_price_post")
			->from("user_UploadPost")
			->where("user_UploadPost.userId", $this->input->post("other_user"))
			->get()
			->result_array();

			$final = [];
			if(!empty($getDetails)){
				foreach($getDetails as $details){
					$details['postimage'] = base_url() . $details['postimage'];
					$final[] = $details;
				}
			}

		$other_user['post_Details'] = $final;

		echo json_encode([
			'status' => 1,
			'message' => 'details found',
			'details' => $other_user
		]);exit;

	}

	public function getfollowUserStories()
	{

		$get = $this->db->select("userFollow.id,userFollow.userId,userFollow.followingUserId,userFollow.status,userFollow.created,userFollow.updated,users.username,concat('" . base_url() . "', users.image) as userImage")
			->from("userFollow")
			->join("users", "users.id = userFollow.followingUserId", "left")
			->where("userFollow.userId", $this->input->post("userId"))
			->get()
			->result_array();

		if (!!$get) {

			foreach ($get as $key => $value) {

				$getId = $get[$key]['followingUserId'];

				$getDetails = $this->db->select("uploadStories.id,uploadStories.userId,uploadStories.mentions,uploadStories.latitude,uploadStories.longitude,uploadStories.created,uploadStories.updated,concat('" . base_url() . "', uploadStories.image) as image,concat('" . base_url() . "', users.image) as userImage,users.username")
					->from("uploadStories")
					->join("users", "users.id = uploadStories.userId", "left")
					->where("uploadStories.userId", $getId)
					->get()
					->result_array();

				$get[$key]['stories'] = $getDetails;
			}

			echo json_encode([

				"success" => "1",
				"message" => "details found",
				"details" => $get
			]);
			exit;
		} else {

			echo json_encode([

				"success" => 0,
				"message" => "details not found!",
			]);
			exit;
		}
	}

	//   public function getfollowUserPostVideos(){

	//       $get = $this->db->select("userFollow.id,userFollow.userId,userFollow.followingUserId,userFollow.status,userFollow.created,userFollow.updated,users.username,concat('" . base_url() . "', users.image) as userImage")
	//               ->from("userFollow")
	//               ->join("users","users.id = userFollow.followingUserId","left")
	//               ->where("userFollow.userId",$this->input->post("userId"))
	//               ->get()
	//               ->result_array();


	//       if(!!$get){

	//           foreach($get as $key => $value){

	//               $getId = $get[$key]['followingUserId'];

	//               $getDetails = $this->db->select("user_UploadPost.id,user_UploadPost.userId,user_UploadPost.hashtagId,user_UploadPost.description,user_UploadPost.latitude,user_UploadPost.longitude,user_UploadPost.restrictions,user_UploadPost.type,user_UploadPost.postlikeCount,user_UploadPost.post_comment_counts,user_UploadPost.video_comment_counts,user_UploadPost.total_price_post,user_UploadPost.created,user_UploadPost.updated,concat('" . base_url() . "', user_UploadPost.postimage) as postimage,concat('" . base_url() . "', user_UploadPost.thumbnail) as thumbnail,concat('" . base_url() . "', user_UploadPost.videopath) as videopath,concat('" . base_url() . "', users.image) as userImage,users.username")
	//               ->from("user_UploadPost")
	//               ->join("users","users.id = user_UploadPost.userId","left")
	//               ->where("user_UploadPost.userId",$getId)
	//               ->get()
	//               ->result_array();

	//               $get[$key]['post&videos'] = $getDetails;



	//           }

	//           echo json_encode([

	//               "success" => "1",
	//               "message" => "details found",
	//               "details" => $get
	//               ]);exit;



	//       }
	//       else{

	//           echo json_encode([

	//               "success" => 0,
	//               "message" => "details not found!",
	//               ]);exit;
	//       }

	//   }

	public function getfollowUserPostVideos()
	{
		//   $get = $this->db->select("userFollow.id userFollowId,userFollow.userId userFollow_userId,userFollow.followingUserId,userFollow.status,userFollow.created,userFollow.updated,users.username,concat('" . base_url() . "', users.image) as userImage,user_UploadPost.id,user_UploadPost.userId,user_UploadPost.hashtagId,user_UploadPost.description,user_UploadPost.latitude,user_UploadPost.longitude,user_UploadPost.restrictions,user_UploadPost.type,user_UploadPost.postlikeCount,user_UploadPost.post_comment_counts,user_UploadPost.video_comment_counts,user_UploadPost.total_price_post,user_UploadPost.created,user_UploadPost.updated,concat('" . base_url() . "', user_UploadPost.postimage) as postimage,concat('" . base_url() . "', user_UploadPost.thumbnail) as thumbnail,concat('" . base_url() . "', user_UploadPost.videopath) as videopath")
		//           ->from("userFollow")
		//           ->join("users","users.id = userFollow.followingUserId","left")
		//           ->join("user_UploadPost","user_UploadPost.userId = userFollow.followingUserId","left")
		//           ->where("userFollow.userId",$this->input->post("userId"))
		//           ->get()
		//           ->result_array();

		$get = $this->db->select("userFollow.id userFollowId,userFollow.userId userFollow_userId,userFollow.followingUserId,users.username,concat('" . base_url() . "', users.image) as userImage,user_UploadPost.id,user_UploadPost.userId,user_UploadPost.hashtagId,user_UploadPost.description,user_UploadPost.latitude,user_UploadPost.longitude,user_UploadPost.restrictions,user_UploadPost.type,user_UploadPost.postlikeCount,user_UploadPost.post_comment_counts,user_UploadPost.video_comment_counts,user_UploadPost.total_price_post,user_UploadPost.created,user_UploadPost.updated,concat('" . base_url() . "', user_UploadPost.postimage) as postimage,concat('" . base_url() . "', user_UploadPost.thumbnail) as thumbnail,concat('" . base_url() . "', user_UploadPost.videopath) as videopath")
			->from("userFollow")
			->join("users", "users.id = userFollow.followingUserId", "left")
			->join("user_UploadPost", "user_UploadPost.userId = userFollow.followingUserId", "left")
			->where("userFollow.userId", $this->input->post("userId"))
			->get()
			->result_array();

		//   print_r($get);
		//   die;

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
			exit;
		}
	}

	public function savePost()
	{

		if ($this->input->post()) {

			$checkPostId = $this->db->get_where("savePost", ['postId' => $this->input->post("postId")])->row_array();

			if (!!$checkPostId) {

				echo json_encode([

					"success" => "0",
					"message" => "Something went wrong - POST already exist!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("userId");
			$data['postId'] = $this->input->post("postId");
			$data['created'] = date("Y-m-d H:i:s");

			$upload = $this->db->insert("savePost", $data);

			$getId = $this->db->insert_id();

			if ($upload == true) {

				$getDetails = $this->db->get_where("savePost", ['id' => $getId])->row_array();

				echo json_encode([

					"success" => "1",
					"message" => "post save successfully",
					"details" => $getDetails,
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
				"message" => "Please enter valid params!",
			]);
			exit;
		}
	}


	public function removeSavePost()
	{

		if ($this->input->post("id") == null) {

			echo json_encode([

				"success" => "0",
				"message" => "param cannot be null!"
			]);
			exit;
		}


		$delete = $this->db->delete('savePost', array('id' => $this->input->post('id')));
		if ($delete) {
			$message['success'] = '1';
			$message['message'] = 'SavePost deleted';
		} else {
			$message['success'] = '0';
			$message['message'] = 'Please try after some time';
		}
		echo json_encode($message);
	}

	public function getSavePost()
	{

		$get = $this->db->select("savePost.id savePostId,savePost.postId,savePost.userId,                                                        .*")
			->from("savePost")
			->join("user_UploadPost", "user_UploadPost.id = savePost.postId", "left")
			->where("savePost.userId", $this->input->post("userId"))
			->get()
			->result_array();

		if (!!$get) {

			foreach ($get as $gets) {

				$gets['postimage'] = base_url() . $gets['postimage'];

				$getData[] = $gets;
			}


			echo json_encode([
				"success" => "1",
				"message" => "details found successully",
				"details" => $getData,
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

	// ============ archivedLiveUsers ===============

	//   public function archivedLive(){

	// 	$checkLiveStatus = $this->db->get_where('userLive', ['id' => $this->input->post('id')])->row_array();
	// 	// if($checkLiveStatus['status'] == 'archived'){
	// 	// 	echo json_encode([
	// 	// 		'status' => '0',
	// 	// 		'message' => 'Live Archived Already'
	// 	// 	]);exit;
	// 	// }
	//     $data['status'] = 'archived';
	//     $data['archivedDate'] = date('Y-m-d H:i:s');
	// 	$rTime = date('H:i:s');

	// // 	$getCreatedTime = $this->db->select('createdTime')
	// // 								->from('userLive')
	// // 								->where('id', $this->input->post('id'))
	// // 								->get()->row_array();
	// // 	$cTime = $getCreatedTime['createdTime'];

	// // 	$archieved = strtotime($rTime);
	// // 	$created = strtotime($cTime);
	// // 	$minutes = round(abs($archieved - $created) / 60,2);

	// // 	$data['totaltimePerLive'] = $minutes;
	// //     $data['archivedTime'] = $rTime;


	// // 	// seting no of minutes, user came live in users table 
	// // 	$getLiveDuration = $this->db->select('hoursLive')->from('users')->where('id', $checkLiveStatus['userId'])->get()->row_array();
	// // 	$totalLive = $getLiveDuration['hoursLive'];

	// // 	$totalLive += $minutes;

	// // 	$this->db->set(['hoursLive' => $totalLive])->where('id', $checkLiveStatus['userId'])->update('users');


	// // 	// setting coin sharing in users table 
	// // 	$getCoins = $this->db->select('coin')->from('users')->where('id', $checkLiveStatus['userId'])->get()->row_array();
	// // 	$coin = $getCoins['coin'];
	// // 	if($coin >= 200000){

	// // 		$coin /= 20000;

	// // 		// print_r($coin);exit;

	// // 		$this->db->set(['coinSharing' => $coin])->where('id', $checkLiveStatus['userId'])->update('users');
	// // 	}


	// // 	//setting basic salary
	// // 	$actualCoin = $getCoins['coin'];
	// // 	$getSal = $this->db->get('hostSalary')->result_array();

	// // 	foreach($getSal as $value){

	// // 		$requirement = $value['coinsRequirement'];

	// // 		if($requirement >= '10000000' && $totalLive >= '3600'){

	// // 			$userData['basicSalary'] = $value['basicSalary'];

	// // 		}else{

	// // 			if($requirement <= $actualCoin && $totalLive >= '2400'){
	// // 				$userData['basicSalary'] = $value['basicSalary'];
	// // 			}

	// // 		}

	// // 	}

	// // 	$this->db->set($userData)->where('id', $checkLiveStatus['userId'])->update('users');

	//     $this->db->set($data)->where('id', $this->input->post('id'))->update('userLive');

	//     $message['status'] = '1';
	//     $message['message'] = 'Live Streming Archived Successfully';
	//     echo json_encode($message);
	//   }

	public function archivedLive()
	{

		if ($this->input->post("id") == null) {

			echo json_encode([
				"success" => "0",
				"message" => "param cannot be null!"
			]);
			exit;
		}

		$live = $this->db->get_where('userLive', ['id' => $this->input->post('id')])->row_array();
		if(empty($live)){
			echo json_encode([
				'success' => '0',
				'message' => 'invalid id'
			]);exit;
		}

		$data['status'] = 'archived';
		$data['archivedDate'] = date('Y-m-d H:i:s');
		$created = $live['created'];

		if($data['archivedDate'] >= date("Y-m-d H:i:s", strtotime($created . '+30 minutes'))){
			$this->give_live_reward($live['userId']);
		}

		$this->Common_Model->update('userLive', $data, 'id', $this->input->post('id'));
		$message['success'] = '1';
		$message['message'] = 'Live Streming Archived Successfully';
		echo json_encode($message);
	}

	protected function give_live_reward($userId){

		// giving 20 diamond and 32 exp to user for coming live more then 30 minutes
		$user = $this->db->get_where('users', ['id' => $userId])->row_array();

		$userdata['diamond'] = $user['diamond'];
		$userdata['exp'] = $user['exp'];
		$userdata['diamond'] += 20;
		$userdata['exp'] += 32;

		$this->db->set($userdata)->where('id', $userId)->update('users');

	}

	public function topLiveUserGifting()
	{

		$getUserByDate = $this->db->select_sum('coin')
			->select('userId,users.*')
			->from('userGiftHistory')
			->join("users", "users.id = userGiftHistory.userId", "left")
			->group_by('userId')
			->where('giftUserId', $this->input->post('userId'))
			// 	  ->where('created', date('Y-m-d'))
			->order_by('coin', 'desc')
			->get()
			->result_array();


		if (!!$getUserByDate) {

			foreach ($getUserByDate as $user) {

				$user['image'] = base_url() . $user['image'];

				$final[] = $user;
			}


			echo json_encode([
				'success' => '1',
				'message' => 'Top gifter details found',
				'details' => $final
			]);
			exit;
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'details not found!'
			]);
			exit;
		}
	}

	public function weeklyTopLiveUserGifting()
	{


		$dateLimit = date("Y-m-d", strtotime("-1 week"));


		$weekly = $this->db->select_sum('coin')
			->select('userId,created,users.*')
			->from('userGiftHistory')
			->join("users", "users.id = userGiftHistory.userId", "left")
			->group_by('userId')
			->where('giftUserId', $this->input->post('userId'))
			->where('created >=', $dateLimit)
			->order_by('coin', 'desc')
			->get()->result_array();


		if (!!$weekly) {

			foreach ($weekly as $user) {

				$user['image'] = base_url() . $user['image'];

				$final[] = $user;
			}


			echo json_encode([
				'success' => '1',
				'message' => 'Top gifter details found',
				'details' => $final
			]);
			exit;
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'details not found!'
			]);
			exit;
		}
	}


	public function monthlyTopLiveUserGifting()
	{

		$dateLimit = date("Y-m-d", strtotime("-1 month"));


		$getUserByMonth = $this->db->select_sum('coin')
			->select('userId,created,users.*')
			->from('userGiftHistory')
			->join("users", "users.id = userGiftHistory.userId", "left")
			->group_by('userId')
			->where('giftUserId', $this->input->post('userId'))
			->where('created >=', $dateLimit)
			->order_by('coin', 'desc')
			->get()->result_array();

		if (!!$getUserByMonth) {

			foreach ($getUserByMonth as $user) {

				$user['image'] = base_url() . $user['image'];

				$final[] = $user;
			}


			echo json_encode([
				'success' => '1',
				'message' => 'Top gifter details found',
				'details' => $final
			]);
			exit;
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'details not found!'
			]);
			exit;
		}
	}

	public function dailyLiveUserGifting()
	{

		$getUserByDate = $this->db->select_sum('coin')
			->select('userId,created,users.*')
			->from('userGiftHistory')
			->join("users", "users.id = userGiftHistory.userId", "left")
			->group_by('userId')
			->where('giftUserId', $this->input->post('userId'))
			->where('created', date('Y-m-d'))
			->order_by('coin', 'desc')
			->get()->result_array();


		if (!!$getUserByDate) {

			foreach ($getUserByDate as $user) {

				$user['image'] = base_url() . $user['image'];

				$final[] = $user;
			}


			echo json_encode([
				'success' => '1',
				'message' => 'Top gifter details found',
				'details' => $final
			]);
			exit;
		} else {
			echo json_encode([
				'success' => '0',
				'message' => 'details not found!'
			]);
			exit;
		}
	}



	public function pkBattle()
	{

		$checkUserId = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
		if (empty($checkUserId)) {
			echo json_encode([
				'status' => 0,
				'message' => 'userId not Exist'
			]);
			exit;
		}
		$checkOtherUserId = $this->db->get_where('users', ['id' => $this->input->post('otherUserId')])->row_array();
		if (empty($checkOtherUserId)) {
			echo json_encode([
				'status' => 0,
				'message' => 'otherUserId not Exist'
			]);
			exit;
		}

		$checkLiveId = $this->db->get_where('userLive', ['id' => $this->input->post('liveId')])->row_array();
		if (empty($checkLiveId)) {
			echo json_encode([
				'status' => 0,
				'message' => 'Invalid LiveId'
			]);
			exit;
		}

		$checkOtherLiveId = $this->db->get_where('userLive', ['id' => $this->input->post('otherLiveId')])->row_array();
		if (empty($checkOtherLiveId)) {
			echo json_encode([
				'status' => 0,
				'message' => 'Invalid otherLiveId'
			]);
			exit;
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
				'status' => 1,
				'message' => 'PKBattle started!! LADO BC',
				'details' => $insertId
			]);
			exit;
		} else {
			echo json_encode([
				'status' => 0,
				'message' => 'technical error'
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
					'status' => 1,
					'message' => 'user 1 has left the battle'
				]);
				exit;
			}

			if ($this->input->post('type') == 2) {
				$data['otherUserStatus'] = 0;

				$this->db->set($data)->where('id', $this->input->post('pkId'))->update('pkbattle');

				echo json_encode([
					'status' => 1,
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
					'status' => 1,
					'message' => 'LIVE ARCHIEVED',
					'details' => $message
				]);
				exit;
			} else {
				echo json_encode([
					'status' => 0,
					'message' => 'LIVE not ARCHIEVED'
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


	public function getpkResult()
	{
		if ($this->input->post()) {

			$checkBattleId = $this->db->get_where('pkbattle', ['id' => $this->input->post('battleId'), 'battleStatus' => 'end'])->row_array();
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

						$check['coin'] = '0';
						$check['result'] = "LOSSER";
						$check['battleId'] = $this->input->post('battleId');
						$final[] = $check;
					} else {
						$final[] = $check['parse'];
					}


					echo json_encode([
						'status' => 1,
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

						$check['coin'] = '0';
						$check['result'] = "LOSSER";
						$check['battleId'] = $this->input->post('battleId');
						$final[] = $check;
					} else {
						$final[] = $check['parse'];
					}


					echo json_encode([
						'status' => 1,
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

						$check['coin'] = '0';
						$check['result'] = "TIE";
						$check['battleId'] = $this->input->post('battleId');
						$final[] = $check;
					} else {
						$final[] = $check['parse'];
					}


					echo json_encode([
						'status' => 1,
						'message' => 'Result',
						'details' => $final
					]);
					exit;
				}
			} else {
				echo json_encode([
					'status' => 0,
					'message' => 'Invalid battle Id and Battle not end'
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
						'status' => 1,
						'message' => 'Live Users Found',
						'details' => $pass
					]);
					exit;
				} else {
					echo json_encode([
						'status' => 0,
						'message' => 'No live User List Found'
					]);
					exit;
				}
			} else {
				echo json_encode([
					'status' => 0,
					'message' => 'userId user not exist'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'status' => 0,
				'message' => 'Enter Valid Data'
			]);
			exit;
		}
	}

	public function someFunctionality()
	{
		if ($this->input->post()) {

			$checkUserId = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (!!$checkUserId) {

				$checkOtherUserId = $this->db->get_where('users', ['id' => $this->input->post('otherUserId')])->row_array();
				if (!!$checkOtherUserId) {

					$level = $checkOtherUserId['my_level'];
					$tlevel = $checkOtherUserId['talent_level'];
					$getLevelImage = $this->db->select('image')->from('user_levels')->where('level', $level)->get()->row_array();
					$gettLevelImage = $this->db->select('image')->from('user_talent_levels')->where('level', $tlevel)->get()->row_array();

					$checkOtherUserId['userLevelImage'] = $getLevelImage['image'];
					$checkOtherUserId['userTalentLevelImage'] = $gettLevelImage['image'];

					$checkList = $this->db->get_where('userFollow', ['userId' => $this->input->post('otherUserId'), 'status' => '1'])->result_array();

					// print_r($checkList);exit;
					$count = 0;
					if (!!$checkList) {


						foreach ($checkList as $key => $list) {
							$checkFriend = $this->db->select('userId')
								->from('userFollow')
								->where('userId', $list['followingUserId'])
								->where('followingUserId', $this->input->post('otherUserId'))
								->where('userFollow.status', '1')
								->get()->row_array();
							$count++;
						}
					}

					$checkOtherUserId['friendsCount'] = $count;

					if ($this->input->post('userId') === $this->input->post('otherUserId')) {
						echo json_encode([
							'status' => '1',
							'message' => 'Both ids are same',
							'details' => $checkOtherUserId
						]);
						exit;
					} else {

						$checkFollowStatus = $this->db->get_where('userFollow', ['userId' => $this->input->post('userId'), 'followingUserId' => $this->input->post('otherUserId'), 'status' => '1'])->row_array();
						if (!!$checkFollowStatus) {
							$checkOtherUserId['followStatus'] = TRUE;
						} else {
							$checkOtherUserId['followStatus'] = FALSE;
						}

						$checkList = $this->db->get_where('userFollow', ['userId' => $this->input->post('otherUserId'), 'status' => '1'])->result_array();

						// print_r($checkList);exit;
						$count = 0;
						if (!!$checkList) {


							foreach ($checkList as $key => $list) {
								$checkFriend = $this->db->select('userId')
									->from('userFollow')
									->where('userId', $list['followingUserId'])
									->where('followingUserId', $this->input->post('otherUserId'))
									->where('userFollow.status', '1')
									->get()->row_array();
								$count++;
							}
						}

						$checkOtherUserId['friendsCount'] = $count;

						echo json_encode([
							'status' => '1',
							'message' => 'otherUserId user details',
							'details' => $checkOtherUserId
						]);
						exit;
					}
				} else {
					echo json_encode([
						'status' => '0',
						'message' => 'otherUserId user not exists'
					]);
					exit;
				}
			} else {
				echo json_encode([
					'status' => '0',
					'message' => 'userId user not exists'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'status' => '0',
				'message' => 'please enter valid data'
			]);
		}
	}

	public function getTopGifter()
	{
		if ($this->input->post()) {

			// 1 for daily 
			// 2 for weekly 
			// 3 for monthly 

			if ($this->input->post('type') == '1') {

				$getUserByDate = $this->db->select_sum('coin')
					->select('userId')
					->from('userGiftHistory')
					->group_by('userId')
					->where('giftUserId', $this->input->post('userId'))
					->where('created', date('Y-m-d'))
					->order_by('coin', 'desc')
					->get()->result_array();

				if (!!$getUserByDate) {

					foreach ($getUserByDate as $key => $user) {

						$getUserByDate[$key]['userInfo'] = $this->db->get_where('users', ['id' => $user['userId']])->row_array();
					}


					echo json_encode([
						'status' => '1',
						'message' => 'Giftings Found for Today',
						'details' => $getUserByDate
					]);
					exit;
				} else {
					echo json_encode([
						'status' => '0',
						'message' => 'No gifting done Today'
					]);
					exit;
				}
			} else if ($this->input->post('type') == '2') {

				$dateLimit = date("Y-m-d", strtotime("-1 week"));

				$getUserByWeek = $this->db->select_sum('coin')
					->select('userId')
					->from('userGiftHistory')
					->group_by('userId')
					->where('giftUserId', $this->input->post('userId'))
					->where('created >=', $dateLimit)
					->order_by('coin', 'desc')
					->get()->result_array();

				if (!!$getUserByWeek) {

					foreach ($getUserByWeek as $key => $user) {

						$getUserByWeek[$key]['userInfo'] = $this->db->get_where('users', ['id' => $user['userId']])->row_array();
					}

					echo json_encode([
						'status' => '1',
						'message' => 'Giftings Found from ' . $dateLimit . ' to Today',
						'details' => $getUserByWeek
					]);
					exit;
				} else {
					echo json_encode([
						'status' => '0',
						'message' => 'No gifting done this Week'
					]);
					exit;
				}
			} else if ($this->input->post('type') == '3') {

				$dateLimit = date("Y-m-d", strtotime("-1 month"));

				$getUserByMonth = $this->db->select_sum('coin')
					->select('userId')
					->from('userGiftHistory')
					->group_by('userId')
					->where('giftUserId', $this->input->post('userId'))
					->where('created >=', $dateLimit)
					->order_by('coin', 'desc')
					->get()->result_array();

				if (!!$getUserByMonth) {

					foreach ($getUserByMonth as $key => $user) {

						$getUserByMonth[$key]['userInfo'] = $this->db->get_where('users', ['id' => $user['userId']])->row_array();
					}

					echo json_encode([
						'status' => '1',
						'message' => 'Giftings Found from ' . $dateLimit . ' to Today',
						'details' => $getUserByMonth
					]);
					exit;
				} else {
					echo json_encode([
						'status' => '0',
						'message' => 'No gifting done this Month'
					]);
					exit;
				}
			} else {
				echo json_encode([
					'status' => '0',
					'message' => 'Enter valid type'
				]);
				exit;
			}
		} else {
			echo json_encode([
				'status' => '0',
				'message' => 'Enter Valid Data!'
			]);
		}
	}

	public function postReels()
	{

		if ($this->input->post()) {

			$data['userId'] = $this->input->post("userId");
			$data['mentions'] = $this->input->post("mentions");
			$data['latitude'] = $this->input->post("latitude");
			$data['longitude'] = $this->input->post("latitude");
			$data['created'] = date("Y-m-d H:i:s");

			if (!empty($_FILES["thumbnail"]["name"])) {
				$name1 = time() . '_' . $_FILES["thumbnail"]["name"];
				$name = str_replace(' ', '_', $name1);
				$liciense_tmp_name = $_FILES["thumbnail"]["tmp_name"];
				$error = $_FILES["thumbnail"]["error"];
				$liciense_path = 'uploads/users/' . $name;
				move_uploaded_file($liciense_tmp_name, $liciense_path);
				$data['thumbnail'] = $liciense_path;
			}
			$name1 = time() . '_' . $this->input->post('videopath');
			$name = str_replace(' ', '_', $name1);
			$liciense_tmp_name = $_FILES["videopath"]["tmp_name"];
			$error = $_FILES["videopath"]["error"];
			$liciense_path = 'uploads/users/' . $name;
			move_uploaded_file($liciense_tmp_name, $liciense_path);
			$data['videopath'] = $liciense_path;

			$upload = $this->db->insert("postReels", $data);

			if ($upload == true) {

				echo json_encode([

					"success" => "1",
					"message" => "Reels added"
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
				"message" => "Please enter valid param!"
			]);
			exit;
		}
	}

	public function getPostReels()
	{

		$GET = $this->db->select("id,userId,mentions,thumbnail,videopath")
			->from("postReels")
			->where("postReels.userId", $this->input->post("userId"))
			->get()
			->result_array();

		if (!!$GET) {

			foreach ($GET as $gets) {

				$gets['thumbnail'] = base_url() . $gets['thumbnail'];
				$gets['videopath'] = base_url() . $gets['videopath'];

				$final[] = $gets;
			}

			echo json_encode([

				"success" => "1",
				"message" => "details found",
				"details" => $final,
			]);
			exit;
		} else {

			echo json_encode([

				"success" => "0",
				"message" => "details not found!"
			]);
			exit;
		}
	}

	public function GiftingOnUser()
	{

		if ($this->input->post()) {

			$checkPurchasedCoin = $this->db->get_where("users", ['id' => $this->input->post("userId")])->row_array();

			$getAmount = $checkPurchasedCoin['purchasedCoin'];

			if ($getAmount >= $this->input->post("price")) {

				$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
				if(empty($user)){
					echo json_encode([
						'status' => 0,
						'message' => 'invalid userId'
					]);exit;
				}

				$otherUser = $this->db->get_where('users', ['id' => $this->input->post('giftUserId')])->row_array();
				if(empty($otherUser)){
					echo json_encode([
						'status' => 0,
						'message' => 'invalid giftUserId'
					]);exit;
				}

				$data['userId'] = $this->input->post("userId");
				$data['giftUserId'] = $this->input->post("giftUserId");
				$data['giftId'] = $this->input->post("giftId");
				$data['coin'] = $this->input->post("price");

				$upload = $this->db->insert("userGiftHistory", $data);

				if ($upload == true) {

					$this->check_gift_to_family($user['id'], $otherUser['id']);

					$deduct['purchasedCoin'] = $getAmount - $data['coin'];

					$this->db->update("users", $deduct, ['id' => $this->input->post("userId")]);

					echo json_encode([

						"success" => "1",
						"message" => "details added succssfully"
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
					"message" => "Invalid price!"
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


	//   ================== family apis ===================== 

	public function createFamily()
	{
		if ($this->input->post()) {

			if (!$this->input->post('userId') || !$this->input->post('familyname') || !$this->input->post('familyTaillight') || !$this->input->post('familyTag') || !$this->input->post('familyLocation') || !$this->input->post('bulletin') || !$this->input->post('joinsettings') || empty($_FILES["family_photo"])) {
				echo json_encode([
					'status' => 0,
					'message' => 'enter all parameters'
				]);
				exit;
			}

			$checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

			if (empty($checkuser)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);
				exit;
			}

			$checkuserFamily = $this->db->get_where('groups', ['userId' => $this->input->post('userId')])->row_array();

			if (!!$checkuserFamily) {
				echo json_encode([
					'status' => 0,
					'message' => 'user can not create more than one family'
				]);
				exit;
			}

			$checkSettingsId = $this->db->get_where('joinSettings', ['id' => $this->input->post('joinsettings')])->row_array();

			if (empty($checkSettingsId)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid joinsettings'
				]);
				exit;
			}

			if (!empty($_FILES["family_photo"]["name"])) {
				$name1 = time() . '_' . $_FILES["family_photo"]["name"];
				$name = str_replace(' ', '_', $name1);
				$liciense_tmp_name = $_FILES["family_photo"]["tmp_name"];
				$error = $_FILES["family_photo"]["error"];
				$liciense_path = 'uploads/users/' . $name;
				move_uploaded_file($liciense_tmp_name, $liciense_path);
				$data['image'] = $liciense_path;
			}


			$data['userId'] = $this->input->post('userId');
			$data['familyname'] = $this->input->post('familyname');
			$data['familyTaillight'] = $this->input->post('familyTaillight');
			$data['familyTag'] = $this->input->post('familyTag');
			$data['familyLocation'] = $this->input->post('familyLocation');
			$data['bulletin'] = $this->input->post('bulletin');
			$data['joinsettings'] = $this->input->post('joinsettings');
			$data['familycreated'] = date('Y-m-d H:i:s');


			if ($this->db->insert('groups', $data)) {

				$memberdata['userId'] = $this->input->post('userId');
				$memberdata['familyId'] = $this->db->insert_id();
				$memberdata['positionType'] = '1';
				$memberdata['date'] = date('Y-m-d H:i:s');

				$this->db->insert('groupsMembers', $memberdata);
				$this->db->set(['positionType' => '1', 'family' => $memberdata['familyId']])->where('id', $this->input->post('userId'))->update('users');

				echo json_encode([
					'status' => 1,
					'message' => 'family created'
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
				'message' => 'enter valid parameters'
			]);
			exit;
		}
	}

	public function getJoinSettings()
	{
		$get = $this->db->get('joinSettings')->result_array();

		echo json_encode([
			'status' => 1,
			'message' => 'settings found',
			'details' => $get
		]);
		exit;
	}

	public function userFamilyDetails()
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

			$checkFamilyStatus = $this->db->get_where('groupsMembers', ['userId' => $this->input->post('userId')])->result_array();

			// print_r($checkFamilyStatus);exit;

			if (empty($checkFamilyStatus)) {
				echo json_encode([
					'status' => 2,
					'message' => 'user not in any family'
				]);
				exit;
			}

			if ($checkFamilyStatus[0]['memberStatus'] == 2) {
				echo json_encode([
					'status' => 2,
					'message' => 'user under review'
				]);
				exit;
			}

			$final = [];
			foreach ($checkFamilyStatus as $family) {

				$final['userdetails'] = $checkuser;
				$final['userdetails']['familyDetails'] = $this->db->get_where('groups', ['id' => $family['familyId']])->row_array();
				$final['userdetails']['familyDetails']['image'] = base_url() . $final['userdetails']['familyDetails']['image'];
				$final['userdetails']['position'] = $this->db->get_where('groupPositions', ['id' => $family['positionType']])->row_array();
			}

			echo json_encode([
				'status' => 1,
				'message' => 'user details found',
				'details' => $final['userdetails']
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

	// public function testing()
	// {
	// 	$time1 = $this->input->post('time');
	// 	$time2 = date('Y-m-d H:i:s', strtotime('+72 hours'));

	// 	print_r($time2);
	// 	echo "...";
	// 	print_r($time1);
	// }

	public function getFamilies()
	{
		if ($this->input->post()) {

			if (!$this->input->post('userId')) {
				echo json_encode([
					'status' => 0,
					'message' => 'pass all parameters'
				]);
				exit;
			}

			$checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

			if (empty($checkUser)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);
				exit;
			}

			$get = $this->db->get('groups')->result_array();

			if (empty($get)) {
				echo json_encode([
					'status' => 0,
					'message' => 'no families found'
				]);
				exit;
			}


			$final = [];
			foreach ($get as $familiy) {
				$familiy['image'] = base_url() . $familiy['image'];
				$familiy['numberOfMembers'] = $this->db->get_where('groupsMembers', ['familyId' => $familiy['id'], 'memberStatus' => '1'])->num_rows();

				$see = $this->db->get_where('groupsMembers', ['userId' => $this->input->post('userId'), 'familyId' => $familiy['id']])->row_array();
				// print_r($familiy);exit;
				if ($see['memberStatus'] == 1) {
					$familiy['userIdStatusWithFamily'] = 1;
				} else if ($see['memberStatus'] == 2) {
					$familiy['userIdStatusWithFamily'] = 2;
				} else if ($see['memberStatus'] == 3) {
					$familiy['userIdStatusWithFamily'] = 3;
				} else if ($see['memberStatus'] == 4) {
					$familiy['userIdStatusWithFamily'] = 0;
				} else if (empty($see)) {
					$familiy['userIdStatusWithFamily'] = 0;
				}

				$final[] = $familiy;
			}

			echo json_encode([
				'status' => 1,
				'message' => 'families found',
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

	public function joinFamily()
	{
		if ($this->input->post()) {

			$checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

			// print_r($checkUser);exit;

			if (empty($checkUser)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);
				exit;
			}

			$checkFamilyId = $this->db->get_where('groups', ['id' => $this->input->post('familyId')])->row_array();

			if (empty($checkFamilyId)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid familyId'
				]);
				exit;
			}

			// check user status with other families
			// $where = "'userId' = " . $this->input->post('userId') . " and 'memberStatus' = '3' or 'memberStatus' = '4' or 'memberStatus' = '2' or 'memberStatus' = '1'";
			$where = "'userId' = " . $this->input->post('userId') . " and 'memberStatus' = '1'";
			$checkotherfamilyuserstatus = $this->db->get_where('groupsMembers', $where)->row_array();

			if (!!$checkotherfamilyuserstatus) {
				echo json_encode([
					'status' => 0,
					'message' => 'user can not join more than one families'
				]);
				exit;
			}


			// check user status with same familyId
			$checkFamilyUserStatus = $this->db->get_where('groupsMembers', ['userId' => $this->input->post('userId'), 'familyId' => $this->input->post('familyId')])->row_array();

			if (!!$checkFamilyUserStatus) {
				if ($checkFamilyUserStatus['memberStatus'] == 1) {
					echo json_encode([
						'status' => 0,
						'message' => 'user already in the family'
					]);
					exit;
				}

				if ($checkFamilyUserStatus['memberStatus'] == 2) {
					echo json_encode([
						'status' => 0,
						'message' => 'user under review'
					]);
					exit;
				}

				if ($checkFamilyUserStatus['memberStatus'] == 4) {

					if (date($checkFamilyUserStatus['statusDate'], strtotime('+72 hours') > date('Y-m-d H:i:s'))) {

						echo json_encode([
							'status' => 0,
							'message' => 'user cant join same family before 72 hours of leaving'
						]);
						exit;
					}
				}
			}

			// check family requirments

			$data['positionType'] = 4;
			$data['familyId'] = $this->input->post('familyId');
			$data['userId'] = $this->input->post('userId');
			$data['memberStatus'] = 2;
			$data['statusDate'] = date('Y-m-d H:i:s');
			$data['date'] = date('Y-m-d H:i:s');

			$getUserPosts = $this->db->get_where('user_UploadPost', ['userId' => $this->input->post('userId'), 'type' => ''])->num_rows();

			if ($checkFamilyId['joinsettings'] == '6') {

				if ($getUserPosts < 10) {
					echo json_encode([
						'status' => 0,
						'message' => 'family setting, minimum 10 posts required'
					]);
					exit;
				} else {
					$data['memberStatus'] = 2;
				}
			} else if ($checkFamilyId['joinsettings'] == '5') {

				if ($getUserPosts < 1) {
					echo json_encode([
						'status' => 0,
						'message' => 'family setting, minimum 1 post required'
					]);
					exit;
				} else {
					$data['memberStatus'] = 2;
				}
			} else if ($checkFamilyId['joinsettings'] == '4') {

				if ($checkUser['leval'] < 20) {
					echo json_encode([
						'status' => 0,
						'message' => 'family setting 4, minimum level 4 required'
					]);
					exit;
				} else {
					$data['memberStatus'] = 2;
				}
			} else if ($checkFamilyId['joinsettings'] == '3') {

				if ($checkUser['leval'] < 5) {
					echo json_encode([
						'status' => 0,
						'message' => 'family setting 3, minimum level 5 required'
					]);
					exit;
				} else {
					$data['memberStatus'] = 2;
				}
			} else if ($checkFamilyId['joinsettings'] == '2') {

				$data['memberStatus'] = 2;
			} else if ($checkFamilyId['joinsettings'] == '1') {

				$data['memberStatus'] = 1;
			}

			if ($checkFamilyUserStatus['memberStatus'] == 3) {
				if ($this->db->set($data)->where('id', $checkFamilyUserStatus['id'])->update('groupsMembers')) {

					if ($checkFamilyId['joinsettings'] == '1') {

						$this->db->set(['positionType' => '4', 'family' => $this->input->post('familyId')])->where('id', $this->input->post('userId'))->update('users');

						echo json_encode([
							'status' => 1,
							'message' => 'family joined'
						]);
						exit;
					} else {

						echo json_encode([
							'status' => 2,
							'message' => 'user under review'
						]);
						exit;
					}
				} else {
					echo json_encode([
						'status' => 0,
						'message' => 'tech error'
					]);
					exit;
				}
			}

			if ($this->db->insert('groupsMembers', $data)) {

				if ($checkFamilyId['joinsettings'] == '1') {

					$this->db->set(['positionType' => '4', 'family' => $this->input->post('familyId')])->where('id', $this->input->post('userId'))->update('users');

					echo json_encode([
						'status' => 1,
						'message' => 'family joined'
					]);
					exit;
				} else {

					echo json_encode([
						'status' => 2,
						'message' => 'user under review'
					]);
					exit;
				}
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


	public function getFamilyDetails()
	{
		if ($this->input->post()) {
			$final = [];

			// $checkMemberInFamily = $this->db->get_where('groupsMembers', ['familyId' => $this->input->post('familyId'), 'userId' => $this->input->post('userId')])->row_array();

			// if(empty($checkMemberInFamily)){
			// 	echo json_encode([
			// 		'status' => 0,
			// 		'message' => 'user not in family'
			// 	]);exit;
			// }

			$checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

			if (empty($checkUser)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);
				exit;
			}

			$checkFamilydetails = $this->db->get_where('groups', ['id' => $this->input->post('familyId')])->row_array();

			// print_r($checkFamilydetails);exit;

			$set = $this->db->get_where('joinSettings', ['id' => $checkFamilydetails['joinsettings']])->row_array();
			$checkFamilydetails['joinsettings'] = $set['setting'];

			if (empty($checkFamilydetails)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid familyId'
				]);
				exit;
			}

			$final['familydetails'] = $checkFamilydetails;
			$final['familydetails']['image'] = base_url() . $final['familydetails']['image'];

			$familyMembers = $this->db->get_where('groupsMembers', ['familyId' => $this->input->post('familyId'), 'memberStatus' => '1'])->result_array();

			$gifterArray = [];
			$recieverArray = [];
			$membersArray = [];
			foreach ($familyMembers as $members) {

				$get = $this->db->select('title')->from('userTitles')->where('id', $members['title'])->get()->row_array();
				$member['position'] = $members['positionType'];
				$member['title'] = $get['title'];
				$member['userId'] = $members['userId'];
				$membersArray[] = $member;

				// print_r($members);exit;

				$where = "'liveId' != 0";
				$getGifters = $this->db->select_sum('coin')
					->select('userId')
					->from('userGiftHistory')
					->where('giftUserId', $members['userId'])
					->where($where)
					->get()->row_array();



				if (empty($getGifters['coin'])) {
					$getGifters['coin'] = "0";
					$getGifters['userId'] = $members['userId'];
				}


				$gifterArray[] = $getGifters;


				$getGifters = $this->db->select_sum('coin')
					->select('giftUserId')
					->from('userGiftHistory')
					->where('giftUserId', $members['userId'])
					->where($where)
					->get()->row_array();

				// print_r($this->db->last_query());

				if (empty($getGifters['coin'])) {
					$getGifters['coin'] = "0";
					$getGifters['giftUserId'] = $members['userId'];
				}


				$recieverArray[] = $getGifters;
			}
			rsort($gifterArray);
			$final['familydetails']['topGifter'] = $gifterArray[0];
			$getTopGifterDetails = $this->db->get_where('users', ['id' => $final['familydetails']['topGifter']['userId']])->row_array();
			$getTopGifterDetails = $this->db->select('name, positionType, image, id userId, gender')
				->where('id', $final['familydetails']['topGifter']['userId'])
				->get('users')->row_array();

			$gifterPosition = $this->db->get_where('groupPositions', ['id' => $getTopGifterDetails['positionType']])->row_array();
			$getTopGifterDetails['position'] = $gifterPosition['position'];
			$final['familydetails']['topGifter']['userDetails'] = $getTopGifterDetails;

			rsort($recieverArray);
			$final['familydetails']['topReciever'] = $recieverArray[0];

			$getTopRecieverDetails = $this->db->select('name, positionType, image, id userId, gender')
				->where('id', $final['familydetails']['topReciever']['giftUserId'])
				->get('users')->row_array();

			$recieverPosition = $this->db->get_where('groupPositions', ['id' => $getTopRecieverDetails['positionType']])->row_array();
			$getTopRecieverDetails['position'] = $recieverPosition['position'];

			$final['familydetails']['topReciever']['userDetails'] = $getTopRecieverDetails;

			sort($membersArray);
			// print_r($membersArray);exit;
			$familyMembers = [];
			foreach ($membersArray as $mem) {
				$getPosition = $this->db->get_where('groupPositions', ['id' => $mem['position']])->row_array();
				$userDetails = $this->db->get_where('users', ['id' => $mem['userId']])->row_array();
				$userDetails['image'] = base_url() . $userDetails['image'];
				$userDetails['followStatus'] = false;
				$userDetails['title'] = $mem['title'] ?: '';
				$get = $this->db->get_where('userFollow', ['userId' => $userDetails['id'], 'followingUserId' => $this->input->post('userId')])->row_array();
				if (!!$get) {
					$userDetails['followStatus'] = true;
				}

				$getPosition['user'] = $userDetails;
				$familyMembers[] = $getPosition;
			}

			$final['familydetails']['memberDetails'] = $familyMembers;

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

	public function changePositions()
	{
		if ($this->input->post()) {

			$checkCaptain = $this->db->get_where('users', ['id' => $this->input->post('captainId')])->row_array();

			if (empty($checkCaptain)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid captainId'
				]);
				exit;
			}

			$checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

			if (empty($checkUser)) {
				echo json_encode([
					'status' => 0,
					'message' => 'inavlid userId'
				]);
				exit;
			}

			$checkFamilyId = $this->db->get_where('groups', ['id' => $this->input->post('familyId')])->row_array();

			if (empty($checkFamilyId)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid familyId'
				]);
				exit;
			}

			$checkUserFamily = $this->db->get_where('groupsMembers', ['userId' => $this->input->post('userId'), 'familyId' => $this->input->post('familyId')])->row_array();

			if (empty($checkUserFamily)) {
				echo json_encode([
					'status' => 0,
					'message' => 'user not in the family'
				]);
				exit;
			}

			if ($this->input->post('type') == '2') {

				$checkNumberOfCCaptains = $this->db->get_where('groupsMembers', ['familyId' => $this->input->post('familyId'), 'positionType' => '2', 'memberStatus' => '1'])->num_rows();

				if ($checkNumberOfCCaptains >= 2) {
					echo json_encode([
						'status' => 0,
						'message' => 'maximum limit reached for Co-Captains'
					]);
					exit;
				}

				$data['positionType'] = $this->input->post('type');
			} else if ($this->input->post('type') == '3') {

				$checkNumberOfAssistants = $this->db->get_where('groupsMembers', ['familyId' => $this->input->post('familyId'), 'positionType' => '3', 'memberStatus' => '1'])->num_rows();

				if ($checkNumberOfAssistants >= 3) {
					echo json_encode([
						'status' => 0,
						'message' => 'maximum limit reached for Assitants'
					]);
					exit;
				}

				$data['positionType'] = $this->input->post('type');
			}

			if ($this->db->set($data)->where('id', $checkUserFamily['id'])->update('groupsMembers') && $this->db->set($data)->where('id', $this->input->post('userId'))->update('users')) {

				echo json_encode([
					'status' => 1,
					'message' => 'position updated'
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


	public function getRequests()
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

			$checkUserInFamily = $this->db->get_where('groupsMembers', ['userId' => $this->input->post('userId')])->row_array();

			if (empty($checkUserInFamily)) {
				echo json_encode([
					'status' => 0,
					'message' => 'user not in any family'
				]);
				exit;
			}

			if ($checkUserInFamily['positionType'] == '3' || $checkUserInFamily['positionType'] == '4') {
				echo json_encode([
					'status' => 0,
					'message' => 'user not a captain or not a co-captain'
				]);
				exit;
			}

			$getRequest = $this->db->get_where('groupsMembers', ['memberStatus' => 2, 'familyId' => $checkUserInFamily['familyId']])->result_array();
			// print_r($this->db->last_query());exit;
			if (empty($getRequest)) {
				echo json_encode([
					'status' => 0,
					'message' => 'no request found'
				]);
				exit;
			}

			$final = [];
			foreach ($getRequest as $request) {
				$getUserDeatils = $this->db->get_where('users', ['id' => $request['userId']])->row_array();
				$getUserDeatils['requestId'] = $request['id'];

				$final[] = $getUserDeatils;
			}

			echo json_encode([
				'status' => 1,
				'message' => 'request found',
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

	public function responseRequest()
	{
		if ($this->input->post()) {

			$checkRequestId = $this->db->get_where('groupsMembers', ['id' => $this->input->post('requestId'), 'memberStatus' => '2'])->row_array();

			if (empty($checkRequestId)) {
				echo json_encode([
					'status' => 0,
					'message' => 'inavlid requestId'
				]);
				exit;
			}

			$familyId = $checkRequestId['familyId'];

			$checkAccepter = $this->db->get_where('groupsMembers', ['userId' => $this->input->post('userId'), 'familyId' => $familyId])->row_array();

			if (empty($checkAccepter)) {
				echo json_encode([
					'status' => 0,
					'message' => 'inavlid userId'
				]);
				exit;
			}

			if ($checkAccepter['positionType'] == '3' || $checkAccepter['positionType'] == '4') {
				echo json_encode([
					'status' => 0,
					'message' => 'userId not a captain nor co captain'
				]);
				exit;
			}

			$data['statusDate'] = date('Y-m-d H:i:s');

			if ($this->input->post('accept') == 1) {

				$data['memberStatus'] = 1;
				$data['positionType'] = 4;
				$data['acceptUserId'] = $this->input->post('userId');

				$udata['positionType'] = '4';
				$udata['family'] = $familyId;

				if ($this->db->set($data)->where('id', $this->input->post('requestId'))->update('groupsMembers') && $this->db->set($udata)->where('id', $checkRequestId['userId'])->update('users') && $this->db->delete('groupsMembers', ['memberStatus' => '2', 'userId' => $this->input->post('userId')])) {
					echo json_encode([
						'status' => 1,
						'message' => 'user accepted',

					]);
					exit;
				}
			} else if ($this->input->post('accept') == 2) {
				$data['memberStatus'] = 3;

				if ($this->db->update($data)->where('id', $this->input->post('requestId')->update('groupsMembers'))) {
					echo json_encode([
						'status' => 2,
						'message' => 'user rejected',

					]);
					exit;
				}


				echo json_encode([
					'status' => 0,
					'message' => 'tech error'
				]);
				exit;
			} else {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid accept type'
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

	public function leaveFamily()
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

			$checkuserinfamily = $this->db->get_where('groupsMembers', ['userId' => $this->input->post('userId'), 'memberStatus' => '1'])->row_array();

			if (empty($checkuserinfamily)) {
				echo json_encode([
					'status' => 0,
					'message' => 'user not in any family'
				]);
				exit;
			}

			if ($checkuserinfamily['positionType'] == 1) {
				echo json_encode([
					'status' => 0,
					'message' => 'captain can not leave the family, delete first'
				]);
				exit;
			}

			if ($this->db->set('memberStatus', 4)->where('id', $checkuserinfamily['id'])->update('groupsMembers')) {

				echo json_encode([
					'status' => 1,
					'message' => 'family left'
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

	public function deleteFamily()
	{

		if ($this->input->post()) {

			$checkUser = $this->db->get_where('users', ['id' => $this->input->post('captainId')])->row_array();

			if (empty($checkUser)) {
				echo json_encode([
					'status' => 0,
					'message' => 'inavlid captainId'
				]);
				exit;
			}

			$checkCaptainFamily = $this->db->get_where('groups', ['userId' => $this->input->post('captainId')])->row_array();

			if (empty($checkCaptainFamily)) {
				echo json_encode([
					'status' => 0,
					'message' => 'user not captain'
				]);
				exit;
			}

			$familyId = $checkCaptainFamily['id'];

			if ($this->db->delete('groups', ['id' => $familyId]) && $this->db->delete('groupsMembers', ['familyId' => $familyId]) && $this->db->set(['positionType' => '0', 'family' => '0'])->where('family', $familyId)->update('users')) {
				echo json_encode([
					'status' => 1,
					'message' => 'family deleted'
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

	public function updateFamilySettings()
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

			if ($checkUser['family'] == NULL || empty($checkUser['family'])) {
				echo json_encode([
					'status' => 0,
					'messsage' => 'user not in any family'
				]);
				exit;
			}

			if ($checkUser['positionType'] != '1') {
				echo json_encode([
					'status' => 0,
					'message' => 'user not a captain'
				]);
				exit;
			}

			$familyId = $checkUser['family'];

			$data = $this->input->post();

			if ($this->db->set($data)->where('id', $familyId)->update('groups')) {

				$get = $this->db->get_where('groups', ['id' => $familyId])->row_array();

				echo json_encode([
					'status' => 1,
					'message' => 'data updated',
					'details' => $get
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

	public function getTitles()
	{
		$get = $this->db->get('userTitles')->result_array();

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

	public function assignTitle()
	{
		if ($this->input->post()) {

			if ($this->input->post('assignerId') == $this->input->post('userId')) {
				echo json_encode([
					'status' => 0,
					'message' => 'assignerId and userId cannot be same'
				]);
				exit;
			}

			$checkCaptain = $this->db->get_where('groupsMembers', ['userId' => $this->input->post('assignerId')])->row_array();

			if ($checkCaptain['positionType'] == 3 || $checkCaptain['positionType'] == 4) {
				echo json_encode([
					'status' => 0,
					'message' => 'enter captain or co-captain'
				]);
				exit;
			}

			$familyId = $checkCaptain['familyId'];
			$familyLevel = $this->db->select('familyLevel')->from('groups')->where('id', $familyId)->get()->row_array();

			$checkUser = $this->db->get_where('groupsMembers', ['userId' => $this->input->post('userId'), 'familyId' => $familyId, 'memberStatus' => '1'])->row_array();
			if (empty($checkUser)) {
				echo json_encode([
					'status' => 0,
					'message' => 'user not in same family'
				]);
				exit;
			}

			$checkRole = $this->db->get_where('userTitles', ['id' => $this->input->post('titleId')])->row_array();

			if (empty($checkRole)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid title id'
				]);
				exit;
			}

			$getUserTitleLevels = $this->db->get_where('userTitleLevels', ['level' => $familyLevel['familyLevel']])->row_array();

			$mainVocalist = $getUserTitleLevels['mainVocalist'];
			$vocalist = $getUserTitleLevels['vocalist'];
			$risingStar = $getUserTitleLevels['risingStar'];

			if ($this->input->post('titleId') == 1) {

				$countMV = $this->db->get_where('groupsMembers', ['familyId' => $familyId, 'memberStatus' => '1', 'title' => '1'])->num_rows();
				// print_r($countMV);exit;
				if ($countMV >= $mainVocalist) {

					echo json_encode([
						'status' => 0,
						'message' => 'this family can not have more than ' . $mainVocalist . ' Main Vocalist'
					]);
					exit;
				} else {
					if ($this->db->set('title', 1)->where(['userId' => $this->input->post('userId'), 'familyId' => $familyId, 'memberStatus' => '1'])->update('groupsMembers')) {
						echo json_encode([
							'status' => 1,
							'message' => 'title added'
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
			} else if ($this->input->post('titleId') == 2) {

				$countV = $this->db->get_where('groupsMembers', ['familyId' => $familyId, 'memberStatus' => '1', 'title' => '2'])->num_rows();

				if ($countV >= $vocalist) {

					echo json_encode([
						'status' => 0,
						'message' => 'this family can not have more than ' . $vocalist . ' Vocalist'
					]);
					exit;
				}

				if ($this->db->set('title', '2')->where(['userId' => $this->input->post('userId'), 'familyId' => $familyId, 'memberStatus' => '1'])->update('groupsMembers')) {
					echo json_encode([
						'status' => 1,
						'message' => 'title added'
					]);
					exit;
				} else {
					echo json_encode([
						'status' => 0,
						'message' => 'tech error'
					]);
					exit;
				}
			} else if ($this->input->post('titleId') == 3) {


				$countRS = $this->db->get_where('groupsMembers', ['familyId' => $familyId, 'memberStatus' => '1', 'title' => '3'])->num_rows();

				if ($countRS >= $risingStar) {

					echo json_encode([
						'status' => 0,
						'message' => 'this family can not have more than ' . $risingStar . ' Rising Star'
					]);
					exit;
				}

				if ($this->db->set('title', '3')->where(['userId' => $this->input->post('userId'), 'familyId' => $familyId, 'memberStatus' => '1'])->update('groupsMembers')) {
					echo json_encode([
						'status' => 1,
						'message' => 'title added'
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
					'message' => 'prince, princess and boss con not be assigned manually'
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

	public function kick()
	{
		if ($this->input->post()) {

			if ($this->input->post('kicker') == $this->input->post('tobeKicked')) {
				echo json_encode([
					'status' => 0,
					'message' => 'kicker id and tobeKicked id can not be same'
				]);
				exit;
			}

			$checkKicker = $this->db->get_where('groupsMembers', ['userId' => $this->input->post('kicker')])->row_array();

			if (empty($checkKicker)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid Kicker'
				]);
				exit;
			}

			if ($checkKicker['positionType'] == 3 || $checkKicker['positionType'] == 4) {
				echo json_encode([
					'status' => 0,
					'message' => 'kicker should be captain or co-captain'
				]);
				exit;
			}

			$familyId = $checkKicker['familyId'];

			$checkToBeKicked = $this->db->get_where('groupsMembers', ['userId' => $this->input->post('tobeKicked'), 'familyId' => $familyId])->row_array();

			if (empty($checkToBeKicked)) {
				echo json_encode([
					'status' => 0,
					'message' => 'inavlid toBeKicked id'
				]);
				exit;
			}

			if ($checkToBeKicked['positionType'] == '1') {
				echo json_encode([
					'status' => 0,
					'message' => 'captain can not be kicked'
				]);
				exit;
			}

			if ($checkToBeKicked['memberStatus'] == '5') {
				echo json_encode([
					'status' => 0,
					'message' => 'user already kicked'
				]);
				exit;
			}

			if ($this->db->set(['memberStatus' => '5', 'statusDate' => date('Y-m-d H:i:s'), 'acceptUserId' => $this->input->post('kicker')])->where('userId', $this->input->post('tobeKicked'))->update('groupsMembers') && $this->db->set(['positionType' => 0, 'family' => 0])->where('id', $this->input->post('tobeKicked'))->update('users')) {
				// print_r($this->db->last_query());exit;

				echo json_encode([
					'status' => 1,
					'mesaage' => 'user has been kicked from family'
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


	public function getFamilyTitleLevelInfo()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {

			$get = $this->db->get('userTitleLevels')->result_array();

			if (empty($get)) {
				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'No Content'
				]);
				exit;
			}

			echo json_encode([
				'status' => 200,
				'message' => 'Ok, Data Found',
				'data' => $get
			]);
			exit;
		} else {
			http_response_code(405);
			echo json_encode([
				'status' => 405,
				'message' => 'Method Not Allowed'
			]);
			exit;
		}
	}

	public function getFamilyTitles()
	{

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$checkfamily = $this->db->get_where('groups', ['id' => $this->input->post('familyId')])->row_array();

			if (empty($checkfamily)) {
				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'No Family Found'
				]);
				exit;
			}

			$users = $this->db->get_where('groupsMembers', ['familyId' => $this->input->post('familyId'), 'memberStatus' => '1', 'title !=' => '0'])->result_array();

			if (empty($users)) {
				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'No Title user Found'
				]);
				exit;
			}

			$u = [];
			foreach ($users as $user) {
				// print_r($user['title']);exit;
				$get = $this->db->select('title')->from('userTitles')->where('id', $user['title'])->get()->row_array();

				$user['title'] = $get['title'];
				$u[] = $user;
			}

			if (empty($u)) {
				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'No Content'
				]);
				exit;
			}

			http_response_code(200);
			echo json_encode([
				'status' => 200,
				'message' => 'ok, data found',
				'data' => $u
			]);
			exit;

			// print_r($u);

		} else {

			http_response_code(405);
			echo json_encode([
				'status' => 405,
				'message' => 'Method Not Allowed'
			]);
			exit;
		}
	}

	public function getFamilyUserPosts()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			// print_r($_SERVER);exit;

			$checkFamilyId = $this->db->get_where('groups', ['id' => $this->input->post('familyId')])->row_array();

			if (empty($checkFamilyId)) {

				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'No Content'
				]);
				exit;
			}

			$getUsers = $this->db->get_where('groupsMembers', ['familyId' => $this->input->post('familyId'), 'memberStatus' => '1'])->result_array();

			if (empty($getUsers)) {
				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'No content'
				]);
				exit;
			}


			$final = [];
			foreach ($getUsers as $user) {

				$post = $this->db->get_where('user_UploadPost', ['userId' => $user['userId'], 'type' => " "])->result_array();
				if (!!$post) {

					foreach ($post as $posts) {

						$posts['following'] = false;
						if ($this->db->get_where('userFollow', ['userId' => $posts['userId'], 'followingUserId' => $this->input->post('userId')])) {
							$posts['following'] = true;
						}
						$posts['userdetails'] = $this->db->get_where('users', ['id' => $posts['userId']])->row_array();
						$posts['postimage'] = base_url() . $posts['postimage'];
						// print_r($posts);exit;
						$final[] = $posts;
					}
				}
			}

			if (empty($final)) {
				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'No Content, No Data Found'
				]);
				exit;
			}

			http_response_code(200);
			shuffle($final);
			echo json_encode([
				'status' => 200,
				'message' => 'Ok, data found',
				'data' => $final
			]);
			exit;
		} else {
			http_response_code(405);
			echo json_encode([
				'status' => 405,
				'message' => 'method not allowed'
			]);
			exit;
		}
	}

	public function getCapAndCoLive()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$checkFamily = $this->db->get_where('groups', ['id' => $this->input->post('familyId')])->row_array();

			if (empty($checkFamily)) {

				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'No Family Found'
				]);
				exit;
			}

			$familyId = $this->input->post('familyId');
			$where = "familyId = " . $familyId . " AND memberStatus = '1' AND positionType = '1' OR positionType = '2'";
			$getCap = $this->db->get_where('groupsMembers', $where)->result_array();

			if (empty($getCap)) {

				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'No Data Found'
				]);
				exit;
			}

			$final = [];
			foreach ($getCap as $cap) {
				// print_r($cap);
				$title = $this->db->select('title')->from('userTitles')->where('id', $cap['title'])->get()->row_array();
				$cap['title'] = $title['title'];
				$position = $this->db->select('position')->from('groupPositions')->where('id', $cap['positionType'])->get()->row_array();
				$cap['positionType'] = $position['position'];
				// $get = $this->db->get_where('userLive', ['hostType' => '3', 'userId' => $cap['userId'], 'status' => 'live'])->order_by('id', 'desc')->row_array();
				$cap['userLive'] = $this->db->select('users.*, userLive.*')
					->from('userLive')
					->join('users', 'users.id = userLive.userId', 'left')
					->where(['hostType' => '3', 'userId' => $cap['userId'], 'userLive.status' => 'live'])
					->order_by('userLive.id', 'desc')
					->get()->row_array();

				if (!!$cap['userLive']) {

					$final[] = $cap;
				}
			}

			if (empty($final)) {
				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'No Live Found'
				]);
				exit;
			}

			http_response_code(200);
			echo json_encode([
				'status' => 200,
				'message' => 'Data Found',
				'data' => $final
			]);
			exit;
		} else {
			http_response_code(405);
			echo json_encode([
				'status' => 405,
				'message' => 'method not allowed'
			]);
			exit;
		}
	}


	public function getUserLiveFamily()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$checkFamily = $this->db->get_where('groups', ['id' => $this->input->post('familyId')])->row_array();

			if (empty($checkFamily)) {
				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'No Family Found'
				]);
				exit;
			}

			$getuser = $this->db->get_where('groupsMembers', ['familyId' => $this->input->post('familyId'), 'memberStatus' => '1'])->result_array();

			$final = [];
			foreach ($getuser as $user) {

				$title = $this->db->select('title')->from('userTitles')->where('id', $user['title'])->get()->row_array();
				$user['title'] = $title['title'];
				$position = $this->db->select('position')->from('groupPositions')->where('id', $user['positionType'])->get()->row_array();
				$user['positionType'] = $position['position'];

				$user['userLive'] = $this->db->select('users.*, userLive.*')
					->from('userLive')
					->join('users', 'users.id = userLive.userId', 'left')
					->where(['hostType' => '1', 'userId' => $user['userId'], 'userLive.status' => 'live'])
					->order_by('userLive.id', 'desc')
					->get()->row_array();

				if (!!$user['userLive']) {
					$final[] = $user;
				}
			}

			if (empty($final)) {
				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'No Live Found'
				]);
				exit;
			}

			http_response_code(200);
			echo json_encode([
				'status' => 200,
				'message' => 'Data Found',
				'data' => $final
			]);
			exit;
		} else {
			http_response_code(405);
			json_encode([
				'status' => 405,
				'message' => 'Method Not Allowed'
			]);
			exit;
		}
	}

	public function topRecieverInFamily()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$checkFamily = $this->db->get_where('groups', ['id' => $this->input->post('familyId')])->row_array();
			// print_r($checkFamily);exit;
			if (empty($checkFamily)) {
				http_response_code(204);
				exit;
			}

			$getuser = $this->db->get_where('groupsMembers', ['familyId' => $this->input->post('familyId'), 'memberStatus' => '1'])->result_array();

			if (empty($getuser)) {
				http_response_code(204);
				exit;
			}

			$final = [];
			foreach ($getuser as $user) {

				$getData = $this->db->select_sum('userGiftHistory.coin')
					->select('users.username, users.image, users.id')
					->from('userGiftHistory')
					->join('users', 'users.id = userGiftHistory.giftUserId', 'left')
					->where('userGiftHistory.created >=', $user['date'])
					->where('userGiftHistory.liveId !=', '0')
					->where('userGiftHistory.giftUserId', $user['userId'])
					->get()->row_array();

				if (!!$getData['coin'] || $getData['coin'] != null) {

					$getData['image'] = base_url() . $getData['image'];

					$final[] = $getData;
				}
			}

			if (empty($final)) {
				http_response_code(204);
				exit;
			}


			rsort($final);
			http_response_code(200);
			echo json_encode([
				'status' => 200,
				'message' => 'Data Found',
				'data' => $final
			]);
			exit;
		} else {
			http_response_code(405);
		}
	}

	public function removeTitle()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			if ($this->input->post('removerId') == $this->input->post('userId')) {
				http_response_code(304);
				echo json_encode([
					'status' => 304,
					'message' => 'both id can not be same'
				]);
				exit;
			}

			$checkRemover = $this->db->get_where('groupsMembers', ['userId' => $this->input->post('removerId')])->row_array();

			$checkMember = $this->db->get_where('groupsMembers', ['familyId' => $checkRemover['familyId'], 'userId' => $this->input->post('userId'), 'memberStatus' => '1'])->row_array();

			if (empty($checkMember)) {
				http_response_code(204);
				exit;
			}

			if (empty($checkRemover)) {
				http_response_code(204);
				exit;
			} else {
				if ($checkRemover['positionType'] == '3' || $checkRemover['positionType'] == '4') {
					http_response_code(304);
					echo json_encode([
						'status' => 304,
						'message' => 'remover should be captain or co captain'
					]);
					exit;
				}

				if ($checkRemover['positionType'] == '2' && $checkMember['positionType'] == '2' || $checkMember['positionType'] == '1') {
					http_response_code(304);
					echo json_encode([
						'status' => 304,
						'message' => 'can not remove for same or higher position member'
					]);
					exit;
				}
			}



			if ($this->db->set('title', '0')->where(['familyId' => $checkRemover['familyId'], 'userId' => $this->input->post('userId'), 'memberStatus' => '1'])->update('groupsMembers')) {
				// print_r($this->db->last_query());exit;
				http_response_code(200);
				echo json_encode([
					'status' => 200,
					'message' => 'title removed'
				]);
				exit;
			} else {
				http_response_code(304);
				echo json_encode([
					'status' => 304,
					'message' => 'Not Modified'
				]);
			}
		} else {
			http_response_code(405);
		}
	}

	public function topGifterInFamily()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$checkFamily = $this->db->get_where('groups', ['id' => $this->input->post('familyId')])->row_array();
			// print_r($checkFamily);exit;
			if (empty($checkFamily)) {
				http_response_code(204);
				exit;
			}

			$getuser = $this->db->get_where('groupsMembers', ['familyId' => $this->input->post('familyId'), 'memberStatus' => '1'])->result_array();

			if (empty($getuser)) {
				http_response_code(204);
				exit;
			}

			$final = [];
			foreach ($getuser as $user) {

				$getData = $this->db->select_sum('userGiftHistory.coin')
					->select('users.username, users.image, users.id')
					->from('userGiftHistory')
					->join('users', 'users.id = userGiftHistory.userId', 'left')
					->where('userGiftHistory.created >=', $user['date'])
					->where('userGiftHistory.liveId !=', '0')
					->where('userGiftHistory.userId', $user['userId'])
					->get()->row_array();

				if (!!$getData['coin'] || $getData['coin'] != null) {

					$getData['image'] = base_url() . $getData['image'];

					$final[] = $getData;
				}
			}

			if (empty($final)) {
				http_response_code(204);
				exit;
			}


			rsort($final);
			http_response_code(200);
			echo json_encode([
				'status' => 200,
				'message' => 'Data Found',
				'data' => $final
			]);
			exit;
		} else {
			http_response_code(405);
		}
	}

	public function getFamilyIncome()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$checkFamily = $this->db->get_where('groups', ['id' => $this->input->post('familyId')])->row_array();
			// print_r($checkFamily);exit;
			if (empty($checkFamily)) {
				http_response_code(204);
				exit;
			}

			$getuser = $this->db->get_where('groupsMembers', ['familyId' => $this->input->post('familyId'), 'memberStatus' => '1'])->result_array();

			if (empty($getuser)) {
				http_response_code(204);
				exit;
			}

			$final = [];
			$totalIncome = 0;

			foreach ($getuser as $user) {

				// print_r($user);

				$getData = $this->db->select('userGiftHistory.created, userGiftHistory.giftUserId, userGiftHistory.coin, users.username, users.image, users.id')
					->from('userGiftHistory')
					->join('users', 'users.id = userGiftHistory.giftUserId', 'left')
					->where('userGiftHistory.created >=', $user['date'])
					->where('userGiftHistory.liveId !=', '0')
					->where('userGiftHistory.giftUserId', $user['userId'])
					->get()->result_array();

				// print_r($getData);

				if (!empty($getData)) {

					foreach ($getData as $data) {
						$data['image'] = base_url() . $data['image'];
						$totalIncome += $data['coin'];
						$final[] = $data;
					}
				}
			}

			// print_r(json_encode($final));exit;

			if (empty($final)) {
				http_response_code(204);
				exit;
			}


			rsort($final);

			if (!empty($this->input->post('dateFrom'))) {

				$month = [];
				foreach ($final as $finale) {

					if ($this->input->post('dateFrom') <= $finale['created'] && $this->input->post('dateTo') >= $finale['created']) {
						$month[] = $finale;
					}
				}

				if (empty($month)) {
					http_response_code(204);
					exit;
				}
			}



			// print_r($totalIncome);exit;
			$captainIncome = $totalIncome / 2;
			$cocaptainIncome = (20 / 100) * $totalIncome;

			$last['totalIncome'] = $totalIncome;
			$last['captainIncome'] = $captainIncome;
			$last['coCaptainIncome'] = $cocaptainIncome;
			if (!empty($this->input->post('dateFrom'))) {
				$last['lost'] = $month;
			} else {
				$last['lost'] = $final;
			}
			http_response_code(200);
			echo json_encode([
				'status' => 200,
				'message' => 'Data Found',
				'data' => $last
			]);
			exit;
		} else {
			http_response_code(405);
		}
	}

	public function addHighLight()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$checkFamily = $this->db->get_where('groups', ['id' => $this->input->post('familyId')])->row_array();

			if (empty($checkFamily)) {
				http_response_code(204);
				exit;
			}

			$data['familyId'] = $this->input->post('familyId');

			$userId = $this->db->get_where('groupsMembers', ['familyId' => $this->input->post('familyId'), 'userId' => $this->input->post('userId'), 'memberStatus' => '1'])->row_array();

			if (empty($userId)) {
				http_response_code(204);
				exit;
			}

			if ($userId['positionType'] == '3' || $userId['positionType'] == '4') {
				http_response_code(304);
			}

			$data['userId'] = $this->input->post('userId');

			$checkHighlight = $this->db->get_where('familyHighlights', ['userId' => $this->input->post('userId'), 'familyId' => $this->input->post('familyId'), 'postId' => $this->input->post('postId')])->row_array();

			if (!!$checkHighlight) {
				http_response_code(304);
				exit;
			}

			$getUser = $this->db->get_where('groupsMembers', ['familyId' => $this->input->post('familyId'), 'memberStatus' => '1'])->result_array();

			if (empty($getUser)) {
				http_response_code(204);
				exit;
			}

			$data['postId'] = '';
			foreach ($getUser as $user) {
				$getPost = $this->db->get_where('user_UploadPost', ['id' => $this->input->post('postId'), 'userId' => $user['userId']])->row_array();

				if (!!$getPost) {
					$data['postId'] = $this->input->post('postId');
				}
			}

			if (empty($data['postId'])) {
				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'invalid postId'
				]);
				exit;
				exit;
			}

			$data['date'] = date('Y-m-d');

			if ($this->db->insert('familyHighlights', $data)) {

				http_response_code(200);
				echo json_encode([
					'status' => 200,
					'message' => 'data inserted'
				]);
				exit;
			} else {
				http_response_code(304);
				exit;
			}
		} else {
			http_response_code(405);
			exit;
		}
	}

	public function getHighlights()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$checkFamily = $this->db->get_where('groups', ['id' => $this->input->post('familyId')])->row_array();
			if (empty($checkFamily)) {
				http_response_code(204);
			}

			$getHiglights = $this->db->get_where('familyHighlights', ['familyId' => $this->input->post('familyId')])->result_array();


			if (empty($getHiglights)) {
				http_response_code(204);
			}

			$final = [];
			foreach ($getHiglights as $highlights) {

				$getPost = $this->db->get_where('user_UploadPost', ['id' => $highlights['postId']])->row_array();
				$getPost['postimage'] = base_url() . $getPost['postimage'];
				$getPost['userDetails'] = $this->db->get_where('users', ['id' => $getPost['userId']])->row_array();

				$final[] = $getPost;
			}

			if (empty($final)) {
				http_response_code(204);
				exit;
			}

			http_response_code(200);
			echo json_encode([
				'status' => 200,
				'message' => 'data found',
				'data' => $final
			]);
			exit;
		} else {
			http_response_code(405);
		}
	}

	public function getNonFamilyUsers()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {

			$get = $this->db->get_where('users', ['positionType' => '0'])->result_array();

			if (empty($get)) {
				http_response_code(204);
				exit;
			}

			http_response_code(200);
			echo json_encode([
				'status' => 200,
				'message' => 'data found',
				'data' => $get
			]);
			exit;
		} else {
			http_response_code(405);
		}
	}

	public function sendInvite()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$checkCaptain = $this->db->get_where('groups', ['userId' => $this->input->post('captainId'), 'id' => $this->input->post('familyId')])->row_array();

			if (empty($checkCaptain)) {
				http_response_code(204);
				exit;
			}

			$joinSettings = $checkCaptain['joinsettings'];

			$checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

			if (empty($checkUser)) {
				http_response_code(304);
				// echo json_encode([
				// 	'status' => 304,
				// 	'message' => 'inavlid userId'
				// ]);
				exit;
			}

			$userLevel = $checkUser['leval'];

			$count = $this->db->get_where('user_UploadPost', ['userId' => $this->input->post('userId'), 'type' => ''])->num_rows();

			$checkUserMember = $this->db->get_where('groupsMembers', ['userId' => $this->input->post('userId'), 'memberStatus' => '1'])->row_array();

			if (!!$checkUserMember) {
				http_response_code(304);
				// echo json_encode([
				// 	'status' => 304,
				// 	'message' => 'user already in family'
				// ]);
				// exit;
			}

			$checkinvitation = $this->db->get_where('familyInvitation', ['userId' => $this->input->post('userId'), 'familyId' => $this->input->post('familyId')])->row_array();

			if (!!$checkinvitation) {
				http_response_code(304);
				exit;
				// echo json_encode([
				// 	'status' => 304,
				// 	'message' => 'invitation already sent'
				// ]);exit;
			}

			// print_r($joinSettings);echo "...";
			// print_r($userLevel);echo "...";
			// print_r($count);exit;

			if ($joinSettings == 3) {

				if ($userLevel < 5) {
					http_response_code(304);
					exit;
					// echo json_encode([
					// 	'status' => 304,
					// 	'messsage' => 'user level 5 required'
					// ]);exit;
				}
			} else if ($joinSettings == 4) {

				if ($userLevel < 20) {
					http_response_code(304);
					exit;
					// echo json_encode([
					// 	'status' => 304,
					// 	'message' => 'user level 20 required'
					// ]);exit;
				}
			} else if ($joinSettings == 5) {

				if ($count < 1) {
					http_response_code(304);
					exit;
					// echo json_encode([
					// 	'status' => 304,
					// 	'message' => 'minimum 1 post required'
					// ]);exit;
				}
			} else if ($joinSettings == 6) {

				if ($count < 10) {
					http_response_code(304);
					exit;
					// echo json_encode([
					// 	'status' => 304,
					// 	'message' => 'minimum 10 post required'
					// ]);exit;
				}
			}


			$data['familyId'] = $this->input->post('familyId');
			$data['userId'] = $this->input->post('userId');
			$data['date'] = date('Y-m-d');

			if ($this->db->insert('familyInvitation', $data)) {

				http_response_code(201);
				echo json_encode([
					'status' => 201,
					'message' => 'request sent successfuly'
				]);
				exit;
			} else {
				http_response_code(304);
				exit;
			}
		} else {
			http_response_code(405);
			exit;
		}
	}

	public function getInviteRequest()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

			if (empty($checkuser)) {
				http_response_code(304);
				exit;
			}

			$getInvitation = $this->db->get_where('familyInvitation', ['userId' => $this->input->post('userId')])->result_array();

			if (empty($getInvitation)) {
				http_response_code(304);
			}

			$final = [];
			foreach ($getInvitation as $family) {

				$family = $this->db->get_where('groups', ['id' => $family['familyId']])->row_array();
				$family['image'] = base_url() . $family['image'];
				$final[] = $family;
			}

			http_response_code(200);
			echo json_encode([
				'status' => 200,
				'message' => 'data found',
				'data' => $final
			]);
			exit;
		} else {

			http_response_code(405);
			exit;
		}
	}

	public function dailyTasks()
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

			$userId = $user['id'];
			$date = date('Y-m-d');

			// all task category
			$dailyTask = $this->db->get('dailyTaskCategory')->result_array();

			$final = [];
			$allTasks = [];
			foreach ($dailyTask as $tasks) {

				// print_r($task);exit;

				// getting individual tasks
				$get = $this->db->get_where('dailyTasks', ['taskId' => $tasks['id']])->result_array();

				foreach ($get as $gets) {

					// making column name for dailyTaskUser table
					$task = 'task' . $gets['id'];

					// checking task is completed or not
					$complete = $this->db->get_where('dailyTaskUser', ['userId' => $userId, 'date' => $date, $task => '1'])->row_array();

					if (empty($complete)) {

						$gets['completed'] = false;
						$gets['collected'] = false;
					} else {

						$gets['completed'] = true;
						$collect = 'collect_' . $task;
						if ($complete[$collect] == '1') {
							$gets['collected'] = true;
						} else {
							$gets['collected'] = false;
						}
					}

					$allTask[] = $gets;
				}

				$tasks['taskDetails'] = $allTask;
				unset($allTask);

				$final[] = $tasks;
			}

			if (empty($final)) {
				http_response_code(204);
				echo json_encode([
					'status' => 204,
					'message' => 'No Data Found'
				]);
				exit;
			}

			http_response_code(200);
			echo json_encode([
				'status' => 200,
				'message' => 'Data Found',
				'user' => $user,
				'data' => $final
			]);
			exit;
		} else {
			http_response_code(405);
			exit;
		}
	}

	public function markTask()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			if ($this->input->post('type') < '1' || $this->input->post('type') > '13') {
				echo json_encode([
					'status' => 0,
					'message' => 'Invalid type'
				]);
				exit;
			}

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (empty($user)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);
				exit;
			}

			$updata['silverCoins'] = $user['silverCoins'];
			$updata['diamond'] = $user['diamond'];
			$updata['exp'] = $user['exp'];
			$updata['coins'] = $user['coins'];
			// vip pending


			if ($this->input->post('type') == '1') {

				$updata['silverCoins'] += 200;
				$updata['exp'] += 1;

				// $data['task1'] = 1;
				$data['collect_task1'] = 1;
			} else if ($this->input->post('type') == '2') {

				// $data['task2'] = 1;
				$data['collect_task2'] = 1;
			} else if ($this->input->post('type') == '3') {

				$updata['silverCoins'] += 400;
				$updata['exp'] += 2;

				// $data['task3'] = 1;
				$data['collect_task3'] = 1;
			} else if ($this->input->post('type') == '4') {

				// $data['task4'] = 1;
				$data['collect_task4'] = 1;
			} else if ($this->input->post('type') == '5') {

				$updata['diamond'] += 5;
				$updata['exp'] += 32;

				// $data['task5'] = 1;
				$data['collect_task5'] = 1;
			} else if ($this->input->post('type') == '6') {

				$updata['diamond'] += 10;
				$updata['exp'] += 32;

				// $data['task6'] = 1;
				$data['collect_task6'] = 1;
			} else if ($this->input->post('type') == '7') {

				$updata['diamond'] += 20;
				$updata['exp'] += 32;

				// $data['task7'] = 1;
				$data['collect_task7'] = 1;
			} else if ($this->input->post('type') == '8') {

				$updata['exp'] += 5;
				$updata['coins'] += 16;

				// $data['task8'] = 1;
				$data['collect_task8'] = 1;
			} else if ($this->input->post('type') == '9') {

				$updata['exp'] += 3;
				$updata['coins'] += 16;

				// $data['task9'] = 1;
				$data['collect_task9'] = 1;
			} else if ($this->input->post('type') == '10') {

				$updata['diamond'] += 10;
				$updata['exp'] += 16;

				// $data['task10'] = 1;
				$data['collect_task10'] = 1;
			} else if ($this->input->post('type') == '11') {

				$updata['diamond'] += 50;
				$updata['exp'] += 16;
				$updata['coins'] += 20;

				// $data['task11'] = 1;
				$data['collect_task11'] = 1;
			} else if ($this->input->post('type') == '12') {

				$updata['exp'] += 16;

				// $data['task12'] = 1;
				$data['collect_task12'] = 1;
			} else {

				$updata['exp'] += 16;

				// $data['task13'] = 1;
				$data['collect_task13'] = 1;
			}

			$checkTask = $this->db->get_where('dailyTaskUser', ['userId' => $this->input->post('userId'), 'date' => date('Y-m-d')])->row_array();

			if (empty($checkTask)) {

				$data['userId'] = $this->input->post('userId');
				$data['date'] = date('Y-m-d');

				$this->db->set($updata)->where('id', $this->input->post('userId'))->update('users');
				$this->db->insert('dailyTaskUser', $data);

				// insert
			} else {

				$this->db->set($updata)->where('id', $this->input->post('userId'))->update('users');
				$this->db->set($data)->where(['userId' => $this->input->post('userId'), 'date' => date('Y-m-d')])->update('dailyTaskUser');

				// update
			}

			echo json_encode([
				'status' => 1,
				'message' => 'Task Completed!'
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

	public function mark_daily_tasks(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			if($this->input->post('type') < 1 || $this->input->post('type') > 13){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid type'
				]);exit;
			}

			$data['userId'] = $user['id'];
			$data['date'] = date('Y-m-d');
			$task = 'task' . $this->input->post('type');
			$data[$task] = '1';

			$checkTask = $this->db->get_where('dailyTaskUser', $data)->row_array();
			if(empty($checkTask)){
				// insert
				$this->db->insert('dailyTaskUser', $data);
				echo json_encode([
					'status' => 1,
					'message' => 'dask completed'
				]);exit;
			}else{
				// update 
				$this->db->set($data)->where('id', $checkTask['id'])->update('dailyTaskUser');
				echo json_encode([
					'status' => 1,
					'message' => 'dask completed'
				]);exit;
			}

		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'Method not allowed'
			]);exit;
		}
	}

	public function dailyLogin()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if (empty($user)) {
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);
				exit;
			}

			$data['date'] = date('Y-m-d');
			$login = $this->db->get_where('dailyLogin', ['userId' => $user['id'], 'date' => $data['date']])->row_array();
			if (empty($login)) {
				$data['userId'] = $user['id'];
				$data['time'] = date('H:i:s');

				if ($this->db->insert('dailyLogin', $data)) {

					$this->daily_login_reward($user['id']);

					echo json_encode([
						'status' => 1,
						'message' => 'user login done'
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
					'message' => 'logged in for day'
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

	protected function daily_login_reward($userId){

		$user = $this->db->get_where('users', ['id' => $userId])->row_array();
		$data['silverCoins'] = $user['silverCoins'];
		$data['silverCoins'] += 200;
		$this->db->Set($data)->where('id', $userId)->update('users');
		return true;

	}

	public function loginLog()
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

			$date = date('Y-m-d');
			$toDate = date('Y-m-d', strtotime("-1 week"));


			$where = "date BETWEEN '" . $toDate . "' AND '" . $date . "'";
			$get = $this->db->select('*')
				->from('dailyLogin')
				->where('userId', $this->input->post('userId'))
				->where($where)
				->order_by('id', 'desc')
				->limit(7)
				->get()->result_array();

			// print_r($this->db->last_query());
			// print_r($get);exit;

			if (empty($get)) {
				echo json_encode([
					'status' => 0,
					'message' => 'user not login'
				]);
				exit;
			} else {
				echo json_encode([
					'status' => 1,
					'message' => 'login log',
					'details' => $get
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

	public function spin_count(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}



			$week = ['seven' => date('Y-m-d', strtotime("-6 days")), 'six' => date('Y-m-d', strtotime("-5 days")), 'five' => date('Y-m-d', strtotime("-4 days")), 'four' => date('Y-m-d', strtotime("-3 days")), 'three' => date('Y-m-d', strtotime("-2 days")), 'two' => date('Y-m-d', strtotime("-1 days")), 'one' => date('Y-m-d')];
			// print_r($week);echo "...";

			$last = [];
			foreach($week as $days){
				
				$get = $this->db->get_where('dailyLogin', ['userId' => $user['id'], 'date' => $days])->row_array();
				if(!!$get){
					$last[] = $get;
				}
			}

			// print_r($last);exit;

			if(count($last) >= '7'){

				$check_count = $this->db->get_where('daily_login_reward_count', ['userId' => $user['id'], 'date' => date('Y-m-d')])->num_rows();

				$check_count = $this->db->get_where('daily_login_reward_count', ['userId' => $user['id'], 'date' => date('Y-m-d')])->num_rows();

				$count = 3 - $check_count ? : 0;

				echo json_encode([
					'status' => 1,
					'message' => 'data found',
					'details' => $count
				]);exit;

			}else{
				echo json_encode([
					'status' => 0,
					'message' => 'seven days streak not found'
				]);exit;
			}


			

		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'Method not allowed'
			]);exit;
		}
	}

	public function daily_login_win(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$week = ['seven' => date('Y-m-d', strtotime("-6 days")), 'six' => date('Y-m-d', strtotime("-5 days")), 'five' => date('Y-m-d', strtotime("-4 days")), 'four' => date('Y-m-d', strtotime("-3 days")), 'three' => date('Y-m-d', strtotime("-2 days")), 'two' => date('Y-m-d', strtotime("-1 days")), 'one' => date('Y-m-d')];
			// print_r($week);echo "...";

			$last = [];
			foreach($week as $days){
				
				$get = $this->db->get_where('dailyLogin', ['userId' => $user['id'], 'date' => $days])->row_array();
				if(!!$get){
					$last[] = $get;
				}
			}

			// print_r($last);exit;

			if(count($last) >= '7'){

				$check_count = $this->db->get_where('daily_login_reward_count', ['userId' => $user['id'], 'date' => date('Y-m-d')])->num_rows();

				if($check_count == 3){
						echo json_encode([
							'status' => 0,
							'message' => 'all spin collected for today'
						]);exit;
				}else{
					$data['userId'] = $user['id'];
					$data['date'] = date('Y-m-d');

					$this->db->insert('daily_login_reward_count', $data);

					echo json_encode([
						'status' => 1,
						'message' => 'spin hit',
						'details' => ++$check_count
					]);exit;
				}

			}else{
				echo json_encode([
					'status' => 0,
					'message' => 'seven days streak not found'
				]);exit;
			}

			// print_r();exit;

		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'Method not allowed'
			]);exit;
		}
	}



	public function get_wheel_of_fortune(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$userId = $this->input->post('userId');
			$date = date('Y-m-d');

			$get = $this->db->get_where('wheel_of_fortune', ['userId' => $userId, 'date' => $date])->row_array();
			if(empty($get)){
				echo json_encode([
					'status' => 0,
					'message' => 'no details found'
				]);exit;
			}else{
				echo json_encode([
					'status' => 1,
					'message' => 'list found',
					'details' => $get
				]);exit;
			}

		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'Method not allowed'
			]);exit;
		}
	}

	public function mark_wheel_of_fortune(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$data['userId'] = $this->input->post('userId');
			$data['date'] = date('Y-m-d');

			$started = false;
			$check = $this->db->get_where('wheel_of_fortune', ['userId' => $data['userId'], 'date' => $data['date']])->row_array();
			if(empty($check)){
				$started = true;
			}

			if($this->input->post('type') == '1'){

				$data['cover_image'] = 1;

			}else if($this->input->post('type') == '2'){

				$data['gift_to_family'] = 1;

			}else if($this->input->post('type') == '3'){

				$data['watch_live'] = 1;

			}else{
				echo json_encode([
					'status' => 0,
					'message' => 'invalid type'
				]);exit;
			}

			if($started == true){

				$this->db->insert('wheel_of_fortune', $data);
				echo json_encode([
					'status' => 1,
					'message' => 'task marked'
				]);exit;

			}else{

				$this->db->set($data)->where(['userId' => $data['userId'], 'date' => $data['date']])->update('wheel_of_fortune');
				echo json_encode([
					'status' => 1,
					'message' => 'tasks marked'
				]);exit;

			}

		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'Method not allowed'
			]);exit;
		}
	}

	public function collect_wheel_fortune(){

		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$date = date('Y-m-d');

			$checkTask = $this->db->get_where('wheel_of_fortune', ['userId' => $user['id'], 'date' => $date])->row_array();
			if(empty($checkTask)){
				echo json_encode([
					'status' => 0,
					'message' => 'no task found for current date'
				]);exit;
			}

			if($checkTask['cover_image'] == '1' && $checkTask['gift_to_family'] == '1' && $checkTask['watch_live'] == '1'){

				if($checkTask['collected'] == '3'){
					echo json_encode([
						'status' => 0,
						'message' => 'daily spin already collected'
					]);exit;
				}else{
					$data['collected'] = $checkTask['collected'];
					$data['collected'] += 1;

					$this->db->set($data)->where('id', $checkTask['id'])->update('wheel_of_fortune');
					echo json_encode([
						'status' => 1,
						'message' => 'spin wheel hit',
						'details' => $data
					]);exit;
				}

			}else{
				echo json_encode([
					'status' => 0,
					'message' => 'complete all tasks',
					'details' => $checkTask
				]);exit;
			}

		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'Method not allowed'
			]);exit;
		}

	}

	protected function check_gift_to_family($userId, $memberId){

		$date = date('Y-m-d');

		$user = $this->db->get_where('users', ['id' => $userId])->row_array();
		if(empty($user['family']) || $user['family'] == null || $user['family'] == '0'){
				return false;
		}

		$family = $this->db->get_where('groupsMembers', ['userId' => $memberId, 'familyId' => $user['family'], 'memberStatus' => '1'])->row_array();
		if(empty($family)){
			return false;
		}else{
			$get = $this->db->get_where('wheel_of_fortune', ['userId' => $userId, 'date' => $date])->row_array();
			if(empty($get)){
				$data['userId'] = $userId;
				$data['date'] = $date;
				$data['gift_to_family'] = '1';

				$this->db->insert('wheel_of_fortune', $data);
			}else{
				if($get['gift_to_family'] == '1'){
					return true;
				}else{
					$this->db->set(['gift_to_family' => '1'])->where('id', $get['id'])->update('wheel_of_fortune');
					return true;
				}
			}
		}



	}

	public function fortune_wheel_prizes(){
		if($_SERVER['REQUEST_METHOD'] === 'GET'){

			$get = $this->db->get('wheelPrizes')->result_array();
			if(empty($get)){
				echo json_encode([
					'status' => 0,
					'message' => 'no data found'
				]);exit;
			}
			echo json_encode([
				'status' => 1,
				'message' => 'data found',
				'details' => $get
			]);exit;

		}else{
			http_response_code(405);
			echo json_encode([
				'status' => 0,
				'message' => 'Method not allowed'
			]);exit;
		}
	}

	public function give_spin_wheel_prize(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			if($this->input->post('type') < 1 || $this->input->post('type') > 8){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid type'
				]);exit;
			}

			$type = $this->input->post('type');
			switch($type){
				case '1':
					// user will get vip for 3days
					break;
				
				case '2':
					// user will get x50 gold coins
					break;

				case '3':
					// user will get 1 free frame
					break;

				case '4':
					// user will get x1 gold coin
					break;

				case '5':
					$data['silverCoins'] = $user['silverCoins'];
					$data['silverCoins'] *= 150;
					break;

				case '6':
					// user will get free frame for 3 days
					break;

				case '7':
					// user will get x5 gold coins
					break;

				case '8':
					// user will get car for 3 days
					break;
			}

			if($this->db->set($data)->where('id', $user['id'])->update('users')){
				echo json_encode([
					'status' => 1,
					'message' => 'prize given',
					'details' => $data
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
				'message' => 'Method not allowed'
			]);exit;
		}
	}

	public function add_silver_coin(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			if(!$this->input->post('coins')){
				echo json_encode([
					'status' => 0,
					'message' => 'coins required'
				]);exit;
			}

			$data['silverCoins'] = $user['silverCoins'];
			$data['silverCoins'] += $this->input->post('coins');

			// print_r($data);exit;

			if($this->db->set($data)->where('id', $user['id'])->update('users')){
				echo json_encode([
					'status' => 1,
					'message' => 'coins added'
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
				'message' => 'Method not allowed'
			]);exit;
		}
	}

	public function get_top_gifter(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$today = date('Y-m-d');

			$final = [];
			if($this->input->post('type') == '1'){
				// daily

				$gifter = $this->db->select_sum('userGiftHistory.coin')
								   ->select('userGiftHistory.userId, userGiftHistory.giftUserId, userGiftHistory.liveId, users.name, users.username, users.image')
								   ->from('userGiftHistory')
								   ->join('users', 'users.id = userGiftHistory.userId', 'left')
								   ->where('liveId !=', '0')
								   ->where('created', $today)
								   ->group_by('userId')
								   ->order_by('userGiftHistory.coin', 'desc')
								   ->get()->result_array();

								   if(!empty($gifter[0]['coin'])){
									foreach($gifter as $gift){
										$gift['image'] = base_url() . $gift['image'];
										$final[] = $gift;
									}
								   }
				
			}else if($this->input->post('type') == '2'){
				// weekly
				$to_date = date('Y-m-d', strtotime('-7 days'));

				$gifter = $this->db->select_sum('userGiftHistory.coin')
									->select('userGiftHistory.userId, userGiftHistory.giftUserId, userGiftHistory.liveId, users.name, users.username, users.image')
									->from('userGiftHistory')
									->join('users', 'users.id = userGiftHistory.userId', 'left')
									->where('liveId !=', '0')
									->where('created <=', $today)
									->where('created >=', $to_date)
									->group_by('userId')
									->order_by('userGiftHistory.coin', 'desc')
									->get()->result_array();

									if(!empty($gifter[0]['coin'])){
									foreach($gifter as $gift){
										$gift['image'] = base_url() . $gift['image'];
										$final[] = $gift;
									}
									}
				
				
			}else if($this->input->post('type') == '3'){
				// monthly
				$to_date = date('Y-m-d', strtotime('-1 month'));

					$gifter = $this->db->select_sum('userGiftHistory.coin')
										->select('userGiftHistory.userId, userGiftHistory.giftUserId, userGiftHistory.liveId, users.name, users.username, users.image')
										->from('userGiftHistory')
										->join('users', 'users.id = userGiftHistory.userId', 'left')
										->where('liveId !=', '0')
										->where('created <=', $today)
										->where('created >=', $to_date)
										->group_by('userId')
										->order_by('userGiftHistory.coin', 'desc')
										->get()->result_array();

										if(!empty($gifter[0]['coin'])){
										foreach($gifter as $gift){
											$gift['image'] = base_url() . $gift['image'];
											$final[] = $gift;
										}
										}
				
				
			}else if($this->input->post('type') == '4'){
				// overall

				$gifter = $this->db->select_sum('userGiftHistory.coin')
									->select('userGiftHistory.userId, userGiftHistory.giftUserId, userGiftHistory.liveId, users.name, users.username, users.image')
									->from('userGiftHistory')
									->join('users', 'users.id = userGiftHistory.userId', 'left')
									->where('liveId !=', '0')
									->group_by('userId')
									->order_by('userGiftHistory.coin', 'desc')
									->get()->result_array();

									if(!empty($gifter[0]['coin'])){
									foreach($gifter as $gift){
										$gift['image'] = base_url() . $gift['image'];
										$final[] = $gift;
									}
									}
				
				
			}else{
				echo json_encode([
					'status' => 0,
					'message' => 'invalid type'
				]);exit;
			}

			if(empty($final)){
				echo json_encode([
					'status' => 0,
					'message' => 'no gifting found'
				]);exit;
			}

			rsort($final);
			echo json_encode([
				'status' => 1,
				'message' => 'details found',
				'details' => $final
			]);exit;



		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}

	}

	public function get_top_gifter_per_live(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$live = $this->db->get_where('userLive', ['id' => $this->input->post('liveId')])->row_array();
			if(empty($live)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid liveId'
				]);exit;
			}

			

			$gifter = $this->db->select_sum('userGiftHistory.coin')
								->select('userGiftHistory.userId, userGiftHistory.giftUserId, userGiftHistory.liveId, users.name, users.username, users.image')
								->from('userGiftHistory')
								->join('users', 'users.id = userGiftHistory.userId', 'left')
								->where('liveId', $live['id'])
								->group_by('userId')
								->order_by('userGiftHistory.coin', 'desc')
								->get()->result_array();

								if(!empty($gifter[0]['coin'])){
									foreach($gifter as $gift){
										$gift['image'] = base_url() . $gift['image'];
										$final[] = $gift;
									}
								}

								if(empty($final)){
									echo json_encode([
										'status' => 0,
										'messsage' => 'no gifting found'
									]);exit;
								}

								rsort($final);
								echo json_encode([
									'status' => 1,
									'message' => 'gifting found',
									'details' => $final
								]);exit;
			
		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}
	}

	public function get_top_gifter_per_user(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$final = [];

			$today = date('Y-m-d');

			if($this->input->post('type') == '1'){

				// daily

				$gifter = $this->db->select_sum('userGiftHistory.coin')
				->select('userGiftHistory.userId, userGiftHistory.giftUserId, userGiftHistory.liveId, users.name, users.username, users.image')
				->from('userGiftHistory')
				->join('users', 'users.id = userGiftHistory.userId', 'left')
				->where('liveId !=', '0')
				->where('userId', $user['id'])
				->where('created', $today)
				->group_by('userId')
				->order_by('userGiftHistory.coin', 'desc')
				->get()->row_array();

				if(!empty($gifter['coin'])){

						$gifter['image'] = base_url() . $gifter['image'];
						$final[] = $gifter;

				}

			}else if($this->input->post('type') == '2'){

				// weekly

				$to_date = date('Y-m-d', strtotime('-7 days'));

				$gifter = $this->db->select_sum('userGiftHistory.coin')
				->select('userGiftHistory.userId, userGiftHistory.giftUserId, userGiftHistory.liveId, users.name, users.username, users.image')
				->from('userGiftHistory')
				->join('users', 'users.id = userGiftHistory.userId', 'left')
				->where('liveId !=', '0')
				->where('userId', $user['id'])
				->where('created >=', $to_date)
				->where('created <=', $today)
				// ->group_by('userId')
				// ->order_by('userGiftHistory.coin', 'desc')
				->get()->row_array();

				if(!empty($gifter['coin'])){

						$gifter['image'] = base_url() . $gifter['image'];
						$final[] = $gifter;

				}

			}else if($this->input->post('type') == '3'){

				// monthly

				$to_date = date('Y-m-d', strtotime('-1 month'));

				$gifter = $this->db->select_sum('userGiftHistory.coin')
				->select('userGiftHistory.userId, userGiftHistory.giftUserId, userGiftHistory.liveId, users.name, users.username, users.image')
				->from('userGiftHistory')
				->join('users', 'users.id = userGiftHistory.userId', 'left')
				->where('liveId !=', '0')
				->where('userId', $user['id'])
				->where('created >=', $to_date)
				->where('created <=', $today)
				// ->group_by('userId')
				// ->order_by('userGiftHistory.coin', 'desc')
				->get()->row_array();

				if(!empty($gifter['coin'])){

						$gifter['image'] = base_url() . $gifter['image'];
						$final[] = $gifter;

				}

			}else if($this->input->post('type') == '4'){

				// overall

				$gifter = $this->db->select_sum('userGiftHistory.coin')
				->select('userGiftHistory.userId, userGiftHistory.giftUserId, userGiftHistory.liveId, users.name, users.username, users.image')
				->from('userGiftHistory')
				->join('users', 'users.id = userGiftHistory.userId', 'left')
				->where('liveId !=', '0')
				->where('userId', $user['id'])
				// ->group_by('userId')
				// ->order_by('userGiftHistory.coin', 'desc')
				->get()->row_array();

				if(!empty($gifter['coin'])){
						$gifter['image'] = base_url() . $gifter['image'];
						$final[] = $gifter;
					}
			}else{
				echo json_encode([
					'status' => 0,
					'message' => 'invalid type'
				]);exit;
			}

								if(empty($final)){
									echo json_encode([
										'status' => 0,
										'messsage' => 'no gifting found'
									]);exit;
								}

								rsort($final);
								echo json_encode([
									'status' => 1,
									'message' => 'gifting found',
									'details' => $final
								]);exit;

			
		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}
	}

	public function get_block_user_list(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$blocked_users = $this->db->get_where('blockUser', ['userId' => $user['id']])->result_array();
			if(empty($blocked_users)){
				echo json_encode([
					'status' => 0,
					'message' => 'no blocked users found'
				]);exit;
			}

			$final = [];
			foreach($blocked_users as $users){
				$users['blockUserId'] = $this->db->select('users.id, users.name, users.username, users.image')->get_where('users', ['id' => $users['blockUserId']])->row_array();
				$users['blockUserId']['image'] = base_url() . $users['blockUserId']['image'];
				$final[] = $users;
			}

			if(empty($final)){
				echo json_encode([
					'status' => 0,
					'message' => 'no data found'
				]);exit;
			}

			echo json_encode([
				'status' => 1,
				'message' => 'details  found',
				'details' => $final
			]);exit;

	
			
		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}
	}

	public function mute_user(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}

			$other_user = $this->db->get_where('users', ['id' => $this->input->post('other_user')])->row_array();
			if(empty($other_user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid other_user'
				]);exit;
			}

			if($user['id'] == $other_user['id']){
				echo json_encode([
					'status' => 0,
					'message' => 'you can not mute yourself'
				]);exit;
			}

			$check_mute = $this->db->get_where('mute_users', ['userId' => $user['id'], 'muted_userId' => $other_user['id']])->row_array();
			if(empty($check_mute)){

				$data['userId'] = $user['id'];
				$data['muted_userId'] = $other_user['id'];
				$data['date'] = date('Y-m-d');

				$this->db->insert('mute_users', $data);
				echo json_encode([
					'status' => 1,
					'message' => 'user muted'
				]);exit;

			}else{

				$this->db->delete('mute_users', ['id' => $check_mute['id']]);
				echo json_encode([
					'status' => 2,
					'message' => 'user unmuted'
				]);exit;

			}	
			
		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method not allowed'
			]);exit;
		}
	}


	public function get_muted_user_list(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			$user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
			if(empty($user)){
				echo json_encode([
					'status' => 0,
					'message' => 'invalid userId'
				]);exit;
			}


			$mute_user = $this->db->get_where('mute_users', ['userId' => $user['id']])->result_array();
			if(empty($mute_user)){
				echo json_encode([
					'status' => 0,
					'message' => 'no users muted'
				]);exit;
			}

			$final = [];
			foreach($mute_user as $user){

				$user['muted_userId'] = $this->db->select('users.name, users.username, users.image')->get_where('users', ['id' => $user['muted_userId']])->row_array();
				$user['muted_userId']['image'] = base_url() . $user['muted_userId']['image'];

				$final[] = $user;

			}

			if(empty($final)){
				echo json_encode([
					'status' => 0,
					'message' => 'no data found'
				]);exit;
			}

			echo json_encodE([
				'status' => 1,
				'message' => 'details found',
				'details' => $final
			]);exit;
			
		}else{
			echo json_encode([
				'status' => 0,
				'message' => 'method  not allowed'
			]);exit;
		}
	}





















































































	// ============== Create group Api ================

	public function createGroup()
	{

		if ($this->input->post()) {

			$checkGroup = $this->db->get_where("createGroup", ['userId' => $this->input->post("userId")])->row_array();

			if (!!$checkGroup) {

				echo json_encode([

					"success" => "0",
					"message" => "Group already created - by this user",
				]);
				exit;
			}

			$getUser = $this->db->get_where("users", ['id' => $this->input->post("userId")])->row_array();

			if (empty($getUser)) {
				echo json_encode([

					"success" => "0",
					"message" => "Invalid userId",
				]);
				exit;
			}

			$check = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("userId")])->row_array();

			if (!!$check) {

				echo json_encode([

					"success" => "0",
					"message" => "Something went wrong! - please try after sometimes",
				]);
				exit;
			}

			//   $checkUser = $this->db->get_where("joinerOf_Group",['userId' => $this->input->post("userId")])->row_array();

			//   if(!!$checkUser){
			//       echo json_encode([

			//           "success" => "0",
			//           "message" => "Something went wrong - this user already a group joiner.cannot make a groupCreater",
			//           ]);exit;

			//   }

			$data['userId'] = $this->input->post("userId");
			$data['group_name'] = $this->input->post("group_name");
			$data['group_size'] = $this->input->post("group_size");
			$data['bio'] = $this->input->post("bio");
			$data['daily_task'] = $this->input->post("daily_task");
			$data['latitude'] = $this->input->post("latitude");
			$data['group_setting'] = $this->input->post("group_setting");
			$data['longitude'] = $this->input->post("longitude");
			$data['type'] = "is_Captain";
			$data['group_join_counts'] = "1";
			$data['created'] = date("Y-m-d H:i:s");

			if (!empty($_FILES["group_photo"]["name"])) {
				$name1 = time() . '_' . $_FILES["group_photo"]["name"];
				$name = str_replace(' ', '_', $name1);
				$liciense_tmp_name = $_FILES["group_photo"]["tmp_name"];
				$error = $_FILES["group_photo"]["error"];
				$liciense_path = 'uploads/users/' . $name;
				move_uploaded_file($liciense_tmp_name, $liciense_path);
				$data['group_photo'] = $liciense_path;
			}

			$upload = $this->db->insert("createGroup", $data);

			$getiD = $this->db->insert_id();

			if ($upload == true) {

				$getdetails = $this->db->get_where("createGroup", ['id' => $getiD])->row_array();

				$getType['group_type'] = $getdetails['type'];

				$this->db->update("users", $getType, ['id' => $this->input->post("userId")]);



				//   $datas['groupId'] = $getiD;
				//   $datas['userId'] = $getdetails['userId'];
				//   $datas['type'] = $getdetails['type'];
				//   $datas['created'] = date("Y-m-d H:i:s");

				//   $this->db->insert("joinGroup",$datas);

				if (!!$getdetails['group_photo']) {

					$getdetails['group_photo'] = base_url() . $getdetails['group_photo'];
				} else {

					$getdetails['group_photo'] = "";
				}

				$addtags = explode(',', $this->input->post("tag"));

				foreach ($addtags as $key => $val) {
					$insert_data = [];
					$insert_data[] = [
						"groupId"    =>    $getiD,
						"tag"    =>    $val,
					];

					$ins = $this->db->insert_Batch("createGroup_tags", $insert_data);
				}

				$getTags = $this->db->get_where("createGroup_tags", ['groupId' => $getiD])->result_array();

				$getdetails['tags'] = $getTags;

				echo json_encode([

					"success" => "1",
					"message" => "Group created",
					"details" => $getdetails
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

	public function removeGroup()
	{

		$checkCaptain = $this->db->get_where("createGroup", ['userId' => $this->input->post("captainId"), 'id' => $this->input->post("groupId")])->row_array();

		if (empty($checkCaptain)) {

			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid details!"
			]);
			exit;
		}

		$remove = $this->db->delete("createGroup", ['userId' => $this->input->post("captainId"), 'id' => $this->input->post("groupId")]);

		if ($remove == true) {



			echo json_encode([

				"success" => "1",
				"message" => "Group removed"
			]);
			exit;
		} else {
			echo json_encode([

				"success" => "0",
				"message" => "Something went wrong!"
			]);
			exit;
		}
	}

	public function editGroup()
	{

		$checkCaptain = $this->db->get_where("createGroup", ['userId' => $this->input->post('captainId')])->row_array();

		if (empty($checkCaptain)) {
			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid captainId!"
			]);
			exit;
		}
	}


	public function getGroup()
	{

		$getGroupDetails = $this->db->select("createGroup.*,users.username")
			->from("createGroup")
			->join("users", "users.id = createGroup.userId", "left")
			->where("createGroup.userId", $this->input->post("userId"))
			->get()
			->result_array();

		if (!!$getGroupDetails) {

			foreach ($getGroupDetails as $key => $value) {

				$getId = $getGroupDetails[$key]['id'];

				$getTags = $this->db->get_where("createGroup_tags", ['groupId' => $getId])->result_array();

				$getGroupDetails[$key]['group_photo'] = base_url() . $getGroupDetails[$key]['group_photo'];

				$getGroupDetails[$key]['Tags'] = $getTags;

				//   $final[] = $gets;

				// print_r($getGroupDetails);
			}
			//   die;

			if (!!$getGroupDetails) {

				echo json_encode([
					"success" => "1",
					"message" => "details found",
					"details" => $getGroupDetails
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

			$getGroupDetailss = $this->db->select("joinerOf_Group.id joinGroupId,joinerOf_Group.userId joinGroupUserId,joinerOf_Group.groupId,joinerOf_Group.changerId,joinerOf_Group.type joinGroup_type,createGroup.*")
				->from("joinerOf_Group")
				->join("createGroup", "createGroup.id = joinerOf_Group.groupId", "left")
				->where("joinerOf_Group.userId", $this->input->post("userId"))
				->get()
				->result_array();


			if (!!$getGroupDetailss) {

				foreach ($getGroupDetailss as $key => $value) {

					$getId = $getGroupDetailss[$key]['id'];

					$getTags = $this->db->get_where("createGroup_tags", ['groupId' => $getId])->result_array();

					$getGroupDetailss[$key]['group_photo'] = base_url() . $getGroupDetailss[$key]['group_photo'];

					$getGroupDetailss[$key]['Tags'] = $getTags;

					//   $final[] = $gets;

					// print_r($getGroupDetails);
				}
				//   die;

				if (!!$getGroupDetailss) {

					echo json_encode([
						"success" => "1",
						"message" => "details found",
						"details" => $getGroupDetailss
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
	}

	public function getAllGroups()
	{

		$getGroupDetails = $this->db->select("createGroup.*,users.username")
			->from("createGroup")
			->join("users", "users.id = createGroup.userId", "left")
			->get()
			->result_array();

		if (!!$getGroupDetails) {

			foreach ($getGroupDetails as $key => $value) {

				$getId = $getGroupDetails[$key]['id'];

				$getTags = $this->db->get_where("createGroup_tags", ['groupId' => $getId])->result_array();

				$getGroupDetails[$key]['group_photo'] = base_url() . $getGroupDetails[$key]['group_photo'];

				$getGroupDetails[$key]['Tags'] = $getTags;
			}


			echo json_encode([
				"success" => "1",
				"message" => "details found",
				"details" => $getGroupDetails
			]);
			exit;
		} else {
			echo json_encode([
				"success" => "0",
				"message" => "details not found!"
			]);
			exit;
		}
	}

	public function getGroupById()
	{

		$getGroupDetails = $this->db->select("createGroup.*,users.username")
			->from("createGroup")
			->join("users", "users.id = createGroup.userId", "left")
			->where("createGroup.id", $this->input->post("id"))
			->get()
			->result_array();

		if (!!$getGroupDetails) {

			foreach ($getGroupDetails as $key => $value) {

				$getId = $getGroupDetails[$key]['id'];

				$getTags = $this->db->get_where("createGroup_tags", ['groupId' => $getId])->result_array();

				$getGroupDetails[$key]['group_photo'] = base_url() . $getGroupDetails[$key]['group_photo'];

				$getGroupDetails[$key]['Tags'] = $getTags;

				//   $final[] = $gets;
			}


			echo json_encode([
				"success" => "1",
				"message" => "details found",
				"details" => $getGroupDetails
			]);
			exit;
		} else {

			echo json_encode([
				"success" => "0",
				"message" => "details not found!"
			]);
			exit;
		}
	}

	//   public function getJoinGroupDetails(){

	//       $get = $this->db->select("cust.*,residence_city.username,residence_city.image,creater.userId groupCreaterId,creater.group_name,g_creater.name group_creater_name,g_creater.username group_creater_username")
	//       ->from("joinerOf_Group cust")
	//       ->join("users residence_city","residence_city.id = cust.userId","left")
	//       ->join("createGroup creater","creater.id = cust.groupId","left")
	//       ->join("users g_creater","g_creater.id = creater.userId","left")
	//       ->where("cust.groupId",$this->input->post("groupId"))
	//       ->get()
	//       ->result_array();

	//       if(!!$get){

	//           foreach($get as $gets){
	//               $gets['image'] = base_url().$gets['image'];

	//               $final[] = $gets;

	//           }

	//           echo json_encode([

	//               "success" => "1",
	//               "message" => "details found",
	//               "details" => $final
	//               ]);exit;
	//       }
	//       else{
	//           echo json_encode([

	//               "success" => "0",
	//               "message" => "details not found!",
	//               ]);exit;
	//       }
	//   }

	public function getJoinGroupDetails()
	{

		$get = $this->db->select("createGroup.id createGroupId,createGroup.userId groupCreaterId,createGroup.group_name,users.name group_creater_name,users.username group_creater_username")
			->from("createGroup")
			->join("users", "users.id = createGroup.userId", "left")
			->where("createGroup.id", $this->input->post("groupId"))
			->get()
			->row_array();

		if (!!$get) {

			$id = $get['createGroupId'];

			$getDetails = $this->db->select("changeSetting_records.*,users.username,concat('" . base_url() . "', users.image) as image")
				->from("changeSetting_records")
				->join("users", "users.id = changeSetting_records.userId", "left")
				->where("changeSetting_records.groupId", $id)
				->where("changeSetting_records.request_status !=", "Pending")
				->where("changeSetting_records.request_status !=", "Reject")
				->get()
				->result_array();

			//   print_r($getDetails);
			//   die;

			$get['joiners'] = $getDetails;

			echo json_encode([

				"success" => "1",
				"message" => "details found",
				"details" => $get
			]);
			exit;
		} else {

			echo json_encode([

				"success" => "0",
				"message" => "details not found!"
			]);
			exit;
		}
	}

	public function joinGroup()
	{

		if ($this->input->post()) {

			$checkJoinRequest = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("userId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew', 'request_status' => 'Pending'])->row_array();
			if (!!$checkJoinRequest) {

				echo json_encode([

					"success" => "0",
					"message" => "Request already exist!",
				]);
				exit;
			}


			$checkJoinUser = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("userId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew', 'request_status' => 'Accept'])->row_array();

			if (!!$checkJoinUser) {

				echo json_encode([

					"success" => "0",
					"message" => "Group already join - by this user",
				]);
				exit;
			}

			$checkJoiner = $this->db->select('changeSetting_records.*')
				->from("changeSetting_records")
				->where("changeSetting_records.userId", $this->input->post("userId"))
				->where("changeSetting_records.groupId", $this->input->post("groupId"))
				->group_start()
				->or_where("changeSetting_records.type", 'co_Captain')
				->or_where("changeSetting_records.type", 'is_Captain')
				->or_where("changeSetting_records.type", 'assistant')
				->group_end()
				->get()
				->row_array();

			if (!!$checkJoiner) {
				echo json_encode([

					"success" => "0",
					"message" => "Group already join - by this user",
				]);
				exit;
			}

			//   $checkJoinUserr = $this->db->get_where("changeSetting_records",['userId' => $this->input->post("userId"),'groupId' => $this->input->post("groupId")])->row_array();

			//   if(!!$checkJoinUserr){

			//       echo json_encode([

			//       "success" => "0",
			//       "message" => "Something went wrong! - please try after sometimes",
			//       ]);exit;

			//   }

			$checkgroup = $this->db->get_where("createGroup", ['id' => $this->input->post("groupId")])->row_array();

			if (empty($checkgroup)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid groupId",
				]);
				exit;
			}

			$checkuser = $this->db->get_where("users", ['id' => $this->input->post("userId")])->row_array();

			if (empty($checkuser)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid userId",
				]);
				exit;
			}

			$checkGroupCreater = $this->db->get_where("createGroup", ['userId' => $this->input->post("userId"), 'id' => $this->input->post("groupId")])->row_array();

			if (!!$checkGroupCreater) {

				echo json_encode([

					"success" => "0",
					"message" => "Something went wrong - this user already a group creater.cannot make a joiner",
				]);
				exit;
			}

			$get_group_size = $this->db->get_where("createGroup", ['id' => $this->input->post("groupId")])->row_array();

			$size = $get_group_size['group_size'];

			$get_num_rows = $this->db->get_where("changeSetting_records", ['groupId' => $this->input->post("groupId")])->num_rows();

			if ($get_num_rows < $size) {

				$get_group_settings = $this->db->get_where("createGroup", ['id' => $this->input->post("groupId")])->row_array();

				$setting = $get_group_settings['group_setting'];

				$userDetails = $this->db->get_where("users", ['id' => $this->input->post("userId")])->row_array();

				$getlevel = $userDetails['leval'];

				if ($setting == "Only 20 level or above can join after reviewing") {

					if ($getlevel >= 20) {

						$data['userId'] = $this->input->post("userId");
						$data['groupId'] = $this->input->post("groupId");
						$data['type'] = 'crew';
						$data['request_status'] = 'Pending';
						$data['created'] = date("Y-m-d H:i:s");

						$join = $this->db->insert("joinerOf_Group", $data);

						$getId = $this->db->insert_id();

						if ($join == true) {

							$this->db->insert("changeSetting_records", $data);

							$getJoinDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

							$getjoinerId['group_type'] = $getJoinDetails['type'];

							$this->db->update("users", $getjoinerId, ['id' => $this->input->post("userId")]);

							echo json_encode([

								"success" => "1",
								"message" => "User join successfully",
								"details" => $getJoinDetails
							]);
							exit;
						} else {
							echo json_encode([
								"success" => "0",
								"message" => "Something went wrong - please try after sometime!"
							]);
							exit;
						}
					} else {
						echo json_encode([
							"success" => "0",
							"message" => "Invalid user's leval!"
						]);
						exit;
					}
				} else {
					echo json_encode([
						"success" => "0",
						"message" => "Invalid setting!"
					]);
					exit;
				}
			} else {
				echo json_encode([
					"success" => "0",
					"message" => "Group join limit exceed...!"
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


	//   public function getJoinGroupRequest(){

	//       $getCaptain = $this->db->select("createGroup.id createGroupId,createGroup.userId createrId,createGroup.group_name,changeSetting_records.*")
	//                               ->from("createGroup")
	//                               ->join("changeSetting_records","changeSetting_records.groupId = createGroup.id")
	//                               ->where("createGroup.userId",$this->input->post("captainId"))
	//                               ->where("createGroup.type","is_Captain")
	//                               ->where("changeSetting_records.type","crew")
	//                               ->where("changeSetting_records.request_status","Pending")
	//                               ->get()
	//                               ->result_array();

	//         if(!!$getCaptain){

	//             echo json_encode([

	//                 "success" => "1",
	//                 "message" => "request found",
	//                 "details" => $getCaptain
	//                 ]);exit;
	//         }
	//         else{
	//             echo json_encode([

	//                 "success" => "0",
	//                 "message" => "request not found!",
	//                  ]);exit;

	//         }

	//   }

	public function getJoinGroupRequest()
	{

		$getDetails = $this->db->select("createGroup.id createGroupId,createGroup.userId createrId,createGroup.group_name,users.username,users.image")
			->from("createGroup")
			->join("users", "users.id = createGroup.userId", "left")
			->where("createGroup.userId", $this->input->post("captainId"))
			->where("createGroup.type", "is_Captain")
			->get()
			->row_array();

		// print_r($getDetails);
		// die;

		if (!!$getDetails) {

			if (!!$getDetails['image']) {

				$getDetails['image'] = base_url() . $getDetails['image'];
			} else {
				$getDetails['image'] = "";
			}

			$gid = $getDetails['createGroupId'];

			$getCrewRequest = $this->db->select("changeSetting_records.*,users.name,users.username,concat('" . base_url() . "', users.image) as image")
				->from("changeSetting_records")
				->join("users", "users.id = changeSetting_records.userId", "left")
				->where("changeSetting_records.groupId", $gid)
				->where("changeSetting_records.type", "crew")
				->where("changeSetting_records.request_status", "Pending")
				->get()
				->result_array();


			$getDetails['join_request'] = $getCrewRequest;


			echo json_encode([

				"success" => "1",
				"message" => "request found",
				"details" => $getDetails
			]);
			exit;
		} else {
			echo json_encode([

				"success" => "0",
				"message" => "request not found!",
			]);
			exit;
		}
	}

	public function coCaptainReceiveJoinRequest()
	{

		$getDetails = $this->db->select("changeSetting_records.id changeSetting_recordsId,changeSetting_records.groupId,changeSetting_records.userId coCaptainId,users.username,users.name,users.image")
			->from("changeSetting_records")
			->join("users", "users.id = changeSetting_records.userId", "left")
			->where("changeSetting_records.userId", $this->input->post("coCaptainId"))
			->get()
			->result_array();

		// print_r($getDetails);
		// die;

		if (!!$getDetails) {

			foreach ($getDetails as $key => $gets) {

				if (!!$gets['image']) {
					$getDetails[$key]['image'] = base_url() . $gets['image'];
				} else {
					$getDetails[$key]['image'] = "";
				}

				$gid = $gets['groupId'];

				$getCrewRequest = $this->db->select("changeSetting_records.id changeSetting_recordsId,changeSetting_records.userId joinercrewId,changeSetting_records.groupId,changeSetting_records.type,changeSetting_records.request_status,,users.name,users.username,concat('" . base_url() . "', users.image) as image")
					->from("changeSetting_records")
					->join("users", "users.id = changeSetting_records.userId", "left")
					->where("changeSetting_records.groupId", $gid)
					->where("changeSetting_records.type", "crew")
					->where("changeSetting_records.request_status", "Pending")
					->get()
					->result_array();


				$getDetails[$key]['join_request'] = $getCrewRequest;
			}

			echo json_encode([

				"success" => "1",
				"message" => "request found",
				"details" => $getDetails
			]);
			exit;
		} else {
			echo json_encode([

				"success" => "0",
				"message" => "request not found!",
			]);
			exit;
		}
	}

	public function acceptJoinRequest()
	{

		$type = $this->input->post("type");

		if ($type == null) {

			echo json_encode([

				"success" => "0",
				"message" => "type cannot be null!"
			]);
			exit;
		}

		if ($type == "0") {


			$checkCoCaptain = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("co_captainId"), 'groupId' => $this->input->post("groupId")])->row_array();

			if (!!$checkCoCaptain) {

				$data['request_status'] = $this->input->post("request_status");

				$update = $this->db->update("changeSetting_records", $data, ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);

				$this->db->set('group_join_counts', 'group_join_counts +1', false)->where('id', $this->input->post('groupId'))->update("createGroup");

				if ($update == true) {

					$this->db->update("joinerOf_Group", $data, ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);


					$this->db->delete("changeSetting_records", ['userId' => $this->input->post("joinerId"), 'type' => "crew", 'request_status' => "Pending", 'groupId !=' => $this->input->post("groupId")]);

					$this->db->delete("joinerOf_Group", ['userId' => $this->input->post("joinerId"), 'type' => "crew", 'request_status' => "Pending", 'groupId !=' => $this->input->post("groupId")]);

					$getAccepted = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")])->row_array();

					echo json_encode([

						"success" => "1",
						"message" => "Request accepted",
						"details" => $getAccepted
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
					"message" => "Please enter valid co-captainId"
				]);
				exit;
			}
		} elseif ($type == "1") {

			$checkCoCaptain = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("co_captainId"), 'groupId' => $this->input->post("groupId")])->row_array();

			if (!!$checkCoCaptain) {

				$data['request_status'] = $this->input->post("request_status");

				$update = $this->db->update("changeSetting_records", $data, ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);

				if ($update == true) {

					$this->db->update("joinerOf_Group", $data, ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);

					$getRejected = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")])->row_array();

					echo json_encode([

						"success" => "1",
						"message" => "Request rejected",
						"details" => $getRejected
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
					"message" => "Please enter valid co-captainId"
				]);
				exit;
			}
		} else {
			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid type!"
			]);
			exit;
		}
	}

	public function captainAcceptRejectrequest()
	{

		$type = $this->input->post("type");

		if ($type == null) {

			echo json_encode([

				"success" => "0",
				"message" => "type cannot be null!"
			]);
			exit;
		}

		if ($type == "0") {

			$checkCaptain = $this->db->get_where("createGroup", ['userId' => $this->input->post("captainId"), 'id' => $this->input->post("groupId")])->row_array();

			if (!!$checkCaptain) {

				$data['request_status'] = $this->input->post("request_status");

				$update = $this->db->update("changeSetting_records", $data, ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);

				$this->db->set('group_join_counts', 'group_join_counts +1', false)->where('id', $this->input->post('groupId'))->update("createGroup");

				if ($update == true) {

					$this->db->update("joinerOf_Group", $data, ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);


					$this->db->delete("changeSetting_records", ['userId' => $this->input->post("joinerId"), 'type' => "crew", 'request_status' => "Pending", 'groupId !=' => $this->input->post("groupId")]);

					$this->db->delete("joinerOf_Group", ['userId' => $this->input->post("joinerId"), 'type' => "crew", 'request_status' => "Pending", 'groupId !=' => $this->input->post("groupId")]);

					$getAccepted = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")])->row_array();

					echo json_encode([

						"success" => "1",
						"message" => "Request accepted",
						"details" => $getAccepted
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
					"message" => "Please enter valid captainId"
				]);
				exit;
			}
		} elseif ($type == "1") {

			$checkCaptain = $this->db->get_where("createGroup", ['userId' => $this->input->post("captainId"), 'id' => $this->input->post("groupId")])->row_array();

			if (!!$checkCaptain) {

				$data['request_status'] = $this->input->post("request_status");

				$update = $this->db->update("changeSetting_records", $data, ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);

				if ($update == true) {

					$this->db->update("joinerOf_Group", $data, ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);

					$getRejected = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")])->row_array();

					echo json_encode([

						"success" => "1",
						"message" => "Request rejected",
						"details" => $getRejected
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
					"message" => "Please enter valid captainId"
				]);
				exit;
			}
		} else {
			echo json_encode([

				"success" => "0",
				"message" => "Please enter valid type!"
			]);
			exit;
		}
	}

	public function joinerLeaveGroup()
	{

		$checkJoiner = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")])->row_array();

		if (empty($checkJoiner)) {

			echo json_encode([

				"success" => "0",
				"message" => "please enter valid userid!"
			]);
			exit;
		}

		$leave = $this->db->delete("changeSetting_records", ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);

		$this->db->set('group_join_counts', 'group_join_counts -1', false)->where('id', $this->input->post('groupId'))->update("createGroup");

		if ($leave == true) {

			$this->db->delete("joinerOf_Group", ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);

			echo json_encode([

				"success" => "1",
				"message" => "Joiner leave from group"
			]);
			exit;
		} else {
			echo json_encode([

				"success" => "0",
				"message" => "Something went wrong!"
			]);
			exit;
		}
	}

	public function captainLeaveGroupJoiner()
	{

		$checkCaptain = $this->db->get_where("createGroup", ['id' => $this->input->post("groupId"), 'userId' => $this->input->post("captainId")])->row_array();

		if (!!$checkCaptain) {

			$leave = $this->db->delete("changeSetting_records", ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);

			$this->db->set('group_join_counts', 'group_join_counts -1', false)->where('id', $this->input->post('groupId'))->update("createGroup");

			if ($leave == true) {

				$this->db->delete("joinerOf_Group", ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);

				echo json_encode([

					"success" => "1",
					"message" => "Joiner leave from group"
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
				"message" => "Invalid captainId!"
			]);
			exit;
		}
	}


	public function coCaptainLeaveGroupJoiner()
	{

		$checkCaptain = $this->db->get_where("changeSetting_records", ['groupId' => $this->input->post("groupId"), 'userId' => $this->input->post("coCaptainId")])->row_array();

		if (!!$checkCaptain) {

			$leave = $this->db->delete("changeSetting_records", ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);

			$this->db->set('group_join_counts', 'group_join_counts -1', false)->where('id', $this->input->post('groupId'))->update("createGroup");

			if ($leave == true) {

				$this->db->delete("joinerOf_Group", ['userId' => $this->input->post("joinerId"), 'groupId' => $this->input->post("groupId")]);

				echo json_encode([

					"success" => "1",
					"message" => "Joiner leave from group"
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
				"message" => "Invalid coCaptainId!"
			]);
			exit;
		}
	}

	public function inviteGroup()
	{

		if ($this->input->post()) {

			$data['userId'] = $this->input->post("userId");
			$data['invite_userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['created'] = date("Y-m-d H:i:s");

			$upload = $this->db->insert("inviteGroup", $data);

			if (!!$upload == true) {

				echo json_encode([

					"success" => "1",
					"message" => "user invite successfully",
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


	public function joinGroupUserDetails()
	{

		$get = $this->db->select("userFollow.id userFollowId,userFollow.userId,userFollow.followingUserId")
			->from("userFollow")
			->where("userFollow.userId", $this->input->post("userId"))
			->get()
			->result_array();


		foreach ($get as $gets) {

			$getId = $gets['followingUserId'];

			$getDetails = $this->db->get_where("joinerOf_Group", ['userId' => $getId])->row_array();

			if (!!$getDetails) {
			} else {

				$user = $this->db->get_where("users", ['id' => $getId])->row_array();

				$final[] = $user;
			}
		}

		if (!!$final) {

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

	public function changeCaptainSettings()
	{
		$type = $this->input->post("type");

		if ($type == null) {

			echo json_encode([

				"success" => "0",
				"message" => "type param cannot be null!"
			]);
			exit;
		}

		if ($type == "joiner_to_captain") {

			$groupCreater = $this->db->get_where("createGroup", ['userId' => $this->input->post("createrGroupId"), 'id' => $this->input->post("groupId")])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - creater!"
				]);
				exit;
			}

			$groupJoiner = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew', 'request_status' => 'Accept'])->row_array();

			if (empty($groupJoiner)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - joiner!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("createrGroupId");
			$data['type'] = 'is_Captain';
			$data['created'] = date("Y-m-d H:i:s");

			$crewToCaptain = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($crewToCaptain == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$gettype['group_type'] = $getDetails['type'];
				$gettypee['type'] = $getDetails['type'];

				$change = $this->db->update("changeSetting_records", $gettypee, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew', 'request_status' => 'Accept']);

				$getChangeSetting = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")])->row_array();


				$this->db->update("users", $gettype, ['id' => $this->input->post("otherUserId")]);


				$datas['userId'] = $getDetails['changerId'];
				$datas['groupId'] = $getDetails['groupId'];
				$datas['changerId'] = $getDetails['changerId'];
				$datas['type'] = 'co_Captain';
				$datas['created'] = date("Y-m-d H:i:s");

				$this->db->insert("joinerOf_Group", $datas);

				$datass['userId'] = $getDetails['changerId'];
				$datass['groupId'] = $getDetails['groupId'];
				$datass['type'] = 'co_Captain';
				$datass['created'] = date("Y-m-d H:i:s");

				$this->db->insert("changeSetting_records", $datass);

				$this->db->set('group_join_counts', 'group_join_counts +1', false)->where('id', $this->input->post('groupId'))->update("createGroup");

				$getCurrentCap = $getDetails['userId'];

				$dataa['userId'] = $getCurrentCap;
				$dataa['type'] = 'is_Captain';
				$datas['update'] = date("Y-m-d H:i:s");

				$this->db->update("createGroup", $dataa, ['id' => $this->input->post("groupId"), 'userId' => $this->input->post("createrGroupId")]);

				echo json_encode([

					"success" => "1",
					"message" => "create joiner to captain successfully",
					"details" => $getChangeSetting
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!"
				]);
				exit;
			}
		} elseif ($type == "co_captain_to_captain") {

			$groupCreater = $this->db->get_where("createGroup", ['userId' => $this->input->post("createrGroupId"), 'id' => $this->input->post("groupId")])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - creater!"
				]);
				exit;
			}

			$groupCoCap = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

			if (empty($groupCoCap)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - co_captain!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("createrGroupId");
			$data['type'] = 'is_Captain';
			$data['created'] = date("Y-m-d H:i:s");

			$coCapToCaptain = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($coCapToCaptain == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();


				$datas['userId'] = $getDetails['changerId'];
				$datas['groupId'] = $getDetails['groupId'];
				$datas['changerId'] = $getDetails['changerId'];
				$datas['type'] = 'co_Captain';
				$datas['created'] = date("Y-m-d H:i:s");

				$this->db->insert("joinerOf_Group", $datas);

				// $update['userId'] = $getDetails['userId'];
				$update['type'] = 'is_Captain';

				$change = $this->db->update("changeSetting_records", $update, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain']);

				$updatee['type'] = 'co_Captain';
				// $updatee['userId'] = $getDetails['changerId'];

				$this->db->update("changeSetting_records", $updatee, ['userId' => $this->input->post("createrGroupId"), 'groupId' => $this->input->post("groupId"), 'type' => 'is_Captain']);

				$getCurrentCap = $getDetails['userId'];

				$dataa['userId'] = $getCurrentCap;
				$dataa['type'] = 'is_Captain';
				$datas['update'] = date("Y-m-d H:i:s");

				$this->db->update("createGroup", $dataa, ['id' => $this->input->post("groupId"), 'userId' => $this->input->post("createrGroupId")]);

				echo json_encode([

					"success" => "1",
					"message" => "create co-captain to captain successfully",
					"details" => $getDetails
				]);
				exit;
			} else {
				echo json_encode([


					"success" => "0",
					"message" => "something went wrong!",
				]);
				exit;
			}
		} elseif ($type == "co_captain_to_assistant") {

			$groupCreater = $this->db->get_where("createGroup", ['userId' => $this->input->post("createrGroupId"), 'id' => $this->input->post("groupId")])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - creater!"
				]);
				exit;
			}

			$groupCoCap = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

			if (empty($groupCoCap)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - co_captain!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("createrGroupId");
			$data['type'] = 'assistant';
			$data['created'] = date("Y-m-d H:i:s");

			$coCapToAssistant = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($coCapToAssistant == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$update['type'] = $getDetails['type'];

				$this->db->update("changeSetting_records", $update, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")]);

				$getDeta = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")])->row_array();


				echo json_encode([

					"success" => "1",
					"message" => "create co-captain to assistant successfully",
					"details" => $getDeta
				]);
				exit;
			} else {
				echo json_encode([


					"success" => "0",
					"message" => "something went wrong!",
				]);
				exit;
			}
		} elseif ($type == "co_captain_to_crew") {

			$groupCreater = $this->db->get_where("createGroup", ['userId' => $this->input->post("createrGroupId"), 'id' => $this->input->post("groupId")])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - creater!"
				]);
				exit;
			}

			$groupCoCap = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

			if (empty($groupCoCap)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - co_captain!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("createrGroupId");
			$data['type'] = 'crew';
			$data['created'] = date("Y-m-d H:i:s");

			$coCapToAssistant = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($coCapToAssistant == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$update['type'] = $getDetails['type'];
				$update['request_status'] = 'Accept';

				$this->db->update("changeSetting_records", $update, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")]);

				$getDeta = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")])->row_array();

				echo json_encode([

					"success" => "1",
					"message" => "create co-captain to crew successfully",
					"details" => $getDeta
				]);
				exit;
			} else {
				echo json_encode([


					"success" => "0",
					"message" => "something went wrong!",
				]);
				exit;
			}
		} elseif ($type == "crew_toAssistant") {

			$groupCreater = $this->db->get_where("createGroup", ['userId' => $this->input->post("createrGroupId"), 'id' => $this->input->post("groupId")])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - creater!"
				]);
				exit;
			}

			// $groupCoCap = $this->db->get_where("joinerOf_Group",['userId' => $this->input->post("otherUserId"),'groupId' => $this->input->post("groupId"),'changerId' => 'type' => 'crew'])->row_array();

			$getCrew = $this->db->select("joinerOf_Group.*")
				->from("joinerOf_Group")
				->where("joinerOf_Group.userId", $this->input->post("otherUserId"))
				->where("joinerOf_Group.groupId", $this->input->post("groupId"))
				->where("joinerOf_Group.type", 'crew')
				->order_by('created', 'desc')
				->get()
				->row_array();

			if (empty($getCrew)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - crew!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("createrGroupId");
			$data['type'] = 'assistant';
			$data['created'] = date("Y-m-d H:i:s");

			$coCapToAssistant = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($coCapToAssistant == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$update['type'] = $getDetails['type'];

				$this->db->update("changeSetting_records", $update, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")]);

				$getDeta = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")])->row_array();

				echo json_encode([

					"success" => "1",
					"message" => "create crew to assistant successfully",
					"details" => $getDeta
				]);
				exit;
			} else {
				echo json_encode([


					"success" => "0",
					"message" => "something went wrong!",
				]);
				exit;
			}
		} elseif ($type == "crew_toCocaptain") {

			$groupCreater = $this->db->get_where("createGroup", ['userId' => $this->input->post("createrGroupId"), 'id' => $this->input->post("groupId")])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - creater!"
				]);
				exit;
			}

			// $groupCoCap = $this->db->get_where("joinerOf_Group",['userId' => $this->input->post("otherUserId"),'groupId' => $this->input->post("groupId"),'changerId' => 'type' => 'crew'])->row_array();

			$getCrew = $this->db->select("joinerOf_Group.*")
				->from("joinerOf_Group")
				->where("joinerOf_Group.userId", $this->input->post("otherUserId"))
				->where("joinerOf_Group.groupId", $this->input->post("groupId"))
				->where("joinerOf_Group.type", 'crew')
				->order_by('created', 'desc')
				->get()
				->row_array();

			if (empty($getCrew)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - crew!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("createrGroupId");
			$data['type'] = 'co_Captain';
			$data['created'] = date("Y-m-d H:i:s");

			$coCapToAssistant = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($coCapToAssistant == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$update['type'] = $getDetails['type'];

				$this->db->update("changeSetting_records", $update, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")]);

				$getDeta = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")])->row_array();

				echo json_encode([

					"success" => "1",
					"message" => "create crew to co_Captain successfully",
					"details" => $getDeta
				]);
				exit;
			} else {
				echo json_encode([


					"success" => "0",
					"message" => "something went wrong!",
				]);
				exit;
			}
		} elseif ($type == "assistantToCrew") {

			$groupCreater = $this->db->get_where("createGroup", ['userId' => $this->input->post("createrGroupId"), 'id' => $this->input->post("groupId")])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - creater!"
				]);
				exit;
			}

			// $groupCoCap = $this->db->get_where("joinerOf_Group",['userId' => $this->input->post("otherUserId"),'groupId' => $this->input->post("groupId"),'changerId' => 'type' => 'crew'])->row_array();

			$getCrew = $this->db->select("joinerOf_Group.*")
				->from("joinerOf_Group")
				->where("joinerOf_Group.userId", $this->input->post("otherUserId"))
				->where("joinerOf_Group.groupId", $this->input->post("groupId"))
				->where("joinerOf_Group.type", 'assistant')
				->order_by('created', 'desc')
				->get()
				->row_array();

			if (empty($getCrew)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - assistant!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("createrGroupId");
			$data['type'] = 'crew';
			$data['created'] = date("Y-m-d H:i:s");

			$coCapToAssistant = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($coCapToAssistant == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$update['type'] = $getDetails['type'];
				$update['request_status'] = 'Accept';

				$this->db->update("changeSetting_records", $update, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")]);

				$getDeta = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")])->row_array();



				echo json_encode([

					"success" => "1",
					"message" => "create assistant to crew successfully",
					"details" => $getDeta
				]);
				exit;
			} else {
				echo json_encode([


					"success" => "0",
					"message" => "something went wrong!",
				]);
				exit;
			}
		} elseif ($type == "assistantToCoCaptain") {

			$groupCreater = $this->db->get_where("createGroup", ['userId' => $this->input->post("createrGroupId"), 'id' => $this->input->post("groupId")])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - creater!"
				]);
				exit;
			}

			// $groupCoCap = $this->db->get_where("joinerOf_Group",['userId' => $this->input->post("otherUserId"),'groupId' => $this->input->post("groupId"),'changerId' => 'type' => 'crew'])->row_array();

			$getCrew = $this->db->select("joinerOf_Group.*")
				->from("joinerOf_Group")
				->where("joinerOf_Group.userId", $this->input->post("otherUserId"))
				->where("joinerOf_Group.groupId", $this->input->post("groupId"))
				->where("joinerOf_Group.type", 'assistant')
				->order_by('created', 'desc')
				->get()
				->row_array();

			if (empty($getCrew)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - assistant!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("createrGroupId");
			$data['type'] = 'co_Captain';
			$data['created'] = date("Y-m-d H:i:s");

			$coCapToAssistant = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($coCapToAssistant == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$update['type'] = $getDetails['type'];

				$this->db->update("changeSetting_records", $update, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")]);

				$getDeta = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")])->row_array();



				echo json_encode([

					"success" => "1",
					"message" => "create assistant to co_Captain successfully",
					"details" => $getDeta
				]);
				exit;
			} else {
				echo json_encode([


					"success" => "0",
					"message" => "something went wrong!",
				]);
				exit;
			}
		} elseif ($type == "assistant_to_captain") {
			$groupCreater = $this->db->get_where("createGroup", ['userId' => $this->input->post("createrGroupId"), 'id' => $this->input->post("groupId")])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - creater!"
				]);
				exit;
			}

			$groupJoiner = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'assistant'])->row_array();

			if (empty($groupJoiner)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - joiner!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("createrGroupId");
			$data['type'] = 'is_Captain';
			$data['created'] = date("Y-m-d H:i:s");

			$crewToCaptain = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($crewToCaptain == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$datas['userId'] = $getDetails['changerId'];
				$datas['groupId'] = $getDetails['groupId'];
				$datas['changerId'] = $getDetails['changerId'];
				$datas['type'] = 'co_Captain';
				$datas['created'] = date("Y-m-d H:i:s");

				$this->db->insert("joinerOf_Group", $datas);

				$update['type'] = $getDetails['type'];

				$change = $this->db->update("changeSetting_records", $update, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'assistant']);

				$updatee['type'] = 'co_Captain';
				// $updatee['userId'] = $getDetails['changerId'];

				$this->db->update("changeSetting_records", $updatee, ['userId' => $this->input->post("createrGroupId"), 'groupId' => $this->input->post("groupId"), 'type' => 'is_Captain']);

				$getDeta = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("createrGroupId"), 'groupId' => $this->input->post("groupId"), 'type' => 'is_Captain'])->row_array();

				$getCurrentCap = $getDetails['userId'];

				$dataa['userId'] = $getCurrentCap;
				$dataa['type'] = 'is_Captain';
				$datas['update'] = date("Y-m-d H:i:s");

				$this->db->update("createGroup", $dataa, ['id' => $this->input->post("groupId"), 'userId' => $this->input->post("createrGroupId")]);

				echo json_encode([

					"success" => "1",
					"message" => "create assistant to captain successfully",
					"details" => $getDeta
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!"
				]);
				exit;
			}
		}
	}


	/**
	 * Co-captain all settings Api.
	 */

	public function changeCoCaptainSettings()
	{
		$type = $this->input->post("type");

		if ($type == null) {

			echo json_encode([

				"success" => "0",
				"message" => "type param cannot be null!"
			]);
			exit;
		}

		if ($type == "crew_to_assistant") {

			$check = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'assistant'])->row_array();

			if (!!$check) {
				echo json_encode([

					"success" => "0",
					"message" => "crew_to_assistant already exist!"
				]);
				exit;
			}

			$groupCreater = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("coCaptainId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid Co-captain!"
				]);
				exit;
			}


			$groupJoiner = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew', 'request_status' => 'Accept'])->row_array();

			if (empty($groupJoiner)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - joiner!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("coCaptainId");
			$data['type'] = 'assistant';
			$data['created'] = date("Y-m-d H:i:s");

			$crewToCaptain = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($crewToCaptain == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$gettype['type'] = $getDetails['type'];

				$this->db->update("changeSetting_records", $gettype, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew', 'request_status' => 'Accept']);

				//    echo $this->db->last_query();
				//    die;

				$getChangeSetting = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'assistant', 'request_status' => 'Accept'])->row_array();

				echo json_encode([

					"success" => "1",
					"message" => "create crew to assistant successfully",
					"details" => $getChangeSetting
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!"
				]);
				exit;
			}
		}
		if ($type == "crew_to_cocaptain") {

			$check = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

			if (!!$check) {
				echo json_encode([

					"success" => "0",
					"message" => "crew_to_cocaptain already exist!"
				]);
				exit;
			}

			$groupCreater = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("coCaptainId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid Co-captain!"
				]);
				exit;
			}


			$groupJoiner = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew', 'request_status' => 'Accept'])->row_array();

			if (empty($groupJoiner)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - joiner!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("coCaptainId");
			$data['type'] = 'co_Captain';
			$data['created'] = date("Y-m-d H:i:s");

			$crewToCaptain = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($crewToCaptain == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$gettypee['type'] = $getDetails['type'];

				$change = $this->db->update("changeSetting_records", $gettypee, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew', 'request_status' => 'Accept']);

				$getChangeSetting = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain', 'request_status' => 'Accept'])->row_array();

				echo json_encode([

					"success" => "1",
					"message" => "create crew to coCaptain successfully",
					"details" => $getChangeSetting
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!"
				]);
				exit;
			}
		}
		if ($type == "cocaptain_to_crew") {

			$check = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew'])->row_array();

			if (!!$check) {
				echo json_encode([

					"success" => "0",
					"message" => "cocaptain_to_crew already exist!"
				]);
				exit;
			}

			$groupCreater = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("coCaptainId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid Co-captain!"
				]);
				exit;
			}

			$getCoId = $groupCreater['userId'];

			if ($getCoId == $this->input->post("otherUserId")) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid otherUserId!"
				]);
				exit;
			}

			$groupJoiner = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

			if (empty($groupJoiner)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - joiner!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("coCaptainId");
			$data['type'] = 'crew';
			$data['created'] = date("Y-m-d H:i:s");

			$crewToCaptain = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($crewToCaptain == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$gettypee['type'] = $getDetails['type'];
				$gettypee['request_status'] = 'Accept';

				$change = $this->db->update("changeSetting_records", $gettypee, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain']);

				$getChangeSetting = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew'])->row_array();

				echo json_encode([

					"success" => "1",
					"message" => "create coCaptain to crew successfully",
					"details" => $getChangeSetting
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!"
				]);
				exit;
			}
		}
		if ($type == "cocaptain_to_assistant") {

			$check = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'assistant'])->row_array();

			if (!!$check) {
				echo json_encode([

					"success" => "0",
					"message" => "cocaptain_to_assistant already exist!"
				]);
				exit;
			}

			$groupCreater = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("coCaptainId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid Co-captain!"
				]);
				exit;
			}

			$getCoId = $groupCreater['userId'];

			if ($getCoId == $this->input->post("otherUserId")) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid otherUserId!"
				]);
				exit;
			}

			$groupJoiner = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

			if (empty($groupJoiner)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - joiner!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("coCaptainId");
			$data['type'] = 'assistant';
			$data['created'] = date("Y-m-d H:i:s");

			$crewToCaptain = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($crewToCaptain == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$gettypee['type'] = $getDetails['type'];
				$gettypee['request_status'] = 'Accept';

				$change = $this->db->update("changeSetting_records", $gettypee, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain']);

				$getChangeSetting = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'assistant'])->row_array();

				echo json_encode([

					"success" => "1",
					"message" => "create coCaptain to assistant successfully",
					"details" => $getChangeSetting
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!"
				]);
				exit;
			}
		}
		if ($type == "assistant_to_crew") {

			$check = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew'])->row_array();

			if (!!$check) {
				echo json_encode([

					"success" => "0",
					"message" => "assistant_to_crew already exist!"
				]);
				exit;
			}

			$groupCreater = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("coCaptainId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid Co-captain!"
				]);
				exit;
			}


			$groupJoiner = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'assistant'])->row_array();

			if (empty($groupJoiner)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - joiner!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("coCaptainId");
			$data['type'] = 'crew';
			$data['created'] = date("Y-m-d H:i:s");

			$crewToCaptain = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($crewToCaptain == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$gettypee['type'] = $getDetails['type'];
				$gettypee['request_status'] = 'Accept';

				$change = $this->db->update("changeSetting_records", $gettypee, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'assistant']);

				$getChangeSetting = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew', 'request_status' => 'Accept'])->row_array();

				echo json_encode([

					"success" => "1",
					"message" => "create assistant to crew successfully",
					"details" => $getChangeSetting
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!"
				]);
				exit;
			}
		}
		if ($type == "assistant_to_Cocaptain") {

			$check = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

			if (!!$check) {
				echo json_encode([

					"success" => "0",
					"message" => "assistant_to_Cocaptain already exist!"
				]);
				exit;
			}

			$groupCreater = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("coCaptainId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid Co-captain!"
				]);
				exit;
			}


			$groupJoiner = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'assistant'])->row_array();

			if (empty($groupJoiner)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - joiner!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("coCaptainId");
			$data['type'] = 'co_Captain';
			$data['created'] = date("Y-m-d H:i:s");

			$crewToCaptain = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($crewToCaptain == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$gettypee['type'] = $getDetails['type'];

				$change = $this->db->update("changeSetting_records", $gettypee, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'assistant']);

				$getChangeSetting = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

				echo json_encode([

					"success" => "1",
					"message" => "create assistant to coCaptain successfully",
					"details" => $getChangeSetting
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!"
				]);
				exit;
			}
		}
	}

	public function testingggset()
	{

		$type = $this->input->post("type");

		if ($type == null) {

			echo json_encode([

				"success" => "0",
				"message" => "type param cannot be null!"
			]);
			exit;
		}

		if ($type == "joiner_to_captain") {

			$groupCreater = $this->db->get_where("createGroup", ['userId' => $this->input->post("createrGroupId"), 'id' => $this->input->post("groupId")])->row_array();

			if (empty($groupCreater)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - creater!"
				]);
				exit;
			}

			$groupJoiner = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew', 'request_status' => 'Accept'])->row_array();

			if (empty($groupJoiner)) {

				echo json_encode([

					"success" => "0",
					"message" => "invalid group - joiner!"
				]);
				exit;
			}

			$data['userId'] = $this->input->post("otherUserId");
			$data['groupId'] = $this->input->post("groupId");
			$data['changerId'] = $this->input->post("createrGroupId");
			$data['type'] = 'is_Captain';
			$data['created'] = date("Y-m-d H:i:s");

			$crewToCaptain = $this->db->insert("joinerOf_Group", $data);

			$getId = $this->db->insert_id();

			if ($crewToCaptain == true) {

				$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();

				$gettype['group_type'] = $getDetails['type'];
				$gettypee['type'] = $getDetails['type'];

				$change = $this->db->update("changeSetting_records", $gettypee, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'crew', 'request_status' => 'Accept']);

				$getChangeSetting = $this->db->get_where("changeSetting_records", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId")])->row_array();


				$this->db->update("users", $gettype, ['id' => $this->input->post("otherUserId")]);


				$datas['userId'] = $getDetails['changerId'];
				$datas['groupId'] = $getDetails['groupId'];
				$datas['changerId'] = $getDetails['changerId'];
				$datas['type'] = 'co_Captain';
				$datas['created'] = date("Y-m-d H:i:s");

				$this->db->insert("joinerOf_Group", $datas);

				$datass['userId'] = $getDetails['changerId'];
				$datass['groupId'] = $getDetails['groupId'];
				$datass['type'] = 'co_Captain';
				$datass['created'] = date("Y-m-d H:i:s");

				$this->db->insert("changeSetting_records", $datass);

				$this->db->set('group_join_counts', 'group_join_counts +1', false)->where('id', $this->input->post('groupId'))->update("createGroup");

				$getCurrentCap = $getDetails['userId'];

				$dataa['userId'] = $getCurrentCap;
				$dataa['type'] = 'is_Captain';
				$datas['update'] = date("Y-m-d H:i:s");

				$this->db->update("createGroup", $dataa, ['id' => $this->input->post("groupId"), 'userId' => $this->input->post("createrGroupId")]);

				echo json_encode([

					"success" => "1",
					"message" => "create joiner to captain successfully",
					"details" => $getChangeSetting
				]);
				exit;
			} else {
				echo json_encode([

					"success" => "0",
					"message" => "something went wrong!"
				]);
				exit;
			}
		}
	}

	public function cooooo()
	{

		$groupCreater = $this->db->get_where("createGroup", ['userId' => $this->input->post("createrGroupId"), 'id' => $this->input->post("groupId")])->row_array();

		if (empty($groupCreater)) {

			echo json_encode([

				"success" => "0",
				"message" => "invalid group - creater!"
			]);
			exit;
		}

		$groupCoCap = $this->db->get_where("joinerOf_Group", ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain'])->row_array();

		if (empty($groupCoCap)) {

			echo json_encode([

				"success" => "0",
				"message" => "invalid group - co_captain!"
			]);
			exit;
		}

		$data['userId'] = $this->input->post("otherUserId");
		$data['groupId'] = $this->input->post("groupId");
		$data['changerId'] = $this->input->post("createrGroupId");
		$data['type'] = 'is_Captain';
		$data['created'] = date("Y-m-d H:i:s");

		$coCapToCaptain = $this->db->insert("joinerOf_Group", $data);

		$getId = $this->db->insert_id();

		if ($coCapToCaptain == true) {

			$getDetails = $this->db->get_where("joinerOf_Group", ['id' => $getId])->row_array();


			$datas['userId'] = $getDetails['changerId'];
			$datas['groupId'] = $getDetails['groupId'];
			$datas['changerId'] = $getDetails['changerId'];
			$datas['type'] = 'co_Captain';
			$datas['created'] = date("Y-m-d H:i:s");

			$this->db->insert("joinerOf_Group", $datas);

			// $update['userId'] = $getDetails['userId'];
			$update['type'] = 'is_Captain';

			$change = $this->db->update("changeSetting_records", $update, ['userId' => $this->input->post("otherUserId"), 'groupId' => $this->input->post("groupId"), 'type' => 'co_Captain']);

			$updatee['type'] = 'co_Captain';
			// $updatee['userId'] = $getDetails['changerId'];

			$this->db->update("changeSetting_records", $updatee, ['userId' => $this->input->post("createrGroupId"), 'groupId' => $this->input->post("groupId"), 'type' => 'is_Captain']);

			$getCurrentCap = $getDetails['userId'];

			$dataa['userId'] = $getCurrentCap;
			$dataa['type'] = 'is_Captain';
			$datas['update'] = date("Y-m-d H:i:s");

			$this->db->update("createGroup", $dataa, ['id' => $this->input->post("groupId"), 'userId' => $this->input->post("createrGroupId")]);

			echo json_encode([

				"success" => "1",
				"message" => "create co-captain to captain successfully",
				"details" => $getDetails
			]);
			exit;
		} else {
			echo json_encode([


				"success" => "0",
				"message" => "something went wrong!",
			]);
			exit;
		}
	}

	public function getFamilyMembersPost()
	{

		$get = $this->db->select("changeSetting_records.id changeSetting_recordsId,changeSetting_records.groupId,changeSetting_records.userId changeSetting_records_userId,changeSetting_records.type,user_UploadPost.*,users.username,users.name,users.image")
			->from("changeSetting_records")
			->join("user_UploadPost", "user_UploadPost.userId = changeSetting_records.userId")
			->join("users", "users.id = changeSetting_records.userId")
			->where("changeSetting_records.groupId", $this->input->post("groupId"))
			->where("user_UploadPost.type !=", "video")
			->where("changeSetting_records.request_status !=", "Pending")
			->where("changeSetting_records.request_status !=", "Reject")
			->get()
			->result_array();

		if (!!$get) {

			foreach ($get as $gets) {

				if ($gets['postimage']) {

					$gets['postimage'] = base_url() . $gets['postimage'];
				} else {
					$gets['postimage'] = "";
				}

				if ($gets['image']) {

					$gets['image'] = base_url() . $gets['image'];
				} else {
					$gets['image'] = "";
				}

				$final[] = $gets;
			}

			echo json_encode([

				"success" => "1",
				"message" => "details found successfully",
				"details" => $final
			]);
			exit;
		} else {
			echo json_encode([


				"success" => "0",
				"message" => "something went wrong!",
			]);
			exit;
		}
	}
}
