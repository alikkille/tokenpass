<?php

namespace TKAccounts\Providers\CMSAuth;

use Exception;
use Illuminate\Support\Facades\Log;

class CMSAccountLoaderMockBuilder {


   public static function installMockCMSAccountLoader() {
        $test_case = new \TKAccounts\Providers\CMSAuth\Mock\MockTestCase();

        $loader_mock = $test_case->getMockBuilder('TKAccounts\Providers\CMSAuth\CMSAccountLoader')
            ->disableOriginalConstructor()
            ->getMock();

        // install the pusher client into the DI container
        app()->bind('TKAccounts\Providers\CMSAuth\CMSAccountLoader', function($app) use ($loader_mock) {
            return $loader_mock;
        });

   }
}