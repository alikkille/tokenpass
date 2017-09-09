
<?php

use PHPUnit_Framework_Assert as PHPUnit;
use TKAccounts\Models\Address;
use TKAccounts\TestHelpers\AddressHelper;
use TKAccounts\TestHelpers\UserHelper;

class InventoryTest extends TestCase
{
    protected $use_database = true;

    public function testInventoryPageLoad()
    {
        $user_helper = app('UserHelper')->setTestCase($this);

        // create a new user and login
        $user = $user_helper->createNewUser();
        $user = $user_helper->loginWithForm($this->app);

        // check loading authorize form
        $response = $this->call('GET', '/inventory');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('>Inventory</', $response->getContent());
    }

    public function testRegisterAddress()
    {
        $this->setupXChainMock();
        $address_helper = app('AddressHelper');
        $user_helper = app('UserHelper')->setTestCase($this);

        // create a new user and login
        $user = $user_helper->createNewUser();
        $user_helper->loginWithForm($this->app);

        $address = $address_helper->createNewAddress($user);

        // Test with a non existant address
//        $response = $this->call('POST', '/inventory/address/new', array(
//            "address" => "1JztLWos5K7LsqW5E78EASgiVBaCe6f7cD",
//            "label" => "My First Address",
//            "public" => true
//        ) , array());
    }

    public function testDeleteAddress()
    {
        $address_helper = app('AddressHelper');
        $user_helper = app('UserHelper')->setTestCase($this);

        $user = $user_helper->createNewUser();
        $user_helper->loginWithForm($this->app);

        $address = $address_helper->createNewAddress($user);

        // Delete an entry correctly
        $response = $this->call('GET', '/inventory/address/1JztLWos5K7LsqW5E78EASgiVBaCe6f7cD/delete', [
            'address' => '1JztLWos5K7LsqW5E78EASgiVBaCe6f7cD',
        ], []);
        PHPUnit::assertContains('Address deleted!', Session::get('message'));

        // Attempt to delete non existant address
        $response = $this->call('GET', '/inventory/address/1FakeAddressNaow/delete', [
            'address' => '1FakeAddressNaow',
        ], []);
        PHPUnit::assertContains('Address not found', Session::get('message'));
    }

    public function testEditAddress()
    {
        $address_helper = app('AddressHelper');
        $user_helper = app('UserHelper')->setTestCase($this);

        $user = $user_helper->createNewUser();
        $user_helper->loginWithForm($this->app);

        $address = $address_helper->createNewAddress($user);

        // Attempt to edit non existant address
        $response = $this->call('POST', '/inventory/address/1FakeAddressNaow/edit', [
            'address' => '1FakeAddressNaow',
        ], []);
        PHPUnit::assertContains('Address not found', Session::get('message'));

        // Edit an entry correctly
        $response = $this->call('POST', '/inventory/address/1JztLWos5K7LsqW5E78EASgiVBaCe6f7cD/edit', [
            'address' => '1JztLWos5K7LsqW5E78EASgiVBaCe6f7cD',
        ], []);
        PHPUnit::assertContains('Address updated!', Session::get('message'));
    }

    public function testToggleAsset()
    {
        $address_helper = app('AddressHelper');
        $user_helper = app('UserHelper')->setTestCase($this);

        $user = $user_helper->createNewUser();
        $user_helper->loginWithForm($this->app);

        $address = $address_helper->createNewAddress($user);

        // toggle an entry correctly
        $response = $this->call('POST', '/inventory/asset/1JztLWos5K7LsqW5E78EASgiVBaCe6f7cD/toggle', [
                'address' => '1JztLWos5K7LsqW5E78EASgiVBaCe6f7cD',
                'toggle'  => true,
                ], []);

        PHPUnit::assertEquals(200, $response->getStatusCode());
    }

    public function testRefreshBalance()
    {
        $this->setupXChainMock();
        $address_helper = app('AddressHelper');
        $user_helper = app('UserHelper')->setTestCase($this);

        $user = $user_helper->createNewUser();
        $user_helper->loginWithForm($this->app);

        $address = $address_helper->createNewAddress($user);

        $response = $this->call('GET', '/inventory/refresh', [], []);
        // PHPUnit::assertContains('Token inventory balances updated!', Session::get('message'));
    }

    public function testCheckPageRefresh()
    {
        $address_helper = app('AddressHelper');
        $user_helper = app('UserHelper')->setTestCase($this);

        $user = $user_helper->createNewUser();
        $user_helper->loginWithForm($this->app);

        $address = $address_helper->createNewAddress($user);

        $response = $this->call('GET', '/inventory/check-refresh', [], []);
        PHPUnit::assertEquals(200, $response->getStatusCode());
    }

    public function testInventoryVerifyAddress()
    {
        $this->setupXChainMock();
        $address_helper = app('AddressHelper');
        $user_helper = app('UserHelper')->setTestCase($this);

        // create a new user and login
        $user = $user_helper->createNewUser();
        $user_helper->loginWithForm($this->app);

        $address = $address_helper->createNewAddress($user, [
            'user_id' => '1',
            'address' => '1sdBCPkJozaAqwLF3mTEgNS8Uu95NMVdp',
        ]);

        $address = $address_helper->createNewAddress($user, [
            'address' => '1sdBCPkJozaAqwLSomeAddress',
        ]);

        // Test with a non existant address
        $response = $this->call('POST', '/inventory/address/1sd444444444444NS8Uu95NMVdp/verify', [
        ], []);
        PHPUnit::assertContains('Address not found', Session::get('message'));

        // Test with a missing signature
        $response = $this->call('POST', '/inventory/address/1sdBCPkJozaAqwLSomeAddress/verify', [
        ], []);
        PHPUnit::assertContains('Signature required', Session::get('message'));

        // Private key used is : KzPMHLZfubuRR8GxZyG2vygqWk391RuEGTqFH1jUtyWgKXrH3FFT
        $address_sig = 'IC8nBnxL1wE8P4jWGkVZI1IVucw4lDAQ3YVK7ZdeQCCbQwCoU+PcQUnEAN5C71pjVVqdyFzbgOJsbp6B0Agwsg8=';
        $sig_message = '4cda873ee9';
        Session::flash('sigval', $sig_message);
        Session::set('1sdBCPkJozaAqwLF3mTEgNS8Uu95NMVdp', $sig_message);

        // Test with all correct info
        $response = $this->call('POST', '/inventory/address/1sdBCPkJozaAqwLF3mTEgNS8Uu95NMVdp/verify', [
            'sig' => $address_sig,
        ], []);

        PHPUnit::assertContains('Address 1sdBCPkJozaAqwLF3mTEgNS8Uu95NMVdp ownership proved successfully!', Session::get('message'));
        PHPUnit::assertEquals(302, $response->status());

        // Test with duplicate registered address
        $alt_address = $address_helper->createNewAddress($user, [
            'user_id' => '2',
            'address' => '1sdBCPkJozaAqwLF3mTEgNS8Uu95NMVdp',
        ]);

        $response = $this->call('POST', '/inventory/address/1sdBCPkJozaAqwLF3mTEgNS8Uu95NMVdp/verify', [
            'sig' => $address_sig,
        ], []);
        PHPUnit::assertContains('The address 1sdBCPkJozaAqwLF3mTEgNS8Uu95NMVdp is already in use by another account', Session::get('message'));
        PHPUnit::assertEquals(302, $response->status());
    }

    protected function setupXChainMock()
    {
        $this->mock_builder = app('Tokenly\XChainClient\Mock\MockBuilder');
        $this->xchain_mock_recorder = $this->mock_builder->installXChainMockClient($this);
    }
}
