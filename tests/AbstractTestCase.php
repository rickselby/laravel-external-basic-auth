<?php

namespace RickSelby\Tests;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
use RickSelby\Laravel\Auth\ExternalBasicAuthServiceProvider;

abstract class AbstractTestCase extends AbstractPackageTestCase
{
    /**
     * Get the service provider class.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return string
     */
    protected function getServiceProviderClass()
    {
        return ExternalBasicAuthServiceProvider::class;
    }
}
