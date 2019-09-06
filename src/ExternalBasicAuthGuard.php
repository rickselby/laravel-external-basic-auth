<?php

namespace RickSelby\Laravel\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Foundation\Application;

class ExternalBasicAuthGuard implements Guard
{
    use GuardHelpers;

    /** @var \Illuminate\Contracts\Foundation\Application */
    protected $app;

    /** @var array */
    protected $config;

    public function __construct(UserProvider $provider, Application $application)
    {
        $this->provider = $provider;
        $this->app = $application;
        $this->config = Config::get('auth.guards.'.Config::get('auth.defaults.guard'));
    }

    /**
     * Get the currently authenticated user.
     *
     * Using $_SERVER rather than Request::server() to aid testing...
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        // Get the user identifier from $_SERVER
        $userID = $_SERVER['REMOTE_USER'] ?? null;

        // Allow local environment to set TEST_USER in .env to test as someone else
        if ($this->app->environment('local')) {
            $userID = env('TEST_USER', $userID);
        }

        // If we haven't got the user identifier yet, check the headers, if we can
        if ($userID === null && isset($this->config['header'])) {
            $userID = $this->app->make(\Illuminate\Http\Request::class)->header($this->config['header']);
        }

        if (isset($this->config['strip'])) {
            $userID = preg_replace('/'.$this->config['strip'].'/', '', $userID);
        }

        if (! isset($this->config['field'])) {
            // No alternate field - the identifier is the ID
            $this->user = $this->provider->retrieveById($userID);
        } else {
            // An alternate field set - look up the user by this field instead
            $this->user = $this->provider->retrieveByCredentials([$this->config['field'] => $userID]);
        }

        // Get the (optional) relationships to eager load for the auth'd user
        $this->eagerLoadRelationships();

        return $this->user;
    }

    /**
     * Eager load relationships for the auth'd user.
     */
    protected function eagerLoadRelationships()
    {
        if (isset($this->config['load']) && $this->user) {
            $this->user->load($this->config['load']);
        }
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @throws \Exception
     */
    public function validate(array $credentials = [])
    {
        // no logging in can be done. True or false? Or exception?
        throw new \Exception('No need to validate...');
    }
}
