<?php

namespace App\Library\Benzina\Pump;

use App\Entity\Accounting;
use App\Entity\AccountingTransaction;
use App\Entity\GatewayCharge;
use App\Entity\GatewayChargeType;
use App\Entity\GatewayCheckout;
use App\Entity\GatewayCheckoutStatus;
use App\Entity\Money;
use App\Entity\Tipjar;
use App\Library\Benzina\Pump\Trait\ArrayPumpTrait;
use App\Library\Benzina\Pump\Trait\ProgressivePumpTrait;
use App\Library\Economy\Payment\CashGateway;
use App\Library\Economy\Payment\CecaGateway;
use App\Library\Economy\Payment\DropGateway;
use App\Library\Economy\Payment\PaypalGateway;
use App\Library\Economy\Payment\StripeGateway;
use App\Library\Economy\Payment\WalletGateway;
use App\Repository\ProjectRepository;
use App\Repository\TipjarRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class CheckoutsPump extends AbstractPump implements PumpInterface
{
    use ArrayPumpTrait;
    use ProgressivePumpTrait;

    private const PLATFORM_TIPJAR_NAME = 'platform';
    private const CHECKOUT_URL_DEFAULT = 'https://www.goteo.org/invest';

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
        'extra_info',
    ];

    public function __construct(
        private UserRepository $userRepository,
        private ProjectRepository $projectRepository,
        private TipjarRepository $tipjarRepository,
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

        $tipjar = $this->getPlatformTipjar();

        $pumped = $this->getPumped(GatewayCheckout::class, $data, ['migratedReference' => 'id']);

        foreach ($data as $key => $record) {
            if ($this->isPumped($record, $pumped, ['migratedReference' => 'id'])) {
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

            $checkout = new GatewayCheckout();
            $checkout->setOrigin($user->getAccounting());
            $checkout->setStatus($this->getCheckoutStatus($record));
            $checkout->setGateway($this->getCheckoutGateway($record));
            $checkout->setGatewayReference($this->getCheckoutReference($record));
            $checkout->setCheckoutUrl(self::CHECKOUT_URL_DEFAULT);
            $checkout->setMigrated(true);
            $checkout->setMigratedReference($record['id']);
            $checkout->setMetadata([
                'payment' => $record['payment'],
                'transaction' => $record['transaction'],
                'preapproval' => $record['preapproval'],
            ]);
            $checkout->setCreatedAt(new \DateTime($record['invested']));
            $checkout->setUpdatedAt(new \DateTime());

            $charge = new GatewayCharge();
            $charge->setType($this->getChargeType($record));
            $charge->setMoney($this->getChargeMoney($record['amount'], $record['currency']));
            $charge->setTarget($project->getAccounting());

            if ($record['donate_amount'] > 0) {
                $tip = new GatewayCharge();
                $tip->setType(GatewayChargeType::Single);
                $tip->setMoney($this->getChargeMoney($record['donate_amount'], $record['currency']));
                $tip->setTarget($tipjar->getAccounting());

                $this->entityManager->persist($tip);
                $checkout->addCharge($tip);
            }

            $this->entityManager->persist($charge);
            $checkout->addCharge($charge);

            if ($checkout->getStatus() === GatewayCheckoutStatus::Charged) {
                foreach ($checkout->getCharges() as $charge) {
                    $transaction = new AccountingTransaction();
                    $transaction->setMoney($charge->getMoney());
                    $transaction->setOrigin($checkout->getOrigin());
                    $transaction->setTarget($charge->getTarget());

                    $charge->setTransaction($transaction);

                    $this->entityManager->persist($transaction);
                    $this->entityManager->persist($charge);
                }
            }

            $this->entityManager->persist($checkout);
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

    private function getPlatformTipjar(): Tipjar
    {
        $tipjar = $this->tipjarRepository->findOneBy(['name' => self::PLATFORM_TIPJAR_NAME]);

        if ($tipjar) {
            return $tipjar;
        }

        $tipjar = new Tipjar();
        $tipjar->setName(self::PLATFORM_TIPJAR_NAME);

        $accounting = new Accounting();
        $accounting->setTipjar($tipjar);

        $this->entityManager->persist($tipjar);
        $this->entityManager->persist($accounting);
        $this->entityManager->flush();

        return $tipjar;
    }

    private function getChargeType(array $record): GatewayChargeType
    {
        if (\in_array($record['method'], ['stripe_subscription'])) {
            return GatewayChargeType::Recurring;
        }

        return GatewayChargeType::Single;
    }

    private function getChargeMoney(int $amount, string $currency): Money
    {
        $amount = $amount * 100;

        if ($amount >= self::MAX_INT) {
            $amount = self::MAX_INT;
        }

        return new Money($amount, $currency);
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
        switch ($record['method']) {
            case 'stripe_subscription':
                return StripeGateway::getName();
            case 'pool':
                return WalletGateway::getName();
            case 'paypal':
                return PaypalGateway::getName();
            case 'tpv':
                return CecaGateway::getName();
            case 'cash':
                return CashGateway::getName();
            case 'drop':
                return DropGateway::getName();
            default:
                return '';
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
