<?php

use TKAccounts\TestHelpers\UserHelper;
use Illuminate\Support\Facades\App;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* PlatformAdminClientTest
*/
class PlatformAdminClientTest extends TestCase {

    protected $use_database = true;

    public function testPlatformAdminClientRoutes() {
        $helper = $this->setupPlatformAdminHelper();
        $helper->beAuthorizedUser();

        $helper->testCreate(collect(app('OAuthClientHelper')->getRandomOAuthClientVars())->forget(['id'])->all());
        $helper->testUpdate(['name' => 'foobar2']);
        $helper->testDelete();
    }


    ////////////////////////////////////////////////////////////////////////

    public function setUp() {
        // putenv('PLATFORM_ADMIN_DEVELOPMENT_MODE_ENABLED=1');
        return parent::setUp();
    }


    protected function setupPlatformAdminHelper() {
        $platform_admin_helper = app('Tokenly\PlatformAdmin\TestHelper\PlatformAdminTestHelper');
        $platform_admin_helper
            ->setRoutePrefix('client')
            ->setRepository(app('TKAccounts\Repositories\OAuthClientRepository'))
            ->setCreateFunction(function() {
                return app('OAuthClientHelper')->createRandomOAuthClient();
            });
        return $platform_admin_helper;
    }


}
