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
$toPhone = '+85269999999';

try {
    // instance new client
    $supermeteor = new \Supermeteor\Client($secret, $sandbox);
    
    // template message predefined in Meta: 
    //Hello {{1}} , this is a confirmation of your consultation on {{2}}.
    $template = new \Supermeteor\WhatsappTemplateMessage(
        'booking_confirmation',
        'en',
        ['John', '2019-01-12']
    );

    // send a whatsapp template message
    $supermeteor->sendWhatsapp($fromPhone, $toPhone, $template);

    // send another whatsapp free message
    $supermeteor->sendWhatsapp($fromPhone, $toPhone, 'hello world!');
}
catch (\Supermeteor\RequestException $e){
    // error will be thrown if not success
    echo $e->getMessage();
}

?>