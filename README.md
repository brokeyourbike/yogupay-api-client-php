# yogupay-api-client

[![Latest Stable Version](https://img.shields.io/github/v/release/brokeyourbike/yogupay-api-client-php)](https://github.com/brokeyourbike/yogupay-api-client-php/releases)
[![Total Downloads](https://poser.pugx.org/brokeyourbike/yogupay-api-client/downloads)](https://packagist.org/packages/brokeyourbike/yogupay-api-client)
[![Maintainability](https://api.codeclimate.com/v1/badges/b2d8d578ca0af0118391/maintainability)](https://codeclimate.com/github/brokeyourbike/yogupay-api-client-php/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/b2d8d578ca0af0118391/test_coverage)](https://codeclimate.com/github/brokeyourbike/yogupay-api-client-php/test_coverage)

YoguPay API Client for PHP

## Installation

```bash
composer require brokeyourbike/yogupay-api-client
```

## Usage

```php
use BrokeYourBike\YoguPay\Client;
use BrokeYourBike\YoguPay\Interfaces\ConfigInterface;

assert($config instanceof ConfigInterface);
assert($httpClient instanceof \GuzzleHttp\ClientInterface);
assert($psrCache instanceof \Psr\SimpleCache\CacheInterface);

$apiClient = new Client($config, $httpClient, $psrCache);
$apiClient->getAuthToken();
```

## Authors
- [Ivan Stasiuk](https://github.com/brokeyourbike) | [Twitter](https://twitter.com/brokeyourbike) | [LinkedIn](https://www.linkedin.com/in/brokeyourbike) | [stasi.uk](https://stasi.uk)

## License
[BSD-3-Clause License](https://github.com/brokeyourbike/yogupay-api-client-php/blob/main/LICENSE)
