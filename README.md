# Supermeteor
Supermeteor is PHP SDK use to create message and email.

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
use Supermeteor\Supermeteor;
```

### 1. For sending sms:

pass type, phone, message as function parameter,
Here is the sample function call for send sms.

#### Type must be: sms or whatsapp

```php
$message = new Supermeteor('<secret_key>');
$result = $message->SendMessage('<type>', '+XXXXXXXXX', 'your message');
```
### 2. For sending email:

pass email, subject, message as function parameter,
Here is the sample function call for send email.
```php
$message = new Supermeteor('<secret_key>');
$result = $message->SendEmail('mail@email.com', 'subject', 'your message');
```
