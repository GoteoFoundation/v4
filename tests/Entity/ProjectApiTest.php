<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectStatus;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProjectApiTest extends ApiTestCase
{
    use ResetDatabase;

    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testGetCollection(): void
    {
        static::createClient()->request('GET', '/v4/projects');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@id' => '/v4/projects']);
        $this->assertJsonContains(['@type' => 'hydra:Collection']);
        $this->assertJsonContains(['hydra:totalItems' => 0]);
        $this->assertJsonContains(['hydra:member' => []]);

        $owner = new User();
        $owner->setUsername('test_user');
        $owner->setEmail('testuser@example.com');
        $owner->setPassword('projectapitestuserpassword');

        $project = new Project();
        $project->setTitle('Test Project');
        $project->setOwner($owner);
        $project->setStatus(ProjectStatus::InEditing);

        $this->entityManager->persist($owner);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        static::createClient()->request('GET', '/v4/projects');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['hydra:totalItems' => 1]);
        $this->assertJsonContains(['hydra:member' => [
            [
                'title' => 'Test Project',
                'status' => ProjectStatus::InEditing->value,
            ],
        ]]);
    }

    public function testPostUnauthorized()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/v4/projects',
            [
                'json' => [
                    'title' => 'ProjectApiTest Project',
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
