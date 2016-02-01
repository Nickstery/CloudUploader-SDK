<?php

namespace Fabric;
use \Dropbox as dbx;

class ServiceFabric{

    public static function auth($type, $code, $config) {

        if ($code == 'code') {
            switch ($type) {
                case 0:
                    $data = \HttpReceiver\HttpReceiver::get('userId','int');
                    return \UploadModels\DropBoxModel::auth($data,$config);
                    break;
                case 1:
                    $data = \HttpReceiver\HttpReceiver::get('userId','int');
                    return \UploadModels\GoogleDriveModel::auth($data,$config);
                    break;
                case 2:
                    $data = \HttpReceiver\HttpReceiver::get('userId','int');
                    return \UploadModels\BoxModel::auth($data,$config);
                    break;
                case 3:
                    $data = \HttpReceiver\HttpReceiver::get('userId','int');
                    return \UploadModels\OneDriveModel::auth($data,$config);
                    break;
            }
        }elseif($code == 'access_token'){
            $result = self::getToken($type, $config);
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
                $result = array();
                try {
                    $result = \UploadModels\DropBoxModel::uploadFile($access_token, $uploadFile, $fileName, $config);
                }catch (dbx\Exception $e){
                    $result = array('status' => 'error', 'msg' => 'Cloud Error');
                }
                break;
            case 1:
                $result = array();
                if(!isset($access_token)) {
                    return array('status' => 'error', 'msg' => 'deniedByUser');
                }
                try {
                    $result = \UploadModels\GoogleDriveModel::uploadFile($access_token, $uploadFile, $fileName,
                        $config);
                }catch(\Exception $e){
                    $result = array('status' => 'error', 'msg' => 'Cloud Error');
                }
                break;
            case 2:
                if(!isset($access_token)) {
                    return array('status' => 'error', 'msg' => 'deniedByUser');
                }
                $result = array();
                try {
                    $result = \UploadModels\BoxModel::uploadFile($access_token, $uploadFile, $fileName, $config);
                } catch(\Exception $e){
                    $result = array('status' => 'error', 'msg' => 'Cloud Error');
                }
                break;
            case 3:
                if(!isset($access_token)) {
                    return array('status' => 'error', 'msg' => 'deniedByUser');
                }
                $result = array();
                try {
                    $result = \UploadModels\OneDriveModel::uploadFile($access_token, $uploadFile, $fileName, $config);
                } catch(\Exception $e){
                    $result = array('status' => 'error', 'msg' => 'Cloud Error');
                }
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
            case 3:
                $result = \UploadModels\OneDriveModel::getToken($config);
                break;
        }
        return $result;
    }

}