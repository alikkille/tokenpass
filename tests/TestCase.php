<?php

use Illuminate\Support\Facades\Artisan;

class TestCase extends Illuminate\Foundation\Testing\TestCase {

    protected $useDatabase = false;

	/**
	 * Creates the application.
	 *
	 * @return \Illuminate\Foundation\Application
	 */
	public function createApplication()
	{
		return require __DIR__.'/../bootstrap/app.php';
	}

    public function setUp()
    {
        parent::setUp();

        if($this->useDatabase)
        {
            $this->setUpDb();
        }
    }

    public function setUpDb()
    {
        $this->app['Illuminate\Contracts\Console\Kernel']->call('migrate');
    }

    public function teardownDb()
    {
        $this->app['Illuminate\Contracts\Console\Kernel']->call('migrate:reset');
    }

}
