<?php

namespace App\Benzina;

use App\Entity\Accounting\Accounting;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectStatus;
use App\Entity\User\User;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Goteo\Benzina\Pump\AbstractPump;
use Goteo\Benzina\Pump\ArrayPumpTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;

class ProjectsPump extends AbstractPump
{
    use ArrayPumpTrait;
    use DoctrinePumpTrait;
    use ProjectsPumpTrait;

    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function supports(mixed $sample): bool
    {
        if (\is_array($sample) && $this->hasAllKeys($sample, self::PROJECT_KEYS)) {
            return true;
        }

        return false;
    }

    public function pump(mixed $record): void
    {
        if (empty($record['name'])) {
            return;
        }

        $status = $this->getProjectStatus($record['status']);
        if (\in_array($status, [ProjectStatus::InEditing, ProjectStatus::Rejected])) {
            return;
        }

        $owner = $this->getProjectOwner($record['owner']);
        if ($owner === null) {
            return;
        }

        $project = new Project();
        $project->setTitle($record['name']);
        $project->setOwner($owner);
        $project->setStatus($status);
        $project->setMigrated(true);
        $project->setMigratedId($record['id']);
        $project->setDateCreated(new \DateTime($record['created']));
        $project->setDateUpdated(new \DateTime());

        $this->persist($project);
    }

    private function getProjectOwner(string $owner): ?User
    {
        return $this->userRepository->findOneBy(['migratedId' => $owner]);
    }

    private function getProjectStatus(int $status): ProjectStatus
    {
        switch ($status) {
            case 1:
                return ProjectStatus::InEditing;
            case 2:
                return ProjectStatus::InReview;
            case 0:
                return ProjectStatus::Rejected;
            case 3:
                return ProjectStatus::InCampaign;
            case 6:
                return ProjectStatus::Unfunded;
            case 4:
                return ProjectStatus::InFunding;
            case 5:
                return ProjectStatus::Fulfilled;
        }
    }

    private function getAccounting(array $record): Accounting
    {
        $accounting = new Accounting();
        $accounting->setCurrency($record['currency']);

        return $accounting;
    }
}
