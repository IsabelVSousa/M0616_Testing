<?php

namespace App\Tests\Controller;

use App\Entity\Nurse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NurseControllerTest extends WebTestCase
{
    // Cambia este prefijo si tu NurseController usa otro #[Route('/...')] a nivel de clase
    private const BASE = '/nurse';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        $em = $this->em();
        foreach ($em->getRepository(Nurse::class)->findAll() as $nurse) {
            $em->remove($nurse);
        }
        $em->flush();

        // Enfermero base para los tests de búsqueda y de duplicados
        $nurse = (new Nurse())
            ->setInternalId('ENF-9001')
            ->setFirstName('Irene')
            ->setLastName('Test')
            ->setLicenseNumber('90000001')
            ->setUsername('irene.test')
            ->setEmail('irene.test@sanidad.es')
            ->setShift('morning')
            ->setPassword(password_hash('Secret1234', PASSWORD_BCRYPT));

        $em->persist($nurse);
        $em->flush();
    }

    protected function tearDown(): void
    {
        $em = $this->em();
        foreach ($em->getRepository(Nurse::class)->findAll() as $nurse) {
            $em->remove($nurse);
        }
        $em->flush();

        parent::tearDown();
    }

    // ---------------------------------------------------------------
    // GET /find-by-name/{name}
    // ---------------------------------------------------------------

    public function testFindByNameReturnsNurse(): void
    {
        $this->client->request('GET', self::BASE . '/find-by-name/Irene');

        $this->assertResponseStatusCodeSame(200);
        $data = $this->jsonResponse();
        $this->assertSame('Irene', $data['first_name']);
        $this->assertSame('ENF-9001', $data['internal_id']);
        $this->assertSame('irene.test', $data['username']);
    }

    public function testFindByNameIsCaseInsensitive(): void
    {
        $this->client->request('GET', self::BASE . '/find-by-name/irene');

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame('Irene', $this->jsonResponse()['first_name']);
    }

    public function testFindByNameNotFound(): void
    {
        $this->client->request('GET', self::BASE . '/find-by-name/Zzzz');

        $this->assertResponseStatusCodeSame(404);
        $this->assertSame(['error' => 'Nurse not found'], $this->jsonResponse());
    }

    public function testFindByNameDoesNotExposePassword(): void
    {
        $this->client->request('GET', self::BASE . '/find-by-name/Irene');

        $this->assertArrayNotHasKey('password', $this->jsonResponse());
    }

    // ---------------------------------------------------------------
    // POST /new
    // ---------------------------------------------------------------

    public function testCreateNurseSuccess(): void
    {
        $this->postJson(self::BASE . '/new', $this->validPayload());

        $this->assertResponseStatusCodeSame(201);
        $data = $this->jsonResponse();
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('Laura', $data['first_name']);
        $this->assertSame('laura.test', $data['username']);
        $this->assertArrayNotHasKey('password', $data);
    }

    public function testCreateNursePersistsHashedPassword(): void
    {
        $this->postJson(self::BASE . '/new', $this->validPayload());
        $this->assertResponseStatusCodeSame(201);

        $em = $this->em();
        $em->clear();
        $nurse = $em->getRepository(Nurse::class)->findOneBy(['username' => 'laura.test']);

        $this->assertNotNull($nurse);
        $this->assertNotSame('MiClave123', $nurse->getPassword());
        $this->assertTrue(password_verify('MiClave123', $nurse->getPassword()));
    }

    public function testCreateWithInvalidJson(): void
    {
        $this->client->request(
            'POST',
            self::BASE . '/new',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{ "internal_id": "ENF-9002", "first_name": "Laura'
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertSame(['error' => 'Invalid JSON'], $this->jsonResponse());
    }

    public function testCreateWithMissingRequiredFields(): void
    {
        $this->postJson(self::BASE . '/new', []);

        $this->assertResponseStatusCodeSame(422);
        $errors = $this->jsonResponse()['errors'];
        foreach (['internal_id', 'first_name', 'license_number', 'username'] as $field) {
            $this->assertArrayHasKey($field, $errors, "Falta el error del campo $field");
        }
    }

    public function testCreateWithInvalidFormats(): void
    {
        $payload = $this->validPayload([
            'internal_id' => 'ABC',
            'license_number' => '123',
            'email' => 'no-es-un-email',
            'shift' => 'weekend',
            'password' => '123',
        ]);

        $this->postJson(self::BASE . '/new', $payload);

        $this->assertResponseStatusCodeSame(422);
        $errors = $this->jsonResponse()['errors'];
        foreach (['internal_id', 'license_number', 'email', 'shift', 'password'] as $field) {
            $this->assertArrayHasKey($field, $errors, "Falta el error del campo $field");
        }
    }

    public function testCreateWithTooLongFirstName(): void
    {
        $this->postJson(self::BASE . '/new', $this->validPayload([
            'first_name' => str_repeat('a', 26),
        ]));

        $this->assertResponseStatusCodeSame(422);
        $this->assertArrayHasKey('first_name', $this->jsonResponse()['errors']);
    }

    public function testCreateWithDuplicateUsername(): void
    {
        // 'irene.test' ya existe por el setUp()
        $this->postJson(self::BASE . '/new', $this->validPayload(['username' => 'irene.test']));

        $this->assertResponseStatusCodeSame(422);
        $this->assertArrayHasKey('username', $this->jsonResponse()['errors']);
    }

    public function testCreateWithDuplicateInternalId(): void
    {
        $this->postJson(self::BASE . '/new', $this->validPayload(['internal_id' => 'ENF-9001']));

        $this->assertResponseStatusCodeSame(422);
        $this->assertArrayHasKey('internal_id', $this->jsonResponse()['errors']);
    }

    public function testCreateWithWrongFieldTypes(): void
    {
        $this->postJson(self::BASE . '/new', $this->validPayload(['first_name' => ['no', 'es', 'texto']]));

        $this->assertResponseStatusCodeSame(422);
        $this->assertArrayHasKey('first_name', $this->jsonResponse()['errors']);
    }

    public function testCreateRejectsGetMethod(): void
    {
        $this->client->request('GET', self::BASE . '/new');

        $this->assertResponseStatusCodeSame(405);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function em(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine.orm.entity_manager');
    }

    private function postJson(string $uri, array $data): void
    {
        $this->client->request(
            'POST',
            $uri,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($data)
        );
    }

    private function jsonResponse(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'internal_id' => 'ENF-9002',
            'first_name' => 'Laura',
            'last_name' => 'Gomez',
            'license_number' => '90000002',
            'username' => 'laura.test',
            'password' => 'MiClave123',
            'email' => 'laura.test@sanidad.es',
            'shift' => 'night',
        ], $overrides);
    }
}