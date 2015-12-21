<?php

namespace UploadModels;


use \Dropbox as dbx;
use Dropbox\Exception;

class DropBoxModel implements \Interfaces\UploadServiceInterface{

    public static function auth() {
        session_start();
        $authorizeUrl = self::getDropBoxAuth()->start();
        return $authorizeUrl;
    }

    public static function getToken() {
        session_start();
        list($accessToken, $userId, $urlState) = self::getDropBoxAuth()->finish($_GET);
        //echo $accessToken;
        if (isset($accessToken) && strlen($accessToken) > 0) {

            return $accessToken;
        }
        return '';
    }


    public static function uploadFile($access_token, $uploadFile) {
        if(!isset($access_token)){
            return array('status' => 'error', 'msg' => 'refreshToken', 'uel' => self::auth());
        }

        if(file_exists($uploadFile)) {

            $dbxClient = new dbx\Client($access_token, "PHP-Example/1.0");

            $f = fopen($uploadFile, "rb");
            try {

                $tmp = explode('/',$uploadFile);
                $fileName = $tmp[sizeof($tmp) - 1];
                $result = $dbxClient->uploadFile("/PDFFiller/". $fileName . ".pdf", dbx\WriteMode::add(), $f);
            }catch(Exception $e){
                return array('status' => 'error', 'msg' => 'refreshToken');
            }

            fclose($f);
            if(!isset($result) || !isset($result['size'])){
                return array('status' => 'error', 'msg' => 'refreshToken');
            }else {
                return array('status' => 'ok');
            }
        }else {
            return array('status' => 'error', 'fileNotExist');
        }
    }


    private static function getDropBoxAuth() {

        $dotenv = new \Dotenv\Dotenv(__DIR__.'/..');
        $dotenv->load();

        $data = array('key' => $_ENV['DROPBOX_KEY'], 'secret' => $_ENV['DROPBOX_SECRET']);

        $appInfo = dbx\AppInfo::loadFromJson($data);
        $clientIdentifier = "my-app/1.0";
        $redirectUri = "http://localhost:63342/TestProject/code.php";
        $csrfTokenStore = new dbx\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');
        return new dbx\WebAuth($appInfo, $clientIdentifier, $redirectUri, $csrfTokenStore);
    }

}