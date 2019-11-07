<?php

// ============== Demo for sending whatsapp message ==============

// includes composer autoloader
require_once '../vendor/autoload.php';
require_once '../Supermeteor.php';

// set your config here
$sandbox = true;
$secret = 'xxxxxxxxxxxxxx';
$fromPhone = '+85268888888';
$toPhone = '+85269999999';

try {
    // instance new client
    $supermeteor = new \Supermeteor\Client($secret, $sandbox);
    // send whatsapp message
    $supermeteor->sendWhatsapp($fromPhone, $toPhone, 'testing...');
}
catch (\Supermeteor\RequestException $e){
    // error will be thrown if not success
    echo $e->getMessage();
}

?>