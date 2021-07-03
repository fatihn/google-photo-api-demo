<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/GooglePhotosClient.php');


use Google\Photos\Library\V1\PhotosLibraryResourceFactory;
use Google\Photos\Types\SharedAlbumOptions;

$code = $_GET['code'];
$scope = $_GET['scope'];


$client = createClient($code);

//Uncomment next line to see usual list-all-media-items feature.
//processPhotos($client);

// Uncomment next line to list all albums with title and id..
getMediaItems($client);

$albumId = 'AGiB0JkUfnmv4wIICsaXOWl8VM866ZnVL0JakqyLCMVI3nHBRl7X1feBBYpJx8nO_aOLl3ZTzruq';
getAlbumContent($client, $albumId);

function createClient($code)
{
    $service = new GooglePhotosClient();
    $accessToken = $service->getAccessToken($code);

    return $service->getPhotosLibraryClient($accessToken);
}

function getMediaItems($client)
{
    $mediaItemList = [];

    $response = $client->listMediaItems();
    foreach ($response->iterateAllElements() as $item) {
        $mediaItem = [];
        $mediaItem['id'] = $item->getId();
        $mediaItem['filename'] = $item->getFilename();
        $mediaItem['mimeType'] = $item->getMimeType();
        $mediaItem['url'] = $item->getBaseUrl();

        $mediaItemList[] = $mediaItem;

        echo '<a href="' . $mediaItem['url'] . '">' . $mediaItem['filename'] . '</a><br/>';

    }
    return $mediaItemList;
}

function getAlbumList($client)
{
    $albumList = [];
    error_log("Listing Albums : ");
    try {
        $response = $client->listAlbums();
        foreach ($response->iterateAllElements() as $albumItem) {
            $album = [];
            $album['id'] = $albumItem->getId();
            $album['title'] = $albumItem->getTitle();
            echo 'Album : ' . $album['title'] . ' : ID : ' .  $album['id'].'<br/><br/>';
            $albumList[] = $album;
        }
    } catch (\Google\ApiCore\ApiException $e) {
        error_log($e->getMessage());
    }

    return $albumList;
}

function getAlbumContent($client, $albumId)
{
    $mediaItemList = [];

    try {
        $response = $client->searchMediaItems(['albumId' => $albumId]);
        foreach ($response->iterateAllElements() as $item) {
            $mediaItem = [];
            $mediaItem['id'] = $item->getId();
            $mediaItem['mimeType'] = $item->getMimeType();
            $mediaItem['filename'] = $item->getFilename();
            $mediaItem['url'] = $item->getBaseUrl();

            echo '<a href="' . $mediaItem['url'] . '">' . $mediaItem['filename'] . '</a><br/><br/>';

            $mediaItemList[] = $mediaItem;

        }
    } catch (\Google\ApiCore\ApiException $e) {
        error_log($e->getMessage());
    }
    return $mediaItemList;
}


function processPhotos($client)
{
    $mediaItemList = getMediaItems($client);
   // $albumList = getAlbumList($client);
    foreach ($mediaItemList as $mediaItem) {


        /*
        try {
            $isAlbumExist = false;

            $fileName = pathinfo($mediaItem['filename'], PATHINFO_FILENAME);
            error_log("Processing File : id:" . $mediaItem['id'] . " title : " . $fileName);

            foreach ($albumList as $album) {
                if ($album['title'] == $fileName) {
                    $isAlbumExist = true;
                    $itemIds = [];
                    $itemIds[] = $mediaItem['id'];
                    error_log(print_r($itemIds, true));
                    $res = $client->batchAddMediaItemsToAlbum($album['id'], $itemIds);
                    error_log(print_r($res, true));

                    error_log("added to existing album : " . $album['title']);
                }

            }
            if ($isAlbumExist == false) {
                $newAlbum = createAlbum($client, $fileName);
                $itemIds = [];
                $itemIds[] = $mediaItem['id'];
                error_log(print_r($itemIds, true));

                $res = $client->batchAddMediaItemsToAlbum($newAlbum->getId(), $itemIds);
                error_log(print_r($res, true));

                $newAlbumObj = [];
                $newAlbumObj['id'] = $newAlbum->getId();
                $newAlbumObj['title'] = $newAlbum->getTitle();
                $albumList[] = $newAlbumObj;
                error_log("added to new album : " . $newAlbumObj['title']);

            }
        } catch (\Google\ApiCore\ApiException $e) {
            error_log("Exception Processing File : id:" . $mediaItem['id'] . " title : " . $fileName);
            error_log($e->getMessage());
        }

        */
    }
}

function createAlbum($client, $albumTitle)
{
    try {
        $newAlbum = PhotosLibraryResourceFactory::album($albumTitle);

        $album = $client->createAlbum($newAlbum);

        // Set the options for the album you want to share
        $options = new SharedAlbumOptions();
        $options->setIsCollaborative(true);
        $options->setIsCommentable(true);
        try {
            $response = $client->shareAlbum($album->getId(), ['sharedAlbumOptions' => $options]);
            // The response contains the shareInfo object, a url, and a token for sharing
            $shareInfo = $response->getShareInfo();
            // Link to the shared album
            $url = $shareInfo->getShareableUrl();

            error_log($url);

            // The share token which other users of your app can use to join the album you shared
            $shareToken = $shareInfo->getShareToken();
            // The options set when sharing this album
            $sharedOptions = $shareInfo->getSharedAlbumOptions();
        } catch (\Google\ApiCore\ApiException $e) {
            // Handle error
        }

        return $album;

    } catch (\Google\ApiCore\ApiException $e) {
        error_log($e->getMessage());
    }
}