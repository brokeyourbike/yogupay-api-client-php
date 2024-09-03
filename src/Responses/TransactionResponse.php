<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\YoguPay\Responses;

use BrokeYourBike\DataTransferObject\JsonResponse;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class TransactionResponse extends JsonResponse
{
    public ?string $status;
    public ?string $message;
    public ?TransactionItem $transaction;
}

class TransactionItem
{
    public int $id;
    public string $transaction_code;
    public string $transaction_type;
    public string $status;
}