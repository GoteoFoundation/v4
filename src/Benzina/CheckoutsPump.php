<?php

namespace App\Benzina;

use App\Entity\Gateway\Charge;
use App\Entity\Gateway\Checkout;
use App\Entity\Money;
use App\Entity\Project\Project;
use App\Entity\Tipjar;
use App\Entity\User\User;
use App\Gateway\ChargeType;
use App\Gateway\CheckoutStatus;
use App\Gateway\Gateway\CashGateway;
use App\Gateway\Gateway\CecaGateway;
use App\Gateway\Gateway\DropGateway;
use App\Gateway\Paypal\PaypalGateway;
use App\Gateway\Stripe\StripeGateway;
use App\Gateway\Tracking;
use App\Gateway\Wallet\WalletGateway;
use App\Repository\Project\ProjectRepository;
use App\Repository\TipjarRepository;
use App\Repository\User\UserRepository;
use App\Service\Gateway\CheckoutService;
use Doctrine\ORM\EntityManagerInterface;
use Goteo\Benzina\Pump\AbstractPump;
use Goteo\Benzina\Pump\ArrayPumpTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;

class CheckoutsPump extends AbstractPump
{
    use ArrayPumpTrait;
    use DoctrinePumpTrait;
    use CheckoutsPumpTrait;

    public const TRACKING_TITLE_V3 = 'v3 Invest ID';
    public const TRACKING_TITLE_PAYMENT = 'v3 Invest Payment';
    public const TRACKING_TITLE_TRANSACTION = 'v3 Invest Transaction';
    public const TRACKING_TITLE_PREAPPROVAL = 'v3 Invest Preapproval';

    public const CHARGE_TITLE_PROJECT = 'Pago en Goteo v3 - Donación a proyecto';
    public const CHARGE_TITLE_POOL = 'Pago en Goteo v3 - Carga de monedero';
    public const CHARGE_TITLE_TIP = 'Pago en Goteo v3 - Propina a la plataforma';

    private const PLATFORM_TIPJAR_NAME = 'platform';

    private const MAX_INT = 2147483647;

    public function __construct(
        private UserRepository $userRepository,
        private ProjectRepository $projectRepository,
        private TipjarRepository $tipjarRepository,
        private EntityManagerInterface $entityManager,
        private CheckoutService $checkoutService,
    ) {}

    public function supports(mixed $sample): bool
    {
        if (\is_array($sample) && $this->hasAllKeys($sample, self::INVEST_KEYS)) {
            return true;
        }

        return false;
    }

    public function pump(mixed $record): void
    {
        if (!$record['user'] || empty($record['user'])) {
            return;
        }

        if (!$record['amount'] || $record['amount'] < 1) {
            return;
        }

        if (!$record['method'] || empty($record['method'])) {
            return;
        }

        $user = $this->getUser($record);
        if ($user === null) {
            return;
        }

        $project = $this->getProject($record);
        $tipjar = $this->getPlatformTipjar();

        $checkout = new Checkout();
        $checkout->setOrigin($user->getAccounting());
        $checkout->setStatus($this->getCheckoutStatus($record));
        $checkout->setGatewayName($this->getCheckoutGateway($record));

        foreach ($this->getCheckoutTrackings($record) as $tracking) {
            $checkout->addTracking($tracking);
        }

        $checkout->setMigrated(true);
        $checkout->setMigratedId($record['id']);

        $checkout->setDateCreated(new \DateTime($record['invested']));
        $checkout->setDateUpdated(new \DateTime());

        $charge = new Charge();
        $charge->setType($this->getChargeType($record));
        $charge->setMoney($this->getChargeMoney($record['amount'], $record['currency']));

        if ($project === null) {
            $charge->setTitle(self::CHARGE_TITLE_POOL);
            $charge->setTarget($user->getAccounting());
        } else {
            $charge->setTitle(self::CHARGE_TITLE_PROJECT);
            $charge->setTarget($project->getAccounting());
        }

        $checkout->addCharge($charge);

        if ($record['donate_amount'] > 0) {
            $tip = new Charge();
            $tip->setType(ChargeType::Single);
            $tip->setTitle(self::CHARGE_TITLE_TIP);
            $tip->setMoney($this->getChargeMoney($record['donate_amount'], $record['currency']));
            $tip->setTarget($tipjar->getAccounting());

            $checkout->addCharge($tip);
        }

        if ($checkout->getStatus() === CheckoutStatus::Charged) {
            $checkout = $this->checkoutService->chargeCheckout($checkout);
        }

        $this->persist($checkout);
    }

    private function getUser(array $record): ?User
    {
        return $this->userRepository->findOneBy(['migratedId' => $record['user']]);
    }

    private function getProject(array $record): ?Project
    {
        if (empty($record['project'])) {
            return null;
        }

        return $this->projectRepository->findOneBy(['migratedId' => $record['project']]);
    }

    private function getPlatformTipjar(): Tipjar
    {
        $tipjar = $this->tipjarRepository->findOneBy(['name' => self::PLATFORM_TIPJAR_NAME]);

        if ($tipjar) {
            return $tipjar;
        }

        $tipjar = new Tipjar();
        $tipjar->setName(self::PLATFORM_TIPJAR_NAME);

        $this->entityManager->persist($tipjar);
        $this->entityManager->flush();

        return $tipjar;
    }

    private function getChargeType(array $record): ChargeType
    {
        if (\in_array($record['method'], ['stripe_subscription'])) {
            return ChargeType::Recurring;
        }

        return ChargeType::Single;
    }

    private function getChargeMoney(int $amount, string $currency): Money
    {
        $amount = $amount * 100;

        if ($amount >= self::MAX_INT) {
            $amount = self::MAX_INT;
        }

        return new Money($amount, $currency);
    }

    private function getCheckoutStatus(array $record): CheckoutStatus
    {
        if ($record['status'] < 1) {
            return CheckoutStatus::Pending;
        }

        return CheckoutStatus::Charged;
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
     * @return Tracking[]
     */
    private function getCheckoutTrackings(array $record): array
    {
        $v3Tracking = new Tracking();
        $v3Tracking->title = self::TRACKING_TITLE_V3;
        $v3Tracking->value = $record['id'];

        $trackings = [$v3Tracking];

        if (!empty($record['payment'])) {
            $payment = new Tracking();
            $payment->title = self::TRACKING_TITLE_PAYMENT;
            $payment->value = $record['payment'];

            $trackings[] = $payment;
        }

        if (!empty($record['transaction'])) {
            $transaction = new Tracking();
            $transaction->title = self::TRACKING_TITLE_TRANSACTION;
            $transaction->value = $record['transaction'];

            $trackings[] = $transaction;
        }

        if (!empty($record['preapproval'])) {
            $preapproval = new Tracking();
            $preapproval->title = self::TRACKING_TITLE_PREAPPROVAL;
            $preapproval->value = $record['preapproval'];

            $trackings[] = $preapproval;
        }

        return $trackings;
    }
}
