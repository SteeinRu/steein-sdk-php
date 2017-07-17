# Steein SDK for PHP

## Installation

The preferred method is via composer. Follow the installation instructions if you do not already have composer installed.
Once composer is installed, execute the following command in your project root to install this library:

```
composer require steein/steein-sdk
```
Finally, be sure to include the autoloader:

```php
require_once '/path/to/your-project/vendor/autoload.php';
```


## Developer Documentation

 * [Steein SDK Documentation](https://www.steein.ru/developers/docs/php/getting_started)
 * [Developer Documentation](https://www.steein.ru/developers/docs/overview)

## Usage

Simple GET example of a user's account.

```php
$steein = new Steein([
    'client_id'             =>  '{id}',
    'client_secret'         => '{secret_key}',
    'default_api_version'   =>  'v2.0'
]);
$steein->setDefaultAccessToken('{access_token}');

$get = $steein->get('/users/show'); //$steein->get('/users/show',['id' => 1]);
$user = $get->getUserModel(); //$get->getDecodedBody()

echo 'ID: '. $user->getId(); //or $user->all();
```

Complete documentation, installation instructions, and examples are available [here](https://www.steein.ru/developers/docs/php/getting_started).

### License

Please see the [license file](https://github.com/SteeinRu/steein-sdk-php/blob/master/LICENSE) for more information.
