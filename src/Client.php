<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\YoguPay;

use Psr\SimpleCache\CacheInterface;
use GuzzleHttp\ClientInterface;
use BrokeYourBike\YoguPay\Responses\TransferResponse;
use BrokeYourBike\YoguPay\Responses\TokenResponse;
use BrokeYourBike\YoguPay\Interfaces\TransactionInterface;
use BrokeYourBike\YoguPay\Interfaces\ConfigInterface;
use BrokeYourBike\YoguPay\Enums\SourceOfFundsEnum;
use BrokeYourBike\YoguPay\Enums\SenderTypeEnum;
use BrokeYourBike\YoguPay\Enums\RemitancePurposeEnum;
use BrokeYourBike\ResolveUri\ResolveUriTrait;
use BrokeYourBike\HttpEnums\HttpMethodEnum;
use BrokeYourBike\HttpClient\HttpClientTrait;
use BrokeYourBike\HttpClient\HttpClientInterface;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
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

        $url = rtrim($this->config->getUrl(), '/') . '/auth/login';

        $response = $this->httpClient->request(HttpMethodEnum::POST->value, $url, $options);
        return new TokenResponse($response);
    }
}
