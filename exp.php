<?php

// require '/vendor/autoload.php';

use Kreait\Firebase\Factory;

defined('BASEPATH') or exit('No direct script access allowed');

class Experience extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
    }

    // private function generateOtp($phone){

    //         $otp = rand(1000, 9999);

    //         $otpData['phone'] = $phone;
    //         $otpData['otp'] = $otp;
    //         $otpData['date'] = date('Y-m-d H:i:s');

    //         if($this->db->insert('otp', $otpData)){

    //             $id = $this->db->insert_id();

    //         return $id;

    //         }else{
    //             return false;
    //         }

    // }

    public function register()
    {
        if ($this->input->post()) {

            if (!$this->input->post('phone')) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'phone required'
                ]);
                exit;
            }

            $checkPhone = $this->db->get_where('users', ['phone' => $this->input->post('phone')])->row_array();

            if (!!$checkPhone) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'phone number already in use'
                ]);
                exit;
            }

            if (!$this->input->post('email')) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'email required'
                ]);
                exit;
            }


            $checkEmail = $this->db->get_where('users', ['email' => $this->input->post('email')])->row_array();

            if (!!$checkEmail) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'email already in use'
                ]);
                exit;
            }

            if (!$this->input->post('password')) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'password required'
                ]);
                exit;
            }

            if (!$this->input->post('username')) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'username required'
                ]);
                exit;
            }

            $data['username'] = $this->input->post('username');
            $data['firebaseId'] = $this->input->post('firebaseId');
            $data['phone'] = $this->input->post('phone');
            $data['email'] = $this->input->post('email');
            $data['password'] = md5($this->input->post('password'));
            $data['regId'] = 1;
            $data['deviceId'] = $this->input->post('deviceId');
            $data['deviceType'] = $this->input->post('deviceType');
            $data['created'] = date('Y-m-d H:i:s');

            if ($this->db->insert('users', $data)) {

                $inId = $this->db->insert_id();

                $userdata = $this->db->get_where('users', ['id' => $inId])->row_array();

                echo json_encode([
                    'status' => 1,
                    'message' => 'user registered',
                    'details' => $userdata
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

    public function login()
    {
        if ($this->input->post()) {

            if (!$this->input->post('email')) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'enter email'
                ]);
                exit;
            }

            if (!$this->input->post('password')) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'enter password'
                ]);
                exit;
            }

            $checkUser = $this->db->get_where('users', ['email' => $this->input->post('email'), 'password' => md5($this->input->post('password'))])->row_array();

            if (empty($checkUser)) {

                echo json_encode([
                    'status' => 0,
                    'message' => 'invalid credentials'
                ]);
                exit;
            } else {

                $this->db->set('regId', '1')->where('id', $checkUser['id'])->update('users');

                echo json_encode([
                    'status' => 1,
                    'message' => 'login success',
                    'details' => $checkUser
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'enter valid data'
            ]);
            exit;
        }
    }


    public function logout()
    {
        if ($this->input->post()) {

            $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (!!$checkUser) {

                $this->db->set('regId', '0')->where('id', $this->input->post('userId'))->update('users');
                echo json_encode([
                    'status' => 1,
                    'message' => 'User Loged Out'
                ]);
                exit;
            } else {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Inavlid UserId'
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'Enter Valid Parameters'
            ]);
            exit;
        }
    }

    public function resendOtp()
    {
        if ($this->input->post()) {

            if (!$this->input->post('phone')) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'phone Required'
                ]);
                exit;
            }

            if (10 < strlen($this->input->post('phone')) || 10 > strlen($this->input->post('phone'))) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Invalid phone Length'
                ]);
                exit;
            }

            $checkPhone = $this->db->get_where('otp', ['phone' => $this->input->post('phone')])->row_array();

            if (!!$checkPhone) {
                $this->db->where('phone', $this->input->post('phone'))->delete('otp');
            }

            $otp = rand(1000, 9999);
            $validFrom = date('H:i:s');
            $validTo = date('H:i:s', strtotime("+30 seconds"));

            $data['otp'] = $otp;
            $data['phone'] = $this->input->post('phone');
            $data['validFrom'] = $validFrom;
            $data['validTo'] = $validTo;

            if ($this->db->insert('otp', $data)) {

                echo json_encode([
                    'status' => 1,
                    'newOtp' => $otp
                ]);
                exit;
            } else {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Tech Error'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'Enter Valid Parameters'
            ]);
            exit;
        }
    }

    public function getAvatar()
    {
        $get = $this->db->get('cameos')->result_array();

        $final = [];
        foreach ($get as $avtar) {
            $avtar['image'] = base_url() . $avtar['image'];
            $final[] = $avtar;
        }
        if (empty($final)) {
            echo json_encode([
                'status' => 0,
                'message' => 'no response from db'
            ]);
            exit;
        }

        echo json_encode([
            'status' => 1,
            'message' => 'Avtar List',
            'details' => $final
        ]);
        exit;
    }

    public function uploadMedia()
    {
        if ($this->input->post()) {

            if (!$this->input->post('userId')) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'userId required'
                ]);
                exit;
            }

            $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkuser)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Invalid userId'
                ]);
                exit;
            }

            if (empty($_FILES['media']['name'])) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'media required'
                ]);
                exit;
            }

            $data['userId'] = $this->input->post('userId');
            if (!empty($_FILES['media']['name'])) {
                $data['media'] = $this->uploadVideo($_FILES['media']);
            }

            if ($this->input->post('access_status') > 3) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'invlid access_status'
                ]);
                exit;
            }

            if ($this->input->post('comments_status') > 1) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'invlid comments_status'
                ]);
                exit;
            }

            if ($this->input->post('type') > 2) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'invlid type'
                ]);
                exit;
            }

            $data['categoryId'] = 5;
            $data['description'] = $this->input->post('description');
            $data['hashtags'] = $this->input->post('hashtags');
            $data['people_tags'] = $this->input->post('people_tags');
            $data['access_status'] = $this->input->post('access_status');
            $data['comments_status'] = $this->input->post('comments_status');
            $data['type'] = $this->input->post('type');
            $data['date'] = date('Y-m-d');
            $data['time'] = date('H:i:s');
            $data['media_type'] = '1';

            if ($this->db->insert('shopping_media', $data)) {
                echo json_encode([
                    'status' => 1,
                    'message' => 'data inserted successfully',
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
                'message' => 'please enter valid parameters'
            ]);
            exit;
        }
    }

    public function shoppingMedia()
    {
        if ($this->input->post()) {

            if (!$this->input->post('userId')) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'userId required'
                ]);
                exit;
            }

            $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkuser)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Invalid userId'
                ]);
                exit;
            }

            if (empty($_FILES['media']['name'])) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'media required'
                ]);
                exit;
            }

            if ($this->input->post('type') > 2) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'invlid type'
                ]);
                exit;
            }

            if(!$this->input->post('categoryId')){
                echo json_encode([
                    'status' => 0,
                    'message' => 'categoryId required'
                ]);exit;
            }

            $category = $this->db->get_where('category', ['id' => $this->input->post('categoryId')])->row_array();
            if(empty($category)){
                echo json_encode([
                    'status' => 0,
                    'message' => 'inavlid categoryId'
                ]);exit;
            }

            // if(gettype($this->input->post('retail_price')) != 'integer'){

            //     echo json_encode([
            //         'status' => 0,
            //         'message' => 'invalid data type for retail price'
            //     ]);exit;

            // }

            // if(gettype($this->input->post('special_price')) != 'integer'){

            //     echo json_encode([
            //         'status' => 0,
            //         'message' => 'invalid data type for retail price'
            //     ]);exit;

            // }

            $ids = [];
            if (!empty($_FILES['productImages']['name'])) {

                $totalImages = count($_FILES['productImages']['name']);
                for ($i = 0; $i < $totalImages; $i++) {
                    $tmp_name = $_FILES['productImages']['tmp_name'][$i];
                    if (!empty($tmp_name)) {

                        $datas['media'] = $this->uploadVideo($_FILES['productImages']);
                        $datas['date'] = date('Y-m-d');
                        $datas['time'] = date('H:i:s');

                        $insert = $this->db->insert('shopping_images', $datas);

                        $id = $this->db->insert_id();

                        $ids[] = $id;
                    }
                }
            }

            $pid = implode(', ', $ids);

            if (!empty($_FILES['media']['name'])) {

                $details['media'] = $this->uploadVideo($_FILES['media']);
            }

            $data['media'] = $details['media'];
            $data['userId'] = $this->input->post('userId');
            $data['categoryId'] = $this->input->post('categoryId');
            $data['product_name'] = $this->input->post('product_name');
            $data['quantity'] = $this->input->post('quantity');
            $data['description'] = $this->input->post('description');
            $data['hashtags'] = $this->input->post('hashtags');
            $data['retail_price'] = $this->input->post('retail_price');
            $data['special_price'] = $this->input->post('special_price');
            $data['links'] = $this->input->post('links');
            $data['product_image_ids'] = $pid;
            $data['type'] = $this->input->post('type');
            $data['date'] = date('Y-m-d');
            $data['time'] = date('H:i:s');
            $data['media_type'] = '2';

            if ($this->db->insert('shopping_media', $data)) {

                echo json_encode([
                    'status' => 1,
                    'message' => 'data inserted successfully'
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
                'message' => 'please enter valid parameters'
            ]);
            exit;
        }
    }

   

    public function getUser()
    {
        if ($this->input->post()) {

            $checkFirebaseId = $this->db->get_where('users', ['firebaseId' => $this->input->post('firebaseId')])->row_array();

            if (empty($checkFirebaseId)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Invalid Firebase Id'
                ]);
                exit;
            } else {
                echo json_encode([
                    'status' => 1,
                    'message' => 'user found',
                    'details' => $checkFirebaseId
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'Enter valid Parameters'
            ]);
            exit;
        }
    }

    public function getUsers()
    {
        $data = $this->db->get('users')->result_array();

        if (empty($data)) {
            echo json_encode([
                'status' => 0,
                'message' => 'No User found',
            ]);
            exit;
        } else {
            echo json_encode([
                'status' => 1,
                'message' => 'Users found',
                'details' => $data
            ]);
            exit;
        }
    }

    private function curlFun($tbl)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://experience-e985f-default-rtdb.firebaseio.com/' . $tbl . '.json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: key=AAAA_OWAVTA:APA91bGI8bRXadj5p5pev41TcObp9SZboIC8jTq1sYqsC-rH-8VEjJ_SRw4zaKR4VqIk4Z0YRPWhArW32rLfslUmKUYYshFCLZUMajDkFzkP5d4j7YTKwIUQuOANev_ipsqUUE_G6aCX',
                'Content-Type: application/json'
            ),
        ));

        $data = json_decode(curl_exec($curl));

        curl_close($curl);

        return $data;
    }

    public function getReqeusts()
    {
        if ($this->input->post()) {

            $checkUser = $this->db->get_where('users', ['firebaseId' => $this->input->post('firebaseId')])->row_array();

            if (empty($checkUser)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'invalid Id'
                ]);
                exit;
            }

            $users = $this->curlFun('Friend_req');

            if (empty($users)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'No Request Found'
                ]);
                exit;
            }

            $firebaseUserId = $this->input->post('firebaseId');
            if (empty($users->$firebaseUserId)) {

                echo json_encode([
                    'status' => 0,
                    'message' => 'no request found'
                ]);
                exit;
            }
            $objUsers = $users->$firebaseUserId;


            $final = [];

            foreach ($objUsers as $key => $value) {
                if ($value->request_type == 'sent') {
                } else {

                    $getdetails = $this->db->get_where('users', ['firebaseId' => $key])->row_array();

                    $singleusers = $this->curlFun('Users');

                    foreach ($singleusers as $keyOne => $value) {
                        if ($keyOne == $key) {
                            $getdetails['photo'] = $value->photo;
                            $getdetails['fireBaseUserName'] = $value->username;
                        }
                    }

                    $final[] = $getdetails;
                }
            }

            if (empty($final)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'No Request Found'

                ]);
                exit;
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
                'message' => 'Please enter valid parameters'
            ]);
            exit;
        }
    }

    public function getMediaByUser()
    {
        if ($this->input->post()) {


            $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkUser)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Invalid userId'
                ]);
                exit;
            }

            $type = $this->input->post('type');

            if (!$this->input->post('type')) {
                $type = '0';
            }

            $mediaArr = [];


            // $media = $this->db->select('media_by_user.*, users.firebaseId, users.image userImage, users.username')
            //     ->from('media_by_user')
            //     ->where('userId', $this->input->post('userId'))
            //     ->where('type', $type)
            //     ->join('users', 'users.id = media_by_user.userId', 'left')
            //     ->get()->result_array();

            $mediaShopping = $this->db->select('shopping_media.*, users.firebaseId, users.image userImage, users.username')
                ->from('shopping_media')
                ->where('userId', $this->input->post('userId'))
                ->where('shopping_media.type', $type)
                ->join('users', 'users.id = shopping_media.userId', 'left')
                ->get()->result_array();

            // print_r($this->db->last_query());exit;

            foreach ($mediaShopping as $fetch) {
                $hh = $fetch['product_image_ids'];
                $fin = explode(',', $hh);
                $mediaa = [];
                foreach ($fin as $ids) {
                    $getMedia = $this->db->get_where('shopping_images', ['id' => $ids])->row_array();
                    $fetch['medias'] = '';

                    if (!!$getMedia) {
                        $mediaa[] = $getMedia['media'];
                        $fetch['medias'] = $mediaa;
                    }
                }

                $mediaArr[] = $fetch;
            }


            if ($type == '0') {

                // $media = $this->db->select('media_by_user.*, users.firebaseId, users.image userImage, users.username')
                //     ->from('media_by_user')
                //     ->where('userId', $this->input->post('userId'))
                //     ->join('users', 'users.id = media_by_user.userId', 'left')
                //     ->get()->result_array();

                $mediaShopping = $this->db->select('shopping_media.*, users.firebaseId, users.image userImage, users.username')
                    ->from('shopping_media')
                    ->where('userId', $this->input->post('userId'))
                    ->join('users', 'users.id = shopping_media.userId', 'left')
                    ->get()->result_array();


                foreach ($mediaShopping as $fetch) {

                    $fetch['incart'] = false;

                    $checkInCart = $this->db->get_where('addToCart', ['userId' => $this->input->post('userId'), 'shopping_media_id' => $fetch['id']])->row_array();

                    if (!!$checkInCart) {
                        $fetch['incart'] = true;
                    }
                    $hh = $fetch['product_image_ids'];
                    $fin = explode(',', $hh);
                    $mediaa = [];
                    foreach ($fin as $ids) {
                        $getMedia = $this->db->get_where('shopping_images', ['id' => $ids])->row_array();

                        $fetch['medias'] = '';

                        if (!!$getMedia) {
                            $mediaa[] = $getMedia['media'];
                            $fetch['medias'] = $mediaa;
                        }
                    }


                    $mediaArr[] = $fetch;
                }
            }

            $last = [];

            foreach ($mediaArr as $fin) {

                // print_r($fin);exit;

                $getLikeCount = $this->db->get_where('likeFeed', ['mediaId' => $fin['id']])->num_rows();
                $getcommentCount = $this->db->get_where('commentFeed', ['mediaId' => $fin['id']])->num_rows();
                $getbookmark = $this->db->get_where('bookmarkFeed', ['mediaId' => $fin['id'], 'userId' => $this->input->post('userId')])->row_array();

                if (empty($getbookmark)) {

                    $book = false;
                } else {
                    $book = true;
                }

                $checkWihlist = $this->db->get_where('wishlist', ['mediaId' => $fin['id'], 'userId' => $this->input->post('userId')])->row_array();

                if (empty($checkWihlist)) {

                    $wish = 0;
                } else {

                    $wish = 1;
                }

                $fin['likeStatus'] = 0;
                $like = $this->db->get_where('likeFeed', ['mediaId' =>  $fin['id'], 'userId' => $this->input->post('userId')])->row_array();
                if(!!($like)){
                    $fin['likeStatus'] = 1;
                }
                $fin['likeCount'] = $getLikeCount;
                $fin['commentCount'] = $getcommentCount;
                $fin['shareCount'] = 0;
                $fin['shoppingStatus'] = 0;
                $fin['bookmarkStatus'] = $book;
                $fin['wishlistStatus'] = $wish;

                $last[] = $fin;
            }

            if (empty($last)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Media not found'
                ]);
                exit;
            }

            echo json_encode([
                'status' => 1,
                'message' => 'media found',
                'details' => $last
            ]);
            exit;
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'Please enter valid parameters'
            ]);
            exit;
        }
    }

    public function getAllMedia()
    {


        if ($this->input->post()) {

            $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();


            if (empty($checkUser)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Invalid userId'
                ]);
                exit;
            }

            $type = $this->input->post('type');

            if (!$this->input->post('type')) {
                $type = '0';
            }

            $mediaArr = [];


            // $media = $this->db->select('media_by_user.*, users.firebaseId, users.image userImage, users.username')
            //     ->from('media_by_user')
            //     ->where('type', $type)
            //     ->join('users', 'users.id = media_by_user.userId', 'left')
            //     ->get()->result_array();

            $mediaShopping = $this->db->select('shopping_media.*, users.firebaseId, users.image userImage, users.username')
                ->from('shopping_media')
                ->where('type', $type)
                ->join('users', 'users.id = shopping_media.userId', 'left')
                ->get()->result_array();

            foreach ($mediaShopping as $fetch) {
                $hh = $fetch['product_image_ids'];
                $fin = explode(',', $hh);
                $mediaa = [];
                foreach ($fin as $ids) {
                    $getMedia = $this->db->get_where('shopping_images', ['id' => $ids])->row_array();
                    $fetch['medias'] = "";

                    if (!!($getMedia)) {

                        $mediaa[] = $getMedia['media'];
                        $fetch['medias'] = $mediaa;
                    }
                }

                $mediaArr[] = $fetch;
            }


            if ($type == '0') {

                // $media = $this->db->select('media_by_user.*, users.firebaseId, users.image userImage, users.username')
                //     ->from('media_by_user')
                //     ->join('users', 'users.id = media_by_user.userId', 'left')
                //     ->get()->result_array();

                $mediaShopping = $this->db->select('shopping_media.*, users.firebaseId, users.image userImage, users.username')
                    ->from('shopping_media')
                    ->join('users', 'users.id = shopping_media.userId', 'left')
                    ->get()->result_array();

                // print_r($this->db->last_query());exit;
                // print_r($mediaShopping);exit;


                foreach ($mediaShopping as $fetch) {
                    $fetch['incart'] = false;

                    $checkInCart = $this->db->get_where('addToCart', ['userId' => $this->input->post('userId'), 'shopping_media_id' => $fetch['id']])->row_array();

                    if (!!$checkInCart) {
                        $fetch['incart'] = true;
                    }
                    // print_r($fetch);exit;
                    $hh = $fetch['product_image_ids'];
                    $fin = explode(',', $hh);
                    $mediaa = [];
                    foreach ($fin as $ids) {


                        $getMedia = $this->db->get_where('shopping_images', ['id' => $ids])->row_array();

                        $fetch['medias'] = "";

                        if (!!($getMedia)) {

                            $mediaa[] = $getMedia['media'];
                            $fetch['medias'] = $mediaa;
                        }
                    }


                    $mediaArr[] = $fetch;
                }
            }

            $last = [];

            foreach ($mediaArr as $fin) {

                // print_r($fin);exit;

                $getLikeCount = $this->db->get_where('likeFeed', ['mediaId' => $fin['id']])->num_rows();
                $getcommentCount = $this->db->get_where('commentFeed', ['mediaId' => $fin['id']])->num_rows();

                $checkLike = $this->db->get_where('likeFeed', ['mediaId' => $fin['id'], 'userId' => $this->input->post('userId')])->row_array();

                $likeStatus = true;

                if (empty($checkLike)) {
                    $likeStatus = false;
                }

                $getbookmark = $this->db->get_where('bookmarkFeed', ['mediaId' => $fin['id'], 'userId' => $this->input->post('userId')])->row_array();

                if (empty($getbookmark)) {

                    $book = false;
                } else {
                    $book = true;
                }

                $checkWihlist = $this->db->get_where('wishlist', ['mediaId' => $fin['id'], 'userId' => $this->input->post('userId')])->row_array();

                if (empty($checkWihlist)) {

                    $wish = 0;
                } else {

                    $wish = 1;
                }

                $fin['likeStatus'] = $likeStatus;
                $fin['likeCount'] = $getLikeCount;
                $fin['commentCount'] = $getcommentCount;
                $fin['shareCount'] = 0;
                $fin['shoppingStatus'] = 0;
                $fin['bookmarkStatus'] = $book;
                $fin['wishlistStatus'] = $wish;

                $last[] = $fin;
            }

            if (empty($last)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Media not found'
                ]);
                exit;
            }

            echo json_encode([
                'status' => 1,
                'message' => 'media found',
                'details' => $last
            ]);
            exit;
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'Please enter valid parameters'
            ]);
            exit;
        }
    }



    public function getHashtags()
    {
        $get = $this->db->get('hashtags')->result_array();

        if (empty($get)) {
            echo json_encode([
                'status' => 0,
                'message' => 'No Hashtag List Found'
            ]);
            exit;
        }

        echo json_encode([
            'status' => 1,
            'message' => 'HashTag List Found',
            'list' => $get
        ]);
        exit;
    }

    public function createHashtag()
    {
        if ($this->input->post()) {

            if (!$this->input->post('hashtag')) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'hashtag required'
                ]);
                exit;
            }

            $data['hashtag'] = $this->input->post('hashtag');
            $data['date'] = date('Y-m-d H:i:s');

            if ($this->db->insert('hashtags', $data)) {

                echo json_encode([
                    'status' => 1,
                    'message' => 'hashtag created',
                ]);
                exit;
            } else {
                echo json_encode([
                    'status' => 0,
                    'message' => 'db error'
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

    public function userNotFriends()
    {
        if ($this->input->post()) {

            if (!$this->input->post('firebaseId')) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'firebaseId is required'
                ]);
                exit;
            }

            $checkuser = $this->db->get_where('users', ['firebaseId' => $this->input->post('firebaseId')])->row_array();

            if (empty($checkuser)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Invalid firebaseId'
                ]);
                exit;
            }

            $friends = $this->curlFun('Friends');

            $friendsArray = [];

            $id = $this->input->post('firebaseId');

            $final = [];

            if (empty($friends->$id)) {

                // $final[] = $this->db->get('users')->result_array();
                $where = "firebaseId != '$id'";
                $final[] = $this->db->where($where)
                    ->get('users')
                    ->result_array();
            } else {

                $friendsArray[] = $friends->$id;


                $friends = [];

                foreach ($friendsArray as $myFriends) {

                    foreach ($myFriends as $key => $friendd) {
                        $friends[] = $key;
                    }
                }

                $ids = implode(',', $friends);

                $where = "firebaseId NOT IN ($ids)";
                $get = $this->db->select('*')
                    ->from('users')
                    ->where_not_in('firebaseId', $ids)
                    ->get()
                    ->result_array();
                $final[] = $get;
            }

            if (empty($final)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'no user found'
                ]);
                exit;
            }

            echo json_encode([
                'status' => 1,
                'message' => 'users found',
                'details' => $final[0]

            ]);
            exit;
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'enter valid data'
            ]);
            exit;
        }
    }

    public function updateProfile()
    {
        if ($this->input->post()) {

            if (!$this->input->post('image')) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'image required'
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

            $data['image'] = $this->input->post('image');

            if ($this->db->set($data)->where('id', $this->input->post('userId'))->update('users')) {
                echo json_encode([
                    'status' => 1,
                    'message' => 'user profile updated'
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

    public function likeDislike()
    {
        if ($this->input->post()) {

            // verify user
            $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkUser)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'invalid userId'
                ]);
                exit;
            }

            // verify media
            $checkMedia = $this->db->get_where('shopping_media', ['id' => $this->input->post('mediaId')])->row_array();

            if (empty($checkMedia)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'invalid mediaId'
                ]);
                exit;
            }

            // check: media is liked or not liked
            $checkLikeStatus = $this->db->get_where('likeFeed', ['mediaId' => $this->input->post('mediaId'), 'userId' => $this->input->post('userId')])->row_array();

            if (empty($checkLikeStatus)) {

                $data['mediaId'] = $this->input->post('mediaId');
                $data['userId'] = $this->input->post('userId');
                $data['created'] = date('Y-m-d H:i:s');

                if ($this->db->insert('likeFeed', $data)) {

                    $getLikeCount = $this->db->get_where('likeFeed', ['mediaId' => $this->input->post('mediaId')])->num_rows();

                    echo json_encode([
                        'status' => 1,
                        'message' => 'media liked',
                        'details' =>  '' . $getLikeCount . ''
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

                if ($this->db->delete('likeFeed', ['mediaId' => $this->input->post('mediaId'), 'userId' => $this->input->post('userId')])) {
                    $getLikeCount = $this->db->get_where('likeFeed', ['mediaId' => $this->input->post('mediaId')])->num_rows();
                    echo json_encode([
                        'status' => 2,
                        'message' => 'media disliked',
                        'details' =>  '' . $getLikeCount . ''
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

            // print_r($checkMedia);exit;

        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'enter valid parameters'
            ]);
            exit;
        }
    }

    public function commentMedia()
    {
        if ($this->input->post()) {

            // verify user
            $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkUser)) {
                $this->sendMessage(0, 'invalid userId', 0);
            }

            // verify media
            // $checkMedia = $this->db->get_where('media_by_user', ['id' => $this->input->post('mediaId')])->row_array();
            $checkMediaa = $this->db->get_where('shopping_media', ['id' => $this->input->post('mediaId')])->row_array();

            if (empty($checkMedia) && empty($checkMediaa)) {
                $this->sendMessage(0, 'invalid mediaId', 0);
            }

            $data['mediaId'] = $this->input->post('mediaId');
            $data['userId'] = $this->input->post('userId');
            $data['comment'] = $this->input->post('comment');
            $data['created'] = date('Y-m-d H:i:s');

            if ($this->db->insert('commentFeed', $data)) {

                $get = $this->db->get_where('commentFeed', ['mediaId' => $this->input->post('mediaId')])->num_rows();
                $row  = '' . $get . '';


                $this->sendMessage(1, 'comment added', $row);
            } else {
                $this->sendMessage(0, 'tech error', 0);
            }
        } else {
            $this->sendMessage(0, 'enter valid parameters', 0);
        }
    }

    private function sendMessage($type, $message, $details)
    {

        if ($details == '0') {
            echo json_encode([
                'status' => $type,
                'message' => $message
            ]);
            exit;
        } else {
            echo json_encode([
                'status' => $type,
                'message' => $message,
                'details' => $details
            ]);
            exit;
        }
    }

    public function getMediaComments()
    {
        if ($this->input->post()) {

            // verify media
            // $checkMedia = $this->db->get_where('media_by_user', ['id' => $this->input->post('mediaId')])->row_array();
            $checkShoppingMedia = $this->db->get_where('shopping_media', ['id' => $this->input->post('mediaId')])->row_array();

            if (empty($checkMedia) && empty($checkShoppingMedia)) {

                $this->sendMessage(0, 'invalid mediaId', 0);
            }

            // get comments 

            $getComments = $this->db->get_where('commentFeed', ['mediaId' => $this->input->post('mediaId')])->result_array();

            if (empty($getComments)) {

                $this->sendMessage(0, 'no comments found', 0);
            } else {

                // print_r($getComments);exit;

                $final = [];
                foreach ($getComments as $comment) {

                    $get = $this->db->get_where('users', ['id' => $comment['userId']])->row_array();

                    $comment['username'] = $get['username'];
                    $comment['userimage'] = $get['image'] ?: "";

                    $final[] = $comment;
                }

                $this->sendMessage(1, 'comments found', $final);
            }
        } else {

            $this->sendMessage(0, 'enter valid parameters', 0);
        }
    }

    public function bookmarkMedia()
    {
        if ($this->input->post()) {

            // verify user
            $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkUser)) {
                $this->sendMessage(0, 'invalid userId', 0);
            }

            // verify media
            $checkMedia = $this->db->get_where('shopping_media', ['id' => $this->input->post('mediaId')])->row_array();

            if (empty($checkMedia)) {
                $this->sendMessage(0, 'invalid mediaId', 0);
            }

            if ($this->input->post('type') > 2 || $this->input->post('type') < 1) {
                $this->sendMessage(0, 'inavlid type', 0);
            }

            // check book mark status 

            $checkBookMarkStatus = $this->db->get_where('bookmarkFeed', ['mediaId' => $this->input->post('mediaId'), 'userId' => $this->input->post('userId')])->row_array();

            if (empty($checkBookMarkStatus)) {

                $data['mediaId'] = $this->input->post('mediaId');
                $data['userId'] = $this->input->post('userId');
                $data['type'] = $this->input->post('type');
                $data['created'] = date('Y-m-d H:i:s');

                if ($this->db->insert('bookmarkFeed', $data)) {
                    $this->sendMessage(1, 'book mark added', 0);
                } else {
                    $this->sendMessage(0, 'tech error', 0);
                }
            } else {

                if ($this->db->delete('bookmarkFeed', ['userId' => $this->input->post('userId'), 'mediaId' => $this->input->post('mediaId')])) {
                    $this->sendMessage(2, 'bookmark removed', 0);
                } else {
                    $this->sendMessage(0, 'tech error', 0);
                }
            }
        } else {
            $this->sendMessage(0, 'enter valid parameters', 0);
        }
    }

    public function addToCart()
    {
        if ($this->input->post()) {

            $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkuser)) {
                $this->sendMessage(0, 'invalid userId', 0);
            }

            $checkShoppingMedia = $this->db->get_where('shopping_media', ['id' => $this->input->post('shopping_media_id')])->row_array();

            if (empty($checkShoppingMedia)) {
                $this->sendMessage(0, 'invalid shopping_media_id', 0);
            }

            $checkInCart = $this->db->get_where('addToCart', ['shopping_media_id' => $this->input->post('shopping_media_id'), 'userId' => $this->input->post('userId')])->row_array();

            if (!!$checkInCart) {
                $this->sendMessage(2, 'product already in cart', 0);
            }

            if ($this->input->post('quantity') <= '0' || !$this->input->post('quantity')) {
                $this->sendMessage(0, 'quantity can not be less then 1 or empty', 0);
            }

            if ($this->input->post('quantity') > $checkShoppingMedia['quantity']) {
                $this->sendMessage(0, 'insufficient quantity', 0);
            }

            $checkIncart = $this->db->get_where('addToCart', ['userId' => $this->input->post('userId'), 'shopping_media_id' => $this->input->post('shopping_media_id')])->row_array();

            $data['userId'] = $this->input->post('userId');
            $data['shopping_media_id'] = $this->input->post('shopping_media_id');
            $data['quantity'] = $this->input->post('quantity');
            $data['single_price'] = $checkShoppingMedia['special_price'];
            $data['total_price'] =  $checkShoppingMedia['special_price'] * $this->input->post('quantity');

            if ($this->db->insert('addToCart', $data)) {

                $this->sendMessage(1, 'product added to cart', 0);
            } else {

                $this->sendMessage(0, 'tech error', 0);
            }
        } else {
            $this->sendMessage(0, 'enter valid parametrs', 0);
        }
    }

    public function myCart()
    {
        if ($this->input->post()) {

            $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkUser)) {
                $this->sendMessage(0, 'invalid userId', 0);
            }

            $getCart = $this->db->get_where('addToCart', ['userId' => $this->input->post('userId')])->result_array();

            if (empty($getCart)) {
                $this->sendMessage(0, 'cart empty', 0);
            }

            $final = [];
            $total = 0;
            foreach ($getCart as $mycart) {
                $total += $mycart['total_price'];
                $getDetails = $this->db->get_where('shopping_media', ['id' => $mycart['shopping_media_id']])->row_array();

                $mycart['productDetails'] = $getDetails;

                $final[] = $mycart;
            }

            $this->sendMessage(1, '' . $total . '', $final);
        } else {
            $this->sendMessage(0, 'enter valid parameters', 0);
        }
    }

    public function getBookMark()
    {
        if ($this->input->post()) {

            $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkUser)) {
                $this->sendMessage(0, 'invalid UserId', 0);
            }

            $checkbook = $this->db->get_where('bookmarkFeed', ['userId' => $this->input->post('userId')])->result_array();

            if (empty($checkbook)) {
                $this->sendMessage(0, 'invalid bookmarkId', 0);
            }

            foreach ($checkbook as $book) {
                if ($book['type'] == 1) {

                    $getDetails = $this->db->get_where('shopping_media', ['id' => $book['mediaId']])->row_array();
                    $mtype = 1;
                } else if ($book['type'] == 2) {

                    $getDetails = $this->db->get_where('shopping_media', ['id' => $book['mediaId']])->row_array();
                    $mtype = 2;
                }

                $getDetails['type'] = $mtype;
                $final[] = $getDetails;
            }

            $this->sendMessage(1, 'list found', $final);
        } else {
            $this->sendMessage(0, 'enetr valid parameters', 0);
        }
    }

    public function deleteFromCart()
    {
        if ($this->input->post()) {

            $checksuer = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
            if (empty($checksuer)) {
                $this->sendMessage(0, 'invalid userId', 0);
            }

            $checkShoppingId = $this->db->get_where('addToCart', ['id' => $this->input->post('cartId')])->row_array();
            if (empty($checkShoppingId)) {
                $this->sendMessage(0, 'inavlid cartId', 0);
            }

            if ($this->db->delete('addToCart', ['id' => $this->input->post('cartId')])) {
                $this->sendMessage(1, 'product removed from cart', 0);
            } else {
                $this->sendMessage(0, 'DB error', 0);
            }
        } else {
            $this->sendMessage(0, 'enter valid parameters', 0);
        }
    }

    public function getPrivateMedia()
    {
        if ($this->input->post()) {

            $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkUser)) {
                $this->sendMessage(0, 'invalid userId', 0);
            }

            $getMedia = $this->db->get_where('shopping_media', ['userId' => $this->input->post('userId'), 'access_status' => '3'])->result_array();

            if (empty($getMedia)) {
                $this->sendMessage(0, 'no data found', 0);
            } else {
                $this->sendMessage(1, 'data found', $getMedia);
            }
        } else {
            $this->sendMessage(0, 'enter valid parameters', 0);
        }
    }

    public function wishlist()
    {
        if ($this->input->post()) {

            $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkuser)) {
                $this->sendMessage(0, 'invalid userId', 0);
            }

            $checkMedia = $this->db->get_where('shopping_media', ['id' => $this->input->post('mediaId')])->row_array();

            if (empty($checkMedia)) {
                $this->sendMessage(0, 'invalid mediaId', 0);
            }

            $checkWishlist = $this->db->get_where('wishlist', ['userId' => $this->input->post('userId'), 'mediaId' => $this->input->post('mediaId')])->row_array();

            if (empty($checkWishlist)) {

                $data['userId'] = $this->input->post('userId');
                $data['mediaId'] = $this->input->post('mediaId');

                if ($this->db->insert('wishlist', $data)) {
                    $this->sendMessage(1, 'wishlist added', 0);
                } else {
                    $this->sendMessage(0, 'DB error', 0);
                }
            } else {

                if ($this->db->delete('wishlist', ['userId' => $this->input->post('userId'), 'mediaId' => $this->input->post('mediaId')])) {
                    $this->sendMessage(2, 'removed from wishlist', 0);
                } else {
                    $this->sendMessage(0, 'Db error', 0);
                }
            }
        } else {
            $this->sendMessage(0, 'enter valid parameters', 0);
        }
    }

    public function getWishlist()
    {
        if ($this->input->post()) {

            $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkUser)) {
                $this->sendMessage(0, 'invalid userId', 0);
            }

            $get = $this->db->get_where('wishlist', ['userId' => $this->input->post('userId')])->result_array();

            if (empty($get)) {
                $this->sendMessage(0, 'wishlist empty', 0);
            } else {
                $final = [];
                foreach ($get as $gets) {
                    $details = $this->db->get_where('shopping_media', ['id' => $gets['mediaId']])->row_array();



                    $final[] = $details;
                }

                if (empty($final)) {
                    $this->sendMessage(0, 'wishlist empty', 0);
                }

                $this->sendMessage(1, 'wishlist found', $final);
            }
        } else {
            $this->sendMessage(0, 'enter valid parameters', 0);
        }
    }

    public function addAdress()
    {
        if ($this->input->post()) {

            $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkuser)) {
                $this->sendMessage(0, 'invalid userId', 0);
            }

            $data['userId'] = $this->input->post('userId');
            $data['name'] = $this->input->post('name');
            $data['number'] = $this->input->post('number');
            $data['alternatenumber'] = $this->input->post('alternatenumber') ?: "";
            $data['pincode'] = $this->input->post('pincode');
            $data['city'] = $this->input->post('city');
            $data['state'] = $this->input->post('state');
            $data['country'] = $this->input->post('country');
            $data['address'] = $this->input->post('address');
            $data['date'] = date('Y-m-d');

            if ($this->db->insert('address', $data)) {
                $this->sendMessage(1, 'address added', 0);
            } else {
                $this->sendMessage(0, 'tech error', 0);
            }
        } else {
            $this->sendMessage(0, 'enter valid parameters', 0);
        }
    }

    public function getAddress()
    {
        if ($this->input->post()) {

            $checkuser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkuser)) {
                $this->sendMessage(0, 'invalid userId', 0);
            }

            $addresses = $this->db->get_where('address', ['userId' => $this->input->post('userId')])->result_array();

            // print_r($addresses);exit;

            if (!empty($addresses)) {
                $this->sendMessage(1, 'address found', $addresses);
            } else {
                $this->sendMessage(0, 'no address found', 0);
            }
        } else {
            $this->sendMessage(0, 'invalid userId', 0);
        }
    }

    public function deleteAddress()
    {
        if ($this->input->post()) {

            $checkAddress = $this->db->get_where('address', ['id' => $this->input->post('addressId')])->row_array();

            if (empty($checkAddress)) {
                $this->sendMessage(0, 'inavlid addressId', 0);
            }

            if ($this->db->delete('address', ['id' => $this->input->post('addressId')])) {
                $this->sendMessage(1, 'address deleted', 0);
            } else {
                $this->sendMessage(0, 'tech error', 0);
            }
        } else {
            $this->sendMessage(0, 'invalid parameters', 0);
        }
    }

    public function calculateTotal()
    {
        if ($this->input->post()) {

            $checkUser = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checkUser)) {
                $this->sendMessage(0, 'invalid userId', 0);
            }

            $getProducts = $this->db->get_where('addToCart', ['userId' => $this->input->post('userId')])->result_array();

            // print_r($getProducts);exit;
            foreach ($getProducts as $products) {

                $getDetails = $this->db->get_where('shopping_media', ['id' => $products['shopping_media_id']])->row_array();


                $userQuantity = $products['quantity'];
                $productQuantity = $getDetails['quantity'];
                if ($productQuantity < $userQuantity) {
                    $errorMessage['quantity'] = $productQuantity;
                    $errorMessage['id'] = $products['shopping_media_id'];
                    $this->sendMessage(0, 'insufficient product quantity', $errorMessage);
                }
            }



            $taxPercentage = 10;

            $data['subtotal'] = $this->input->post('subtotal');
            $data['tax'] = ($taxPercentage / 100) * $data['subtotal'];
            $data['shippingChargers'] = 5.0;
            $data['reward'] = 0.0;
            $data['adminCommision'] = 1.9;
            $data['total'] = array_sum($data);


            $this->sendMessage(1, 'order summary', $data);
        } else {
            $this->sendMessage(0, 'enter valid parameters', 0);
        }
    }

    public function checkout()
    {
        if ($this->input->post()) {

            $checksuer = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if (empty($checksuer)) {
                $this->sendMessage(0, 'invalid userId', 0);
            }

            $getProducts = $this->db->get_where('addToCart', ['userId' => $this->input->post('userId')])->result_array();

            $final = [];
            $final['subtotal'] = 0;
            foreach ($getProducts as $products) {

                $getDetails = $this->db->get_where('shopping_media', ['id' => $products['shopping_media_id']])->row_array();

                $userQuantity = $products['quantity'];
                $productQuantity = $getDetails['quantity'];
                if ($productQuantity < $userQuantity) {
                    $errorMessage['quantity'] = $productQuantity;
                    $errorMessage['id'] = $products['shopping_media_id'];
                    $this->sendMessage(0, 'insufficient product quantity', $errorMessage);
                }
                $productQuantity -= $userQuantity;

                $this->db->set('quantity', $productQuantity)->where('id', $products['shopping_media_id'])->update('shopping_media');

                $final['subtotal'] += $products['total_price'];
            }



            $taxPercentage = 10;

            $rowdata['subtotal'] = $final['subtotal'];
            $rowdata['tax'] = ($taxPercentage / 100) * $rowdata['subtotal'];
            $rowdata['shippingChargers'] = 5.0;
            $rowdata['reward'] = 0.0;
            $rowdata['adminCommision'] = 1.9;
            $rowdata['total'] = array_sum($rowdata);

            $data['userId'] = $this->input->post('userId');
            $data['productIds'] = $this->input->post('productIds');
            $data['subtotal'] = $rowdata['subtotal'];
            $data['shipping'] = $rowdata['shippingChargers'];
            $data['tax'] = $rowdata['tax'];
            $data['adminCommision'] = $rowdata['adminCommision'];
            $data['total'] = $rowdata['total'];

            if ($this->db->insert('shoppingHistory', $data)) {
                $this->sendMessage(1, 'product purchased', 0);
            } else {
                $this->sendMessage(0, 'tech error', 0);
            }
        } else {
            $this->sendMessage(0, 'enter valid parameters', 0);
        }
    }

    public function userOverAllData()
    {
        if ($this->input->post()) {

            if (!$this->input->post('firebaseId')) {
                $this->sendMessage(0, 'firebaseId required', 0);
            }

            $getUser = $this->db->get_where('users', ['firebaseId' => $this->input->post('firebaseId')])->row_array();
            if (empty($getUser)) {
                $this->sendMessage(0, 'invalid firebaseId', 0);
            }

            $id = $this->input->post('firebaseId');

            $friends = $this->curlFun('Friends');

            if (empty($friends)) {
                $arList = [];
            } else {

                $list = $friends->$id;

                $arList = (array)$list;
            }


            $data['friendsCount'] = count($arList);
            $data['coins'] = 0;
            $data['review'] = 0;

            $likeCount = 0;

            $getMedia = $this->db->get_where('shopping_media', ['userId' => $getUser['id'], 'media_type' => '1'])->result_array();
            if (empty($getMedia)) {
                $likeCount = 0;
            } else {

                foreach ($getMedia as $media) {

                    $getCount = $this->db->get_where('likeFeed', ['mediaId' => $media['id']])->num_rows();
                    $likeCount += $getCount;
                }
            }

            $data['likeCount'] = $likeCount;

            $this->sendMessage(1, 'data found', $data);
        } else {
            $this->sendMessage(0, 'enter valid parameters', 0);
        }
    }

    public function deleteMedia(){
        if($this->input->post()){

            $getMedia = $this->db->get_where('shopping_media', ['userId' => $this->input->post('userId'), 'id' => $this->input->post('mediaId')])->row_array();
            if(empty($getMedia)){
                $this->sendMessage(0,'No Media Found',0);exit;
            }

            if($this->db->delete('shopping_media', ['id' => $this->input->post('mediaId')])){

                $this->sendMessage(1,'Media Deleted',0);exit;

            }else{
                $this->sendMessage(0,'DB error',0);exit;
            }

        }else{
            $this->sendMessage(0,'method not allowed',0);
        }
    }

    protected function uploadVideo($file)
    {
        require APPPATH . '/libraries/vendor/autoload.php';

        try {

            $client = \Aws\S3\S3Client::factory([
                'version' => 'latest',
                'region'  => 'us-east-2',
                'credentials' => [
                    'key'    => "AKIAXUGXTAECMXYPFZVP",
                    'secret' => "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",
                ]
            ]); //exit;

            $return = $client->putObject([
                'Bucket'     => 'webbaysys',
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

    private function secretToken(){

        require_once('application/libraries/stripe-php/init.php');

        $stripe = new \Stripe\StripeClient('sk_test_iX6rMKNGZWBT7LCVgivd0vFE00RgKQOOlg');

        return $stripe;
        
    }

    public function createOrderFromCart(){
        if($this->input->post()){

            $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();

            if(empty($user)){
                $this->sendMessage(0,'invalid userId',0);
            }

            $products = $this->db->get_where('addToCart', ['userId' => $this->input->post('userId')])->result_array();

            if(empty($products)){
                $this->sendMessage(0,'no product inCart',0);
            }

            $address = $this->db->get_where('address', ['userId' => $this->input->post('userId'), 'id' => $this->input->post('addressId')])->row_array();

            if(empty($address)){
                $this->sendMessage(0,'invalid addressId',0);
            }

            $ids = [];
            $totalPrice = 0;
            foreach($products as $product){

                $ids[] = $product['shopping_media_id'];
                $totalPrice += $product['total_price'];

            }

            $data['productId'] = implode(",", $ids);
            $orderdetails = $this->db->from('orderDetails')->order_by('id', 'desc')->get()->row_array();

            if(empty($orderdetails)){
                $data['orderCal'] = 5555;
            }else{
                $orderdetails['orderCal'] += 1;
                $data['orderCal'] = $orderdetails['orderCal'];
            }

            $data['userId'] = $this->input->post('userId');
            $data['orderId'] = 'EXPODRID' . $data['orderCal'];
            $data['purchasePrice'] = $totalPrice;
            $data['addressId'] = $this->input->post('addressId');
            $data['status'] = 'pending';
            $data['date'] = date('Y-m-d');
            $data['time'] = date('H:i:s');

            $this->db->delete('orderDetails', ['userId' => $this->input->post('userId'), 'status' => 'pending']);
            if($this->db->insert('orderDetails', $data)){
                $id = $this->db->insert_id();
                $get = $this->db->get_where('orderDetails', ['id' => $id])->row_array();
                // print_r($this->db->last_query());exit;
                $this->sendMessage(1,'orderId generated', $get);
            }else{
                $this->sendMessage(0,'DB Error',0);
            }


        }else{
            $this->sendMessage(0,'method nopt allowed',0);
        }
    }

    public function createOrderDirectBuy(){

        if($this->input->post()){

            $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
            if(empty($user)){
                $this->sendMessage(0,'invalid userId',0);
            }

            $product = $this->db->get_where('shopping_media', ['id' => $this->input->post('productId'), 'media_type' => '2'])->row_array();
            if(empty($product)){
                $this->sendMessage(0,'invalid productId',0);
            }

            $address = $this->db->get_where('address', ['userId' => $this->input->post('userId'), 'id' => $this->input->post('addressId')])->row_array();

            if(empty($address)){
                $this->sendMessage(0,'invalid addressId',0);
            }


            $data['productId'] = $this->input->post('productId');
            $orderdetails = $this->db->from('orderDetails')->order_by('id', 'desc')->get()->row_array();

            if(empty($orderdetails)){
                $data['orderCal'] = 5555;
            }else{
                $orderdetails['orderCal'] += 1;
                $data['orderCal'] = $orderdetails['orderCal'];
            }

            $data['userId'] = $this->input->post('userId');
            $data['orderId'] = 'EXPODRID' . $data['orderCal'];
            $data['purchasePrice'] = $this->input->post('price');
            $data['quantity'] = $this->input->post('quantity');
            $data['addressId'] = $this->input->post('addressId');
            $data['status'] = 'pending';
            $data['date'] = date('Y-m-d');
            $data['time'] = date('H:i:s');

            $this->db->delete('orderDetails', ['userId' => $this->input->post('userId'), 'status' => 'pending']);
            if($this->db->insert('orderDetails', $data)){
                $id = $this->db->insert_id();
                $get = $this->db->get_where('orderDetails', ['id' => $id])->row_array();
                // print_r($this->db->last_query());exit;
                $this->sendMessage(1,'orderId generated', $get);
            }else{
                $this->sendMessage(0,'DB Error',0);
            }

        }else{
            $this->sendMessage(0,'Method not allowed',0);
        }

    }

    public function regeisterCard()
	{
		try {

            // $check = $this->db->get_where('customerCardDetails', ['cardNumber' => $this->input->post('cardNumber'), 'save' => '1'])->row_array();
            // if(!!$check){
            //     $this->sendMessage(0,'card already in use',0);exit;
            // }

			$stripe = $this->secretToken();

			// generating token for card
			$token = $stripe->tokens->create([
				'card' => [
					'number' => $this->input->post('cardNumber'),
					'exp_month' => $this->input->post('expMonth'),
					'exp_year' =>  $this->input->post('expYear'),
					'cvc' =>  $this->input->post('cvv'),
				],
			]);

			// creating customer
			$customer = $stripe->customers->create([

				'email' =>  $this->input->post('email'),
				'source' => $token

			]);

			// print_r($customer->id);exit;

			$data['userId'] = $this->input->post('userId');
			$data['customerId'] = $customer->id;
			$data['cardNumber'] = $this->input->post('cardNumber');
			$data['expMonth'] = $this->input->post('expMonth');
			$data['expYear'] = $this->input->post('expYear');
			$data['cvv'] = $this->input->post('cvv');
			$data['cardType'] = $token['card']->brand;
			$data['country'] = $token['card']->country;
			$data['save'] = $this->input->post('save');
			$data['date'] = date('y-m-d');

			if ($this->db->insert('customerCardDetails', $data)) {

				echo json_encode([
					'status' => 1,
					'message' => 'data inserted',
					'data' => $data
				]);
				exit;
			} else {
				echo json_encode([
					'status' => 0,
					'message' => 'DB error'
				]);
				exit;
			}
		} catch (exception $e) {

			echo json_encode([
				'status' => 0,
				'message' => $e->getMessage()
			]);
			exit;
		}
	}

    public function makePayment(){

        if($this->input->post()){

            try{

                $orderId = $this->db->get_where('orderDetails', ['orderId' => $this->input->post('orderId'), 'status' => 'pending'])->row_array();
                if(empty($orderId)){
                    $this->sendMessage(0,'invalid orderId or order completed',0);
                }
    
                $customerId = $this->db->get_where('customerCardDetails', ['customerId' => $this->input->post('customerId')])->row_array();
                if(empty($customerId)){
                    $this->sendMessage(0,'invalid customerId',0);
                }
    
                if($customerId['userId'] != $orderId['userId']){
                    $this->sendMessage(0,'orderId and customerId userId not match',0);exit;
                }

                $stripe = $this->secretToken();

                $charge = $stripe->paymentIntents->create([
                    'customer' => $customerId['customerId'],
                    'amount' => $orderId['purchasePrice'],
                    'currency' => 'usd',
                    'description' => 'coins',
                    'automatic_payment_methods' => [
                        'enabled' => true,
                    ],
                    'metadata' => array(
                        'order_id' => $orderId['orderId']
                    )
                ]);


                $data['purchaseId'] = $charge->id;
                $data['cusId'] = $charge->customer;
                $data['purchaseDate'] = date('Y-m-d h:i:s');
                $data['status'] = 'completed';

                $this->db->set($data)->where('orderId', $this->input->post('orderId'))->update('orderDetails');
                $allProducts = $this->db->get_where('addToCart', ['userId' => $customerId['userId']])->result_array();
                if(!!$allProducts){

                    foreach($allProducts as $product){

                        $this->db->delete('wishlist', ['userId' => $product['userId'], 'mediaId' => $product['shopping_media_id']]);

                        $this->db->insert('userShoppingProducts', $product);
                    }

                    $this->db->delete('addToCart', ['userId' => $customerId['userId']]);

                }
                
                $this->sendMessage(1,'order completed',0);

            }catch (exception $e){
                $this->sendMessage(0,$e->getMessage(),0);
            }

        }else{
            $this->sendMessage(0,'Method Not Allowed',0);
        }

    }

    public function saveCards(){
        if($this->input->post()){

            $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
            if(empty($user)){
                $this->sendMessage(0,'invalid userId', 0);
            }

            $get = $this->db->get_where('customerCardDetails', ['userId' => $this->input->post('userId'), 'save' => '1'])->result_array();
            if(empty($get)){
                $this->sendMessage(0,'No Cards Saved',0);
            }

            $this->sendMessage(1,'cards list found', $get);

        }else{
            $this->sendMessage(0,'Method Not Allowed',0);
        }
    }

    public function removeCard(){
        if($this->input->post()){

            $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
            if(empty($user)){
                $this->sendMessage(0,'inavlid userId',0);
            }

            $card = $this->db->get_where('customerCardDetails', ['userId' => $this->input->post('userId'), 'customerId' => $this->input->post('customerId')])->row_array();
            if(empty($card)){
                $this->sendMessage(0,'No card Found',0);
            }

            if($this->db->delete('customerCardDetails', ['id' => $card['id']])){
                $this->sendMessage(1,'card deleted',0);
            }else{
                $this->sendMessage(0,'DB error',0);
            }

        }else{
            $this->sendMessage(0,'Method Not Allowed',0);
        }
    }

    public function deleteComment(){
        if($this->input->post()){

            $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
            if(empty($user)){
                $this->sendMessage(0,'invalid userId',0);
            }

            $mediaId = $this->db->get_where('shopping_media', ['id' => $this->input->post('mediaId')])->row_array();
            if(empty($mediaId)){
                $this->sendMessage(0,'invalid mediaId',0);
            }

            $commentId = $this->db->get_where('commentFeed', ['mediaId' => $this->input->post('mediaId'), 'id' => $this->input->post('commentId'), 'userId' => $this->input->post('userId')])->row_array();
            if(empty($commentId)){
                $this->sendMessage(0,'invalid commentId',0);
            }

            if($this->db->delete('commentFeed',['id' => $this->input->post('commentId')])){

                $this->sendMessage(1,'comment deleted',0);

            }else{
                $this->sendMessage(0,'DB Error',0);
            }

        }else{
            
        }
    }

    public function getCountries(){
        $get = $this->db->get('countries')->result_array();

        if(empty($get)){
            $this->sendMessage(0,'No list found',0);
        }else{
            $this->sendMessage(1, 'list found', $get);
        }
    }

    public function getState(){

        if($this->input->post()){

            $country = $this->db->get_where('countries', ['id' => $this->input->post('countryId')])->row_array();
            if(empty($country)){
                $this->sendMessage(0,'invalid countryId',0);exit;
            }

            $state = $this->db->get_where('states', ['country_id' => $this->input->post('countryId')])->result_array();
            if(empty($state)){
                $this->sendMessage(0,'no state found',0);exit;
            }else{
                $this->sendMessage(1,'state found',$state);exit;
            }

        }else{
            $this->sendMessage(0,'Method Not Allowed',0);
        }

    }

    public function getCity(){
        if($this->input->post()){

            $state = $this->db->get_where('states', ['id' => $this->input->post('stateId')])->row_array();
            if(empty($state)){
                $this->sendMessage(0,'invalid stateId',0);
            }

            $city = $this->db->get_where('cities', ['state_id' => $this->input->post('stateId')])->result_array();
            if(empty($city)){
                $this->sendMessage(0,'no city found',0);
            }else{
                $this->sendMessage(1,'list found',$city);
            }

        }else{
            $this->sendMessage(0,'Method not allowed',0);
        }
    }

    public function getProductsCategory(){
        $get = $this->db->get('category')->result_array();

        if(empty($get)){
            $this->sendMessage(0,'No category Found',0);
        }else{
            $this->sendMessage(1,'category list found',$get);
        }
    }    

    public function getProductsByCategory(){
        if($this->input->post()){

            $category = $this->db->get_where('category', ['id' => $this->input->post('categoryId')])->row_array();
            if(empty($category)){
                $this->sendMessage(0,'invalid categoryId',0);
            }

            $product = $this->db->get_where('shopping_media', ['id' => $this->input->post('productId')])->row_array();
            if(empty($product)){
                $this->sendMessage(0,'invalid productId', 0);
            }

            $products = $this->db->get_where('shopping_media', ['id !=' => $product['id'] , 'categoryId' => $this->input->post('categoryId'), 'media_type' => '2'])->result_array();
            if(empty($products)){
                $this->sendMessage(0,'No Products Found',0);
            }else{

                $final = [];

                foreach($products as $productss){

                    $incart = $this->db->get_where('addToCart', ['userId' => $this->input->post('userId'), 'shopping_media_id' => $productss['id']])->row_array();
                    if(empty($incart)){
                        $productss['incart'] = false;
                    }else{
                        $productss['incart'] = true;
                    }

                    $bookmark = $this->db->get_where('bookmarkFeed', ['userId' => $this->input->post('userId'), 'mediaId' => $productss['id']])->row_array();
                    if(empty($bookmark)){
                        $productss['bookmarkStatus'] = false;
                    }else{
                        $productss['bookmarkStatus'] = true;
                    }

                    $wishlist = $this->db->get_where('wishlist', ['userId' => $this->input->post('userId'), 'mediaId' => $productss['id']])->row_array();
                    if(empty($wishlist)){
                        $productss['wishlistStatus'] = 0;
                    }else{
                        $productss['wishlistStatus'] = 1;
                    }



                    $final[] = $productss;

                }

                $this->sendMessage(1,'categories found', $final);
            }

        }else{
            $this->sendMessage(0,'Method not allowed', 0);
        }
    }



    public function myOrders(){
        if($this->input->post()){

            $user = $this->db->get_where('users', ['id' => $this->input->post('userId')])->row_array();
            if(empty($user)){
                $this->sendMessage(0,'inavlid userId',0);
            }

            $orders = $this->db->get_where('orderDetails', ['userId' => $this->input->post('userId'), 'status' => 'completed'])->result_array();
            if(empty($orders)){
                $this->sendMessage(0,'No orders found',0);
            }

            $final = [];
            foreach($orders as $order){

                $multiple = false;

                if($order['quantity'] == '0'){

                    $ids = explode(',',$order['productId']);
                    $ar = [];
                    foreach($ids as $id){

                        $product = $this->db->get_where('shopping_media', ['id' => $id])->row_array();
                        if(!!$product){

                            $ar[] = $product;

                        }   
                    }

                    $multiple = true;

                }else{

                    $ar = $this->db->get_where('shopping_media', ['id' => $order['productId']])->row_array();

                }

                if($multiple == true){

                    foreach($ar as $arr){

                        $final[] = $arr;
                    }
                }else{

                    $final[] = $ar;
                }
                unset($ar);

            }

            if(empty($final)){
                $this->sendMessage(0,'no details found',0);
            }

            $fin = [];
            foreach($final as $finale){
                $finale['incart'] = false;
                $finale['wishlistStatus'] = false;
                $fin[] = $finale;
            }

            $this->sendMessage(1,'details found',$fin);

        }else{
            $this->sendMessage(0,'Method not allowed',0);
        }
    }


}
