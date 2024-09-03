<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\YoguPay\Enums;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
enum ErrorCodeEnum: string
{
    case FAIL = '0';
    case SUCCESS = '1';
    case NOT_FOUND = '2';
    case EXCEPTION = '500';
    case UNAVAILABLE = '-3';
}
