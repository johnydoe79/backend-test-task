<?php

namespace App\Controller;

use App\Entity\DiscountCoupon;
use App\Entity\Product;
use App\Entity\Tax;
use App\Service\PriceCalculateService;
use App\Validator\EntityExists;
use App\Validator\TaxNumber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;


class CalculationController extends AbstractController
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
        } else {
            $discount = 0;
        }
        $taxRate = $entityManager->getRepository(Tax::class)->findOneBy(['countryCode' => $countryCode])->getTaxRate();

        //Рассчитываем скидку
        $finalPrice = PriceCalculateService::calculatePrice($rawPrice, $taxRate, $discount);

        // Возвращаем ответ с кодом 201 (Created)
        return new Response('Final price is ' . $finalPrice, Response::HTTP_OK);
    }
}
