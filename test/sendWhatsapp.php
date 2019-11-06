<?php

require_once '../vendor/autoload.php';
require_once '../Supermeteor.php';

$secret = 'xxxxxxxxxxxxxx';

$sandbox = true;
$fromPhone = '+85268888888';
$toPhone = '+85269999999';

try {
    $supermeteor = new \Supermeteor\Client($secret, $sandbox);
    $supermeteor->sendWhatsapp($fromPhone, $toPhone, 'testing...');
}
catch (\Supermeteor\RequestException $e){
    echo $e->getMessage();
}
