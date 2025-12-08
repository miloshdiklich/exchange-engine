<?php

namespace App\Domain\Trading\Enums;

enum OrderSide: string
{
  case BUY = 'buy';
  case SELL = 'sell';
}