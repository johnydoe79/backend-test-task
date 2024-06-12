<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class PriceCalculateService
{
    public static function calculatePrice(float $rawPrice, float $taxRate, float $discount, string $discountType): float
    {
        $finalPrice = match ($discountType) {
            'fix' => ($rawPrice-$discount)*(1+$taxRate/100),
            'percent' => $rawPrice*(1-$discount/100)*(1+$taxRate/100),
            default => $rawPrice*(1+$taxRate/100)
        };
        return $finalPrice;
    }

}