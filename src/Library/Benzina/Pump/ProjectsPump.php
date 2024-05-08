<?php

namespace App\Library\Benzina\Pump;

use App\Entity\Accounting;
use App\Entity\Project;
use App\Entity\ProjectStatus;
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

        foreach ($data as $key => $record) {
            if (!\array_key_exists($record['owner'], $owners)) {
                continue;
            }

            if (empty($record['name'])) {
                continue;
            }

            $project = new Project;
            $project->setTitle($record['name']);
            $project->setOwner($owners[$record['owner']]);
            $project->setStatus($this->getProjectStatus($record['status']));
            $project->setMigrated(true);
            $project->setMigratedReference($record['id']);

            $accounting = $this->getAccounting($record);
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

    private function getProjectStatus(int $status): ProjectStatus
    {
        switch ($status) {
            case 0:
                return ProjectStatus::Rejected;
            case 1:
                return ProjectStatus::Editing;
            case 2:
                return ProjectStatus::Reviewing;
            case 3:
                return ProjectStatus::InCampaign;
            case 4:
                return ProjectStatus::Funded;
            case 5:
                return ProjectStatus::Fulfilled;
            case 6:
                return ProjectStatus::Unfunded;
        }
    }

    private function getAccounting(array $record): Accounting
    {
        $accounting = new Accounting;
        $accounting->setCurrency($record['currency']);

        return $accounting;
    }
}
