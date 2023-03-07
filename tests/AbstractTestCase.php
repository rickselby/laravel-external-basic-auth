<?php

namespace RickSelby\Tests;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
use RickSelby\Laravel\Auth\ExternalBasicAuthServiceProvider;

abstract class AbstractTestCase extends AbstractPackageTestCase
{
    protected static function getServiceProviderClass(): string
    {
        return ExternalBasicAuthServiceProvider::class;
    }
}
