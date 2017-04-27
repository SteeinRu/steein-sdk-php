# Steein SDK for PHP

## Installation

The Steein SDK can be installed with Composer. Run this command:

```
composer require steein/steein-sdk
```

## Usage

Simple GET example of a user's account.

```php

$sdk = new Steein([
    'client_id' =>  '{client_id}',
    'client_secret' => '{client_secret}',
    'default_api_version' => 'v2.0',
]);
$sdk->setDefaultAccessToken($accessToken);

$account = $sdk->get('/account/show');
$response = $account->getUserModel()->toArray();
```

Complete documentation, installation instructions, and examples are available [here](https://www.steein.ru/developers/docs/overview).

