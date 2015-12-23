<?php

namespace Fabric;
use \Dropbox as dbx;
@session_start();

class ServiceFabric{

    public static function auth($type, $code, $config) {

        if ($code == 'code') {
            switch ($type) {
                case 0:
                    $data = $_REQUEST['userId'];

                    return \UploadModels\DropBoxModel::auth($data,$config);
                    break;
                case 1:
                    $data = $_REQUEST['userId'];
                    return \UploadModels\GoogleDriveModel::auth($data,$config);
                    break;
                case 2:
                    $data = $_REQUEST['userId'];
                    return \UploadModels\BoxModel::auth($data,$config);
                    break;
            }
        }elseif($code == 'access_token'){
            $result = self::getToken($type);
            $data = array('service' => $type, 'token_data' => $result);
            return $data;

        }

    }


    public static function uploadFile($type, $access_token, $uploadFile, $fileName, $config) {

        $result = array('status' => 'error', 'msg' => 'Wrong service type');

        if(!isset($type)) {
            return $result;
        }

        switch($type){
            case 0:
                if(!isset($access_token)) {
                    return array('status' => 'error', 'msg' => 'deniedByUser');
                }
                $result = \UploadModels\DropBoxModel::uploadFile($access_token, $uploadFile, $fileName, $config);
                break;
            case 1:
                if(!isset($access_token)) {
                    return array('status' => 'error', 'msg' => 'deniedByUser');
                }
                $result = \UploadModels\GoogleDriveModel::uploadFile($access_token, $uploadFile, $fileName, $config);
                break;
            case 2:
                if(!isset($access_token)) {
                    return array('status' => 'error', 'msg' => 'deniedByUser');
                }
                $result = \UploadModels\BoxModel::uploadFile($access_token, $uploadFile, $fileName, $config);
                break;
        }
        return $result;
    }

    public static function getToken($type, $config){

        $result = '';
        switch($type){
            case 0:
                $result = \UploadModels\DropBoxModel::getToken($config);
                break;
            case 1:
                $result = \UploadModels\GoogleDriveModel::getToken($config);
                break;
            case 2:
                $result = \UploadModels\BoxModel::getToken($config);
                break;
        }
        return $result;
    }

}