<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;


class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_product', methods: 'GET')]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        // Получаем данные из сущности
        $productData = $em->getRepository(Product::class)->findAll();

        // Преобразуем данные в JSON и возвращаем ответ
        return $this->json($productData);
    }

    #[Route('/products', name: 'app_create_product', methods: 'POST')]
    public function create(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        // Получаем данные из POST запроса
        $data = json_decode($request->getContent(), true);

        // Валидация данных
        $constraints = new Assert\Collection([
            'name' => new Assert\NotBlank(['message' => 'The name of product must not be blank.']),
            'price' => [
                new Assert\NotBlank(['message' => 'The price must not be blank.']),
                new Assert\Type(['type' => 'numeric', 'message' => 'The price must be a numeric value.']),
            ],
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
        $entity = new Product();
        $entity->setName($data['name']);
        $entity->setPriceInEuro($data['price']);

        // Сохраняем сущность в базу данных
        $entityManager->persist($entity);
        $entityManager->flush();

        // Возвращаем ответ с кодом 201 (Created)
        return new Response('Product created', Response::HTTP_CREATED);
    }
}
