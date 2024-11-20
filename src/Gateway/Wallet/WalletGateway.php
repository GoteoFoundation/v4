<?php

namespace App\Gateway\Wallet;

use App\Entity\Accounting\Transaction;
use App\Entity\Gateway\Checkout;
use App\Entity\Money;
use App\Gateway\ChargeType;
use App\Gateway\GatewayInterface;
use App\Library\Economy\MoneyService;
use App\Service\Gateway\CheckoutService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WalletGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'wallet';
    }

    public static function getSupportedChargeTypes(): array
    {
        return [
            ChargeType::Single,
        ];
    }

    public function __construct(
        private WalletService $wallet,
        private MoneyService $money,
        private CheckoutService $checkoutService,
        private EntityManagerInterface $entityManager,
    ) {}

    public function process(Checkout $checkout): Checkout
    {
        $origin = $checkout->getOrigin();
        $available = $this->wallet->getBalance($origin);

        if ($this->money->isLess($available, $this->getChargeTotal($checkout))) {
            throw new \Exception("Can't spend more than what you have!");
        }

        $charges = $checkout->getCharges();
        foreach ($charges as $charge) {
            $transaction = new Transaction();
            $transaction->setMoney($charge->getMoney());
            $transaction->setOrigin($origin);
            $transaction->setTarget($charge->getTarget());

            $expenditure = $this->wallet->spend($transaction);

            $this->entityManager->persist($expenditure);
            $this->entityManager->flush();
        }

        $checkout = $this->checkoutService->chargeCheckout($checkout);

        return $checkout;
    }

    public function handleRedirect(Request $request): RedirectResponse
    {
        return new RedirectResponse('');
    }

    public function handleWebhook(Request $request): Response
    {
        return new Response();
    }

    private function getChargeTotal(Checkout $checkout): Money
    {
        $total = new Money(0, $checkout->getOrigin()->getCurrency());

        $charges = $checkout->getCharges();
        foreach ($charges as $charge) {
            $total = $this->money->add($charge->getMoney(), $total);
        }

        return $total;
    }
}
