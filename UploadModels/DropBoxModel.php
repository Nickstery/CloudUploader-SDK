<?php

namespace UploadModels;

use \Dropbox as dbx;
use Dropbox\Exception;

class DropBoxModel implements \Interfaces\UploadServiceInterface{

    public static function auth($state, $config) {

        $authorizeUrl = self::getDropBoxAuth($config)->start($_REQUEST['userId']);
        return $authorizeUrl;
    }

    public static function getToken($config) {
        try {
            list($accessToken, $userId, $urlState) = self::getDropBoxAuth($config)->finish($_GET);
            if (isset($accessToken) && strlen($accessToken) > 0) {
                return $accessToken;
            }
        }catch(dbx\Exception_BadRequest $e){

        }
        return '';
    }


    public static function uploadFile($access_token, $uploadFile, $fileName, $config) {

        if(!isset($access_token)){
            return array('status' => 'error', 'msg' => 'refreshToken', 'url' => self::auth($_REQUEST['userId'], $config));
        }

        if(file_exists($uploadFile)) {

            $dbxClient = new dbx\Client($access_token, "PHP-Example/1.0");


            $f = fopen($uploadFile, "rb");
            try {
                if (!isset($fileName) || strlen($fileName) == 0 || $fileName == '0') {
                    $tmp = explode('/', $uploadFile);
                    $fileName = $tmp[sizeof($tmp) - 1];
                }else{
                    $fileName .= '.pdf';
                }

                $result = $dbxClient->uploadFile("/PDFFiller/". $fileName, dbx\WriteMode::add(), $f);
            }catch(Exception $e){
                return array('status' => 'error', 'msg' => 'refreshToken', 'url' => self::auth($_REQUEST['userId'], $config));
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


    private static function getDropBoxAuth($config) {

        $data = array('key' => $config['DROPBOX_KEY'], 'secret' => $config['DROPBOX_SECRET']);

        $appInfo = dbx\AppInfo::loadFromJson($data);
        $clientIdentifier = "my-app/1.0";
        $redirectUri = "https://local.pdffiller.com/en/cloud_export/callback";
        $csrfTokenStore = new dbx\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');

        return new dbx\WebAuth($appInfo, $clientIdentifier, $redirectUri, $csrfTokenStore);
    }

}