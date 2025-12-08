<?php

namespace App\Domain\Trading\Enums;

enum OrderStatus: string
{
  case OPEN = 1;
  case FILLED = 2;
  case CANCELLED = 3;
}