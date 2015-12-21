<?php

namespace Fabric;
use \Dropbox as dbx;


class ServiceFabric{

    public static function auth($type, $code) {
        if ($code == 'code') {
            switch ($type) {
                case 0:
                    @session_start();
                    return \UploadModels\DropBoxModel::auth();
                    break;
                case 1:
                    return \UploadModels\GoogleDriveModel::auth();
                    break;
                case 2:
                    return \UploadModels\BoxModel::auth();
                    break;
            }
        }elseif($code == 'access_token'){

            $result = self::getToken($type);
            $data = array('service' => $type, 'token_data' => $result);
            return $data;

        }

    }


    public static function uploadFile($type, $access_token, $uploadFile) {

        $result = array('status' => 'error', 'msg' => 'Wrong service type');

        if(!isset($type)) {
            return $result;
        }

        switch($type){
            case 0:
                @session_start();
                if(!isset($access_token)) {
                    return array('status' => 'error', 'msg' => 'deniedByUser');
                }
                $result = \UploadModels\DropBoxModel::uploadFile($access_token, $uploadFile);
                break;
            case 1:
                if(!isset($access_token)) {
                    return array('status' => 'error', 'msg' => 'deniedByUser');
                }
                $result = \UploadModels\GoogleDriveModel::uploadFile($access_token, $uploadFile);
                break;
            case 2:
                if(!isset($access_token)) {
                    return array('status' => 'error', 'msg' => 'deniedByUser');
                }
                $result = \UploadModels\BoxModel::uploadFile($access_token, $uploadFile);
                break;
        }
        return $result;
    }

    public static function getToken($type){

        $result = '';
        switch($type){
            case 0:
                @session_start();
                $result = \UploadModels\DropBoxModel::getToken();
                break;
            case 1:
                $result = \UploadModels\GoogleDriveModel::getToken();
                break;
            case 2:
                $result = \UploadModels\BoxModel::getToken();
                break;
        }
        return $result;
    }

}