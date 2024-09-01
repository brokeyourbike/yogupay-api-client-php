<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\YoguPay\Tests;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use BrokeYourBike\YoguPay\Responses\PayoutResponse;
use BrokeYourBike\YoguPay\Responses\EstimateResponse;
use BrokeYourBike\YoguPay\Interfaces\TransactionInterface;
use BrokeYourBike\YoguPay\Interfaces\ConfigInterface;
use BrokeYourBike\YoguPay\Enums\ErrorCodeEnum;
use BrokeYourBike\YoguPay\Enums\ChannelEnum;
use BrokeYourBike\YoguPay\Client;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class EstimateTest extends TestCase
{
    /** @test */
    public function it_can_handle_failed_response(): void
    {
        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "status": "-3",
                "message": "Unavailable",
                "error": "You have exceeded your daily transaction limit."
            }');

        /** @var \Mockery\MockInterface $mockedClient */
        $mockedClient = \Mockery::mock(\GuzzleHttp\Client::class);
        $mockedClient->shouldReceive('request')->once()->andReturn($mockedResponse);

        $mockedCache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $mockedCache->method('has')->willReturn(true);
        $mockedCache->method('get')->willReturn('secure-token');

        /**
         * @var ConfigInterface $mockedConfig
         * @var \GuzzleHttp\Client $mockedClient
         * @var CacheInterface $mockedCache
         * */
        $api = new Client($mockedConfig, $mockedClient, $mockedCache);

        $requestResult = $api->estimate('GBP', 'KES', 1000);
        $this->assertInstanceOf(EstimateResponse::class, $requestResult);
        $this->assertEquals('Unavailable', $requestResult->message);
    }
}