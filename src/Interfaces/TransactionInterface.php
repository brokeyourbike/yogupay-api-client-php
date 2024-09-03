<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\YoguPay\Interfaces;

use DateTime;
use BrokeYourBike\YoguPay\Enums\SegmentEnum;
use BrokeYourBike\YoguPay\Enums\DestinationEnum;
use BrokeYourBike\YoguPay\Enums\ChannelEnum;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
interface TransactionInterface
{
    public function getReference(): string;
    public function getCurrency(): string;
    public function getAmount(): float;
    public function getChannel(): ChannelEnum;
    public function getPurpose(): ?string;

    public function getRecipientCountry(): string;
    public function getRecipientName(): string;
    public function getRecipientBankCode(): ?string;
    public function getRecipientAccountNumber(): ?string;
    public function getRecipientPhone(): ?string;
    public function getRecipientEmail(): ?string;
}
