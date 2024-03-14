<?php

namespace RickSelby\Tests;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use RickSelby\Laravel\Auth\ExternalBasicAuthGuard;

class GuardTest extends TestCase
{
    /** @var MockObject|Application */
    private $application;

    /** @var MockObject|UserProvider */
    private $userProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userProvider = $this->createMock(UserProvider::class);
        $this->application = $this->createMock(Application::class);

        Config::set('auth.defaults.guard', 'foo');
    }

    protected function getUser()
    {
        $guard = new ExternalBasicAuthGuard($this->userProvider, $this->application);
        $guard->user();
    }

    /**
     * @test
     *
     * @dataProvider userProvider
     */
    public function it_uses_remote_user_field_to_find_user($username)
    {
        $_SERVER['REMOTE_USER'] = $username;

        $this->application->method('environment')->willReturn(false);
        $this->userProvider->expects($this->once())->method('retrieveById')->with($username)->willReturn(true);

        $this->getUser();
    }

    /**
     * @test
     *
     * @dataProvider userProvider
     */
    public function it_uses_remote_user_if_local($username)
    {
        $_SERVER['REMOTE_USER'] = $username;

        $this->application->method('environment')->with('local')->willReturn(true);
        $this->userProvider->expects($this->once())->method('retrieveById')->with($username)->willReturn(true);

        $this->getUser();
    }

    /**
     * @test
     *
     * @dataProvider userProvider
     */
    public function it_checks_environment_when_local($username)
    {
        putenv('TEST_USER='.$username);

        $this->application->method('environment')->willReturn(true);
        $this->userProvider->expects($this->once())->method('retrieveById')->with($username)->willReturn(true);

        $this->getUser();
    }

    /**
     * @test
     *
     * @dataProvider userProvider
     */
    public function environment_overrides_remote_user_when_local($username)
    {
        $_SERVER['REMOTE_USER'] = 'bob';
        putenv('TEST_USER='.$username);

        $this->application->method('environment')->willReturn(true);
        $this->userProvider->expects($this->once())->method('retrieveById')->with($username)->willReturn(true);

        $this->getUser();
    }

    /**
     * @test
     *
     * @dataProvider userProvider
     */
    public function environment_ignored_when_not_local($username)
    {
        $_SERVER['REMOTE_USER'] = $username;
        putenv('TEST_USER=bob');

        $this->application->method('environment')->willReturn(false);
        $this->userProvider->expects($this->once())->method('retrieveById')->with($username)->willReturn(true);

        $this->getUser();
    }

    /** @test */
    public function user_retrieval_uses_alternate_field()
    {
        $_SERVER['REMOTE_USER'] = 'abc123';
        Config::set('auth.guards.foo.field', 'field');

        $this->application->method('environment')->willReturn(false);
        $this->userProvider->expects($this->once())->method('retrieveByCredentials')->willReturn(true);

        $this->getUser();
    }

    /** @test */
    public function user_retrieval_strips_stuff()
    {
        $username = 'bob';
        $_SERVER['REMOTE_USER'] = $username.'STRIPTHIS';
        Config::set('auth.guards.foo.strip', 'STRIPTHIS');

        $this->application->method('environment')->willReturn(false);
        $this->userProvider->expects($this->once())->method('retrieveById')->with($username)->willReturn(true);

        $this->getUser();
    }

    /** @test */
    public function eager_load_is_called_if_required()
    {
        $_SERVER['REMOTE_USER'] = 'abc123';
        Config::set('auth.guards.foo.load', 'load-fields');

        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())->method('load')->with('load-fields');

        $this->application->method('environment')->willReturn(false);
        $this->userProvider->method('retrieveById')->willReturn($userMock);

        $this->getUser();
    }

    /**
     * @test
     *
     * @dataProvider userProvider
     */
    public function header_checked_if_required($user)
    {
        Config::set('auth.guards.foo.header', 'header-field');

        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('header')->with('header-field')->willReturn($user);

        $this->userProvider->method('retrieveById')->with($user)->willReturn(true);
        $this->application->method('environment')->with('local')->willReturn(false);
        $this->application->method('make')->with(Request::class)->willReturn($request);

        $this->getUser();
    }

    /**
     * @before
     */
    public function clearValues()
    {
        if (isset($_SERVER['REMOTE_USER'])) {
            unset($_SERVER['REMOTE_USER']);
        }
        putenv('TEST_USER');
    }

    static public function userProvider()
    {
        return [
            ['test'],
            ['abc123'],
            ['1'],
            ['0'],
            ['abcdefghijklmnop'],
        ];
    }
}
