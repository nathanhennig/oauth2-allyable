# Allyable Provider for OAuth 2.0 Client

This package provides Allyable OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require nathanhennig/oauth2-allyable
```

## Usage

Usage is the same as The League's OAuth client, using `\Nathanhennig\OAuth2\Client\Provider\Allyable` as the provider.

### Authorization Code Flow

```php
$provider = new Nathanhennig\OAuth2\Client\Provider\Allyable([
    'clientId'          => '{allyable-client-id}',
    'clientSecret'      => '{allyable-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url'
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getId());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

## Authorization flow example - Temporary Section
1. Put the url https://qa.allyable.com/connect/authorize?client_id=allyacademy&client_secret=a12592612109197ae8825948ca998b2b88f0b37bd222b9b6fd971982361cd2dd%3D&redirect_uri=https%3A%2F%2Flocalhost%3A8000%2Fhome&scope=openid%20profile%20email%20phone%20offline_access&response_type=code&state=651e0e09e5c8487dbd519be4b3897e20  to the browser address string.


https://qa.allyable.com/connect/authorize?client_id=<client_id>&client_secret=<client_secret>&redirect_uri=<redirect_url>&scope=openid%20profile%20email%20phone%20offline_access&response_type=code&state=<random_string>


2. Do the sign in and after that page should be redirected to the redirect url specified in configuration.
image.png
Here the code and state parameter are interesting for us.
Code is the authorization code, it is used for getting refresh token.
State is some random string which was provided on previous step and client can use it to validate that redirect was correct - state on first step and second step should be equal.
State is optional parameter.

3. Get the refresh token by authorization code
```
curl --location --request POST 'https://qa.allyable.com/connect/token' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'client_id=allyacademy' \
--data-urlencode 'client_secret=3d9e0eece8e368b7103fde7ad5cbff58' \
--data-urlencode 'code=<your_code>' \
--data-urlencode 'grant_type=authorization_code' \
--data-urlencode 'redirect_uri=https://localhost:8000/home'
```
Response contains:
* "id_token" - can be used for logout
* "refresh_token" - is needed to get new access_token after it is expired.
* "access_token" - token to authorize in our system, in case with sso - to get user information


4. Get the user information
```
curl --location --request GET 'https://qa.allyable.com/connect/userinfo' \
--header 'Authorization: Bearer 84A936F1116A4BB504D25C4F0256772294EAA5EA9595B353DE8F796AD5A8A08B'
```


5. Renew the access token using refresh token
```
curl --location --request POST 'https://qa.allyable.com/connect/token' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'refresh_token=4BA310123A6B554E239E49F493070F546D087520AFFAFEAC80D7DF3C2C30524F' \
--data-urlencode 'grant_type=refresh_token' \
--data-urlencode 'client_id=allyacademy' \
--data-urlencode 'client_secret=3d9e0eece8e368b7103fde7ad5cbff58'
```

6. Logout
Put https://qa.allyable.com/connect/endsession?id_token_hint=eyJhbGciOiJSUzI1NiIsImtpZCI6IjNBREJEMjZFRDE5MjVBRkY1RDk3NTVEM0I5ODlDRjlBNUMxRDM3MzZSUzI1NiIsInR5cCI6IkpXVCIsIng1dCI6Ik90dlNidEdTV3Y5ZGwxWFR1WW5QbWx3ZE56WSJ9.eyJuYmYiOjE2NTM1ODY1MDksImV4cCI6MTY1MzU4NjgwOSwiaXNzIjoiaHR0cHM6Ly9sb2NhbGhvc3Q6NDQzNTAiLCJhdWQiOiJ0ZXN0IiwiaWF0IjoxNjUzNTg2NTA5LCJhdF9oYXNoIjoicnB1Ny1jZldnQ041WmVPMl9GWkdmUSIsInN1YiI6IjZiYzhjZWUwLWEwM2UtNDMwYi05NzExLTQyMGFiMGQ2YTU5NiIsImF1dGhfdGltZSI6MTY1MzU4NjEwMywiaWRwIjoibG9jYWwiLCJhbXIiOlsicHdkIl19.X2IWyePD0T0bzrU_e-MvfO-PJqGBfWV_oD5LDU46F7vawooqkt8Q435DXYgUTVdKkb7Zm-aU1wX3FXgfU18NeTvrHqUb3kdzryMGWEuapB9xrILv4c1-WGbTsIbiHPeMNQAf2bJ2sp0jFCIft3n4kj3e9ZwXap_t1IvkzRlcq0CUDXwJYsaq9OVqlIQYM3l1isXxDv9lpveYsAYMYt_NNMUbYx1ko4KzBeBUnk3qE5LTOBn24DJexC5z7sXvUAtsnoBh-zj61kUfl0x27ulDom4Wg4RlpLMf4SZVUtFpt5aXu3h3f9cc1biMYiLDG59HgsHCPIY9sKXTzGTODG_5bQ&post_logout_redirect_uri=https%3A%2F%2Flocalhost%3A8000%2Flogin&state=651e0e09e5c8487dbd519be4b3897e20  to the browser address string.


## Endpoints - Temporary Section
1. Authorize
```
curl --location --request GET 'https:/qa.allyable.com/connect/authorize
?client_id=<client_id>
&client_secret=<client_secret>
&redirect_uri=<redirect_url>
&scope=openid%20profile%20email%20phone%20offline_access
&response_type=code
&state=<some_random_string (optional)>'
```


2. Refresh Token
```
curl --location --request POST 'https://qa.allyable.com/connect/token' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'client_id=<client_id>' \
--data-urlencode 'client_secret=<client_secret>' \
--data-urlencode 'code=<authorization_code>' \
--data-urlencode 'grant_type=authorization_code' \
--data-urlencode 'redirect_uri=<redirect_url>'
```


3. Access token
```
curl --location --request POST 'https://qa.allyable.com/connect/token' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'client_id=<client_id>' \
--data-urlencode 'client_secret=<client_secret>' \
--data-urlencode 'refresh_token=<refresh_token>' \
--data-urlencode 'grant_type=refresh_token'
```


4. User information
```
curl --location --request GET 'https://qa.allyable.com/connect/userinfo' \
--header 'Authorization: Bearer <access_token>'
```


5. Logout
```
curl --location --request GET 'https://qa.allyable.com/connect/endsession
?id_token_hint=<id_token>
&post_logout_redirect_uri=<post_logout_redirect_url>
&state=<some_random_string (optional)>'
```

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/nathanhennig/oauth2-allyable/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Nathan Hennig](https://github.com/nathanhennig)
- [All Contributors](https://github.com/nathanhennig/oauth2-allyable/contributors)


## License

None
