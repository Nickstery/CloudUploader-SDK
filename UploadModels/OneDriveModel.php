<?php
namespace UploadModels;

use Krizalys\Onedrive\Client;
use Psr\Log\InvalidArgumentException;

class OneDriveModel implements \Interfaces\UploadServiceInterface {

    public static function auth($state, $config) {

    }

    public static function uploadFile($access_token, $uploadFile, $fileName, $config) {
        return '';
    }


    public static function getToken($config) {
        return '';
    }

    private static function getOneDriveClient($config) {
        return new Client(array(
            'client_id' => $config['ONEDRIVE_CLIENT_ID']
        ));
    }

    private static function getFolder($access_token, $config) {

    }

    public static function getOneDriveConfig($config){
        $data['client_id'] = $config['ONEDRIVE_CLIENT_ID'];
        $data['project_secret'] = $config['ONEDRIVE_CLIENT_SECRET'];
        $data['redirect_uri'] = $config['ONEDRIVE_CALLBACK_URI'];
        return $config;
    }

}