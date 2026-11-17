<?php

// Stub Guzzle at the namespace boundary so this test cannot make network calls.
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

    class FakeResponse {
        private $body;
        public function __construct($body) { $this->body = $body; }
        public function getBody() { return $this; }
        public function getContents() { return $this->body; }
    }
    class FakeClient {
        public $calls = [];
        public $body = '{"msg":"OK"}';
        public $error;
        public function request($method, $url, $options) {
            $this->calls[] = [$method, $url, $options];
            if ($this->error) { throw $this->error; }
            return new FakeResponse($this->body);
        }
    }
    function check($condition, $message) {
        if (!$condition) { throw new \Exception($message); }
    }
    function expectRequestException($callback) {
        try { $callback(); } catch (\Supermeteor\RequestException $e) { return $e; }
        throw new \Exception('Expected RequestException');
    }
    function fixture($sandbox = false) {
        $sdk = new \Supermeteor\Client('test-secret', $sandbox);
        $fake = new FakeClient();
        $sdk->setHttpClient($fake);
        return [$sdk, $fake];
    }

    $tests = [
        'payload, URL, secure bounded options and decoded response' => function () {
            foreach ([false, true] as $sandbox) {
                list($sdk, $fake) = fixture($sandbox);
                check($sdk->sendEmail('user@example.com', 'Subject', '<p>Body</p>') === ['msg' => 'OK'], 'response');
                check(count($fake->calls) === 1, 'injected client was not called once');
                list($method, $url, $options) = $fake->calls[0];
                check($method === 'POST', 'method');
                check($url === ($sandbox ? 'https://api-uat.supermeteor.com' : 'https://api.supermeteor.com') . '/email/send', 'URL');
                check($options['json'] === ['secret' => 'test-secret', 'email' => 'user@example.com', 'subject' => 'Subject', 'message' => '<p>Body</p>'], 'payload');
                check(isset($options['verify']) && $options['verify'] === true, 'TLS verification');
                check(isset($options['connect_timeout']) && $options['connect_timeout'] > 0 && is_finite((float) $options['connect_timeout']), 'connect timeout');
                check(isset($options['timeout']) && $options['timeout'] > 0 && is_finite((float) $options['timeout']), 'timeout');
            }
        },
        'blank arguments throw without sending' => function () {
            foreach ([['', 's', 'm'], ['a', '', 'm'], ['a', 's', '']] as $args) {
                list($sdk, $fake) = fixture();
                $e = expectRequestException(function () use ($sdk, $args) { $sdk->sendEmail(...$args); });
                check(strpos($e->getMessage(), 'must not be blank') !== false, 'validation message');
                check(count($fake->calls) === 0, 'invalid input sent');
            }
        },
        'request exceptions retain cause' => function () {
            list($sdk, $fake) = fixture();
            $fake->error = new \RuntimeException('offline failure', 12);
            $e = expectRequestException(function () use ($sdk) { $sdk->sendEmail('a', 's', 'm'); });
            check($e->getPrevious() === $fake->error && $e->getCode() === 12, 'cause or code');
        },
        'invalid JSON raises an error' => function () {
            list($sdk, $fake) = fixture();
            $fake->body = '{invalid';
            $e = expectRequestException(function () use ($sdk) { $sdk->sendEmail('a', 's', 'm'); });
            check(count($fake->calls) === 1 && $e->getPrevious() instanceof \UnexpectedValueException, 'invalid JSON was not detected');
        },
    ];
    $failures = 0;
    foreach ($tests as $name => $test) {
        try { $test(); echo "PASS: $name\n"; }
        catch (\Throwable $e) { ++$failures; echo "FAIL: $name: {$e->getMessage()}\n"; }
    }
    exit($failures ? 1 : 0);
}
