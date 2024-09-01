<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\YoguPay\Enums;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
enum CollectionNetworkEnum: string
{
    case MPESA_DIRECT = 'MPESADIRECT';
    case BANK_TRANSFER = 'BANKTRANSFER';
    case TRANSFER_NG = 'TRANSFER_NG';
}
