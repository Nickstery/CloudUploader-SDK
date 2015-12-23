<?php

namespace UploadModels;


use HttpReceiver\HttpRecieiver;

class BoxModel implements \Interfaces\UploadServiceInterface {

    public static function auth($state,$config) {
        $box = self::getBox($config);
        $url = $box->get_code();
        $url .= '&state='.$state;
        return $url;
    }

    public static function uploadFile($access_token, $uploadFile, $fileName, $config) {

        $box = self::getBox($config);
        $res = $box->create_folder($config['SAVE_FOLDER'], '0',$access_token);
        $userId = HttpRecieiver::get('state','int');
        if(!isset($userId) || strlen($userId) == 0){
            $userId = HttpRecieiver::get('userId','int');
        }

        if ($res['status'] == 'ok') {

            try{
                if (!isset($fileName) || strlen($fileName) == 0 || $fileName == '0') {
                    $tmp = explode('/', $uploadFile);
                    $fileName = $tmp[sizeof($tmp) - 1];
                }
                $answer = $box->put_file($uploadFile, $fileName.'_'.time().'.pdf',$res['id'], $access_token);
            }catch(\Exception $e){
                return array('status' => 'error', 'msg' => 'refreshToken', 'url' => self::auth($userId, $config));
            }

            if(is_array($answer->entries) && sizeof($answer->entries) > 0){
                return array('status' => 'ok');
            }else{
                return array('status' => 'error', 'msg' => 'refreshToken', 'url' => self::auth($userId, $config));
            }

        } else {
            return array('status' => 'error', 'msg' => 'refreshToken', 'url' => self::auth($userId, $config));
        }
    }

    public static function getToken($config) {

        $box = self::getBox($config);


        $url = $box->token_url;
        if(!empty($box->refresh_token)) {
            $params = array('grant_type' => 'refresh_token', 'refresh_token' => $box->refresh_token, 'client_id' => $box->client_id, 'client_secret' => $box->client_secret);
        } else {
            $params = array('grant_type' => 'authorization_code', 'code' => HttpRecieiver::get('code','string'), 'client_id' => $box->client_id, 'client_secret' => $box->client_secret);
        }

        $data = json_decode($box->post($url, $params), true);
        if(isset($data['error'])){
            return '';
        }
        return $data['access_token'];
    }


    private static function getBox($config) {

        return new \Apibox\Apibox(
            $config['BOX_CLIENT_ID'],
            $config['BOX_CLIENT_SECRET'],
            $config['BOX_REDIRECT_URI']
        );
    }

}