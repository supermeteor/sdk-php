# Supermeteor
Supermeteor is PHP SDK use to create cloud message: whatsapp, sms and email etc

How to use:

install using composer
```bash
composer require supermeteor/sdk-php
```
include vendor/autoload.php in your file

```php
require_once '../vendor/autoload.php';
```


include package in your file
```php
use Supermeteor\Client;
```

### 1. For sending sms:

pass type, phone, message as function parameter,
Here is the sample function call for send sms.

#### Type must be: sms

```php
$message = new Supermeteor('<secret_key>');
$result = $message->SendMessage('<type>', '+XXXXXXXXX', 'your message');
```
### 2. For sending email:

pass email, subject, message as function parameter,
Here is the sample function call for send email.
```php
$message = new Supermeteor('<secret_key>');
$result = $message->sendEmail('mail@email.com', 'subject', 'your message');
```

### 3. For sending whatsapp:

pass email, subject, message as function parameter,
Here is the sample function call for send email.
```php
$message = new Supermeteor('<secret_key>');
$fromPhone = '+852 6444 4444'
$toPhone = '+852 6888 8888'
$result = $message->sendWhatsapp($fromPhone, $toPhone, 'your message');
```
