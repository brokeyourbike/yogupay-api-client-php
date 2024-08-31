<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\YoguPay\Tests;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use BrokeYourBike\YoguPay\Responses\TokenResponse;
use BrokeYourBike\YoguPay\Interfaces\ConfigInterface;
use BrokeYourBike\YoguPay\Client;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class FetchAuthTokenRawTest extends TestCase
{
    /** @test */
    public function it_can_prepare_request(): void
    {
        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://example.com/');
        $mockedConfig->method('getUsername')->willReturn('john');
        $mockedConfig->method('getPassword')->willReturn('p@ssword');

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "status": "1",
                "message": "Login successful",
                "image": "https://example.com/tp/images/avatar.png",
                "token": "**",
                "username": "*",
                "is_verified": 1,
                "password_status": 0,
                "country": "GB",
                "country_code": "GB",
                "phone_number": "111111",
                "full_name": "*",
                "number_of_transactions": 0
            }');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->once()->andReturn($mockedResponse);

        $mockedCache = $this->getMockBuilder(CacheInterface::class)->getMock();

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);
        $requestResult = $api->fetchAuthTokenRaw();

        $this->assertInstanceOf(TokenResponse::class, $requestResult);
        $this->assertEquals('**', $requestResult->token);
    }
}