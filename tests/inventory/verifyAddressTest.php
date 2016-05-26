
<?php

use \PHPUnit_Framework_Assert as PHPUnit;
use TKAccounts\TestHelpers\UserHelper;
use TKAccounts\TestHelpers\AddressHelper;

class InventoryTest extends TestCase
{

    protected $use_database = true;

    public function testInventoryPageLoad() {
        $user_helper = app('UserHelper')->setTestCase($this);

        // create a new user and login
        $user = $user_helper->createNewUser();
        $user = $user_helper->loginWithForm($this->app);

        // check loading authorize form
        $response = $this->call('GET', '/inventory');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<h1>Token Inventory', $response->getContent());
    }
}