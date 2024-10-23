<?php

namespace App\Library\Benzina\Pump;

use App\Entity\Accounting;
use App\Entity\GatewayCharge;
use App\Entity\GatewayChargeType;
use App\Entity\GatewayCheckout;
use App\Entity\GatewayCheckoutStatus;
use App\Entity\GatewayTracking;
use App\Entity\Money;
use App\Entity\Tipjar;
use App\Library\Benzina\Pump\Trait\ArrayPumpTrait;
use App\Library\Benzina\Pump\Trait\DoctrinePumpTrait;
use App\Library\Economy\Payment\CashGateway;
use App\Library\Economy\Payment\CecaGateway;
use App\Library\Economy\Payment\DropGateway;
use App\Library\Economy\Payment\PaypalGateway;
use App\Library\Economy\Payment\StripeGateway;
use App\Library\Economy\Payment\WalletGateway;
use App\Repository\ProjectRepository;
use App\Repository\TipjarRepository;
use App\Repository\UserRepository;
use App\Service\GatewayCheckoutService;
use Doctrine\ORM\EntityManagerInterface;

class CheckoutsPump extends AbstractPump implements PumpInterface
{
    use ArrayPumpTrait;
    use DoctrinePumpTrait;

    public const TRACKING_TITLE_V3 = 'v3 Invest ID';
    public const TRACKING_TITLE_PAYMENT = 'v3 Invest Payment';
    public const TRACKING_TITLE_TRANSACTION = 'v3 Invest Transaction';
    public const TRACKING_TITLE_PREAPPROVAL = 'v3 Invest Preapproval';

    public const CHARGE_TITLE_PROJECT = 'Pago en Goteo v3 - Donación a proyecto';
    public const CHARGE_TITLE_POOL = 'Pago en Goteo v3 - Carga de monedero';
    public const CHARGE_TITLE_TIP = 'Pago en Goteo v3 - Propina a la plataforma';

    private const PLATFORM_TIPJAR_NAME = 'platform';

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
        private EntityManagerInterface $entityManager,
        private GatewayCheckoutService $checkoutService,
    ) {}

    public function supports(mixed $batch): bool
    {
        if (!\is_array($batch) || !\array_key_exists(0, $batch)) {
            return false;
        }

        return $this->hasAllKeys($batch[0], self::INVEST_KEYS);
    }

    public function pump(mixed $batch): void
    {
        $batch = $this->skipPumped($batch, 'id', GatewayCheckout::class, 'migratedId');

        $tipjar = $this->getPlatformTipjar();

        $users = $this->getPumpedUsers($batch);
        $projects = $this->getPumpedProjects($batch);

        foreach ($batch as $key => $record) {
            if (!$record['user'] || empty($record['user'])) {
                continue;
            }

            if (!$record['amount'] || $record['amount'] < 1) {
                continue;
            }

            if (!$record['method'] || empty($record['method'])) {
                continue;
            }

            $user = $users[$record['user']];

            $checkout = new GatewayCheckout();
            $checkout->setOrigin($user->getAccounting());
            $checkout->setStatus($this->getCheckoutStatus($record));
            $checkout->setGateway($this->getCheckoutGateway($record));

            foreach ($this->getCheckoutTrackings($record) as $tracking) {
                $checkout->addGatewayTracking($tracking);
            }

            $checkout->setMigrated(true);
            $checkout->setMigratedId($record['id']);

            $checkout->setDateCreated(new \DateTime($record['invested']));
            $checkout->setDateUpdated(new \DateTime());

            $charge = new GatewayCharge();
            $charge->setType($this->getChargeType($record));
            $charge->setMoney($this->getChargeMoney($record['amount'], $record['currency']));

            if (empty($record['project'])) {
                $charge->setTitle(self::CHARGE_TITLE_POOL);
                $charge->setTarget($user->getAccounting());
            }

            if (!empty($record['project'])) {
                $project = $projects[$record['project']];

                $charge->setTitle(self::CHARGE_TITLE_PROJECT);
                $charge->setTarget($project->getAccounting());
            }

            $checkout->addCharge($charge);

            if ($record['donate_amount'] > 0) {
                $tip = new GatewayCharge();
                $tip->setType(GatewayChargeType::Single);
                $tip->setTitle(self::CHARGE_TITLE_TIP);
                $tip->setMoney($this->getChargeMoney($record['donate_amount'], $record['currency']));
                $tip->setTarget($tipjar->getAccounting());

                $checkout->addCharge($tip);
            }

            if ($checkout->getStatus() === GatewayCheckoutStatus::Charged) {
                $checkout = $this->checkoutService->chargeCheckout($checkout);
            }

            $this->entityManager->persist($checkout);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @return array<string, \App\Entity\User>
     */
    private function getPumpedUsers(array $batch): array
    {
        $users = $this->userRepository->findBy(['migratedId' => \array_map(function ($batch) {
            return $batch['user'];
        }, $batch)]);

        $usersByMigratedId = [];
        foreach ($users as $user) {
            $usersByMigratedId[$user->getMigratedId()] = $user;
        }

        return $usersByMigratedId;
    }

    /**
     * @return array<string, \App\Entity\Project>
     */
    private function getPumpedProjects(array $batch): array
    {
        $projects = $this->projectRepository->findBy(['migratedId' => \array_map(function ($batch) {
            return $batch['project'];
        }, $batch)]);

        $projectsByMigratedId = [];
        foreach ($projects as $project) {
            $projectsByMigratedId[$project->getMigratedId()] = $project;
        }

        return $projectsByMigratedId;
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

    /**
     * @return GatewayTracking[]
     */
    private function getCheckoutTrackings(array $record): array
    {
        $v3Tracking = new GatewayTracking();
        $v3Tracking->title = self::TRACKING_TITLE_V3;
        $v3Tracking->value = $record['id'];

        $trackings = [$v3Tracking];

        if (!empty($record['payment'])) {
            $payment = new GatewayTracking();
            $payment->title = self::TRACKING_TITLE_PAYMENT;
            $payment->value = $record['payment'];

            $trackings[] = $payment;
        }

        if (!empty($record['transaction'])) {
            $transaction = new GatewayTracking();
            $transaction->title = self::TRACKING_TITLE_TRANSACTION;
            $transaction->value = $record['transaction'];

            $trackings[] = $transaction;
        }

        if (!empty($record['preapproval'])) {
            $preapproval = new GatewayTracking();
            $preapproval->title = self::TRACKING_TITLE_PREAPPROVAL;
            $preapproval->value = $record['preapproval'];

            $trackings[] = $preapproval;
        }

        return $trackings;
    }
}
