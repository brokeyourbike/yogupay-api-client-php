<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\YoguPay\Tests;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use BrokeYourBike\YoguPay\Responses\PayoutResponse;
use BrokeYourBike\YoguPay\Interfaces\TransactionInterface;
use BrokeYourBike\YoguPay\Interfaces\ConfigInterface;
use BrokeYourBike\YoguPay\Enums\ErrorCodeEnum;
use BrokeYourBike\YoguPay\Enums\ChannelEnum;
use BrokeYourBike\YoguPay\Client;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class PayoutTest extends TestCase
{
    /** @test */
    public function it_can_prepare_request(): void
    {
        $transaction = $this->getMockBuilder(TransactionInterface::class)->getMock();
        $transaction->method('getChannel')->willReturn(ChannelEnum::BANK_TRANSFER);

        /** @var TransactionInterface $transaction */
        $this->assertInstanceOf(TransactionInterface::class, $transaction);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "status": 1,
                "trx_code": "CODE123",
                "message": "Success You have successfully initiated a transfer."
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

        $requestResult = $api->payout($transaction);
        $this->assertInstanceOf(PayoutResponse::class, $requestResult);
        $this->assertEquals(ErrorCodeEnum::SUCCESS->value, $requestResult->status);
        $this->assertEquals('Success You have successfully initiated a transfer.', $requestResult->message);
    }

    /** @test */
    public function it_can_handle_failed_response(): void
    {
        $transaction = $this->getMockBuilder(TransactionInterface::class)->getMock();
        $transaction->method('getChannel')->willReturn(ChannelEnum::BANK_TRANSFER);

        /** @var TransactionInterface $transaction */
        $this->assertInstanceOf(TransactionInterface::class, $transaction);

        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')
            ->willReturn('{
                "status": 0,
                "message": "Duplicate Transaction"
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

        $requestResult = $api->payout($transaction);
        $this->assertInstanceOf(PayoutResponse::class, $requestResult);
        $this->assertEquals(ErrorCodeEnum::FAIL->value, $requestResult->status);
        $this->assertEquals('Duplicate Transaction', $requestResult->message);
    }
}