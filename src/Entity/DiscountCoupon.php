<?php

namespace App\Entity;

use App\Repository\DiscountCouponRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DiscountCouponRepository::class)]
class DiscountCoupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $couponCode = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'numeric', message: 'The price must be a numeric value.')]
    private ?float $discount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

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
