<?php
namespace UploadModels;

use Psr\Log\InvalidArgumentException;

class GoogleDriveModel implements \Interfaces\UploadServiceInterface {

    public static function auth($state, $config) {
        $client = self::getGoogleClient($config);
        $client->setState($state);
        $auth_url = $client->createAuthUrl();
        return filter_var($auth_url, FILTER_SANITIZE_URL);
    }

    public static function uploadFile($access_token, $uploadFile, $fileName, $config) {

        if (!isset($access_token)) {
            return;
        }

        $userId = \HttpReceiver\HttpReceiver::get('userId', 'string');
        $client = self::getGoogleClient($config);
        try {
            $access_token = (array)$access_token;
            $client->setAccessToken($access_token);
        }catch (\InvalidArgumentException $e){

            return array('status' => 'error', 'msg' => 'refreshToken', 'url' => self::auth($userId, $config));
        }

        $service = new \Google_Service_Drive($client);

        //Insert a file
        $file = new \Google_Service_Drive_DriveFile();

        if (!isset($fileName) || strlen($fileName) == 0 || $fileName == '0') {
            $tmp = explode('/', $uploadFile);
            $fileName = $tmp[sizeof($tmp) - 1];
        }else{
            $fileName .= '.pdf';
        }

        $file->setTitle($fileName);
        $file->setDescription('A test document');
        $file->setMimeType('application/pdf');

        $data = file_get_contents($uploadFile);

        $folderInfo = self::getFolder($access_token, $config);
        $id = 0;
        if($folderInfo['status'] === 'ok'){
            $id = $folderInfo['id'];
        }else{
            return array('status' => 'error', 'msg' => 'refreshToken', 'url' => self::auth($userId, $config));
        }
        $parent = new \Google_Service_Drive_ParentReference();
        $parent->setId($id);
        $file->setParents(array($parent));

        try {
            $createdFile = $service->files->insert($file, array(
                'data' => $data,
                'mimeType' => 'application/pdf',
                'uploadType' => 'resumable'
            ));
        }catch(\Exception $e){
            return array('status' => 'error', 'msg' => 'refreshToken', 'url' => self::auth($userId, $config));
        }

        if(isset($createdFile) && isset($createdFile['id']) && strlen($createdFile['id']) > 0){
            return array('status' => 'ok');
        }else{
            return array('status' => 'error', 'msg' => 'refreshToken', 'url' => self::auth($userId, $config));
        }
    }


    public static function getToken($config) {

        $client = self::getGoogleClient($config);
        $code = \HttpReceiver\HttpReceiver::get('code','string');
        $client->authenticate($code);
        return $client->getAccessToken();

    }

    private static function getGoogleClient($config) {
        $client = new \Google_Client();

        $config = self::getGoogleConfig($config);

        $client->setAuthConfigFile($config);

        $userId = \HttpReceiver\HttpReceiver::get('userId', 'int');

        if(!isset($userId)){
            $userId = \HttpReceiver\HttpReceiver::get('state', 'int');
        }

        $client->setRedirectUri($config['GOOGLEDRIVE_REDIRECT2']);
        $client->addScope(\Google_Service_Drive::DRIVE);
        return $client;
    }

    private static function getFolder($access_token, $config) {
        $client = self::getGoogleClient($config);
        $client->setAccessToken($access_token);
        $service = new \Google_Service_Drive($client);

        $parameters['q'] = "mimeType='application/vnd.google-apps.folder' and 'root' in parents and trashed=false";
        try {
            $data = $service->files->listFiles($parameters);
        }catch(\Exception $e){
            return array('status' => 'error');
        }
        $files = $data->getItems();
        foreach($files as $file) {
            if($file['title'] == 'PDFFiller'){
                return array('status' => 'ok', 'id' => $file['id']);
            }
        }

        $file = new \Google_Service_Drive_DriveFile();

        $file->setTitle('PDFFiller'); //name of the folder
        $file->setDescription('PDFFiller uploads');
        $file->setMimeType('application/vnd.google-apps.folder');
        $createdFile = $service->files->insert($file, array(
            'mimeType' => 'application/vnd.google-apps.folder',
        ));

        return array('status' => 'ok', 'id' => $createdFile->id);
    }

    public static function getGoogleConfig($config){
        $data['client_id'] = $config['GOOGLEDRIVE_CLIENTID'];
        $data['project_id'] = $config['GOOGLEDRIVE_PROJECTID'];
        $data['auth_uri'] = $config['GOOGLEDRIVE_AUTHURL'];
        $data['token_uri'] = $config['GOOGLEDRIVE_TOKEN_URL'];
        $data['auth_provider_x509_cert_url'] = $config['GOOGLEDRIVE_AUTHPROV'];
        $data['client_secret'] = $config['GOOGLEDRIVE_CLIENTSECRET'];
        // $data['redirect_uris'][] = $config['GOOGLEDRIVE_REDIRECT1'];
        $data['redirect_uris'][] = $config['GOOGLEDRIVE_REDIRECT2'];

        $config['installed'] = $data;
        return $config;
    }


}