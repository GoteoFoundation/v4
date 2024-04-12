<?php

namespace App\Library\Benzina\Pump;

use App\Entity\GatewayCharge;
use App\Entity\GatewayChargeType;
use App\Entity\GatewayCheckout;
use App\Entity\GatewayCheckoutStatus;
use App\Entity\Money;
use App\Entity\Project;
use App\Library\Economy\Payment\CashGateway;
use App\Library\Economy\Payment\CecaGateway;
use App\Library\Economy\Payment\DropGateway;
use App\Library\Economy\Payment\PaypalGateway;
use App\Library\Economy\Payment\StripeGateway;
use App\Library\Economy\Payment\WalletGateway;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class InvestsPump implements PumpInterface
{
    use ArrayPumpTrait;
    use ProgressivePumpTrait;

    private const MAX_INT = 2147483647;

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
        $users = $this->getUsers($data);
        $projects = $this->getProjects($data);

        $pumped = $this->getPumped(GatewayCheckout::class, $data, ['migratedReference' => 'id']);

        foreach ($data as $key => $record) {
            if ($this->isPumped($record, $pumped)) {
                continue;
            }

            if (!\array_key_exists($record['project'], $projects)) {
                continue;
            }

            if (!$record['amount'] || $record['amount'] < 1) {
                continue;
            }

            if (!$record['method'] || empty($record['method'])) {
                continue;
            }

            $user = $users[$record['user']];
            $project = $projects[$record['project']];

            $charge = new GatewayCharge;
            $charge->setType($this->getChargeType($record));
            $charge->setMoney($this->getChargeMoney($record, $project));
            $charge->setTarget($project->getAccounting());

            $checkout = new GatewayCheckout;
            $checkout->setOrigin($user->getAccounting());
            $checkout->addCharge($charge);
            $checkout->setStatus($this->getCheckoutStatus($record));
            $checkout->setGateway($this->getCheckoutGateway($record));
            $checkout->setGatewayReference($this->getCheckoutReference($record));
            $checkout->setMigrated(true);
            $checkout->setMigratedReference($record['id']);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function getUsers(array $data): array
    {
        $users = $this->userRepository->findBy(['migratedReference' => \array_map(function ($data) {
            return $data['user'];
        }, $data)]);

        $usersByMigratedReference = [];
        foreach ($users as $user) {
            $usersByMigratedReference[$user->getMigratedReference()] = $user;
        }

        return $usersByMigratedReference;
    }

    private function getProjects(array $data): array
    {
        $projects = $this->projectRepository->findBy(['migratedReference' => \array_map(function ($data) {
            return $data['project'];
        }, $data)]);

        $projectsByMigratedReference = [];
        foreach ($projects as $project) {
            $projectsByMigratedReference[$project->getMigratedReference()] = $project;
        }

        return $projectsByMigratedReference;
    }

    private function getChargeType(array $record): GatewayChargeType
    {
        if (\in_array($record['method'], ['stripe_subscription'])) {
            return GatewayChargeType::Recurring;
        }

        return GatewayChargeType::Single;
    }

    private function getChargeMoney(array $record, Project $project): Money
    {
        $amount = $record['amount'] * 100;

        if ($amount >= self::MAX_INT) {
            $amount = self::MAX_INT;
        }

        return new Money($amount, $project->getAccounting()->getCurrency());
    }

    private function getCheckoutStatus(array $record): GatewayCheckoutStatus
    {
        if ($record['status'] < 1) {
            return GatewayCheckoutStatus::Pending;
        }

        return GatewayCheckoutStatus::Charged;
    }

    private function getCheckoutGateway(array $record): string
    {
        if ($record['method'] === 'stripe_subscription') {
            return StripeGateway::getName();
        }

        if ($record['method'] === 'pool') {
            return WalletGateway::getName();
        }

        if ($record['method'] === 'tpv') {
            return CecaGateway::getName();
        }

        if ($record['method'] === 'paypal') {
            return PaypalGateway::getName();
        }

        if ($record['method'] === 'cash') {
            return CashGateway::getName();
        }

        if ($record['method'] === 'drop') {
            return DropGateway::getName();
        }
    }

    private function getCheckoutReference(array $record): string
    {
        if (!empty($record['payment'])) {
            return $record['payment'];
        }

        if (!empty($record['transaction'])) {
            return $record['transaction'];
        }

        if (!empty($record['preapproval'])) {
            return $record['preapproval'];
        }

        return '';
    }
}
