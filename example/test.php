<?php

require_once '../vendor/autoload.php';
require_once '../Supermeteor.php';

$secret = 'xxxxxxxxxxxxxx';
$secret = '974a06a28c6c811c70d5b3b52dbb0776b07deb96';

$sandbox = true;
$fromPhone = '+8615015935642';
$toPhone = '+85264860659';

try {
    $supermeteor = new \Supermeteor\Client($secret, $sandbox);
    $supermeteor->sendWhatsapp($fromPhone, $toPhone, 'testing...');
}
catch (\Supermeteor\RequestException $e){
    echo $e->getMessage();
}
