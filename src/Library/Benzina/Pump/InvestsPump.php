<?php

namespace App\Library\Benzina\Pump;

use App\Entity\GatewayCharge;
use App\Entity\GatewayCheckout;
use App\Entity\Money;
use App\Entity\Project;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class InvestsPump implements PumpInterface
{
    use ArrayPumpTrait;

    private const INVEST_KEYS = [
        'id',
        'user',
        'project',
        'account',
        'amount',
        'amount_original',
        'currency',
        'currency_rate',
        'donate_amount',
        'status',
        'anonymous',
        'resign',
        'invested',
        'charged',
        'returned',
        'preapproval',
        'payment',
        'transaction',
        'method',
        'admin',
        'campaign',
        'datetime',
        'drops',
        'droped',
        'call',
        'matcher',
        'issue',
        'pool',
        'extra_info'
    ];

    public function __construct(
        private UserRepository $userRepository,
        private ProjectRepository $projectRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function supports(mixed $data): bool
    {
        if (!\is_array($data) || !\array_key_exists(0, $data)) {
            return false;
        }

        return $this->hasAllKeys($data[0], self::INVEST_KEYS);
    }

    public function process(mixed $data): void
    {
        $invested = $this->getInvested($data);
        $investors = $this->getInvestors($data);

        foreach ($data as $key => $data) {
            if (
                !$data['project'] ||
                // Ignore invests to non-migrated projects
                // Shouldn't be much, as the non-migrated are projects that were never funded in any way    
                !\array_key_exists($data['project'], $invested)
            ) {
                continue;
            }

            if ($data['amount'] === 0) {
                continue;
            }

            /** @var User */
            $user = $investors[$data['user']];

            /** @var Project */
            $project = $invested[$data['project']];

            $origin = $user->getAccounting();
            $target = $project->getAccounting();

            $money = new Money($data['amount'] * 100, $data['currency']);

            $reference = $this->getGatewayReference($data);

            if ($data['method'] === 'tpv') {
                $charge = new GatewayCharge;
                $charge->setMoney($money);
                $charge->setTarget($target);
                $charge->setExtradata([
                    'migrated' => true,
                    'migratedReference' => $data['id']
                ]);

                $checkout = new GatewayCheckout;
                $checkout->setOrigin($origin);
                $checkout->addCharge($charge);
                $checkout->setGateway($data['method']);
                $checkout->setGatewayReference($reference);
            }

            $transaction = new Transaction;
            $transaction->setMoney($money);
            $transaction->setOrigin($origin);
            $transaction->setTarget($target);

            $this->entityManager->persist($transaction);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function getInvested(array $data): array
    {
        $projects = $this->projectRepository->findBy(['migratedReference' => \array_map(function ($data) {
            return $data['project'];
        }, $data)]);

        $invested = [];
        foreach ($projects as $project) {
            $invested[$project->getMigratedReference()] = $project;
        }

        return $invested;
    }

    private function getInvestors(array $data): array
    {
        $users = $this->userRepository->findBy(['migratedReference' => \array_map(function ($data) {
            return $data['user'];
        }, $data)]);

        $investors = [];
        foreach ($users as $user) {
            $investors[$user->getMigratedReference()] = $user;
        }

        return $investors;
    }

    private function getGatewayReference(array $data): string
    {
        if (!empty($data['payment'])) {
            return $data['payment'];
        }

        if (!empty($data['transaction'])) {
            return $data['transaction'];
        }

        if (!empty($data['preapproval'])) {
            return $data['preapproval'];
        }

        return '';
    }
}
