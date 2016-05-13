
<?php

use \PHPUnit_Framework_Assert as PHPUnit;
use TKAccounts\TestHelpers\UserHelper;

class BitcoinAuthTest extends TestCase {

    protected $use_database = true;

    public function testBitcoinAuthorizeFormGet() {
        // check loading authorize form
        $response = $this->call('GET', '/auth/bitcoin');

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($response->getContent());
        $sigval = $dom->getElementsByTagName('strong')->item(1)->textContent;

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<form', $response->getContent());
        $this->assertInternalType('string',$sigval);
    }

    public function testBitcoinAuthorizeFormPostCorrectData() {
        $this->setupXChainMock();
        $user_helper = app('UserHelper')->setTestCase($this);
        $response = $user_helper->sendBitcoinLoginRequest(true);

        //$this->assertEquals(200 , $response->status());
        //$this->assertRedirectedTo('dashboard');
    }

    public function testBitcoinAuthorizeFormPostIncorrectData() {
        $this->setupXChainMock();
        $user_helper = app('UserHelper')->setTestCase($this);
        $response = $user_helper->sendBitcoinLoginRequest(false);

        $this->assertNotNull($response);
        $this->assertEquals(302 , $response->status());
    }

    protected function setupXChainMock() {
        $this->mock_builder = app('Tokenly\XChainClient\Mock\MockBuilder');
        $this->xchain_mock_recorder = $this->mock_builder->installXChainMockClient($this);
    }

}