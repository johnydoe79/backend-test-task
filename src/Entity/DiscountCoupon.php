<?php

namespace App\Entity;

use App\Repository\DiscountCouponRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscountCouponRepository::class)]
class DiscountCoupon
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private ?string $couponCode = null;

    #[ORM\Column]
    private ?float $discount = null;

    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }

    public function setCouponCode(string $couponCode): static
    {
        $this->couponCode = $couponCode;

        return $this;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): static
    {
        $this->discount = $discount;

        return $this;
    }
}
