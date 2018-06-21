<?php
use Illuminate\Support\Facades\Redis;
use Zaichaopan\OnlineStatus\OnlineStatusEventServiceProvider;

abstract class TestCase extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [OnlineStatusEventServiceProvider::class];
    }

    public function setUp()
    {
        parent::setUp();
        Eloquent::unguard();
        $this->artisan('migrate', [
            '--database' => 'testbench'
        ]);
    }

    public function tearDown()
    {
        \Schema::drop('users');
        Redis::flushall();

        parent::tearDown();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        \Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('email');
            $table->rememberToken();
            $table->timestamps();
        });
    }
}
