<?php

namespace Supermeteor;

class Client
{
    protected $host = 'https://api.supermeteor.com';
    protected $hostSandbox = 'https://api-uat.supermeteor.com';
	
    public $secretKey, $statusCode, $message, $sandbox;
    
    /**
     * Client constructor.
     * @param $secretKey
     * @param bool $sandbox
     */
    public function __construct($secretKey, $sandbox = false)
    {
        $this->secretKey = $secretKey;
        $this->sandbox = $sandbox;
    }
    
    /**
     * @return string
     */
    public function getHost(){
        return $this->sandbox ? $this->hostSandbox : $this->host;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param mixed $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function response()
    {
        $message = ["message" => $this->message];
        $response = json_encode($message);

        return $response;
    }
    
    /**
     * @param $type
     * @param $phone
     * @param $message
     * @return bool
     */
    public function ValidateSendMessageRequest($type, $phone, $message)
    {
        if (strtolower($type) == 'sms' || strtolower($type) == 'whatsapp') {
            if ($phone == '') {
                $this->statusCode = 400;
                $this->message = 'phone must not be blank';
                return false;
            } else if ($message == '') {
                $this->statusCode = 400;
                $this->message = 'message must not be blank';
                return false;
            }
        } else {
            $this->statusCode = 400;
            $this->message = 'type must be sms or whatsapp';
            return false;
        }

        return true;
    }
    
    /**
     * @param $type
     * @param $phone
     * @param $message
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws RequestException
     */
    public function sendMessage($type, $phone, $message)
    {
        // validate if type, phone or message must not blank.
        $valid = $this->ValidateSendMessageRequest($type, $phone, $message);
        if (!$valid) {
            throw new RequestException($this->message);
        }

        // check which type of message to send.
        switch (strtolower($type)) {
            // for type sms
            case strtolower($type) == 'sms':
                $url = "{$this->getHost()}/sms/send";
                break;
            // for type whatsaap
            case strtolower($type) == 'whatsapp':
                $url = "{$this->getHost()}/whatsapp/send";
                break;
            default:
                $this->statusCode = 400;
                $this->message = 'Type must be sms or whatsapp only.';
                $response = $this->response();
                return $response;
        }

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('POST', $url, [
                \GuzzleHttp\RequestOptions::JSON => [
                    'secret' => $this->secretKey,
                    'phone' => $phone,
                    'message' => $message
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e){
        	throw new RequestException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * @param $email
     * @param $subject
     * @param $message
     * @return bool
     */
    public function ValidateSendEmailRequest($email, $subject, $message)
    {
        if ($email == '') {
            $this->statusCode = 400;
            $this->message = 'email must not be blank';
            return false;
        } else if ($subject == '') {
            $this->statusCode = 400;
            $this->message = 'subject must not be blank';
            return false;
        } else if ($message == '') {
            $this->statusCode = 400;
            $this->message = 'message must not be blank';
            return false;
        }
        return true;
    }
    
    /**
     * @param $email
     * @param $subject
     * @param $message
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws RequestException
     */
    public function sendEmail($email, $subject, $message)
    {
        // validate if email, message, subject must not blank.
        $valid = $this->ValidateSendEmailRequest($email, $subject, $message);

        if (!$valid) {
            $response = $this->response();
            return $response;
        }

        $url = "{$this->getHost()}/email/send";
        $payload = [
            "secret" => $this->secretKey,
            "email" => $email,
            "subject" => $subject,
            "message" => $message
        ];
        
        try {
	        $client = new \GuzzleHttp\Client();
	
	        $response = $client->request(
		        'POST', $url, [\GuzzleHttp\RequestOptions::JSON => $payload]);
	        $contents = json_decode($response->getBody()->getContents(), true);
	        return $contents;
        }
        catch (\Exception $e){
	        throw new RequestException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * @param $fromPhone
     * @param $toPhone
     * @param $message
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws RequestException
     */
    public function sendWhatsapp($fromPhone, $toPhone, $message)
    {

        $url = "{$this->getHost()}/whatsapp/send";
        $payload = [
            "secret" => $this->secretKey,
            "fromPhone" => $fromPhone,
            "phone" => $toPhone,
            "message" => $message
        ];
        
        try {
	        $client = new \GuzzleHttp\Client();
	
	        $response = $client->request(
		        'POST', $url, [\GuzzleHttp\RequestOptions::JSON => $payload]);
	        $contents = json_decode($response->getBody()->getContents(), true);
	        return $contents;
        }
        catch (\Exception $e){
	        throw new RequestException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
