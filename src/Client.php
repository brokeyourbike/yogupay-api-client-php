<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\YoguPay;

use Psr\SimpleCache\CacheInterface;
use GuzzleHttp\ClientInterface;
use BrokeYourBike\YoguPay\Responses\TokenResponse;
use BrokeYourBike\YoguPay\Responses\PayoutResponse;
use BrokeYourBike\YoguPay\Responses\EstimateResponse;
use BrokeYourBike\YoguPay\Interfaces\TransactionInterface;
use BrokeYourBike\YoguPay\Interfaces\ConfigInterface;
use BrokeYourBike\YoguPay\Enums\CollectionNetworkEnum;
use BrokeYourBike\YoguPay\Enums\ChannelEnum;
use BrokeYourBike\ResolveUri\ResolveUriTrait;
use BrokeYourBike\HttpEnums\HttpMethodEnum;
use BrokeYourBike\HttpClient\HttpClientTrait;
use BrokeYourBike\HttpClient\HttpClientInterface;
use BrokeYourBike\HasSourceModel\HasSourceModelTrait;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class Client implements HttpClientInterface
{
    use HttpClientTrait;
    use ResolveUriTrait;
    use HasSourceModelTrait;

    private ConfigInterface $config;
    private CacheInterface $cache;

    public function __construct(ConfigInterface $config, ClientInterface $httpClient, CacheInterface $cache)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    public function authTokenCacheKey(): string
    {
        return get_class($this) . ':authToken:';
    }

    public function getAuthToken(): string
    {
        if ($this->cache->has($this->authTokenCacheKey())) {
            $cachedToken = $this->cache->get($this->authTokenCacheKey());
            if (is_string($cachedToken)) {
                return $cachedToken;
            }
        }

        $response = $this->fetchAuthTokenRaw();
        $this->cache->set($this->authTokenCacheKey(), $response->token, 300);
        return (string) $response->token;
    }

    public function fetchAuthTokenRaw(): TokenResponse
    {
        $options = [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
            ],
            \GuzzleHttp\RequestOptions::JSON => [
                'username' => $this->config->getUsername(),
                'password' => $this->config->getPassword(),
            ],
        ];

        $response = $this->httpClient->request(
            HttpMethodEnum::POST->value,
            (string) $this->resolveUriFor(rtrim($this->config->getUrl(), '/'), '/auth/login'),
            $options
        );

        return new TokenResponse($response);
    }

    public function estimate(string $from, string $to, float $amount): EstimateResponse
    {
        $options = [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'token' => $this->getAuthToken(),
            ],
            \GuzzleHttp\RequestOptions::JSON => [
                'currency_pair' => "{$from}_{$to}",
                'amount' => $amount,
            ],
        ];

        $response = $this->httpClient->request(
            HttpMethodEnum::POST->value,
            (string) $this->resolveUriFor(rtrim($this->config->getUrl(), '/'), '/api/get-estimate'),
            $options
        );

        return new EstimateResponse($response);
    }

    public function payout(TransactionInterface $transaction): PayoutResponse
    {
        $network = match ($transaction->getChannel()) {
            ChannelEnum::MOBILE_MONEY => CollectionNetworkEnum::MPESA_DIRECT,
            ChannelEnum::BANK_TRANSFER => match ($transaction->getCurrency()) {
                'NGN' => CollectionNetworkEnum::TRANSFER_NG,
                default => CollectionNetworkEnum::BANK_TRANSFER,
            },
        };

        $options = [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'token' => $this->getAuthToken(),
            ],
            \GuzzleHttp\RequestOptions::JSON => [
                'channel' => $transaction->getChannel()->value,
                'collection_network' => $network->value,
                'source_currency' => $transaction->getCurrency(),
                'source_amount' => $transaction->getAmount(),
                'country_code' => $transaction->getRecipientCountry(),
                'recipient_name' => $transaction->getRecipientName(),
                'bank_code' => $transaction->getRecipientBankCode(),
                'bank_account_number' => $transaction->getRecipientAccountNumber(),
                'msisdn' => $transaction->getRecipientPhone(),
                'recipient_email' => $transaction->getRecipientEmail(),
                'purpose_of_funds' => $transaction->getPurpose(),
                'payment_reference' => $transaction->getReference(),
            ],
        ];

        $response = $this->httpClient->request(
            HttpMethodEnum::POST->value,
            (string) $this->resolveUriFor(rtrim($this->config->getUrl(), '/'), '/auth/payout'),
            $options
        );

        return new PayoutResponse($response);
    }
}
