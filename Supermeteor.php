<?php

namespace Supermeteor;

class Supermeteor
{
    public $secretKey, $statusCode, $message;

    /**
     * Supermeteor constructor.
     */
    public function __construct($secretKey)
    {
        $this->secretKey = $secretKey;
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
        http_response_code($this->statusCode);
        header('Content-Type: application/json');
        $message = ["message" => $this->message];
        $response = json_encode($message);

        return $response;
    }

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

    public function sendMessage($type, $phone, $message)
    {
        // validate if type, phone or message must not blank.
        $valid = $this->ValidateSendMessageRequest($type, $phone, $message);
        if (!$valid) {
            $response = $this->response();
            return $response;
        }

        // check which type of message to send.
        switch (strtolower($type)) {
            // for type sms
            case strtolower($type) == 'sms':
                $url = 'https://email-uat.lncknight.com/sms/send';
                break;
            // for type whatsaap
            case strtolower($type) == 'whatsapp':
                $url = 'https://email-uat.lncknight.com/whatsapp/send';
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
            $result = json_decode($response->getBody());
            $resultArr = ['jobId' => $result->jobId];
            header('Content-Type: application/json');
            $result = json_encode($resultArr);

            return $result;
        } catch (\Exception $e){

            http_response_code($e->getCode());
            header('Content-Type: application/json');
            $response = $e->getResponse();
            $response = $response->getBody()->getContents();

            return $response;
        }
    }

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

    public function sendEmail($email, $subject, $message)
    {
        // validate if email, message, subject must not blank.
        $valid = $this->ValidateSendEmailRequest($email, $subject, $message);

        if (!$valid) {
            $response = $this->response();
            return $response;
        }

        $url = 'https://email-uat.lncknight.com/email/send';
        $payload = [
            "secret" => $this->secretKey,
            "email" => $email,
            "subject" => $subject,
            "message" => $message
        ];

        $client = new \GuzzleHttp\Client();

        $response = $client->request(
            'POST', $url, [\GuzzleHttp\RequestOptions::JSON => [$payload]]);
        $contents = $response->getBody()->getContents();
        
        return $contents;
    }
}
