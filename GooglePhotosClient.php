<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php');

use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Photos\Library\V1\PhotosLibraryClient;

class GooglePhotosClient
{
    public function getAccessToken($authCode)
    {
        $client = $this->createClient();
        return $client->fetchAccessTokenWithAuthCode($authCode);
    }

    private function createClient()
    {
        $client = new \Google_Client();
        $client->setApplicationName('Abc App');
        $client->setScopes(\Google_Service_PhotosLibrary::PHOTOSLIBRARY);
        $client->setRedirectUri("http://127.0.0.1:8005/redirect.php");
        $client->setAuthConfig("credentials.json");
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        return $client;
    }

    public function createAuthUrl($redirectUri)
    {
        $client = $this->createClient();
        $client->setRedirectUri($redirectUri);
        return $client->createAuthUrl();
    }


    public function getPhotosLibraryClient($accessToken)
    {
        $client = $this->createClient();
        $client->setAccessToken($accessToken);
        if ($client->isAccessTokenExpired())
        {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        }

        $authCredentials = new UserRefreshCredentials(\Google_Service_PhotosLibrary::PHOTOSLIBRARY, [
            "client_id" => $client->getClientId(),
            "client_secret" => $client->getClientSecret(),
            "refresh_token" => $client->getRefreshToken()
        ]);
        return new PhotosLibraryClient(['credentials' => $authCredentials]);
    }

}