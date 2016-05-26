<?php

use PHPUnit_Framework_Assert as PHPUnit;
use TKAccounts\Models\Address;
use TKAccounts\Models\OAuthClient;
use TKAccounts\TestHelpers\UserHelper;

/*
* APIControllerTest
*/
class APIControllerTest extends TestCase {

    const SATOSHI = 100000000;

    protected $use_database = true;

    public function testCheckTokenAccessAPI() {
        $user_helper = $this->buildUserHelper();

        // create a user
        $user = $user_helper->createNewUser();

        // create a new address
        Address::unguard();
        $new_address = Address::create([
            'user_id'  => $user['id'],
            'type'     => 'BTC',
            'address'  => '1JztLWos5K7LsqW5E78EASgiVBaCe6f7cD',
            'label'    => 'Addr One',
            'verified' => 1,
            'public'   => 1,
        ]);

        // add a new balance entry to the new address
        DB::Table('address_balances')->insert([
            'address_id' => $new_address->id,
            'asset'      => 'TOKENLY',
            'balance'    => 10 * self::SATOSHI,
            'updated_at' => time(),
        ]);
        DB::Table('address_balances')->insert([
            'address_id' => $new_address->id,
            'asset'      => 'LTBCOIN',
            'balance'    => 5000 * self::SATOSHI,
            'updated_at' => time(),
        ]);

        // create an oauth client
        $oauth_client = app('OAuthClientHelper')->createSampleOAuthClient();

        // add the scope
        $oauth_scope = app('TKAccounts\Repositories\OAuthScopeRepository')->create([
            'id'          => 'tca',
            'description' => 'TCA Access',
        ]);

        // create an oauth client connection
        $oauth_client_id = $oauth_client['id'];
        DB::table('client_connections')->insert([
            'uuid'       => '00000001',
            'user_id'    => $user['id'],
            'client_id'  => $oauth_client_id,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $oauth_connection = (array)DB::table('client_connections')->where('uuid', '00000001')->first();

        // create connection
        DB::table('client_connection_scopes')->insert([
            'connection_id' => $oauth_connection['id'],
            'scope_id'      => $oauth_scope['uuid'],
        ]);

        $route = route('api.tca.check', ['username' => $user['username']]);


        // check 10 TOKENLY (should be true)
        $token = 'TOKENLY';
        $query_params = ['client_id' => $oauth_client_id, 'TOKENLY' => 10];
        $request = Request::create($route, 'GET', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);

        // check 11 TOKENLY (should be false)
        $token = 'TOKENLY';
        $query_params = ['client_id' => $oauth_client_id, 'TOKENLY' => 11];
        $request = Request::create($route, 'GET', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);

        // check 10 TOKENLY AND 5000 LTBCOIN (should be true)
        $query_params = ['client_id' => $oauth_client_id, 'TOKENLY' => 10, 'LTBCOIN' => 5000];
        $request = Request::create($route, 'GET', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);

        // check 10 TOKENLY AND 5001 LTBCOIN (should be false)
        $query_params = ['client_id' => $oauth_client_id, 'TOKENLY' => 10, 'LTBCOIN' => 5001];
        $request = Request::create($route, 'GET', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);

        // check 10 TOKENLY OR 5001 LTBCOIN (should be true)
        $query_params = ['client_id' => $oauth_client_id, 'TOKENLY' => 10, 'LTBCOIN' => 5001, 'stackop_1' => 'OR'];
        $request = Request::create($route, 'GET', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);

        // check 11 TOKENLY OR 5001 LTBCOIN (should be false)
        $query_params = ['client_id' => $oauth_client_id, 'TOKENLY' => 11, 'LTBCOIN' => 5001, 'stackop_1' => 'OR'];
        $request = Request::create($route, 'GET', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);
    }
    
    public function testInstantVerifyAddressAPI()
    {
        // create a user
        $user_helper = $this->buildUserHelper();
        $user = $user_helper->createNewUser();
        $alt_user = $user_helper->createAltUser();
        $mock_builder = app('Tokenly\XChainClient\Mock\MockBuilder');
        $mock_builder->setBalances(['BTC' => 0.123]);
        $mock = $mock_builder->installXChainMockClient($this);
        
        $user->uuid = '1234567890'; 
        $user->save(); //set a predictable uuid so we can test with a premade signature

        $alt_user->uuid = '1234';
        $alt_user->save();

        // Private key used is : KzPMHLZfubuRR8GxZyG2vygqWk391RuEGTqFH1jUtyWgKXrH3FFT
        $new_address = '1sdBCPkJozaAqwLF3mTEgNS8Uu95NMVdp';
        $address_sig = 'IG528OHUJCPC7nNizE4G51+3ogrXV9zAV+pQjNNxCAXDSeZgXUHLp/hIiNH3FBz0ollMjOFU8XJHUPCMg/+4dlI=';
        $alt_address_sig = 'Hzk9Inq3too7fJqiZKFcWbD/YhaYzl6e2LmoSYCLldYsPwYDiZTlZJaK/3izovOzd8/wissGMigqG36LB19k9nM=';
        $sig_message = Address::getInstantVerifyMessage($user);
        $alt_sig_message = Address::getInstantVerifyMessage($alt_user);

        //test with all correct info
        $route = route('api.instant-verify', $user->username); //set proper route
        $query_params = ['msg' => $sig_message, 'sig' => $address_sig, 'address' => $new_address];
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);

        //test with all correct info but duplicate address of different user
        $route = route('api.instant-verify', $alt_user->username); //set proper route
        $query_params = ['msg' => $alt_sig_message, 'sig' => $alt_address_sig, 'address' => $new_address];
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);
        PHPUnit::assertContains('Address already authenticated', $json_data['error']);

        //test with a bogus user
        $route = route('api.instant-verify', 123123);
        $query_params = ['msg' => $sig_message, 'sig' => $address_sig, 'address' => $new_address];
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);
        
        //test with no address
        $query_params = ['msg' => $sig_message, 'sig' => $address_sig];
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);
        
        //test with missing input
        $query_params = ['sig' => $address_sig, 'address' => $new_address];
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);        
        
        //test with wrong message
        $route = route('api.instant-verify', $user->username);
        $query_params = ['msg' => 'qwerty', 'sig' => $address_sig, 'address' => $new_address];
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);
        
    }


    ////////////////////////////////////////////////////////////////////////


    protected function buildUserHelper() {
        $user_helper = app('UserHelper')->setTestCase($this);
        return $user_helper;
    }


}
