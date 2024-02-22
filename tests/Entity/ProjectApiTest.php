<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProjectApiTest extends ApiTestCase
{
    use ResetDatabase;

    public function testGetCollection(): void
    {
        static::createClient()->request('GET', '/v4/projects');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@id' => '/v4/projects']);
        $this->assertJsonContains(['hydra:member' => []]);
    }

    public function testPostUnauthorized(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/v4/projects',
            ['json' => [
                'title' => 'ProjectApiTest Project'
            ]]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
