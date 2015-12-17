<?php
namespace Interfaces;

interface UploadServiceInterface{

    public static function auth();
    public static function uploadFile($access_token);
    public static function getToken();

}