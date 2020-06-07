<?php

namespace Supermeteor;

class Client
{
    protected $host = 'https://api.supermeteor.com';
    protected $hostSandbox = 'https://api-uat.supermeteor.com';
	
    public $secretKey, $statusCode, $message, $sandbox;
    
    protected $httpClient;
    
    /**
     * Client constructor.
     * @param $secretKey
     * @param bool $sandbox
     */
    public function __construct($secretKey, $sandbox = false)
    {
        $this->secretKey = $secretKey;
        $this->sandbox = $sandbox;
        
        $this->httpClient = new \GuzzleHttp\Client([
            'verify' => false,
            'timeout' => 10,
            'connection_timeout' => 10,
        ]);
    }
    
    public function setHttpClient($client){
        $this->httpClient = $client;
    }
    
    public function getHttpClient(){
        if ($this->httpClient instanceof \GuzzleHttp\Client){
            return $this->httpClient;
        }
    
    
        $curl = curl_init();
    
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_URL => "https://api-uat.supermeteor.com/sms/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => "POST",
//            CURLOPT_POSTFIELDS => "<JSON>",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json"
            ),
        ));
    
        return $curl;
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

        $client = $this->getHttpClient();
        try {
            $data = [
                'secret' => $this->secretKey,
                'phone' => $phone,
                'message' => $message
            ];
            if ($client instanceof \GuzzleHttp\Client){
                $response = $client->request('POST', $url, [
                    \GuzzleHttp\RequestOptions::JSON => $data
                ]);
                return json_decode($response->getBody()->getContents(), true);
            }
            else {
                $curl = $client;
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                $response = curl_exec($curl);
                
                $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                $err = curl_error($curl);
                if ($err){
                    curl_close($curl);
                    throw new \Exception($err);
                }
                
                if ($statusCode < 200 || $statusCode > 299){
                    curl_close($curl);
                    throw new \Exception($response, $statusCode);
                }
    
                curl_close($curl);
                
                return json_decode($response, true);
            }
            
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
	        $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);
	
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
     * @param string|[] $toPhone using Array if for bulk send
     * @param $message
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws RequestException
     */
    public function sendWhatsapp($fromPhone, $toPhone, $message)
    {
        $payload = [
            "secret" => $this->secretKey,
            "fromPhone" => $fromPhone,
            "message" => $message
        ];

        if (is_array($toPhone)){
            $url = "{$this->getHost()}/whatsapp/bulkSend";
            $payload['phones'] = $toPhone;
        }
        else {
            $url = "{$this->getHost()}/whatsapp/send";
            $payload['phone'] = $toPhone;
        }

        try {
	        $client = new \GuzzleHttp\Client([
                'verify' => false,
            ]);
	
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
