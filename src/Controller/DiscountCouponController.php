<?php

namespace App\Controller;

use App\Entity\DiscountCoupon;
use App\Validator\EntityExists;
use App\Validator\Percent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;


class DiscountCouponController extends AbstractController
{
    #[Route('/discounts', name: 'app_discount_coupon', methods: 'GET')]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        // Получаем данные из сущности
        $couponData = $em->getRepository(DiscountCoupon::class)->findAll();

        // Преобразуем данные в JSON и возвращаем ответ
        return $this->json($couponData);
    }

    #[Route('/discounts', name: 'app_create_coupon', methods: 'POST')]
    public function create(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        // Получаем данные из POST запроса
        $data = json_decode($request->getContent(), true);

        // Проверка значения couponType
        $couponType = $data['couponType'] ?? null;

        // Собираем наборы валидаций с учетом значения couponType
        $discountConstraints = [
            new Assert\NotBlank(['message' => 'The value of discount must not be blank.']),
            new Assert\Type(['type' => 'numeric', 'message' => 'The discount must be a numeric value.']),
        ];

        if ($couponType === 'percent') {
            $discountConstraints[] = new Percent();
        }

        // Валидация данных
        $constraints = new Assert\Collection([
            'couponCode' => new Assert\NotBlank(['message' => 'The code of coupon must not be blank.']),
            'discount' => $discountConstraints,
            'couponType' => new EntityExists(['entityClass' => 'App\Entity\CouponType']),
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errorMessages = [];
            foreach ($violations as $violation) {
                $errorMessages[] = $violation->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Создаем новую сущность
        $entity = new DiscountCoupon();
        $entity->setCouponCode($data['couponCode']);
        $entity->setDiscount($data['discount']);

        // Сохраняем сущность в базу данных
        $entityManager->persist($entity);
        $entityManager->flush();

        // Возвращаем ответ с кодом 201 (Created)
        return new Response('Coupon created', Response::HTTP_CREATED);
    }
}
