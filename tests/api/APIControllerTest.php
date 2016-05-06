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
        $mock_builder = app('Tokenly\XChainClient\Mock\MockBuilder');
        $mock_builder->setBalances(['BTC' => 0.123]);
        $mock = $mock_builder->installXChainMockClient($this);
        
        $user->uuid = '1234567890'; 
        $user->save(); //set a predictable uuid so we can test with a premade signature
        
        $new_address = '1GGsaA2kBEUW1HRc5KvMnzEKpmHbQqzcmP';
        $address_sig = 'IHnyXpEMX+Dhu/em3SYEC+pLZPQYI1EblsjIGpPEVy2SmPJ1p6CBDvy71llh6lYMt5SxTx51SOImSpIp1PQoGUI=';
        $sig_message = Address::getInstantVerifyMessage($user);

        //test with a bogus user
        $route = route('api.instant-verify', 123123);
        $query_params = ['msg' => $sig_message, 'sig' => $address_sig, 'address' => $new_address];
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);
        
        //test with no address
        $route = route('api.instant-verify', $user->username); //set proper route
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
        $query_params = ['msg' => 'qwerty', 'sig' => $address_sig, 'address' => $new_address];
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);        
        

        //test with all correct info
        $query_params = ['msg' => $sig_message, 'sig' => $address_sig, 'address' => $new_address];
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);        
        
    }


    ////////////////////////////////////////////////////////////////////////


    protected function buildUserHelper() {
        $user_helper = app('UserHelper')->setTestCase($this);
        return $user_helper;
    }


}
