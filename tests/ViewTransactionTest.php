<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\YoguPay\Tests;

use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use BrokeYourBike\YoguPay\Responses\TransactionResponse;
use BrokeYourBike\YoguPay\Interfaces\ConfigInterface;
use BrokeYourBike\YoguPay\Enums\TransactionStatusEnum;
use BrokeYourBike\YoguPay\Enums\ErrorCodeEnum;
use BrokeYourBike\YoguPay\Client;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class ViewTransactionTest extends TestCase
{
    /** @test */
    public function it_can_prepare_request(): void
    {
        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')->willReturn('{
            "status": 0,
            "transaction": {
                "id": 2427,
                "transaction_code": "ABCD1234",
                "transaction_type": "MOBILE MONEY",
                "transaction_destination_currency_code": "KES",
                "destination_amount": 10,
                "transaction_charge": 0,
                "total_amount_charged": 10,
                "recipient_msisdn": 123456789,
                "recipient_bank_account": "123456789",
                "recipient_bank_code": "123",
                "recipient_email": "john@doe.com",
                "recipient_name": "John Doe",
                "created_at": "0001-01-01 18:51:50",
                "status": 2
            }
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

        $requestResult = $api->viewTransaction('ABCD1234');
        $this->assertInstanceOf(TransactionResponse::class, $requestResult);
        $this->assertNotNull($requestResult->transaction);
        $this->assertEquals('ABCD1234', $requestResult->transaction->transaction_code);
        $this->assertEquals(TransactionStatusEnum::SUCCESS->value, $requestResult->transaction->status);
    }

    /** @test */
    public function it_can_handle_failed_response(): void
    {
        $mockedConfig = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $mockedConfig->method('getUrl')->willReturn('https://api.example/');

        $mockedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $mockedResponse->method('getStatusCode')->willReturn(200);
        $mockedResponse->method('getBody')->willReturn('{
            "status": 2,
            "message": "Transaction not found"
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

        $requestResult = $api->viewTransaction('code');
        $this->assertInstanceOf(TransactionResponse::class, $requestResult);
        $this->assertEquals(ErrorCodeEnum::NOT_FOUND->value, $requestResult->status);
        $this->assertEquals('Transaction not found', $requestResult->message);
    }
}