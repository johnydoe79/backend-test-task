<?php

namespace App\Controller;

use App\Entity\DiscountCoupon;
use App\Entity\Product;
use App\Entity\Tax;
use App\Service\PriceCalculateService;
use App\Validator\EntityExists;
use App\Validator\PaymentProcessor;
use App\Validator\TaxNumber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;


class PurchaseController extends AbstractController
{
    #[Route('/calculate-price', name: 'app_calculate_price', methods: 'POST')]
    public function calculatePrice(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        // Получаем данные из POST запроса
        $data = json_decode($request->getContent(), true);

        // Валидация данных
        $constraints = new Assert\Collection([
            'product' => new EntityExists(['entityClass' => 'App\Entity\Product']),
            'taxNumber' => [
                new Assert\NotBlank(['message' => 'The tax number must not be blank.']),
                new TaxNumber()
            ],
            'couponCode' => new Assert\Optional([
                new EntityExists(['entityClass' => 'App\Entity\DiscountCoupon']),
            ])
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errorMessages = [];
            foreach ($violations as $violation) {
                $errorMessages[] = $violation->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        //можно эти действия перенести в сервис, а из контроллера передавать в него идентификаторы
        $countryCode = substr($data['taxNumber'], 0, 2);
        $rawPrice = $entityManager->getRepository(Product::class)->find($data['product'])->getPriceInEuro();
        if (isset($data['couponCode'])) {
            $discount = $entityManager->getRepository(DiscountCoupon::class)->find($data['couponCode'])->getDiscount();
            $discountType = $entityManager->getRepository(DiscountCoupon::class)->find($data['couponCode'])->getCoupounType()->getCode();
        } else {
            $discount = 0;
            $discountType = '';
        }
        $taxRate = $entityManager->getRepository(Tax::class)->findOneBy(['countryCode' => $countryCode])->getTaxRate();

        //Рассчитываем скидку
        $finalPrice = PriceCalculateService::calculatePrice($rawPrice, $taxRate, $discount, $discountType);

        // Возвращаем ответ с кодом 201 (Created)
        return new Response('Final price is ' . $finalPrice, Response::HTTP_OK);
    }

    #[Route('/purchase', name: 'app_purchase', methods: 'POST')]
    public function purchase(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        // Получаем данные из POST запроса
        $data = json_decode($request->getContent(), true);

        // Валидация данных
        $constraints = new Assert\Collection([
            'product' => new EntityExists(['entityClass' => 'App\Entity\Product']),
            'taxNumber' => [
                new Assert\NotBlank(['message' => 'The tax number must not be blank.']),
                new TaxNumber()
            ],
            'couponCode' => new Assert\Optional([
                new EntityExists(['entityClass' => 'App\Entity\DiscountCoupon']),
            ]),
            'paymentProcessor' => new PaymentProcessor(),
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errorMessages = [];
            foreach ($violations as $violation) {
                $errorMessages[] = $violation->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        //можно эти действия перенести в сервис, а из контроллера передавать в него идентификаторы
        $countryCode = substr($data['taxNumber'], 0, 2);
        $rawPrice = $entityManager->getRepository(Product::class)->find($data['product'])->getPriceInEuro();
        if (isset($data['couponCode'])) {
            $discount = $entityManager->getRepository(DiscountCoupon::class)->find($data['couponCode'])->getDiscount();
            $discountType = $entityManager->getRepository(DiscountCoupon::class)->find($data['couponCode'])->getCoupounType()->getCode();
        } else {
            $discount = 0;
            $discountType = '';
        }
        $taxRate = $entityManager->getRepository(Tax::class)->findOneBy(['countryCode' => $countryCode])->getTaxRate();

        //Рассчитываем скидку
        $finalPrice = PriceCalculateService::calculatePrice($rawPrice, $taxRate, $discount, $discountType);

        try {
            $result = match ($data['paymentProcessor']) {
                'paypal' => (new PaypalPaymentProcessor())->pay(round($finalPrice)),
                'stripe' => (new StripePaymentProcessor())->processPayment($finalPrice),
                default => throw new \InvalidArgumentException('Unknown payment processor')
            };
        } catch (\Exception $e) {
            // Возвращаем ошибку с кодом 400 и описанием ошибки
            return new JsonResponse(['error' => 'Payment processing error: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        // Возвращаем ошибку с кодом 400 и неизвестной ошибкой
        if (!is_null($result) && !$result) {
            return new JsonResponse(['error' => 'Unknown Payment processing error: '], Response::HTTP_BAD_REQUEST);
        }

        // Возвращаем успешный ответ с кодом 200
        return new JsonResponse(['message' => 'Payment processed successfully'], Response::HTTP_OK);
    }
}
