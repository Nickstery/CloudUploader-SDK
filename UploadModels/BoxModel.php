<?php

namespace UploadModels;

class BoxModel implements \Interfaces\UploadServiceInterface{

    public static function auth() {
        $box = self::getBox();
        $box->get_code();
    }

    public static function uploadFile($access_token, $uploadFile, $fileId) {

        $box = self::getBox();
        $res = $box->create_folder('PDFFiller', '0',$access_token);

        if ($res['status'] == 'ok') {

            try{

                $fileName = preg_split( "~[/.]~", $uploadFile );
                $index = sizeof($fileName);
                $fileName = $fileName[$index - 2];
                $fileName .= '_'.$fileId;

                $answer = $box->put_file($uploadFile, $fileName.'.pdf',$res['id'], $access_token);
            }catch(\Exception $e){
                return array('status' => 'error', 'msg' => 'refreshToken');
            }

            if(is_array($answer->entries) && sizeof($answer->entries) > 0){
                return array('status' => 'ok');
            }else{
                return array('error', 'msg' => 'refreshToken');
            }

        } else {
            return array('status' => 'error', 'msg' => 'refreshToken');
        }
    }

    public static function getToken() {

        $box = self::getBox();

        $url = $box->token_url;
        if(!empty($box->refresh_token)) {
            $params = array('grant_type' => 'refresh_token', 'refresh_token' => $box->refresh_token, 'client_id' => $box->client_id, 'client_secret' => $box->client_secret);
        } else {
            $params = array('grant_type' => 'authorization_code', 'code' => $_REQUEST['code'], 'client_id' => $box->client_id, 'client_secret' => $box->client_secret);
        }
            $data = json_decode($box->post($url, $params), true);
            return $data['access_token'];
    }


    private static function getBox() {

        $dotenv = new \Dotenv\Dotenv(__DIR__.'/..');
        $dotenv->load();

        return new \Apibox\Apibox(
            $_ENV['BOX_CLIENT_ID'],
            $_ENV['BOX_CLIENT_SECRET'],
            $_ENV['BOX_REDIRECT_URI']
        );
    }


}