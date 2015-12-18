<?php
namespace Interfaces;

interface UploadServiceInterface{

    public static function auth();
    public static function uploadFile($access_token, $uploadFile);
    public static function getToken();

}