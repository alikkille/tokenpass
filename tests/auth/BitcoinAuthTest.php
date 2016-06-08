
<?php

use \PHPUnit_Framework_Assert as PHPUnit;
use TKAccounts\TestHelpers\UserHelper;

class BitcoinAuthTest extends TestCase {

    protected $use_database = true;

    public function testBitcoinAuthorizeFormGet() {
        // check loading authorize form
        $response = $this->call('GET', '/auth/bitcoin');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<form', $response->getContent());
        PHPUnit::assertInternalType('string', Session::get('sigval'));
    }

    public function testPRNGeneration() {
        $file_content = file_get_contents('database/wordlists/english.txt');
        $dictionary = explode(PHP_EOL, $file_content);

        $x = 1;
        $list = [];
        while($x <= 1000) {
            $one = random_int(0, 2047);
            $two = random_int(0, 2047);
            $code = random_int(0, 99);
            array_push($list, $dictionary[$one]. ' ' .$dictionary[$two]. ' ' .$code);
            $x++;
        }

        $unique = array_unique($list);
        PHPUNIT::assertEquals($unique, $list);

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