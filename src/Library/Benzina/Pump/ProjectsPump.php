<?php

namespace App\Library\Benzina\Pump;

use App\Entity\Accounting\Accounting;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectStatus;
use App\Entity\User\User;
use App\Library\Benzina\Pump\Trait\ArrayPumpTrait;
use App\Library\Benzina\Pump\Trait\DoctrinePumpTrait;
use App\Repository\User\UserRepository;
use App\Service\LocalizationService;
use Doctrine\ORM\EntityManagerInterface;

class ProjectsPump extends AbstractPump implements PumpInterface
{
    use ArrayPumpTrait;
    use DoctrinePumpTrait;
    use ProjectsPumpTrait;

    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private LocalizationService $localizationService,
    ) {}

    public function supports(mixed $batch): bool
    {
        if (!\is_array($batch) || !\array_key_exists(0, $batch)) {
            return false;
        }

        return $this->hasAllKeys($batch[0], self::PROJECT_KEYS);
    }

    public function pump(mixed $batch): void
    {
        $batch = $this->skipPumped($batch, 'id', Project::class, 'migratedId');

        $owners = $this->getOwners($batch);

        foreach ($batch as $key => $record) {
            if (!$this->isPumpable($record)) {
                continue;
            }

            if (!\array_key_exists($record['owner'], $owners)) {
                continue;
            }

            $project = new Project();
            $project->setTranslatableLocale($this->getProjectLang($record['lang']));
            $project->setTitle($record['name']);
            $project->setDescription($record['description']);
            $project->setOwner($owners[$record['owner']]);
            $project->setStatus($this->getProjectStatus($record['status']));
            $project->setMigrated(true);
            $project->setMigratedId($record['id']);
            $project->setDateCreated(new \DateTime($record['created']));
            $project->setDateUpdated(new \DateTime());

            $this->entityManager->persist($project);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function isPumpable(array $record): bool
    {
        if (empty($record['id']) || empty($record['name'])) {
            return false;
        }

        return true;
    }

    /**
     * @return User[]
     */
    private function getOwners(array $record): array
    {
        $users = $this->userRepository->findBy(['migratedId' => \array_map(function ($record) {
            return $record['owner'];
        }, $record)]);

        $owners = [];
        foreach ($users as $user) {
            $owners[$user->getMigratedId()] = $user;
        }

        return $owners;
    }

    private function getProjectLang(string $lang): string
    {
        if (empty($lang)) {
            return $this->localizationService->getDefaultLanguage();
        }

        return $this->localizationService->getLanguage($lang);
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
