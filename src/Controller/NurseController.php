<?php

namespace App\Controller;

use App\Entity\Nurse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

use App\Repository\NurseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/nurse')]
final class NurseController extends AbstractController
{

#[Route('/new', name: 'app_nurse_new', methods: ['POST'])]
public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    ValidatorInterface $validator
): JsonResponse {
    try {
        $data = $request->toArray();
    } catch (\Throwable) {
        return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
    }

    // Los setters exigen string: comprueba los tipos antes de usarlos
    if (isset($data['license_number']) && is_int($data['license_number'])) {
        $data['license_number'] = (string) $data['license_number'];
    }
    $errors = [];
    foreach (['internal_id', 'first_name', 'last_name', 'license_number', 'username', 'email', 'shift', 'password'] as $field) {
        if (isset($data[$field]) && !is_string($data[$field])) {
            $errors[$field][] = 'Must be a string';
        }
    }
    if ($errors) {
        return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $nurse = new Nurse();
    $nurse->setInternalId($data['internal_id'] ?? '');
    $nurse->setFirstName($data['first_name'] ?? '');
    $nurse->setLastName($data['last_name'] ?? null);
    $nurse->setLicenseNumber($data['license_number'] ?? '');
    $nurse->setUsername($data['username'] ?? '');
    $nurse->setEmail($data['email'] ?? null);
    $nurse->setShift($data['shift'] ?? null);

    // Reglas de la entidad (NotBlank, Length, Regex, Email, Choice, UniqueEntity)
    foreach ($validator->validate($nurse) as $violation) {
        $errors[$violation->getPropertyPath()][] = $violation->getMessage();
    }

    // La contraseña se valida en texto plano, antes de hashearla
    if (isset($data['password'])) {
        $passwordViolations = $validator->validate($data['password'], [
            new Assert\Length(min: 8, max: 72),
        ]);
        foreach ($passwordViolations as $violation) {
            $errors['password'][] = $violation->getMessage();
        }
    }

    if ($errors) {
        return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    if (isset($data['password'])) {
        $nurse->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
    }

    try {
        $entityManager->persist($nurse);
        $entityManager->flush();
    } catch (UniqueConstraintViolationException) {
        // Por si dos peticiones simultáneas pasan la validación a la vez
        return $this->json(['error' => 'Nurse already exists'], Response::HTTP_CONFLICT);
    }

    return $this->json([
        'id' => $nurse->getId(),
        'internal_id' => $nurse->getInternalId(),
        'first_name' => $nurse->getFirstName(),
        'last_name' => $nurse->getLastName(),
        'license_number' => $nurse->getLicenseNumber(),
        'username' => $nurse->getUsername(),
        'email' => $nurse->getEmail(),
        'shift' => $nurse->getShift(),
    ], Response::HTTP_CREATED);
}
    
    #[Route('/index', name: 'nurse_index', methods: ['GET'])]
    public function index(NurseRepository $nurseRepository): JsonResponse
    {
        $nurses = $nurseRepository->findAll();

        $data = [];

        foreach ($nurses as $nurse) {
            $data[] = [
                'id' => $nurse->getId(),
                'internal_id' => $nurse->getInternalId(),
                'first_name' => $nurse->getFirstName(),
                'last_name' => $nurse->getLastName(),
                'license_number' => $nurse->getLicenseNumber(),
                'username' => $nurse->getUsername(),
                'email' => $nurse->getEmail(),
                'shift' => $nurse->getShift(),
            ];
        }

        return $this->json($data);
    }


    #[Route('/login', name: 'nurse_login', methods: ['POST'])]
    public function loginNurses(Request $request, NurseRepository $nurseRepository): JsonResponse{
                $data = json_decode($request->getContent(), true);
        if (!is_array($data) || empty($data['user']) || empty($data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'El usuario y la contraseña son obligatorios',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $nurse = $nurseRepository->findOneBy(['username' => $data['user']]);


        if ($nurse === null || $nurse->getPassword() !== $data['password']) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

         return $this->json([
            'success' => true,
            'message' => 'Login correcto',
        ]);

    }

    #[Route('/nurses', name: 'nurse_get_nurses', methods: ['GET'])]
    public function getNurses(): JsonResponse
    {
        return $this->json($this->loadNurses());
    }

    private function loadNurses(): array
    {
        $path = $this->getParameter('kernel.project_dir') . '/data/nurses.json';
        $json = file_get_contents($path);

        return json_decode($json, true);

    }

    #[Route('/find-by-name/{name}', name: 'app_nurse_find_by_name', methods: ['GET'])]
    public function findByName(string $name, NurseRepository $nurseRepository): JsonResponse
    {
        $nurse = $nurseRepository->createQueryBuilder('n')
            ->where('LOWER(n.first_name) = LOWER(:name)')
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$nurse) {
            return $this->json(['error' => 'Nurse not found'], 404);
        }

        return $this->json([
            'id' => $nurse->getId(),
            'internal_id' => $nurse->getInternalId(),
            'first_name' => $nurse->getFirstName(),
            'last_name' => $nurse->getLastName(),
            'license_number' => $nurse->getLicenseNumber(),
            'username' => $nurse->getUsername(),
            'email' => $nurse->getEmail(),
            'shift' => $nurse->getShift(),
        ]);
    }
}