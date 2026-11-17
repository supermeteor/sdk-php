<?php

// Stub Guzzle so an unexpected client cannot access the network.
namespace GuzzleHttp {
    class Client {
        public function __construct($options = []) {}
        public function request($method, $url, $options = []) {
            throw new \RuntimeException('Unexpected non-injected client');
        }
    }
    class RequestOptions { const JSON = 'json'; }
}

namespace {
    require __DIR__ . '/../RequestException.php';
    require __DIR__ . '/../Client.php';

    class VerifyResponse {
        private $body;
        public function __construct($body) { $this->body = $body; }
        public function getBody() { return $this; }
        public function getContents() { return $this->body; }
    }
    class VerifyFakeClient {
        public $calls = [];
        public $body = '{}';
        public $error;
        public function request($method, $url, $options) {
            $this->calls[] = [$method, $url, $options];
            if ($this->error) { throw $this->error; }
            return new VerifyResponse($this->body);
        }
    }
    function verifyAssert($condition, $message) {
        if (!$condition) { throw new \Exception($message); }
    }
    function verifyException($callback) {
        try { $callback(); } catch (\Supermeteor\RequestException $e) { return $e; }
        throw new \Exception('Expected RequestException');
    }
    function verifyFixture($sandbox = false) {
        $sdk = new \Supermeteor\Client('test-secret', $sandbox);
        $fake = new VerifyFakeClient();
        $sdk->setHttpClient($fake);
        return [$sdk, $fake];
    }
    function verifyCall($call, $url, $payload) {
        list($method, $actualUrl, $options) = $call;
        verifyAssert($method === 'POST' && $actualUrl === $url, 'method or URL');
        verifyAssert($options['json'] === $payload, 'payload');
        verifyAssert($options['verify'] === true, 'TLS verification');
        verifyAssert($options['connect_timeout'] === 10, 'connect timeout');
        verifyAssert($options['timeout'] === 20, 'overall timeout');
    }

    $tests = [
        'start and check payloads, URLs, responses and secure options' => function () {
            foreach ([false, true] as $sandbox) {
                list($sdk, $fake) = verifyFixture($sandbox);
                $host = $sandbox ? 'https://api-uat.supermeteor.com' : 'https://api.supermeteor.com';
                $fake->body = '{"session_id":"opaque-id","status":"pending"}';
                verifyAssert($sdk->verifyStart('+6591234567') === ['session_id' => 'opaque-id', 'status' => 'pending'], 'start response');
                verifyCall($fake->calls[0], $host . '/sms/verifyStart', ['secret' => 'test-secret', 'phone_number' => '+6591234567']);
                $fake->body = '{"status":"failed","reason":"incorrect_code"}';
                verifyAssert($sdk->verifyCheck('opaque-id', '001234') === ['status' => 'failed', 'reason' => 'incorrect_code'], 'check response');
                verifyCall($fake->calls[1], $host . '/sms/verifyCheck', ['secret' => 'test-secret', 'session_id' => 'opaque-id', 'code' => '001234']);
                $fake->body = '{"status":"verified"}';
                verifyAssert($sdk->verifyCheck('opaque-id', '001234') === ['status' => 'verified'], 'verified response');
                verifyAssert(count($fake->calls) === 3, 'unexpected retry');
            }
        },
        'invalid inputs never send' => function () {
            list($sdk, $fake) = verifyFixture();
            foreach (['', '6591234567', '+012345', '+1234567890123456', '+65 91234567', null, 123] as $phone) {
                verifyException(function () use ($sdk, $phone) { $sdk->verifyStart($phone); });
            }
            foreach (['', '  ', null, 123] as $session) {
                verifyException(function () use ($sdk, $session) { $sdk->verifyCheck($session, '001234'); });
            }
            foreach (['', '12345', '1234567', '１２３４５６', '12a456', 123456, null] as $code) {
                verifyException(function () use ($sdk, $code) { $sdk->verifyCheck('opaque-id', $code); });
            }
            verifyAssert(count($fake->calls) === 0, 'invalid request sent');
        },
        'request errors and malformed JSON wrap causes without retry' => function () {
            foreach (['verifyStart', 'verifyCheck'] as $method) {
                list($sdk, $fake) = verifyFixture();
                $args = $method === 'verifyStart' ? ['+6591234567'] : ['opaque-id', '001234'];
                foreach ([409, 503, 0] as $code) {
                    $fake->error = new \RuntimeException('offline failure', $code);
                    $e = verifyException(function () use ($sdk, $method, $args) { $sdk->$method(...$args); });
                    verifyAssert($e->getPrevious() === $fake->error && $e->getCode() === $code, 'request error');
                }
                verifyAssert(count($fake->calls) === 3, 'unexpected retry on error');
                $fake->error = null;
                $fake->body = '{invalid';
                $e = verifyException(function () use ($sdk, $method, $args) { $sdk->$method(...$args); });
                verifyAssert($e->getPrevious() instanceof \UnexpectedValueException && count($fake->calls) === 4, 'JSON error');
            }
        },
    ];
    $failures = 0;
    foreach ($tests as $name => $test) {
        try { $test(); echo "PASS: $name\n"; }
        catch (\Throwable $e) { ++$failures; echo "FAIL: $name: {$e->getMessage()}\n"; }
    }
    exit($failures ? 1 : 0);
}
