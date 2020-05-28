<?php

// ============== Demo for sending whatsapp message ==============
// github url: https://github.com/supermeteor/sdk-php/
// install: composer require supermeteor/sdk-php

// includes composer autoloader
require_once '../vendor/autoload.php';
require_once '../Supermeteor.php';

// set your config here
$sandbox = true;
$secret = 'xxxxxxxxxxxxxx';
$fromPhone = '+85268888888';
$toPhones = [
    '+85269999999',
    '+85266666666',
    '+85267777777',
];

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