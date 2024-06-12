<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class PriceCalculateService
{
    public static function calculatePrice(float $rawPrice, float $taxRate, float $discount): float
    {
        return $rawPrice*(1-$discount/100)*(1+$taxRate/100);
    }

}