<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/GooglePhotosClient.php');

$service = new GooglePhotosClient();
$url = $service->createAuthUrl("http://127.0.0.1:8005/redirect.php");
?>
<html>
<body>
<div class="flex-center position-ref full-height">
    <div class="content">
        Authenticate with Google<br/>
        <a href="<?=$url?>">Auth</a>
    </div>
</div>
</body>
</html>