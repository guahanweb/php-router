<?php
use PHPUnit\Framework\TestCase;
use GuahanWeb\Http;

class RequestTest extends TestCase {
    protected function tearDown() {
        $_SERVER = null;
    }

    public function testBasicAttributes() {
        // Mock request
        $_SERVER = array(
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/'
        );

        $request = new Http\Request();
        $this->assertEquals($request->method, 'GET');
        $this->assertEquals($request->uri, '/');
    }
}
