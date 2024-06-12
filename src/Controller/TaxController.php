<?php

namespace App\Controller;

use App\Entity\Tax;
use App\Validator\Percent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;


class TaxController extends AbstractController
{
    #[Route('/taxes', name: 'app_tax', methods: 'GET')]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        // Получаем данные из сущности
        $taxData = $em->getRepository(Tax::class)->findAll();

        // Преобразуем данные в JSON и возвращаем ответ
        return $this->json($taxData);
    }

    #[Route('/taxes', name: 'app_create_tax', methods: 'POST')]
    public function create(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        // Получаем данные из POST запроса
        $data = json_decode($request->getContent(), true);

        // Валидация данных
        $constraints = new Assert\Collection([
            'countryName' => new Assert\NotBlank(['message' => 'The name of country must not be blank.']),
            'countryCode' => [
                new Assert\NotBlank(['message' => 'The code of country must not be blank.']),
                new Assert\Length(max: 2, maxMessage: 'Tax number cannot be longer than {{ limit }} characters')
            ],
            'taxRate' => [
                new Assert\NotBlank(['message' => 'The price must not be blank.']),
                new Assert\Type(['type' => 'numeric', 'message' => 'The tax rate must be a numeric value.']),
                new Percent(),
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
        $entity = new Tax();
        $entity->setCountryName($data['countryName']);
        $entity->setCountryCode($data['countryCode']);
        $entity->setTaxRate($data['taxRate']);

        // Сохраняем сущность в базу данных
        $entityManager->persist($entity);
        $entityManager->flush();

        // Возвращаем ответ с кодом 201 (Created)
        return new Response('Tax created', Response::HTTP_CREATED);
    }
}
