# Using the Twitter authentication source with SimpleSAMLphp

To get an API key and a secret, register the application at:

* <http://twitter.com/oauth_clients>

Now you have you configure the authsource in `authsources.php`.

```php
    // Twitter OAuth Authentication API.
    // Register your application to get an API key here:
    //  http://twitter.com/oauth_clients
    'twitter' => [
        'authtwitter:Twitter',
        'key' => 'key retrieved during registration of your app',
        'secret' => 'secret retrieved during registration of your app',

        // The oAuth scope to include in the request
        'scope' => 'read',

        // Forces the user to enter their credentials to ensure the correct
        // users account is authorized.
        // Details: https://dev.twitter.com/docs/api/1/get/oauth/authenticate
        'force_login' => false,
    ],
```

## Testing authentication

On the SimpleSAMLphp frontpage, go to the *Authentication* tab,
and use the link:

* *Test configured authentication sources*

Then choose the *twitter* authentication source.

Expected behaviour would then be that you are sent to twitter,
and asked to login. The first time a user uses your application to login,
he/she is asked for consent.
You will then be authenticated in SimpleSAMLphp and see an attribute set
with data delivered by Twitter.
