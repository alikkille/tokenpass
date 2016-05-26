<?php

use PHPUnit_Framework_Assert as PHPUnit;
use TKAccounts\Models\Address;
use TKAccounts\Models\OAuthClient;
use TKAccounts\Models\Provisional;
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
    
    public function testProvisionalSourceAPI()
    {
        $mock_builder = app('Tokenly\XChainClient\Mock\MockBuilder');
        $mock_builder->setBalances(['BTC' => 0.123]);
        $mock = $mock_builder->installXChainMockClient($this);

        // create an oauth client
        $oauth_client = app('OAuthClientHelper')->createSampleOAuthClient();        

        $source_address = '1GGsaA2kBEUW1HRc5KvMnzEKpmHbQqzcmP';
        $source_address2 = '1157iDqgnkG87kyGSh1iF93grt1HQFVCHw';
        $source_address3 = '13tCQM6Nse3zugyYEJKZBuHAbr7irYx2Xp ';
        $proof = 'IHnyXpEMX+Dhu/em3SYEC+pLZPQYI1EblsjIGpPEVy2SmPJ1p6CBDvy71llh6lYMt5SxTx51SOImSpIp1PQoGUI=';
        $proof_suffix = '_'.Provisional::getProofHash($oauth_client['id']);
        
        $default_params = array('client_id' => $oauth_client['id']);
        $query_params = $default_params;
        
        //register source address
        $route = route('api.tca.provisional.register');
        $query_params['address'] = $source_address;
        $query_params['proof'] = $proof;
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);        
        
        //register with asset restrictions
        $route = route('api.tca.provisional.register');
        $query_params['address'] = $source_address2;
        $query_params['assets'] = array('TOKENLY', 'LTBCOIN');
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);        
        
        //register with comma separated asset restrictions
        $route = route('api.tca.provisional.register');
        $query_params['address'] = $source_address3;
        $query_params['assets'] = 'TOKENLY, LTBCOIN';
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);          
        
        //delete a source address
        $route = route('api.tca.provisional.delete', $source_address);
        $query_params = $default_params;
        $request = Request::create($route, 'DELETE', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);      
        PHPUnit::assertTrue($json_data['result']);   
        
        //get list of source addresses
        $route = route('api.tca.provisional.list');
        $request = Request::create($route, 'GET', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);    
        PHPUnit::assertContains('whitelist', $json_data);    
        PHPUnit::assertEquals($proof_suffix, $json_data['proof_suffix']);    
        
        //make sure deletion really worked
        PHPUnit::assertThat($json_data['whitelist'], $this->logicalNot($this->arrayHasKey($source_address)));
        
    }
    
    public function testProvisionalTransactionAPI()
    {
        $mock_builder = app('Tokenly\XChainClient\Mock\MockBuilder');
        $mock_builder->setBalances(['BTC' => 0.123]);
        $mock = $mock_builder->installXChainMockClient($this);
        
        //register user
        $user_helper = $this->buildUserHelper();
        $user = $user_helper->createNewUser();

        // create an oauth client
        $oauth_client = app('OAuthClientHelper')->createSampleOAuthClient();    
        
        //setup some variables
        $source_address = '1BY44aSERnwUGNKBhTY8Zqp83FbjUXNxVS';
        $destination = '1GGsaA2kBEUW1HRc5KvMnzEKpmHbQqzcmP';
        $proof = 'IHnyXpEMX+Dhu/em3SYEC+pLZPQYI1EblsjIGpPEVy2SmPJ1p6CBDvy71llh6lYMt5SxTx51SOImSpIp1PQoGUI=';
        $fingerprint = 'asdfghjklqwertyuiop';
        
        $default_params = array('client_id' => $oauth_client['id']);
        $query_params = $default_params;
        
        //add destination address to users TCA address list
        Address::unguard();
        $new_address = Address::create([
            'user_id'  => $user['id'],
            'type'     => 'BTC',
            'address'  => $destination,
            'label'    => 'Addr One',
            'verified' => 1,
            'public'   => 1,
        ]);

        // add a new balance entry to the new address
        DB::Table('address_balances')->insert([
            'address_id' => $new_address->id,
            'asset'      => 'SOUP',
            'balance'    => 1 * self::SATOSHI,
            'updated_at' => time(),
        ]);        
        
        //register source address to whitelist
        $route = route('api.tca.provisional.register');
        $query_params['address'] = $source_address;
        $query_params['proof'] = $proof;
        $query_params['assets'] = array('SOUP', 'LTBCOIN');
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);        
        
        //submit a provisional transaction / token promise with invalid asset
        $route = route('api.tca.provisional.tx.register');
        $query_params = $default_params;
        $query_params['source'] = $source_address;
        $query_params['destination'] = $destination;
        $query_params['asset'] = 'TOKENLY';
        $query_params['quantity'] = intval(1250*self::SATOSHI);
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);      
        
        //submit promise with valid asset, but value greater than balance
        $query_params['asset'] = 'SOUP';
        $query_params['quantity'] = intval(1250000000*self::SATOSHI);
        $query_params['expiration'] = time()+3600;
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);   
        
        //submit with invalid expiration
        $query_params['quantity'] = intval(1250*self::SATOSHI);
        $query_params['expiration'] = 100;
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);           
        
        //submit real promise 
        $query_params['expiration'] = time()+3600;
        $query_params['fingerprint'] = $fingerprint;
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);           
        PHPUnit::assertContains('tx', $json_data);
        $promise_id = $json_data['tx']['promise_id'];
        
        //get provisional tx/promise
        $query_params = $default_params;
        $route = route('api.tca.provisional.tx.get', $fingerprint);
        $request = Request::create($route, 'GET', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);           
        PHPUnit::assertContains('tx', $json_data);
        
        //register another one
        $route = route('api.tca.provisional.tx.register');
        $query_params = $default_params;
        $query_params['source'] = $source_address;
        $query_params['destination'] = $destination;        
        $query_params['asset'] = 'SOUP';        
        $query_params['quantity'] = intval(1250*self::SATOSHI);
        $query_params['expiration'] = time()+3600;
        $query_params['ref'] = 'test ref data';
        $request = Request::create($route, 'POST', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);           
        PHPUnit::assertContains('tx', $json_data);
        $promise_id2 = $json_data['tx']['promise_id'];  
        
        //update provisional tx with invalid amount
        $route = route('api.tca.provisional.tx.update', $promise_id);
        $query_params = $default_params;
        $query_params['quantity'] = intval(12500000*self::SATOSHI);
        $request = Request::create($route, 'PATCH', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);
        
        //update provisional tx with invalid expiration
        $query_params['quantity'] = intval(2000*self::SATOSHI);
        $query_params['expiration'] = 100;
        $request = Request::create($route, 'PATCH', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true); 
        PHPUnit::assertFalse($json_data['result']);           
        
        //update provisional tx for real
        $query_params['expiration'] = time()+86400;
        $query_params['txid'] = '1091247b29e452673851411c2df733ba10ed872c57540726821c26d1afb39fc9';
        $query_params['ref'] = 'testing update';
        $request = Request::create($route, 'PATCH', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true); 
        PHPUnit::assertTrue($json_data['result']);       
        PHPUnit::assertContains('tx', $json_data);
        PHPUnit::assertEquals($query_params['ref'], $json_data['tx']['ref']);
        
        //delete second provisional tx
        $route = route('api.tca.provisional.tx.delete', $promise_id2);
        $query_params = $default_params;
        $request = Request::create($route, 'DELETE', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);         
        PHPUnit::assertTrue($json_data['result']);
        
        //confirm deletion
        $route = route('api.tca.provisional.tx.get', $promise_id2);
        $request = Request::create($route, 'GET', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertFalse($json_data['result']);                 
        
        //get list of promised transactions
        $route = route('api.tca.provisional.tx.list');
        $request = Request::create($route, 'GET', $query_params, []);
        $response = app('Illuminate\Contracts\Http\Kernel')->handle($request);
        $json_data = json_decode($response->getContent(), true);
        PHPUnit::assertTrue($json_data['result']);           
        PHPUnit::assertContains('list', $json_data);           
        
        //make sure provisional balance is applied to user
        $balances = Address::getAllUserBalances($user['id']);
        PHPUnit::assertArrayHasKey('SOUP', $balances);
        PHPUnit::assertEquals((2000+1)*self::SATOSHI, $balances['SOUP']);
    }
    

    


    ////////////////////////////////////////////////////////////////////////


    protected function buildUserHelper() {
        $user_helper = app('UserHelper')->setTestCase($this);
        return $user_helper;
    }


}
