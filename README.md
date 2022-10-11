# Laravel External Basic Auth

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
![Packagist Version](https://img.shields.io/packagist/v/rickselby/laravel-external-basic-auth)

This is a guard for Laravel that assumes the value in `$_SERVER['REMOTE_USER']`
is the identifier for the currently logged in user.

Laravel's built-in basic auth still auths the username and passwords against the `users` table,
which in our case will not contain their password.

## Usage

    composer require rickselby/laravel-external-basic-auth

Then, edit your `config/auth.php` file, and under `guards`, set the appropriate
driver to `external`; e.g.

    'guards' => [
        'web' => [
            'driver' => 'external',
            'provider' => 'users',
        ],
    ],

## Alternate lookup field

By default, the package will match the `$_SERVER['REMOTE_USER']` value against the `id` of the user model.
If the `$_SERVER['REMOTE_USER']` value is in a different field 
(e.g. the user model has a standard auto-incrementable integer for an ID, and a separate `username` field)
then the package can look up a user by this field instead.

Edit your `config/auth.php` file, and under the appropriate `guard`, add a `field` setting:

    'guards' => [
        'web' => [
            'driver' => 'external',
            'provider' => 'users',
            'field' => 'username',
        ],
    ],

## Eager load relationships

It may be desirable to eager load relationships for the authenticated user.

Edit your `config/auth.php` file, and under the appropriate `guard`, add a `load` setting:

    'guards' => [
        'web' => [
            'driver' => 'external',
            'provider' => 'users',
            'load' => [
                'permissions',
                'roles',
            ],
        ],
    ],

## Looking for REMOTE_USER in headers

If your app is running in a docker container, or some other situation where your app is separated from the authentication,
it may be desirable to pass the `REMOTE_USER` to the app by headers.

Edit your `config/auth.php` file, and under the appropriate `guard`, add a `header` setting:

    'guards' => [
        'web' => [
            'driver' => 'external',
            'provider' => 'users',
            'header' => 'X-forwarded-REMOTE_USER',
        ],
    ],

## Stripping a string from the user identifier

If your authentication field has a part you do not wish to use (e.g. user@domain.com), this can be stripped:

    'guards' => [
        'web' => [
            'driver' => 'external',
            'provider' => 'users',
            'strip' => '@domain.com',
        ],
    ],
