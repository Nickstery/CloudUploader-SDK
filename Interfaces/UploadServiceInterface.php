<?php
namespace Interfaces;

interface UploadServiceInterface{

    public static function auth();
    public static function uploadFile($access_token, $uploadFile, $fileId);
    public static function getToken();

}