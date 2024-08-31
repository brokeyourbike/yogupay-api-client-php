<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\YoguPay\Enums;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
enum ChannelEnum: string
{
    case MOBILE_MONEY = 'MOBILEMONEY';
    case BANK_TRANSFER = 'BANKTRANSFER';
}
