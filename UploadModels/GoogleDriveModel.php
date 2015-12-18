<?php
namespace UploadModels;

class GoogleDriveModel implements \Interfaces\UploadServiceInterface {

    public static function auth()
    {
        $client = self::getGoogleClient();
        $auth_url = $client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    }

    public static function uploadFile($access_token, $uploadFile)
    {

        if(!isset($access_token)){
            return;
        }

        $client = self::getGoogleClient();
        $client->setAccessToken($access_token);
        $service = new \Google_Service_Drive($client);

        //Insert a file
        $file = new \Google_Service_Drive_DriveFile();
        $file->setTitle('test_'.time().'.pdf');
        $file->setDescription('A test document');
        $file->setMimeType('text/plain');

        $data = file_get_contents($uploadFile);

        $folderInfo = self::getFolder($access_token);
        $id = 0;
        if($folderInfo['status'] === 'ok'){
            $id = $folderInfo['id'];
        }else{
            return array('status' => 'error', 'msg' => 'refreshToken');
        }
        $parent = new \Google_Service_Drive_ParentReference();
        $parent->setId($id);
        $file->setParents(array($parent));

        try {
            $createdFile = $service->files->insert($file, array(
                'data' => $data,
                'mimeType' => 'text/plain',
                'uploadType' => 'resumable'
            ));
        }catch(\Exception $e){
            return array('status' => 'error', 'msg' => 'refreshToken');
        }

        if(isset($createdFile) && isset($createdFile['id']) && strlen($createdFile['id']) > 0){
            return array('status' => 'ok');
        }else{
            return array('status' => 'error', 'msg' => 'refreshToken');
        }
    }


    public static function getToken() {
        $client = self::getGoogleClient();

        $client->authenticate($_GET['code']);
        return $client->getAccessToken();
    }

    private static function getGoogleClient() {
        $client = new \Google_Client();


        $config = self::getGoogleConfig();

        $client->setAuthConfigFile($config);

        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/TestProject/code.php?type=1');
        $client->addScope(\Google_Service_Drive::DRIVE);
        return $client;
    }

    private static function getFolder($access_token) {
        $client = self::getGoogleClient();
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

    public static function getGoogleConfig(){
        $config = array();

        $dotenv = new \Dotenv\Dotenv(__DIR__.'/..');
        $dotenv->load();

        $data['client_id'] = $_ENV['GOOGLEDRIVE_CLIENTID'];
        $data['project_id'] = $_ENV['GOOGLEDRIVE_PROJECTID'];
        $data['auth_uri'] = $_ENV['GOOGLEDRIVE_AUTHURL'];
        $data['token_uri'] = $_ENV['GOOGLEDRIVE_TOKEN_URL'];
        $data['auth_provider_x509_cert_url'] = $_ENV['GOOGLEDRIVE_AUTHPROV'];
        $data['client_secret'] = $_ENV['GOOGLEDRIVE_CLIENTSECRET'];
        $data['redirect_uris'][] = $_ENV['GOOGLEDRIVE_REDIRECT1'];
        $data['redirect_uris'][] = $_ENV['GOOGLEDRIVE_REDIRECT2'];

        $config['installed'] = $data;
        return $config;
    }


}