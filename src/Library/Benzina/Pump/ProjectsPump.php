<?php

namespace App\Library\Benzina\Pump;

use App\Entity\Accounting;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProjectsPump implements PumpInterface
{
    use ArrayPumpTrait;

    private const PROJECT_KEYS = [
        'id',
        'name',
        'subtitle',
        'lang',
        'currency',
        'currency_rate',
        'status',
        'translate',
        'progress',
        'owner',
        'node',
        'amount',
        'mincost',
        'maxcost',
        'days',
        'num_investors',
        'popularity',
        'num_messengers',
        'num_posts',
        'created',
        'updated',
        'published',
        'success',
        'closed',
        'passed',
        'contract_name',
        'contract_nif',
        'phone',
        'contract_email',
        'address',
        'zipcode',
        'location',
        'country',
        'image',
        'description',
        'motivation',
        'video',
        'video_usubs',
        'about',
        'goal',
        'related',
        'spread',
        'reward',
        'category',
        'keywords',
        'media',
        'media_usubs',
        'currently',
        'project_location',
        'scope',
        'resource',
        'comment',
        'contract_entity',
        'contract_birthdate',
        'entity_office',
        'entity_name',
        'entity_cif',
        'post_address',
        'secondary_address',
        'post_zipcode',
        'post_location',
        'post_country',
        'amount_users',
        'amount_call',
        'maxproj',
        'analytics_id',
        'facebook_pixel',
        'social_commitment',
        'social_commitment_description',
        'execution_plan',
        'sustainability_model',
        'execution_plan_url',
        'sustainability_model_url',
        'sign_url',
        'sign_url_action'
    ];

    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function supports(mixed $data): bool
    {
        if (!\is_array($data) || !\array_key_exists(0, $data)) {
            return false;
        }

        return $this->hasAllKeys($data[0], self::PROJECT_KEYS);
    }

    public function process(mixed $data): void
    {
        $owners = $this->getOwners($data);

        foreach ($data as $key => $data) {
            if (!\array_key_exists($data['owner'], $owners)) {
                continue;
            }

            if (empty($data['name'])) {
                continue;
            }

            $project = new Project;
            $project->setTitle($data['name']);
            $project->setOwner($owners[$data['owner']]);
            $project->setMigrated(true);
            $project->setMigratedReference($data['id']);

            $accounting = $this->getAccounting($data);
            $accounting->setProject($project);

            $this->entityManager->persist($project);
            $this->entityManager->persist($accounting);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @return User[]
     */
    private function getOwners(array $data): array
    {
        $users = $this->userRepository->findBy(['migratedReference' => \array_map(function ($data) {
            return $data['owner'];
        }, $data)]);

        $owners = [];
        foreach ($users as $user) {
            $owners[$user->getMigratedReference()] = $user;
        }

        return $owners;
    }

    private function getAccounting(array $data): Accounting
    {
        $accounting = new Accounting;
        $accounting->setCurrency($data['currency']);

        return $accounting;
    }
}
