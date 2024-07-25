<?php

namespace App\Tests\Library\Benzina\Pump;

use App\Entity\User;
use App\Library\Benzina\Benzina;
use App\Library\Benzina\Pump\UsersPump;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class UsersPumpTest extends KernelTestCase
{
    use ResetDatabase;

    private UsersPump $pump;
    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        self::bootKernel();

        $this->pump = static::getContainer()->get(UsersPump::class);
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testAcceptsOnlyUserCollection()
    {
        $object = $this->pump->supports(new \ArrayObject([
            ['id', 'username', 'password'], ['id', 'username', 'password']]
        ));

        $this->assertFalse($object);

        $arrayWithMissingKeys = $this->pump->supports([
            ['id', 'username', 'password'], ['id', 'username', 'password']
        ]);

        $this->assertFalse($arrayWithMissingKeys);

        $singleItemWithAllKeys = $this->pump->supports(UsersPump::USER_KEYS);

        $this->assertFalse($singleItemWithAllKeys);

        $collectionWithAllKeys = $this->pump->supports([ UsersPump::USER_KEYS, UsersPump::USER_KEYS ]);

        $this->assertFalse($collectionWithAllKeys);
    }

    public function testProcessesUserData()
    {
        $testUser = [
            'id' => 'test-user-id',
            'name' => 'Test User name',
            'location' => '',
            'email' => 'testemail@test.test',
            'password' => 'testuserpassword',
            'gender' => 'F',
            'birthyear' => '2024',
            'entity_type' => 0,
            'legal_entity' => 0,
            'origin_register' => NULL,
            'about' => 'Test User description',
            'keywords' => NULL,
            'active' => true,
            'avatar' => 'test-user-avatar.jpg',
            'contribution' => NULL,
            'twitter' => NULL,
            'facebook' => NULL,
            'instagram' => NULL,
            'identica' => NULL,
            'linkedin' => NULL,
            'amount' => 25,
            'num_patron' => 0,
            'num_patron_active' => 0,
            'worth' => 1,
            'created' => '2024-00-00',
            'modified' => '2024-00-00',
            'token' => '007fac056211808madeuptoken',
            'rememberme' => '',
            'hide' => 0,
            'confirmed' => 1,
            'lang' => 'es',
            'node' => 'goteo',
            'num_invested' => NULL,
            'num_owned' => NULL,
        ];

        $supports = $this->pump->supports([ $testUser ]);

        $this->assertTrue($supports);

        $usersPrePumping = $this->entityManager->getRepository(User::class)
            ->findAll();

        $this->assertCount(0, $usersPrePumping);

        $this->pump->process([ $testUser ]);

        $usersPostPumping = $this->entityManager->getRepository(User::class)
            ->findAll();

        $this->assertCount(1, $usersPostPumping);

        $user = $usersPostPumping[0];

        $this->assertEquals($testUser['email'], $user->getEmail());
        $this->assertNotEquals($testUser['confirmed'], $user->isEmailConfirmed());

        $this->assertTrue($user->isMigrated());
        $this->assertEquals($testUser['id'], $user->getMigratedReference());
    }
}
