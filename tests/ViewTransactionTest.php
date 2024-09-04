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
                "id": 1234,
                "transaction_code": "ABCD1234",
                "channel": "API",
                "transaction_type": "MOBILE MONEY",
                "transaction_source_currency_code": "KES",
                "transaction_destination_currency_code": "KES",
                "source_amount": 10,
                "destination_amount": 10,
                "buy_rate": 0,
                "sell_rate": 0,
                "margin": 0,
                "source_currency_to_usd": 0,
                "destination_currency_to_usd": 0,
                "baseline_forex_rate": 1,
                "markup_forex_rate": 0,
                "effective_forex_rate": null,
                "collection_gateway_fee": 0,
                "payment_gateway_fee": 0,
                "yogupay_fee": 0,
                "transaction_charge": 0,
                "total_amount_charged": 10,
                "revenue_margin": null,
                "beneficiary_id": null,
                "recipient_msisdn": 2540000000000,
                "recipient_bank_account": "1234567890",
                "recipient_bank_code": "111",
                "account_type": null,
                "document_type": null,
                "document_id": null,
                "bank_name": null,
                "payment_method": null,
                "branch_code": null,
                "sender_id": 2,
                "recipient_email": "john@doe.com",
                "recipient_name": "john doe",
                "recipient_country_code": null,
                "source_of_funds": null,
                "sender_narrative": "",
                "sender_country_code": "KE",
                "send_via": "MPESADIRECT",
                "third_party_ref_id": null,
                "third_party_response": null,
                "third_party_payload": null,
                "customer_cash_in_id": null,
                "payment_reference": "reference1234",
                "created_at": "2024-09-04 01:15:03",
                "updated_at": "2024-09-04 01:15:03",
                "prembly_status": 0,
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